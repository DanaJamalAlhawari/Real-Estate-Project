<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\RealEstateUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class PropertyPhotoSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->isLocal()) {
            throw new RuntimeException('This seeder is for local demo listings only.');
        }

        $root = 'assets/properties/Data';
        if (!File::isDirectory(public_path($root))) {
            $root = 'assets/images/properties/Data';
        }

        $categories = [
            'Single_Apartment' => 'apartment',
            'Student_Apartment' => 'student_apartment',
            'Studios' => 'studio',
            'Vactions' => 'vacation',
        ];

        $folders = collect(File::allFiles(public_path($root)))
            ->filter(fn ($file) => in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png', 'webp'], true))
            ->groupBy(fn ($file) => str_replace('\\', '/', $file->getRelativePath()));

        if ($folders->isEmpty()) {
            throw new RuntimeException('No listing images were found.');
        }

        $email = getenv('MAKAN_SEED_AGENT_EMAIL');
        $password = getenv('MAKAN_SEED_AGENT_PASSWORD');
        if (!$email || !$password || strlen($password) < 16) {
            throw new RuntimeException('Set MAKAN_SEED_AGENT_EMAIL and a password of at least 16 characters.');
        }

        DB::transaction(function () use ($email, $password, $folders, $categories, $root) {
            $agent = RealEstateUser::firstOrCreate(['email' => $email], ['password' => $password, 'role' => 'agent']);
            if ($agent->role !== 'agent' || (!$agent->wasRecentlyCreated && $agent->agentProfile?->agency_name !== 'Makan demo listings')) {
                throw new RuntimeException('The seed email belongs to an existing account outside the photo seeder.');
            }

            if ($agent->wasRecentlyCreated) {
                $agent->agentProfile()->create(['first_name' => 'Makan', 'last_name' => 'Demo', 'phone' => '+962790000000', 'agency_name' => 'Makan demo listings']);
            }

            foreach ($folders->sortKeys() as $folder => $files) {
                $category = $categories[Str::before($folder, '/')] ?? null;
                if (!$category) {
                    continue;
                }

                $folderName = basename($folder);
                $title = Property::CATEGORIES[$category].' — '.Str::headline($folderName);
                $property = Property::firstOrNew(['agent_id' => $agent->id, 'title' => $title]);
                if (!$property->exists) {
                    $property->fill([
                        'description' => 'Local demo listing for the '.$folderName.' photo collection.',
                        'category' => $category,
                        'price' => 250,
                        'city' => 'Amman',
                        'address' => 'Demo listing — address not supplied',
                        'ownership_deed_url' => 'NULL',
                    ]);
                    $property->verification_status = 'verified';
                    $property->save();
                }

                $images = $files->sortBy(fn ($file) => [strtolower($file->getBasename('.'.$file->getExtension())) !== 'default', $file->getFilename()])->values();
                foreach ($images as $index => $image) {
                    $property->images()->firstOrCreate(
                        ['image_url' => $root.'/'.$folder.'/'.$image->getFilename()],
                        ['is_primary' => $index === 0],
                    );
                }
            }
        });
    }
}
