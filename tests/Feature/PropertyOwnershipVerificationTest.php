<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\RealEstateUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PropertyOwnershipVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_uploads_their_document_and_admin_can_approve_or_reject_it(): void
    {
        $this->withoutVite();
        Storage::fake('local');
        $agent = $this->account('agent');
        $property = $this->property($agent, 'Owned listing');
        $otherProperty = $this->property($this->account('agent'), 'Another agent’s listing');
        $document = UploadedFile::fake()->createWithContent('deed.pdf', "%PDF-1.4\n%%EOF");

        $this->get(route('admin.verifications'))->assertRedirect(route('auth.login'));
        $this->actingAs($this->account('tenant'))->get(route('admin.verifications'))->assertForbidden();
        $this->actingAs($agent)->get(route('owner.properties'))->assertOk()
            ->assertSee($property->title)->assertDontSee($otherProperty->title);
        $this->get(route('owner.properties.edit', $otherProperty))->assertNotFound();
        $this->get(route('owner.properties.edit', $property))->assertOk()
            ->assertSee('name="property_id" value="' . $property->id . '"', false);
        $this->post(route('properties.verification.store'), [
            'property_id' => $otherProperty->id,
            'document' => $document,
        ])->assertNotFound();

        $admin = $this->account('admin');
        foreach (['approve' => 'verified', 'reject' => 'rejected'] as $decision => $status) {
            $this->actingAs($agent)->post(route('properties.verification.store'), [
                'property_id' => $property->id,
                'document' => UploadedFile::fake()->createWithContent('deed.pdf', "%PDF-1.4\n%%EOF"),
            ])->assertRedirect(route('owner.properties.edit', $property));

            $property->refresh();
            $this->assertSame('pending', $property->verification_status);
            $this->assertNull($property->reviewed_by);
            $this->assertNull($property->verified_at);
            /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
            $disk = Storage::disk('local');

            $disk->assertExists($property->ownership_deed_url);

            $documentUrl = route('admin.verifications.document', $property);
            $this->get($documentUrl)->assertForbidden();
            $this->actingAs($admin)->get(route('admin.verifications'))->assertOk()
                ->assertSee($property->title)->assertDontSee($otherProperty->title);
            $this->get($documentUrl)->assertOk()->assertHeader('Content-Type', 'application/pdf');

            $updateUrl = route('admin.verifications.update', $property);
            $this->patch($updateUrl, [
                'decision' => $decision,
                'note' => 'Decision recorded',
            ])->assertRedirect(route('admin.verifications'));

            $property->refresh();
            $this->assertSame($status, $property->verification_status);
            $this->assertEquals($admin->id, $property->reviewed_by);
            $this->assertSame('Decision recorded', $property->admin_notes);
            $this->assertSame($decision === 'approve', $property->verified_at !== null);
        }
    }

    private function account(string $role): RealEstateUser
    {
        return RealEstateUser::forceCreate([
            'email' => fake()->unique()->safeEmail(),
            'password' => 'test-password',
            'role' => $role,
        ]);
    }

    private function property(RealEstateUser $agent, string $title): Property
    {
        return Property::forceCreate([
            'agent_id' => $agent->id,
            'title' => $title,
            'description' => 'Test property',
            'category' => 'apartment',
            'area_sqm' => 120,
            'city' => 'Amman',
            'address' => 'Test address',
            'ownership_deed_url' => 'placeholder.pdf',
        ]);
    }
}
