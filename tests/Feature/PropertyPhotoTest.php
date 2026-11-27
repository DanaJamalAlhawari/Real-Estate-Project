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
            'area' => 'Khalda',
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
    private function disk(string $name): FilesystemAdapter
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk($name);

        return $disk;
    }
}
