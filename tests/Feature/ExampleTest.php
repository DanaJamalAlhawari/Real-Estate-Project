<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_makan_pages_render(): void
    {
        $routes = [
            'home',
            'auth.login', 'auth.register', 'auth.forgot-password', 'auth.reset-password',
            'auth.verify-email', 'auth.onboarding',
            'seeker.search', 'seeker.property', 'seeker.profile', 'seeker.bookings', 'seeker.chat',
            'owner.dashboard', 'owner.properties', 'owner.property-form', 'owner.requests',
            'owner.account', 'owner.chat',
            'admin.dashboard', 'admin.users', 'admin.places', 'admin.chat',
        ];

        foreach ($routes as $route) {
            $this->get(route($route))->assertOk()->assertSee('id="makanHomes"', false);
        }
    }

    public function test_legacy_property_urls_keep_the_listing_parameter(): void
    {
        $this->get('/seeker/property.html?home=2')
            ->assertRedirect(route('seeker.property', ['home' => 2]));
    }

    public function test_landing_cards_link_to_their_own_property(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('seeker.property', ['home' => 2]))
            ->assertSee('Shared apartment');
    }
}
