<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\RealEstateUser;
use Illuminate\Database\Seeder;

class FavoriteTestSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = RealEstateUser::updateOrCreate(
            ['email' => 'tenant@example.com'],
            ['role' => 'tenant', 'password' => 'password']
        );

        $tenant->tenantProfile()->updateOrCreate([], [
            'first_name' => 'Test',
            'last_name' => 'Tenant',
            'phone' => '0790000001',
        ]);

        $agent = RealEstateUser::updateOrCreate(
            ['email' => 'agent@example.com'],
            ['role' => 'agent', 'password' => 'password']
        );

        $agentProfile = $agent->agentProfile()->updateOrCreate([], [
            'first_name' => 'Test',
            'last_name' => 'Agent',
            'phone' => '0790000002',
            'agency_name' => 'Demo Agency',
        ]);
        $agentProfile->forceFill(['verification_status' => 'verified'])->save();

        $properties = [
            [
                'title' => 'شقة للتجربة في عمّان',
                'title_en' => 'Demo apartment in Amman',
                'category' => 'apartment',
                'price' => 350,
                'area_sqm' => 120,
                'address' => 'شارع الجامعة، عمّان',
                'address_en' => 'University Street, Amman',
                'bedrooms' => 2,
                'bathrooms' => 1,
            ],
            [
                'title' => 'استوديو للتجربة في عمّان',
                'title_en' => 'Demo studio in Amman',
                'category' => 'studio',
                'price' => 220,
                'area_sqm' => 45,
                'address' => 'وسط صويلح، عمّان',
                'address_en' => 'Central Sweileh, Amman',
                'bedrooms' => 1,
                'bathrooms' => 1,
            ],
        ];

        $savedProperties = [];

        foreach ($properties as $data) {
            $property = Property::firstOrNew([
                'agent_id' => $agent->id,
                'title' => $data['title'],
            ]);

            $property->forceFill(array_merge($data, [
                'agent_id' => $agent->id,
                'description' => 'عقار تجريبي لاختبار خاصية المفضلة.',
                'description_en' => 'Demo listing for testing favorites.',
                'city' => 'عمّان',
                'city_en' => 'Amman',
                'status' => 'available',
                'ownership_deed_url' => 'demo/deed.pdf',
                'verification_status' => 'verified',
                'verified_at' => now(),
            ]))->save();

            if ($data['category'] === 'studio') {
                $photos = [
                    'assets/images/studio.jpg',
                    'assets/images/interior.jpg',
                ];
            } else {
                $photos = [
                    'assets/images/apartment.jpg',
                    'assets/images/living.jpg',
                ];
            }

            foreach ($photos as $index => $photo) {
                $image = $property->images()->firstOrNew([
                    'image_url' => $photo,
                ]);

                $image->image_url = $photo;
                $image->is_primary = ($index === 0);

                $image->save();
            }

            $savedProperties[] = $property;
        }

        $tenant->favoriteProperties()->syncWithoutDetaching([
            $savedProperties[0]->id,

        ]);
    }
}
