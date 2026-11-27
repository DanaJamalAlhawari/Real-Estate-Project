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
    private const AGENT_EMAIL = 'makan-demo-agent-76198e8019@example.com';

    private const LISTINGS = [
        'singleApartment1' => ['weibdeh', 'Weibdeh Garden Apartment', 'شقة حديقة اللويبدة'],
        'singleApartment2' => ['abdali', 'Abdali Boulevard Apartment', 'شقة بوليفارد العبدلي'],
        'singleApartment3' => ['rainbow', 'Rainbow Street Apartment', 'شقة شارع الرينبو'],
        'singleApartment4' => ['madaba', 'Madaba Old Town Apartment', 'شقة مادبا القديمة'],
        'singleApartment5' => ['salt', 'Salt Heritage Apartment', 'شقة السلط التراثية'],
        'studentApratment1' => ['jubeiha', 'University Gate Student Apartment', 'شقة طلابية عند بوابة الجامعة الأردنية'],
        'studentApratment2' => ['irbid', 'Irbid University Quarter Apartment', 'شقة طلابية في حي الجامعة بإربد'],
        'studentApratment3' => ['sweileh', 'Sweileh Student Residence', 'سكن طلابي في صويلح'],
        'studentApratment4' => ['jubeiha', 'Queen Rania Student Apartment', 'شقة طلابية في شارع الملكة رانيا'],
        'studentApratment5' => ['irbid', 'Irbid Campus Walk Apartment', 'شقة طلابية قرب حرم الجامعة في إربد'],
        'studionumber1' => ['weibdeh', 'Weibdeh Courtyard Studio', 'استوديو فناء اللويبدة'],
        'studionumber2' => ['shmeisani', 'Shmeisani City Studio', 'استوديو الشميساني'],
        'studionumber3' => ['abdali', 'Abdali Walk Studio', 'استوديو ممشى العبدلي'],
        'studionumber4' => ['madaba', 'Madaba Mosaic Studio', 'استوديو مادبا للفسيفساء'],
        'studionumber5' => ['jerash', 'Jerash City Centre Studio', 'استوديو وسط جرش'],
        'vaction1' => ['aqaba', 'Aqaba Corniche Retreat', 'بيت عطلات على كورنيش العقبة'],
        'vaction 2' => ['ayla', 'Ayla Waterfront Holiday Apartment', 'شقة عطلات على واجهة أيلة البحرية'],
        'vaction 3' => ['salt', 'Salt Heritage Holiday Home', 'بيت عطلات في السلط التراثية'],
        'vaction 4' => ['aqaba', 'Al Hafayer Holiday Apartment', 'شقة عطلات في الحفائر'],
    ];

    public function run(): void
    {
        if (! app()->isLocal()) {
            throw new RuntimeException('This seeder is for local demo listings only.');
        }

        $root = 'assets/properties/Data';

        $categories = [
            'Single_Apartment' => 'apartment',
            'Student_Apartment' => 'student_apartment',
            'Studios' => 'studio',
            'Vactions' => 'vacation',
        ];

        $folders = collect(File::allFiles(public_path($root)))
            ->filter(fn($file) => in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png', 'webp'], true))
            ->groupBy(fn($file) => str_replace('\\', '/', $file->getRelativePath()));

        if ($folders->isEmpty()) {
            throw new RuntimeException('No listing images were found.');
        }

        $email = getenv('MAKAN_SEED_AGENT_EMAIL') ?: self::AGENT_EMAIL;
        $password = getenv('MAKAN_SEED_AGENT_PASSWORD');

        DB::transaction(function () use ($email, $password, $folders, $categories, $root) {
            $agent = RealEstateUser::where('email', $email)->first();
            if ($agent === null) {
                if (! $password || strlen($password) < 16) {
                    throw new RuntimeException('Set MAKAN_SEED_AGENT_PASSWORD to a password of at least 16 characters to create the demo account.');
                }

                $agent = RealEstateUser::firstOrCreate(['email' => $email], ['password' => $password, 'role' => 'agent']);
            }
            if ($agent->role !== 'agent' || (! $agent->wasRecentlyCreated && $agent->agentProfile?->agency_name !== 'Makan demo listings')) {
                throw new RuntimeException('The seed email belongs to an existing account outside the photo seeder.');
            }

            if ($agent->wasRecentlyCreated) {
                $agent->agentProfile()->create(['first_name' => 'Makan', 'last_name' => 'Demo', 'phone' => '+962790000000', 'agency_name' => 'Makan demo listings']);
            }

            $existing = Property::withTrashed()->where('agent_id', $agent->id)->with('images')->get();
            $listingsByFolder = [];
            foreach ($existing as $property) {
                foreach ($property->images as $image) {
                    $folder = $this->photoFolder($image->image_url);
                    if ($folder !== null) {
                        $listingsByFolder[$folder] = $property;
                    }
                }
            }

            foreach ($folders->sortKeys() as $folder => $files) {
                $category = $categories[Str::before($folder, '/')] ?? null;
                if (! $category) {
                    continue;
                }

                $folderName = basename($folder);
                if (! isset(self::LISTINGS[$folderName])) {
                    throw new RuntimeException('No demo location is defined for ' . $folder);
                }

                [$site, $title, $titleAr] = self::LISTINGS[$folderName];
                $legacyTitle = Property::CATEGORIES[$category] . ' — ' . Str::headline($folderName);
                $property = $listingsByFolder[$folder]
                    ?? $existing->firstWhere('title', $legacyTitle)
                    ?? new Property(['agent_id' => $agent->id]);
                if (! $property->exists) {
                    $property->fill([
                        'description' => 'Demo property listing.',
                        'description_ar' => 'إعلان عقار تجريبي.',
                        'category' => $category,
                        'price' => 250,
                        'area_sqm' => match ($category) {
                            'apartment' => 120,
                            'student_apartment' => 80,
                            'studio' => 45,
                            'vacation' => 150,
                            default => 100,
                        },
                        'ownership_deed_url' => 'NULL',
                    ]);
                    $property->verification_status = 'verified';
                }

                if (! $property->trashed() && (! $property->exists || $property->title === $legacyTitle)) {
                    $this->setListingLocation($property, $site, $title, $titleAr);
                }

                $images = $files->sortBy(function ($file) use ($folderName) {
                    $filename = strtolower($file->getFilename());
                    $basename = pathinfo($filename, PATHINFO_FILENAME);

                    $isDefault = $basename === 'default';

                    $isPreferredCover = $folderName === 'vaction 4'
                        && $filename === 'livig-room.jpeg';

                    return [
                        $isDefault ? 0 : ($isPreferredCover ? 1 : 2),
                        $filename,
                    ];
                })->values();

                $primaryImage = null;

                foreach ($images as $index => $image) {
                    $suffix = $folder . '/' . $image->getFilename();

                    $currentPath = $root . '/' . $suffix;
                    $legacyPath = 'assets/images/properties/Data/' . $suffix;

                    $propertyImage = $property->images()
                        ->whereNull('room_id')
                        ->where('image_url', $currentPath)
                        ->first();

                    if (! $propertyImage) {
                        $propertyImage = $property->images()
                            ->whereNull('room_id')
                            ->where('image_url', $legacyPath)
                            ->first();

                        if ($propertyImage) {
                            $propertyImage->update([
                                'image_url' => $currentPath,
                            ]);
                        } else {
                            $propertyImage = $property->images()->create([
                                'image_url' => $currentPath,
                                'is_primary' => false,
                            ]);
                        }
                    }

                    if ($index === 0) {
                        $primaryImage = $propertyImage;
                    }
                }

                if ($primaryImage) {
                    $property->images()
                        ->whereNull('room_id')
                        ->where('id', '!=', $primaryImage->id)
                        ->update(['is_primary' => false]);

                    $primaryImage->update([
                        'is_primary' => true,
                    ]);
                }
            }

            if ($email === self::AGENT_EMAIL) {
                foreach ($existing->whereNull('deleted_at') as $property) {
                    if ($property->title === 'Ayman Home') {
                        $this->setListingLocation($property, 'downtown', 'Abu Ayoub Street Apartment', 'شقة شارع أبو أيوب الأنصاري');
                    } elseif ($property->title === 'Amman' && $property->city === 'Amman' && $property->address === 'Amman') {
                        $this->setListingLocation($property, 'rainbow', 'Rainbow Terrace Studio', 'استوديو شرفة الرينبو');
                    }
                }
            }
        });
    }

    private function photoFolder(string $path): ?string
    {
        foreach (['assets/properties/Data/', 'assets/images/properties/Data/'] as $root) {
            if (str_starts_with($path, $root)) {
                return dirname(Str::after($path, $root));
            }
        }

        return null;
    }

    private function setListingLocation(Property $property, string $site, string $title, string $titleAr): void
    {
        [$city, $cityAr, , , $address, $addressAr, $latitude, $longitude] = config('jordan_locations.sites.' . $site);
        $property->fill([
            'title' => $title,
            'title_en' => $title,
            'title_ar' => $titleAr,
            'city' => $city,
            'city_en' => $city,
            'city_ar' => $cityAr,
            'address' => $address,
            'address_en' => $address,
            'address_ar' => $addressAr,
        ]);

        if ($property->latitude === null || $property->longitude === null) {
            $property->latitude ??= $latitude;
            $property->longitude ??= $longitude;
            $property->location_is_approximate = true;
        }

        Property::withoutTimestamps(fn() => $property->save());
    }
}
