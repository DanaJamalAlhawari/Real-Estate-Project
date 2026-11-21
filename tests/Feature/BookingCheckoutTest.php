<?php

namespace Tests\Feature;

use App\Events\BookingActivity;
use App\Models\Booking;
use App\Models\Property;
use App\Models\RealEstateUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
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
        Event::assertDispatched(BookingActivity::class, fn ($event) =>
            $event->broadcastOn()->name === 'private-inbox.'.$tenant->id
            && str_contains($event->broadcastWith()['preview'], 'You can now pay.')
            && $event->broadcastWith()['url'] === route('payments.mock.show', $booking, false)
        );

        $this->put(route('properties.update', $property), [
            'title' => $property->title, 'description' => $property->description,
            'category' => 'apartment', 'price' => 620, 'city' => $property->city,
            'address' => $property->address,
        ])->assertRedirect(route('owner.properties'));
        $this->assertSame('pending', $property->fresh()->verification_status);
        $this->assertNull($property->fresh()->approved_commission_rate);

        $this->actingAs($otherTenant)->get($checkout)->assertNotFound();
        $this->post($pay, ['payment_method' => 'cash', 'outcome' => 'success'])->assertNotFound();
        $this->actingAs($tenant)->get(route('seeker.bookings'))->assertSee($checkout)
            ->assertSee('You can now pay.');
        $this->get($checkout)->assertOk()->assertSee('Mock payment')->assertSee('310.00 JOD');
        $this->from($checkout)->post($pay, ['payment_method' => 'cash', 'outcome' => 'failure'])
            ->assertRedirect($checkout)->assertSessionHasErrors(['payment' => 'Mock payment failed. You can try again.']);
        $this->withCookie(config('session.cookie'), session()->getId())
            ->get($checkout)->assertSee('Mock payment failed');
        $this->assertDatabaseCount('payments', 0);
        $this->assertDatabaseCount('commissions', 0);
        $this->from($checkout)->post($pay, ['payment_method' => 'invalid', 'outcome' => 'success'])
            ->assertSessionHasErrors('payment_method');

        $form = ['payment_method' => 'cash', 'outcome' => 'success', 'amount' => 1, 'commission_rate' => 0];
        $this->post($pay, $form)->assertRedirect($checkout);
        $this->post($pay, $form)->assertRedirect($checkout);
        $payment = $booking->payments()->firstOrFail();
        $this->post(route('payments.mock.confirm', $payment))->assertRedirect($checkout);
        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseCount('commissions', 1);
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id, 'booking_id' => $booking->id, 'tenant_id' => $tenant->id,
            'amount' => 310, 'status' => 'successful', 'payment_method' => 'cash',
        ]);
        $this->assertDatabaseHas('commissions', [
            'booking_id' => $booking->id, 'payment_id' => $payment->id, 'agent_id' => $agent->id,
            'commission_rate' => 6.5, 'commission_amount' => 20.15, 'agent_net_amount' => 289.85,
            'status' => 'pending', 'paid_at' => null,
        ]);
        $this->get($checkout)->assertSee('Payment recorded successfully')
            ->assertSee('20.15 JOD')->assertSee('289.85 JOD');
        $this->actingAs($agent)->get(route('owner.earnings'))->assertOk()->assertSee('289.85');
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
            'first_name' => 'Test', 'last_name' => 'Person',
            'agency_name' => 'Test Agency', 'license_number' => 'ignored',
            'emergency_contact' => 'ignored', 'student_id' => 'ignored',
            'password' => 'test-password', 'password_confirmation' => 'test-password', 'terms' => '1',
        ];
        $this->post($register, $common + [
            'role' => 'tenant', 'email' => 'tenant@example.test', 'phone' => '+962711111111',
        ])->assertRedirect(route('seeker.search'));
        $this->assertDatabaseHas('tenant_profiles', [
            'first_name' => 'Test', 'last_name' => 'Person', 'phone' => '+962711111111',
            'emergency_contact' => null, 'student_id' => null,
        ]);
        $this->post(route('logout'));
        $this->post($register, $common + [
            'role' => 'agent', 'owner_type' => 'agency', 'email' => 'agent@example.test', 'phone' => '+962722222222',
        ])->assertRedirect(route('owner.dashboard'));
        $this->assertDatabaseHas('agent_profiles', [
            'phone' => '+962722222222', 'agency_name' => 'Test Agency', 'license_number' => null,
        ]);
    }

    public function test_assistant_offers_real_city_and_type_options_and_keeps_bookings_private(): void
    {
        $agent = $this->account('agent');
        $tenant = $this->account('tenant');
        $otherTenant = $this->account('tenant');
        $visible = $this->listing($agent, ['title' => 'Visible studio', 'category' => 'studio', 'city' => 'Irbid']);
        $private = $this->listing($agent, ['title' => 'Private booking title', 'verification_status' => 'pending', 'city' => 'Hidden city']);
        foreach ([[$tenant, $visible], [$otherTenant, $private]] as [$customer, $property]) {
            Booking::forceCreate([
                'tenant_id' => $customer->id, 'property_id' => $property->id, 'status' => 'pending',
                'start_date' => '2027-02-05', 'end_date' => '2027-02-06', 'duration' => 1, 'total_price' => 10,
            ]);
        }
        $this->actingAs($tenant)->get(route('seeker.search'))->assertOk()
            ->assertSee('Makan Assistant')->assertSee('Irbid')->assertSee('Visible studio')
            ->assertDontSee('Hidden city')->assertDontSee('Private booking title');
        $this->get(route('seeker.search', ['city' => 'Irbid', 'category' => 'studio']))
            ->assertOk()->assertSee('Visible studio');
        $this->get(route('seeker.search', ['city' => 'Other city']))->assertOk()->assertSee('0 homes');
    }

    private function account(string $role): RealEstateUser
    {
        return RealEstateUser::forceCreate([
            'role' => $role, 'email' => fake()->unique()->safeEmail(), 'password' => 'test-password',
        ]);
    }

    private function listing(RealEstateUser $agent, array $attributes = []): Property
    {
        return Property::forceCreate($attributes + [
            'agent_id' => $agent->id, 'title' => 'Test home', 'description' => 'Test property',
            'category' => 'apartment', 'price' => 310, 'city' => 'Amman', 'address' => 'Test address',
            'status' => 'available', 'verification_status' => 'verified',
            'ownership_deed_url' => 'verifications/deed.pdf',
        ]);
    }
}
