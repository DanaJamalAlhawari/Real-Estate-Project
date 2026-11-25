<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\RealEstateUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyDiscoveryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_discovery_searches_both_languages_and_keeps_filters_on_the_next_page(): void
    {
        $agent = $this->account('agent');

        foreach (range(1, 7) as $number) {
            $this->listing($agent, [
                'title' => "Garden home {$number}",
                'description_ar' => 'حديقة مشمسة',
                'address_ar' => 'شارع الياسمين',
            ]);
        }

        $this->listing($agent, ['title' => 'Too expensive', 'description_ar' => 'حديقة', 'address_ar' => 'الياسمين', 'price' => 500]);
        $this->listing($agent, ['title' => 'Hidden matching home', 'description_ar' => 'حديقة', 'address_ar' => 'الياسمين', 'status' => 'hidden']);
        $this->listing($agent, ['title' => 'Only one word matches', 'description_ar' => 'حديقة']);
        $filters = ['q' => 'حديقة الياسمين', 'city' => 'عمان', 'max' => 250, 'category' => 'studio'];
        $this->actingAs($this->account('tenant'));

        $this->get(route('seeker.search', $filters))->assertOk()
            ->assertSee('7 homes')
            ->assertSee('Showing 1–6 of 7 results')
            ->assertSee('Garden home 7')
            ->assertDontSee('Garden home 1')
            ->assertDontSee('Too expensive')
            ->assertDontSee('Hidden matching home')
            ->assertDontSee('Only one word matches')
            ->assertSee(route('seeker.search', $filters + ['page' => 2]));

        $this->get(route('seeker.search', $filters + ['page' => 2]))->assertOk()
            ->assertSee('Garden home 1')
            ->assertDontSee('Garden home 7');
    }

    public function test_language_switch_preserves_the_current_search_and_localizes_existing_property_data(): void
    {
        $this->listing($this->account('agent'), [
            'title' => 'Sunny studio',
            'title_ar' => 'استوديو مشمس',
            'city' => 'Amman',
        ]);
        $filters = ['q' => 'Sunny', 'category' => 'studio'];

        $this->get(route('home', $filters + ['lang' => 'ar']))
            ->assertRedirect(route('home').'/?'.http_build_query($filters))
            ->assertSessionHas('locale', 'ar');

        $this->get(route('home', $filters))->assertOk()
            ->assertSee('lang="ar" dir="rtl"', false)
            ->assertSee('عقارات متوفرة الآن')
            ->assertSee('استوديو مشمس')
            ->assertSee('عمّان')
            ->assertDontSee('Sunny studio');

        $this->get(route('home', $filters + ['lang' => 'en']))
            ->assertRedirect(route('home').'/?'.http_build_query($filters))
            ->assertSessionHas('locale', 'en');

        $this->get(route('home', $filters))->assertOk()
            ->assertSee('lang="en" dir="ltr"', false)
            ->assertSee('Sunny studio')
            ->assertDontSee('استوديو مشمس');
    }

    public function test_property_search_keeps_owner_and_favorites_lists_scoped(): void
    {
        $agent = $this->account('agent');
        $own = $this->listing($agent, ['title' => 'First riverside home']);
        $other = $this->listing($this->account('agent', 'other-agent'), ['title' => 'Second riverside home']);
        $tenant = $this->account('tenant');
        $tenant->favoriteProperties()->attach($other);

        $this->actingAs($agent)->get(route('owner.properties', ['q' => 'riverside']))->assertOk()
            ->assertSee($own->title)
            ->assertDontSee($other->title);

        $this->actingAs($tenant)->get(route('seeker.favorites', ['q' => 'riverside']))->assertOk()
            ->assertSee($other->title)
            ->assertDontSee($own->title);

        $this->get(route('seeker.favorites', ['q' => '%']))->assertOk()
            ->assertDontSee($other->title);
    }

    public function test_an_agent_can_save_both_language_versions_of_an_existing_property(): void
    {
        $agent = $this->account('agent');
        $property = $this->listing($agent, [
            'title' => 'استوديو بجانب الحديقة',
        ]);

        $this->actingAs($agent)->put(route('properties.update', $property), [
            'title' => 'استوديو بجانب الحديقة',
            'title_ar' => 'استوديو قرب الحديقة',
            'title_en' => 'Studio by the park',
            'description' => $property->description,
            'category' => $property->category,
            'city' => $property->city,
            'price' => $property->price,
            'address' => $property->address,
        ])->assertRedirect(route('owner.properties'));

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'title' => 'استوديو بجانب الحديقة',
            'title_ar' => 'استوديو قرب الحديقة',
            'title_en' => 'Studio by the park',
            'verification_status' => 'verified',
        ]);
    }

    public function test_home_search_matches_title_and_area_and_discover_has_a_name_only_filter(): void
    {
        $agent = $this->account('agent');
        $this->listing($agent, ['title' => 'Courtyard apartment', 'area_ar' => 'اللويبدة']);
        $this->listing($agent, ['title' => 'Garden apartment', 'description' => 'Courtyard apartment']);

        $this->get(route('home', ['q' => 'Courtyard اللويبدة']))->assertOk()
            ->assertSee('Courtyard apartment')->assertDontSee('Garden apartment');

        $this->actingAs($this->account('tenant'))->get(route('seeker.search', ['name' => 'Courtyard']))->assertOk()
            ->assertSee('Courtyard apartment')->assertDontSee('Garden apartment');
    }

    public function test_discovery_uses_the_folder_photo_and_details_show_the_map_and_count_views(): void
    {
        $agent = $this->account('agent');
        $property = $this->listing($agent, [
            'title' => 'Ayla Apartment',
            'latitude' => 29.54953,
            'longitude' => 34.98715,
            'location_is_approximate' => true,
        ]);
        $folder = 'assets/properties/Data/Vactions/Vactions/vaction 2/';
        $property->images()->create(['image_url' => $folder.'default.jpg', 'is_primary' => true]);
        $property->images()->create(['image_url' => $folder.'living-room.jpg']);

        $this->actingAs($this->account('tenant'))->get(route('seeker.search'))->assertOk()
            ->assertSee('vaction%202/living-room.jpg')->assertDontSee('vaction%202/default.jpg');

        $this->get(route('seeker.property.details', ['property_id' => $property->id]))->assertOk()
            ->assertSee('vaction%202/default.jpg')->assertSee('vaction%202/living-room.jpg')
            ->assertSee('marker=29.54953%2C34.98715')->assertSee('Approximate location');

        $this->actingAs($agent)->get(route('owner.dashboard'))->assertOk()
            ->assertViewHas('dailyPropertyViews', fn (array $days) => count($days) === 14 && $days[13]['amount'] === 1)
            ->assertSee('1 view');
    }

    private function account(string $role, ?string $username = null): RealEstateUser
    {
        return RealEstateUser::forceCreate([
            'email' => ($username ?? $role).'@example.test',
            'role' => $role,
            'password' => 'test-password',
        ]);
    }

    private function listing(RealEstateUser $agent, array $attributes): Property
    {
        return Property::forceCreate($attributes + [
            'agent_id' => $agent->id,
            'description' => 'Test listing',
            'category' => 'studio',
            'city' => 'Amman',
            'address' => 'Test address',
            'price' => 200,
            'ownership_deed_url' => 'test.pdf',
            'status' => 'available',
            'verification_status' => 'verified',
        ]);
    }
}
