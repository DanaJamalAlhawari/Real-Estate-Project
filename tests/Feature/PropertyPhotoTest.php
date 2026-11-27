<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\RealEstateUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Illuminate\Filesystem\FilesystemAdapter;

class PropertyPhotoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        Storage::fake('public');
        Storage::fake('local');
    }

    private function agent(): RealEstateUser
    {
        return RealEstateUser::forceCreate([
            'email' => fake()->unique()->safeEmail(),
            'role' => 'agent',
            'password' => 'test-password',
        ]);
    }

    private function payload(): array
    {
        return [
            'title' => 'Test apartment',
            'description' => 'Test property description',
            'category' => 'apartment',
            'price' => 350,
            'city' => 'Amman',
            'area_sqm' => 120,
            'address' => 'Test street',
            'latitude' => 31.98,
            'longitude' => 35.85,
            'location_is_approximate' => true,
            'bedrooms' => 3,
            'bathrooms' => 2,
            'action' => 'publish',
            'document' => UploadedFile::fake()
                ->create('deed.pdf', 100, 'application/pdf'),
        ];
    }

    private function photo(string $name = 'photo.jpg'): UploadedFile
    {
        return UploadedFile::fake()->image($name, 800, 600);
    }

    private function photos(int $count): array
    {
        return array_map(
            fn($index) => $this->photo("photo-{$index}.jpg"),
            range(1, $count)
        );
    }

    private function listing(RealEstateUser $agent): Property
    {
        return Property::forceCreate([
            'agent_id' => $agent->id,
            'title' => 'Existing apartment',
            'description' => 'Existing property',
            'category' => 'apartment',
            'price' => 350,
            'area_sqm' => 120,
            'city' => 'Amman',
            'address' => 'Test street',
            'status' => 'available',
            'verification_status' => 'verified',
            'ownership_deed_url' => 'verifications/deed.pdf',
        ]);
    }

    private function existingPhoto(
        Property $property,
        string $name,
        bool $primary = false
    ) {
        $path = 'properties/' . $property->id . '/' . $name;

        $this->disk('public')->put($path, 'Test image');

        return $property->images()->create([
            'image_url' => $path,
            'is_primary' => $primary,
        ]);
    }

    private function updatePayload(Property $property): array
    {
        return [
            'title' => $property->title,
            'description' => $property->description,
            'category' => $property->category,
            'price' => $property->price,
            'city' => $property->city,
            'address' => $property->address,
            'area_sqm' => $property->area_sqm,
        ];
    }

    public function test_property_requires_a_default_photo(): void
    {
        $this->actingAs($this->agent())
            ->post(route('properties.store'), $this->payload())
            ->assertSessionHasErrors('default_photo');

        $this->assertDatabaseCount('properties', 0);
        $this->assertDatabaseCount('property_images', 0);
    }

    public function test_property_can_be_created_with_only_a_default_photo(): void
    {
        $agent = $this->agent();

        $response = $this->actingAs($agent)
            ->post(route('properties.store'), $this->payload() + [
                'default_photo' => $this->photo('my-home.jpg'),
            ]);

        $property = $agent->properties()->firstOrFail();

        $response->assertRedirect(
            route('owner.properties.edit', $property)
        );

        $this->assertSame(1, $property->images()->count());

        $cover = $property->coverImage();

        $this->assertNotNull($cover);
        $this->assertTrue($cover->is_primary);

        $this->disk('public')->assertExists($cover->image_url);
        $this->disk('local')->assertExists(
            $property->ownership_deed_url
        );
    }

    public function test_property_accepts_one_cover_and_nine_additional_photos(): void
    {
        $agent = $this->agent();

        $response = $this->actingAs($agent)
            ->post(route('properties.store'), $this->payload() + [
                'default_photo' => $this->photo('cover.jpg'),
                'photos' => $this->photos(9),
            ]);

        $property = $agent->properties()->firstOrFail();

        $response->assertRedirect(
            route('owner.properties.edit', $property)
        );

        $this->assertSame(10, $property->images()->count());

        $this->assertSame(
            1,
            $property->images()->where('is_primary', true)->count()
        );

        foreach ($property->images as $image) {
            $this->disk('public')->assertExists($image->image_url);
        }
    }

    public function test_property_rejects_more_than_ten_photos(): void
    {
        $this->actingAs($this->agent())
            ->post(route('properties.store'), $this->payload() + [
                'default_photo' => $this->photo(),
                'photos' => $this->photos(10),
            ])
            ->assertSessionHasErrors('photos');

        $this->assertDatabaseCount('properties', 0);
        $this->assertDatabaseCount('property_images', 0);
    }

    public function test_default_photo_must_be_an_image(): void
    {
        $this->actingAs($this->agent())
            ->post(route('properties.store'), $this->payload() + [
                'default_photo' => UploadedFile::fake()
                    ->create('invalid.pdf', 100, 'application/pdf'),
            ])
            ->assertSessionHasErrors('default_photo');

        $this->assertDatabaseCount('properties', 0);
    }

    public function test_default_photo_cannot_exceed_five_megabytes(): void
    {
        $this->actingAs($this->agent())
            ->post(route('properties.store'), $this->payload() + [
                'default_photo' => $this->photo()->size(5121),
            ])
            ->assertSessionHasErrors('default_photo');

        $this->assertDatabaseCount('properties', 0);
    }

    public function test_additional_photos_must_be_valid_images(): void
    {
        $this->actingAs($this->agent())
            ->post(route('properties.store'), $this->payload() + [
                'default_photo' => $this->photo(),
                'photos' => [
                    UploadedFile::fake()
                        ->create('invalid.pdf', 100, 'application/pdf'),
                ],
            ])
            ->assertSessionHasErrors('photos.0');

        $this->assertDatabaseCount('properties', 0);
    }

    public function test_agent_can_update_property_without_replacing_cover(): void
    {
        $agent = $this->agent();
        $property = $this->listing($agent);

        $cover = $this->existingPhoto($property, 'cover.jpg', true);

        $this->actingAs($agent)
            ->put(
                route('properties.update', $property),
                $this->updatePayload($property)
            )
            ->assertRedirect(route('owner.properties'));

        $this->assertTrue($cover->fresh()->is_primary);
        $this->assertSame(1, $property->images()->count());

        $this->disk('public')->assertExists($cover->image_url);
    }

    public function test_agent_cannot_delete_cover_without_replacement(): void
    {
        $agent = $this->agent();
        $property = $this->listing($agent);

        $cover = $this->existingPhoto($property, 'cover.jpg', true);

        $this->existingPhoto($property, 'bedroom.jpg');

        $this->actingAs($agent)
            ->put(
                route('properties.update', $property),
                $this->updatePayload($property) + [
                    'deleted_images' => [$cover->id],
                ]
            )
            ->assertSessionHasErrors('default_photo');

        $this->assertDatabaseHas('property_images', [
            'id' => $cover->id,
            'is_primary' => true,
        ]);

        $this->assertSame(2, $property->images()->count());

        $this->disk('public')->assertExists($cover->image_url);
    }

    public function test_agent_can_replace_cover_and_keep_old_photo(): void
    {
        $agent = $this->agent();
        $property = $this->listing($agent);

        $oldCover = $this->existingPhoto(
            $property,
            'old-cover.jpg',
            true
        );

        $this->actingAs($agent)
            ->put(
                route('properties.update', $property),
                $this->updatePayload($property) + [
                    'default_photo' => $this->photo('new-cover.jpg'),
                ]
            )
            ->assertRedirect(route('owner.properties'));

        $newCover = $property->fresh()->coverImage();

        $this->assertNotNull($newCover);
        $this->assertNotSame($oldCover->id, $newCover->id);
        $this->assertFalse($oldCover->fresh()->is_primary);
        $this->assertTrue($newCover->is_primary);

        $this->assertSame(2, $property->images()->count());

        $this->disk('public')->assertExists($oldCover->image_url);
        $this->disk('public')->assertExists($newCover->image_url);
    }

    public function test_agent_can_delete_old_cover_and_upload_replacement(): void
    {
        $agent = $this->agent();
        $property = $this->listing($agent);

        $oldCover = $this->existingPhoto(
            $property,
            'old-cover.jpg',
            true
        );

        $this->actingAs($agent)
            ->put(
                route('properties.update', $property),
                $this->updatePayload($property) + [
                    'default_photo' => $this->photo('replacement.jpg'),
                    'deleted_images' => [$oldCover->id],
                ]
            )
            ->assertRedirect(route('owner.properties'));

        $this->assertDatabaseMissing('property_images', [
            'id' => $oldCover->id,
        ]);

        $this->assertSame(1, $property->images()->count());

        $newCover = $property->fresh()->coverImage();

        $this->assertNotNull($newCover);
        $this->assertTrue($newCover->is_primary);

        $this->disk('public')->assertMissing($oldCover->image_url);
        $this->disk('public')->assertExists($newCover->image_url);
    }

    public function test_update_normalizes_multiple_primary_images(): void
    {
        $agent = $this->agent();
        $property = $this->listing($agent);

        $first = $this->existingPhoto($property, 'first.jpg', true);
        $second = $this->existingPhoto($property, 'second.jpg', true);

        $this->actingAs($agent)
            ->put(
                route('properties.update', $property),
                $this->updatePayload($property)
            )
            ->assertRedirect(route('owner.properties'));

        $this->assertSame(
            1,
            $property->images()->where('is_primary', true)->count()
        );

        $this->assertTrue($first->fresh()->is_primary);
        $this->assertFalse($second->fresh()->is_primary);
    }

    public function test_gallery_always_places_cover_first(): void
    {
        $property = $this->listing($this->agent());

        $first = $this->existingPhoto($property, 'bedroom.jpg');
        $second = $this->existingPhoto($property, 'kitchen.jpg');
        $cover = $this->existingPhoto($property, 'cover.jpg', true);

        $gallery = $property->fresh()->galleryImages();

        $this->assertSame(
            [$cover->id, $first->id, $second->id],
            $gallery->pluck('id')->all()
        );
    }

    public function test_agent_cannot_delete_another_property_photo(): void
    {
        $agent = $this->agent();

        $property = $this->listing($agent);
        $this->existingPhoto($property, 'cover.jpg', true);

        $otherProperty = $this->listing($this->agent());

        $otherPhoto = $this->existingPhoto(
            $otherProperty,
            'private-cover.jpg',
            true
        );

        $this->actingAs($agent)
            ->put(
                route('properties.update', $property),
                $this->updatePayload($property) + [
                    'deleted_images' => [$otherPhoto->id],
                ]
            )
            ->assertSessionHasErrors('deleted_images');

        $this->assertDatabaseHas('property_images', [
            'id' => $otherPhoto->id,
            'property_id' => $otherProperty->id,
        ]);

        $this->disk('public')->assertExists($otherPhoto->image_url);
    }

    public function test_update_cannot_exceed_total_photo_limit(): void
    {
        $agent = $this->agent();
        $property = $this->listing($agent);

        $this->existingPhoto($property, 'cover.jpg', true);

        foreach (range(1, 9) as $index) {
            $this->existingPhoto($property, "existing-{$index}.jpg");
        }

        $this->actingAs($agent)
            ->put(
                route('properties.update', $property),
                $this->updatePayload($property) + [
                    'photos' => [$this->photo('extra.jpg')],
                ]
            )
            ->assertSessionHasErrors('photos');

        $this->assertSame(10, $property->images()->count());
    }
    public function test_property_requires_area_sqm(): void
    {
        $payload = $this->payload();

        unset($payload['area_sqm']);

        $this->actingAs($this->agent())
            ->post(route('properties.store'), $payload + [
                'default_photo' => $this->photo(),
            ])
            ->assertSessionHasErrors('area_sqm');

        $this->assertDatabaseCount('properties', 0);
    }
    public function test_property_rejects_invalid_area_sqm(): void
    {
        $agent = $this->agent();

        $invalidAreas = [
            null,
            '',
            0,
            -50,
            'abc',
            '120.555',
            '100000000',
        ];

        foreach ($invalidAreas as $area) {
            $payload = $this->payload();

            $payload['area_sqm'] = $area;
            $payload['default_photo'] = $this->photo();

            $this->actingAs($agent)
                ->post(route('properties.store'), $payload)
                ->assertSessionHasErrors('area_sqm');
        }

        $this->assertDatabaseCount('properties', 0);
    }
    public function test_property_can_save_decimal_area_sqm(): void
    {
        $agent = $this->agent();

        $payload = $this->payload();

        $payload['area_sqm'] = '125.75';
        $payload['default_photo'] = $this->photo();

        $response = $this->actingAs($agent)
            ->post(route('properties.store'), $payload);

        $response->assertSessionHasNoErrors();

        $property = $agent->properties()->firstOrFail();

        $response->assertRedirect(
            route('owner.properties.edit', $property)
        );

        $this->assertSame('125.75', $property->area_sqm);
    }
    public function test_agent_can_update_property_area_sqm(): void
    {
        $agent = $this->agent();

        $property = $this->listing($agent);

        $this->existingPhoto($property, 'cover.jpg', true);

        $payload = $this->updatePayload($property);

        $payload['area_sqm'] = '150.50';

        $this->actingAs($agent)
            ->put(route('properties.update', $property), $payload)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('owner.properties'));

        $this->assertSame(
            '150.50',
            $property->fresh()->area_sqm
        );
    }
    public function test_property_can_be_created_with_bilingual_fields(): void
    {
        $agent = $this->agent();

        $payload = $this->payload();

        unset(
            $payload['title'],
            $payload['description'],
            $payload['city'],
            $payload['address']
        );
        $payload['title_ar'] = 'شقة مفروشة حديثة';
        $payload['title_en'] = 'Modern furnished apartment';

        $payload['description_ar'] = 'شقة مفروشة حديثة';
        $payload['description_en'] = 'Modern furnished apartment';

        $payload['city_ar'] = 'عمّان';
        $payload['city_en'] = 'Amman';

        $payload['address_ar'] = 'شارع وصفي التل';
        $payload['address_en'] = 'Wasfi Al Tal Street';

        $payload['default_photo'] = $this->photo();

        $response = $this->actingAs($agent)
            ->post(route('properties.store'), $payload);

        $response->assertSessionHasNoErrors();

        $property = $agent->properties()->firstOrFail();




        $this->assertDatabaseHas('properties', [
            'id' => $property->id,

            'description' => 'Modern furnished apartment',
            'description_ar' => 'شقة مفروشة حديثة',
            'description_en' => 'Modern furnished apartment',

            'city' => 'Amman',
            'city_ar' => 'عمّان',
            'city_en' => 'Amman',

            'address' => 'Wasfi Al Tal Street',
            'address_ar' => 'شارع وصفي التل',
            'address_en' => 'Wasfi Al Tal Street',
            'title' => 'Modern furnished apartment',
            'title_ar' => 'شقة مفروشة حديثة',
            'title_en' => 'Modern furnished apartment',
        ]);
    }
    public function test_property_can_be_created_with_arabic_fields_only(): void
    {
        $agent = $this->agent();

        $payload = $this->payload();

        unset(
            $payload['title'],
            $payload['description'],
            $payload['city'],
            $payload['address']
        );

        $payload['title_ar'] = 'شقة مفروشة للإيجار';

        $payload['description_ar'] = 'شقة مفروشة';
        $payload['city_ar'] = 'عمّان';
        $payload['address_ar'] = 'شارع الجامعة';

        $payload['default_photo'] = $this->photo();

        $this->actingAs($agent)
            ->post(route('properties.store'), $payload)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('properties', [
            'agent_id' => $agent->id,
            'description' => 'شقة مفروشة',
            'city' => 'عمّان',
            'address' => 'شارع الجامعة',
            'title' => 'شقة مفروشة للإيجار',
            'title_ar' => 'شقة مفروشة للإيجار',
            'title_en' => null,
        ]);
    }

    public function test_edit_form_preserves_legacy_property_fields(): void
    {
        $this->withoutVite();

        $agent = $this->agent();

        $property = $this->listing($agent);

        $property->forceFill([
            'description' => 'Legacy apartment description',
            'description_ar' => null,
            'description_en' => null,
            'title' => 'Legacy apartment title',
            'title_ar' => null,
            'title_en' => null,

            'city' => 'Amman',
            'city_ar' => null,
            'city_en' => null,

            'address' => 'Legacy University Street',
            'address_ar' => null,
            'address_en' => null,
        ])->save();

        $this->actingAs($agent)
            ->get(route('owner.properties.edit', $property))
            ->assertOk()
            ->assertSee('value="Legacy apartment title"', false)
            ->assertSee('Legacy apartment description')
            ->assertSee('value="Amman"', false)
            ->assertSee('value="Legacy University Street"', false);
    }

    public function test_agent_can_update_bilingual_property_fields(): void
    {
        $agent = $this->agent();

        $property = $this->listing($agent);

        $this->existingPhoto($property, 'cover.jpg', true);

        $payload = $this->updatePayload($property);

        unset(
            $payload['title'],
            $payload['description'],
            $payload['city'],
            $payload['address']
        );

        $payload['title_ar'] = 'شقة فاخرة بعد التعديل';
        $payload['title_en'] = 'Updated luxury apartment';
        $payload['description_ar'] = 'شقة حديثة بعد التعديل';
        $payload['description_en'] = 'Updated modern apartment';

        $payload['city_ar'] = 'إربد';
        $payload['city_en'] = 'Irbid';

        $payload['address_ar'] = 'شارع الجامعة';
        $payload['address_en'] = 'University Street';

        $this->actingAs($agent)
            ->put(route('properties.update', $property), $payload)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('owner.properties'));

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,

            'description' => 'Updated modern apartment',
            'description_ar' => 'شقة حديثة بعد التعديل',
            'description_en' => 'Updated modern apartment',

            'city' => 'Irbid',
            'city_ar' => 'إربد',
            'city_en' => 'Irbid',

            'address' => 'University Street',
            'address_ar' => 'شارع الجامعة',
            'address_en' => 'University Street',
            'title' => 'Updated luxury apartment',
            'title_ar' => 'شقة فاخرة بعد التعديل',
            'title_en' => 'Updated luxury apartment',
        ]);
    }
    public function test_property_can_be_created_with_english_title_only(): void
    {
        $agent = $this->agent();

        $payload = $this->payload();

        unset($payload['title'], $payload['title_ar']);

        $payload['title_en'] = 'Modern apartment for rent';
        $payload['default_photo'] = $this->photo();

        $this->actingAs($agent)
            ->post(route('properties.store'), $payload)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('properties', [
            'agent_id' => $agent->id,
            'title' => 'Modern apartment for rent',
            'title_en' => 'Modern apartment for rent',
            'title_ar' => null,
        ]);
    }



    private function disk(string $name): FilesystemAdapter
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk($name);

        return $disk;
    }
}
