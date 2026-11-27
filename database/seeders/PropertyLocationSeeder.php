<?php

namespace Database\Seeders;

use App\Models\Property;
use Illuminate\Database\Seeder;

class PropertyLocationSeeder extends Seeder
{
    public function run(): void
    {
        Property::query()->where(fn($query) => $query->whereNull('latitude')->orWhereNull('longitude'))
            ->eachById(function (Property $property): void {
                $coordinates = $this->coordinates($property);
                if ($coordinates === null) {
                    return;
                }

                Property::withoutTimestamps(function () use ($property, $coordinates): void {
                    $property->latitude ??= $coordinates[0];
                    $property->longitude ??= $coordinates[1];
                    $property->location_is_approximate = true;
                    $property->save();
                });
            });
    }

    /** @return array{float, float}|null */
    private function coordinates(Property $property): ?array
    {
        $cities = $this->normalizedValues($property, 'city');
        $addresses = $this->normalizedValues($property, 'address');

        foreach (config('jordan_locations.sites') as $site) {
            [$city, $cityAr,,, $street, $streetAr, $latitude, $longitude] = $site;

            if (! array_intersect($cities, [
                $this->normalize($city),
                $this->normalize($cityAr)
            ])) {
                continue;
            }

            foreach ([$street, $streetAr, ...($site[8] ?? [])] as $alias) {
                $normalizedAlias = $this->normalize($alias);

                if ($normalizedAlias === '') {
                    continue;
                }

                foreach ($addresses as $address) {
                    if (str_contains($address, $normalizedAlias)) {
                        return [$latitude, $longitude];
                    }
                }
            }
        }

        foreach (config('jordan_locations.cities') as $city) {
            $coordinates = array_splice($city, -2);

            if (array_intersect($cities, array_map($this->normalize(...), $city))) {
                return $coordinates;
            }
        }

        return null;
    }

    /** @return list<string> */
    private function normalizedValues(Property $property, string $field): array
    {
        return array_values(array_unique(array_filter(array_map($this->normalize(...), [$property->{$field}, $property->{$field . '_ar'}, $property->{$field . '_en'}]))));
    }

    private function normalize(?string $value): string
    {
        return strtr(mb_strtolower(trim($value ?? '')), ['ّ' => '', 'أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا']);
    }
}
