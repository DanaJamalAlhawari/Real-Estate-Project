<?php

namespace Tests\Feature;

use App\Events\BookingActivity;
use App\Models\Booking;
use App\Models\Property;
use App\Models\RealEstateUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BookingCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->travelTo(now()->setDate(2027, 1, 15));
    }

    public function test_accepted_booking_notifies_its_tenant_and_can_be_paid_once(): void
    {
        Event::fake([BookingActivity::class]);
        Storage::fake('local')->put('verifications/deed.pdf', 'Test deed');
        $agent = $this->account('agent');
        $tenant = $this->account('tenant');
        $otherTenant = $this->account('tenant');
        $property = $this->listing($agent, [
            'category' => 'studio',
            'verification_status' => 'pending',
        ]);
        $property->images()->create([
            'image_url' => 'properties/test-cover.jpg',
            'is_primary' => true,
        ]);

        $review = route('admin.verifications', ['property' => $property->id]);
        $approval = route('admin.verifications.update', $property);
        $this->actingAs($tenant)->patch($approval, ['decision' => 'approve'])->assertForbidden();
        $this->actingAs($this->account('admin'))->get($review)->assertOk()
            ->assertSee('Commission rate (%)')->assertSee('value="3"', false);
        $this->from($review)->patch($approval, ['decision' => 'approve', 'commission_rate' => '101'])
            ->assertSessionHasErrors('commission_rate');
        $this->patch($approval, ['decision' => 'approve', 'commission_rate' => '6.50'])
            ->assertRedirect(route('admin.verifications'));
        $this->assertEquals(6.5, $property->fresh()->approved_commission_rate);

        $this->actingAs($tenant)->post(route('seeker.bookings.store', $property), [
            'moveIn' => '2027-02-01',
            'moveOut' => '2027-03-01',
            'total_price' => 1,
        ])->assertRedirect(route('seeker.bookings'));
        $booking = $tenant->bookings()->firstOrFail();
        $this->assertSame('310.00', $booking->total_price);
        $checkout = route('payments.mock.show', $booking);
        $pay = route('payments.mock.pay', $booking);
        $this->get($checkout)->assertForbidden();
        $this->get(route('seeker.bookings'))->assertDontSee($checkout);

        $this->actingAs($agent)->patch(route('owner.requests.accept', $booking))
            ->assertRedirect(route('owner.requests'))->assertSessionHas('success');
        $this->assertEquals(6.5, $booking->fresh()->approved_commission_rate);
        Event::assertDispatched(
            BookingActivity::class,
            fn($event) => $event->broadcastOn()->name === 'private-inbox.' . $tenant->id
                && str_contains($event->broadcastWith()['preview'], 'You can now pay.')
                && $event->broadcastWith()['url'] === route('payments.mock.show', $booking, false)
        );

        $this->put(route('properties.update', $property), [
            'title' => $property->title,
            'description' => $property->description,
            'category' => 'apartment',
            'price' => 620,
            'city' => $property->city,
            'address' => $property->address,
        ])->assertRedirect(route('owner.properties'));
        $this->assertSame('pending', $property->fresh()->verification_status);
        $this->assertNull($property->fresh()->approved_commission_rate);

        $this->actingAs($otherTenant)->get($checkout)->assertNotFound();
        $this->post($pay, $this->demoCard())->assertNotFound();
        $this->actingAs($tenant)->get(route('seeker.bookings'))->assertSee($checkout)
            ->assertSee('You can now pay.');
        $this->get($checkout)->assertOk()->assertSee('Mock payment')->assertSee('310.00 JOD')
            ->assertSee('name="card_number"', false)->assertSee('name="card_expiry"', false)->assertSee('name="card_cvv"', false);
        $this->from($checkout)->post($pay, ['payment_method' => 'cash', 'outcome' => 'success'])
            ->assertRedirect($checkout)->assertSessionHasErrors(['card_number', 'card_expiry', 'card_cvv']);
        $this->from($checkout)->post($pay, [
            'card_number' => '4000 0000 0000 0002',
            'card_expiry' => '01/20',
            'card_cvv' => '999',
        ])->assertRedirect($checkout)->assertSessionHasErrors(['card_number', 'card_expiry', 'card_cvv'])
            ->assertSessionMissing('_old_input.card_number')
            ->assertSessionMissing('_old_input.card_expiry')
            ->assertSessionMissing('_old_input.card_cvv');
        $this->withCookie(config('session.cookie'), session()->getId())
            ->get($checkout)->assertSee('Enter the demo expiration date 12/30.')->assertDontSee('4000 0000 0000 0002');
        $this->assertDatabaseCount('payments', 0);
        $this->assertDatabaseCount('commissions', 0);
        $form = $this->demoCard() + ['amount' => 1, 'commission_rate' => 0];
        $this->post($pay, $form)->assertRedirect($checkout);
        $this->post($pay, $form)->assertRedirect($checkout);
        $payment = $booking->payments()->firstOrFail();
        $this->from($checkout)->post(route('payments.mock.confirm', $payment))->assertSessionHasErrors('card_number');
        $this->post(route('payments.mock.confirm', $payment), $this->demoCard())->assertRedirect($checkout);
        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseCount('commissions', 1);
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'booking_id' => $booking->id,
            'tenant_id' => $tenant->id,
            'amount' => 310,
            'status' => 'successful',
            'payment_method' => 'credit_card',
        ]);
        $this->assertDatabaseHas('commissions', [
            'booking_id' => $booking->id,
            'payment_id' => $payment->id,
            'agent_id' => $agent->id,
            'commission_rate' => 6.5,
            'commission_amount' => 20.15,
            'agent_net_amount' => 289.85,
            'status' => 'pending',
            'paid_at' => null,
        ]);
        $this->get($checkout)->assertSee('Payment recorded successfully')
            ->assertSee('20.15 JOD')->assertSee('289.85 JOD')->assertDontSee('name="card_number"', false);
        $this->actingAs($agent)->get(route('owner.earnings'))->assertOk()->assertSee('289.85');
        $this->actingAs(RealEstateUser::where('role', 'admin')->firstOrFail())
            ->get(route('admin.dashboard'))->assertOk()
            ->assertViewHas('monthlyCommissionRevenue', fn(array $months) => (float) $months[5]['amount'] === 20.15);
    }

    public function test_reserved_periods_block_overlapping_requests_and_acceptance_but_allow_checkout_day(): void
    {
        Event::fake([BookingActivity::class]);
        $agent = $this->account('agent');
        $firstTenant = $this->account('tenant');
        $secondTenant = $this->account('tenant');
        $property = $this->listing($agent);
        $request = route('seeker.bookings.store', $property);
        $details = route('seeker.property.details', ['property_id' => $property->id]);

        $this->actingAs($firstTenant)->post($request, ['moveIn' => '2027-02-01', 'moveOut' => '2027-03-01'])
            ->assertRedirect(route('seeker.bookings'));
        $this->actingAs($secondTenant)->post($request, ['moveIn' => '2027-01-30', 'moveOut' => '2027-02-02'])
            ->assertRedirect(route('seeker.bookings'));
        $first = $firstTenant->bookings()->firstOrFail();
        $second = $secondTenant->bookings()->firstOrFail();
        $this->actingAs($this->account('agent'))->patch(route('owner.requests.accept', $first))->assertNotFound();
        $this->actingAs($agent)->patch(route('owner.requests.accept', $first))->assertSessionHas('success');
        $this->patch(route('owner.requests.accept', $second))->assertSessionHas('error');
        $this->assertSame('pending', $second->fresh()->status);
        $this->assertEquals(10, $first->fresh()->approved_commission_rate);

        $this->actingAs($secondTenant)->get($details)->assertOk()->assertSee('End date (checkout)')
            ->assertSee('booking-calendar.js')->assertSee('2027-02-01')->assertSee('2027-03-01')
            ->assertDontSee('name="duration"', false);
        $this->from($details)->post($request, ['moveIn' => '2027-02-27', 'moveOut' => '2027-03-02'])
            ->assertRedirect($details)->assertSessionHasErrors('moveIn');
        $this->post($request, ['moveIn' => '2027-03-01', 'moveOut' => '2027-03-03'])
            ->assertRedirect(route('seeker.bookings'));
        $adjacent = $secondTenant->bookings()->latest('id')->firstOrFail();
        $this->assertSame('20.00', $adjacent->total_price);
        $this->actingAs($agent)->patch(route('owner.requests.accept', $adjacent))->assertSessionHas('success');
        $this->assertSame('confirmed', $adjacent->fresh()->status);
    }

    public function test_registration_saves_role_specific_fields_and_ignores_removed_fields(): void
    {
        $register = route('auth.register.store');
        $this->get(route('auth.register'))->assertOk()->assertSee('id="roleOwner"', false)
            ->assertSee('name="agency_name"', false)->assertDontSee('name="license_number"', false)
            ->assertDontSee('name="emergency_contact"', false)->assertDontSee('name="student_id"', false);

        $common = [
            'first_name' => 'Test',
            'last_name' => 'Person',
            'agency_name' => 'Test Agency',
            'license_number' => 'ignored',
            'emergency_contact' => 'ignored',
            'student_id' => 'ignored',
            'password' => 'test-password',
            'password_confirmation' => 'test-password',
            'terms' => '1',
        ];
        $this->post($register, $common + [
            'role' => 'tenant',
            'email' => 'tenant@example.test',
            'phone' => '+962711111111',
        ])->assertRedirect(route('seeker.search'));
        $this->assertDatabaseHas('tenant_profiles', [
            'first_name' => 'Test',
            'last_name' => 'Person',
            'phone' => '+962711111111',
            'emergency_contact' => null,
            'student_id' => null,
        ]);
        $this->post(route('logout'));
        $this->post($register, $common + [
            'role' => 'agent',
            'owner_type' => 'agency',
            'email' => 'agent@example.test',
            'phone' => '+962722222222',
        ])->assertRedirect(route('owner.dashboard'));
        $this->assertDatabaseHas('agent_profiles', [
            'phone' => '+962722222222',
            'agency_name' => 'Test Agency',
            'license_number' => null,
        ]);
    }

    public function test_assistant_uses_verified_locations_and_only_the_current_tenants_bookings(): void
    {
        $agent = $this->account('agent');
        $tenant = $this->account('tenant');
        $otherTenant = $this->account('tenant');
        $visible = $this->listing($agent, ['title' => 'Visible studio', 'category' => 'studio', 'city' => 'Irbid']);
        $private = $this->listing($agent, ['title' => 'Private booking title', 'verification_status' => 'pending', 'city' => 'Hidden city']);
        foreach ([[$tenant, $visible], [$otherTenant, $private]] as [$customer, $property]) {
            Booking::forceCreate([
                'tenant_id' => $customer->id,
                'property_id' => $property->id,
                'status' => 'pending',
                'start_date' => '2027-02-05',
                'end_date' => '2027-02-06',
                'duration' => 1,
                'total_price' => 10,
            ]);
        }
        $this->actingAs($tenant)->get(route('seeker.search'))->assertOk()
            ->assertSee('Makan Assistant')->assertSee('Irbid')->assertSee('Visible studio')
            ->assertDontSee('Hidden city')->assertDontSee('Private booking title');
        $this->get(route('seeker.search', ['city' => 'Irbid', 'category' => 'studio']))
            ->assertOk()->assertSee('Visible studio');
        $this->get(route('seeker.search', ['city' => 'Other city']))->assertOk()->assertSee('0 homes');

        $url = rtrim(config('services.ollama.url'), '/') . '/api/chat';
        Http::preventStrayRequests();
        Http::fake([$url => Http::sequence()
            ->push(['message' => ['content' => '{"intent":"bookings","reply":"Untrusted model text"}']])
            ->push(['message' => ['content' => '{"intent":"listings","search":"Irbid"}']])
            ->push(['error' => 'model not found'], 404)]);

        $this->postJson(route('assistant.chat'), ['message' => 'How is my booking?'])
            ->assertOk()->assertExactJson(['reply' => 'Visible studio — Pending (2027-02-05 to 2027-02-06)']);
        $this->postJson(route('assistant.chat'), ['message' => 'Find listings in Irbid'])
            ->assertOk()->assertSee('Visible studio')->assertDontSee('Private booking title');
        $this->postJson(route('assistant.chat'), ['message' => 'Find listings in Irbid'])
            ->assertServiceUnavailable()
            ->assertJsonFragment(['message' => 'Ollama could not find ' . config('services.ollama.model') . '. Install it with ollama pull ' . config('services.ollama.model') . ' and check OLLAMA_MODEL.']);
        Http::assertSent(function ($request) use ($url) {
            return $request->url() === $url && $request['model'] === config('services.ollama.model')
                && $request['format'] === 'json'
                && $request['messages'][1]['content'] === 'How is my booking?'
                && ! str_contains($request->body(), 'Visible studio')
                && ! str_contains($request->body(), 'Private booking title');
        });
    }

    public function test_only_the_tenant_can_cancel_pending_or_accepted_unpaid_bookings(): void
    {
        Event::fake([BookingActivity::class]);
        $agent = $this->account('agent');
        $tenant = $this->account('tenant');
        $otherTenant = $this->account('tenant');
        $property = $this->listing($agent);
        $pending = $this->booking($tenant, $property, 'pending');
        $unpaid = $this->booking($tenant, $property, 'confirmed');
        $paid = $this->booking($tenant, $property, 'confirmed');
        $paid->payments()->create([
            'tenant_id' => $tenant->id,
            'amount' => 20,
            'payment_method' => 'credit_card',
            'status' => 'successful',
        ]);

        $cancel = fn(Booking $booking) => route('seeker.bookings.cancel', $booking);
        $this->actingAs($otherTenant)->patch($cancel($pending))->assertNotFound();
        $this->actingAs($tenant)->get(route('seeker.bookings'))->assertOk()->assertSee($cancel($pending))
            ->assertSee($cancel($unpaid))->assertDontSee($cancel($paid));
        $this->patch($cancel($pending))->assertRedirect(route('seeker.bookings'))->assertSessionHas('success');
        $this->patch($cancel($unpaid))->assertSessionHas('success');
        $this->patch($cancel($paid))->assertSessionHas('error');

        $this->assertSame('cancelled', $pending->fresh()->status);
        $this->assertSame('cancelled', $unpaid->fresh()->status);
        $this->assertSame('confirmed', $paid->fresh()->status);
        $this->get(route('payments.mock.show', $unpaid))->assertForbidden();
        Event::assertDispatchedTimes(BookingActivity::class, 2);
    }

    public function test_cart_pays_approved_bookings_once_and_rolls_back_when_an_item_changes(): void
    {
        $agent = $this->account('agent');
        $tenant = $this->account('tenant');
        $otherTenant = $this->account('tenant');
        $property = $this->listing($agent);
        $first = $this->booking($tenant, $property, 'confirmed');
        $second = $this->booking($tenant, $property, 'confirmed');
        $cancelled = $this->booking($tenant, $property, 'cancelled');
        $private = $this->booking($otherTenant, $property, 'confirmed');
        $cart = route('payments.mock.cart');
        $pay = route('payments.mock.cart.pay');

        $this->actingAs($tenant)->get($cart)->assertOk()->assertSee('40.00 JOD')
            ->assertSee('name="card_number"', false)->assertSee('name="card_expiry"', false)->assertSee('name="card_cvv"', false);
        $this->from($cart)->post($pay, ['bookings' => [$first->id, $second->id]])
            ->assertRedirect($cart)->assertSessionHasErrors(['card_number', 'card_expiry', 'card_cvv']);
        $this->post($pay, $this->demoCard() + ['bookings' => [$first->id, $private->id]])->assertNotFound();
        $this->from($cart)->post($pay, $this->demoCard() + ['bookings' => [$first->id, $cancelled->id]])
            ->assertRedirect($cart)->assertSessionHasErrors('payment');
        $this->assertDatabaseCount('payments', 0);
        $this->assertDatabaseCount('commissions', 0);

        $payload = $this->demoCard() + ['bookings' => [$first->id, $second->id], 'amount' => 1];
        $this->post($pay, $payload)->assertRedirect(route('seeker.bookings'));
        $this->post($pay, $payload)->assertRedirect(route('seeker.bookings'));

        $this->assertDatabaseCount('payments', 2);
        $this->assertDatabaseCount('commissions', 2);
        foreach ([$first, $second] as $booking) {
            $this->assertDatabaseHas('payments', ['booking_id' => $booking->id, 'amount' => 20, 'status' => 'successful']);
        }
        $this->get($cart)->assertOk()->assertSee('Your cart is empty');
        $this->get(route('payments.mock.show', $first))->assertOk()->assertSee('Payment recorded successfully');
    }

    public function test_agent_can_delete_a_listing_with_bookings_and_preserve_checkout_history(): void
    {
        $agent = $this->account('agent');
        $tenant = $this->account('tenant');
        $property = $this->listing($agent);
        $pending = $this->booking($tenant, $property, 'pending');
        $approved = $this->booking($tenant, $property, 'confirmed');
        $delete = route('properties.destroy', $property);

        $this->actingAs($this->account('agent'))->delete($delete)->assertNotFound();
        $this->actingAs($agent)->delete($delete)->assertRedirect(route('owner.properties'))->assertSessionHas('success');

        $this->assertSoftDeleted($property);
        $this->assertSame('cancelled', $pending->fresh()->status);
        $this->assertSame('confirmed', $approved->fresh()->status);
        $this->get(route('owner.properties'))->assertOk()->assertDontSee($property->title);
        $this->get(route('owner.requests'))->assertOk()->assertSee($property->title);
        $this->actingAs($tenant)->get(route('seeker.property.details', ['property_id' => $property->id]))->assertNotFound();
        $this->get(route('payments.mock.show', $approved))->assertOk()->assertSee($property->title);
        $this->post(route('payments.mock.pay', $approved), $this->demoCard())
            ->assertRedirect(route('payments.mock.show', $approved));
    }

    private function demoCard(): array
    {
        return ['card_number' => '4242 4242 4242 4242', 'card_expiry' => '12/30', 'card_cvv' => '123'];
    }

    private function booking(RealEstateUser $tenant, Property $property, string $status): Booking
    {
        return Booking::forceCreate([
            'tenant_id' => $tenant->id,
            'property_id' => $property->id,
            'status' => $status,
            'start_date' => '2027-02-05',
            'end_date' => '2027-02-07',
            'duration' => 2,
            'total_price' => 20,
        ]);
    }

    private function account(string $role): RealEstateUser
    {
        return RealEstateUser::forceCreate([
            'role' => $role,
            'email' => fake()->unique()->safeEmail(),
            'password' => 'test-password',
        ]);
    }

    private function listing(RealEstateUser $agent, array $attributes = []): Property
    {
        return Property::forceCreate($attributes + [
            'agent_id' => $agent->id,
            'title' => 'Test home',
            'description' => 'Test property',
            'category' => 'apartment',
            'price' => 310,
            'city' => 'Amman',
            'address' => 'Test address',
            'status' => 'available',
            'verification_status' => 'verified',
            'ownership_deed_url' => 'verifications/deed.pdf',
        ]);
    }
}
