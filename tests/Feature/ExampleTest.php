<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\RealEstateUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_makan_pages_render_for_their_roles(): void
    {
        $this->withoutVite();
        foreach (['home', 'auth.login', 'auth.register', 'auth.forgot-password', 'auth.reset-password', 'auth.verify-email', 'auth.onboarding'] as $route) {
            $this->get(route($route))->assertOk();
        }

        $rolePages = [
            'tenant' => ['seeker.search', 'seeker.profile', 'seeker.bookings', 'seeker.chat', 'seeker.favorites'],
            'agent' => ['owner.dashboard', 'owner.properties', 'owner.property-form', 'owner.requests', 'owner.account', 'owner.chat'],
            'admin' => ['admin.dashboard', 'admin.users', 'admin.verifications', 'admin.chat', 'admin.account'],
        ];

        foreach ($rolePages as $role => $routes) {
            $this->actingAs($this->account($role));
            foreach ($routes as $route) {
                $this->get(route($route))->assertOk();
            }
        }
    }

    public function test_legacy_property_urls_keep_the_listing_parameter(): void
    {
        $this->actingAs($this->account('tenant'))->get('/seeker/property.html?home=2')
            ->assertRedirect(route('seeker.property', ['home' => 2]));
    }

    public function test_discover_links_to_a_favorites_page_that_shows_only_saved_homes(): void
    {
        $this->withoutVite();
        $agent = $this->account('agent');
        $tenant = $this->account('tenant');
        $listing = [
            'agent_id' => $agent->id, 'description' => 'Test listing', 'category' => 'studio',
            'city' => 'Amman', 'address' => 'Test address', 'ownership_deed_url' => 'test.pdf',
            'status' => 'available', 'verification_status' => 'verified','area_sqm' => 45,
        ];
        $saved = Property::forceCreate(['title' => 'Saved studio'] + $listing);
        $unsaved = Property::forceCreate(['title' => 'Unsaved studio'] + $listing);
        $tenant->favoriteProperties()->attach($saved);

        $discover = route('seeker.search');
        $favorites = route('seeker.favorites');
        $this->actingAs($tenant)->get($discover)->assertOk()
            ->assertSee('href="'.$favorites.'"', false)
            ->assertSee($saved->title)->assertSee($unsaved->title)
            ->assertDontSee('id="all-tab"', false)->assertDontSee('id="favorites-tab"', false);

        $this->get($favorites)->assertOk()
            ->assertSee('aria-current="page"', false)
            ->assertSee($saved->title)->assertDontSee($unsaved->title)
            ->assertSee('Remove from favorites');
        $this->from($favorites)->delete(route('seeker.favorites.destroy', $saved))->assertRedirect($favorites);
        $this->get($favorites)->assertOk()->assertSee('No favorites yet');
    }

    public function test_landing_cards_link_to_their_own_verified_property(): void
    {
        $agent = $this->account('agent');
        $property = Property::forceCreate([
            'agent_id' => $agent->id,
            'title' => 'Example verified studio',
            'description' => 'Test listing',
            'category' => 'studio',
            'area_sqm' => 45,
            'city' => 'Amman',
            'address' => 'Test address',
            'ownership_deed_url' => 'test.pdf',
            'verification_status' => 'verified',
        ]);

        $this->get(route('home'))->assertOk()
            ->assertSee(route('seeker.property.details', ['property_id' => $property->id]))
            ->assertSee($property->title);

        config(['app.home_excluded_property_id' => $property->id]);

        $this->get(route('home', ['category' => 'studio']))->assertOk()->assertDontSee($property->title);
        $this->withoutVite()->actingAs($this->account('tenant'))
            ->get(route('seeker.search'))->assertOk()->assertSee($property->title);
    }

    private function account(string $role): RealEstateUser
    {
        return RealEstateUser::forceCreate([
            'email' => "$role@example.test",
            'role' => $role,
            'password' => 'test-password',
        ]);
    }
}
