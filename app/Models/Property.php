<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    use SoftDeletes;

    protected $table = 'properties';

    protected $fillable = [
        'agent_id',
        'title',
        'description',
        'category',
        'price',
        'city',
        'area',
        'address',
        'latitude',
        'longitude',
        'location_is_approximate',
        'bedrooms',
        'bathrooms',
        'ownership_deed_url',
        'title_ar',
        'description_ar',
        'city_ar',
        'area_ar',
        'address_ar',
        'title_en',
        'description_en',
        'city_en',
        'area_en',
        'address_en',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'location_is_approximate' => 'boolean',
            'bedrooms' => 'integer',
            'bathrooms' => 'integer',
            'verified_at' => 'datetime',
        ];
    }

    public const CATEGORIES = [
        'studio' => 'Studio',
        'student_apartment' => 'Student apartment',
        'apartment' => 'Apartment',
        'vacation' => 'Vacation',
    ];

    public const TRANSLATABLE_FIELDS = ['title', 'description', 'city', 'area', 'address'];

    private const CITY_NAMES = [
        'Amman' => ['عمّان', 'عمان'],
        'Irbid' => ['إربد', 'اربد'],
        'Zarqa' => ['الزرقاء', 'زرقاء'],
        'Aqaba' => ['العقبة', 'عقبة'],
        'Salt' => ['السلط', 'سلط'],
        'Madaba' => ['مادبا'],
        'Jerash' => ['جرش'],
        'Ajloun' => ['عجلون'],
        'Karak' => ['الكرك', 'كرك'],
        'Mafraq' => ['المفرق', 'مفرق'],
        'Ma’an' => ['معان'],
        'Tafilah' => ['الطفيلة', 'طفيلة'],
    ];

    public function localized(string $field, ?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();
        $original = $this->getAttribute($field);
        $translation = $this->getAttribute($field . '_' . $locale);

        if (filled($translation)) {
            return $translation;
        }

        if ($field === 'city' && filled($original)) {
            foreach (self::CITY_NAMES as $english => $arabic) {
                if (in_array(mb_strtolower($original), [mb_strtolower($english), ...$arabic], true)) {
                    return $locale === 'ar' ? $arabic[0] : $english;
                }
            }
        }

        return $original;
    }

    public function scopeMatchingSearch(Builder $query, string $search): Builder
    {
        return $this->matchSearchTerms($query, $search, self::TRANSLATABLE_FIELDS, true);
    }

    public function scopeMatchingTitle(Builder $query, string $search): Builder
    {
        return $this->matchSearchTerms($query, $search, ['title']);
    }

    public function scopeMatchingTitleOrArea(Builder $query, string $search): Builder
    {
        return $this->matchSearchTerms($query, $search, ['title', 'area']);
    }

    /** @param list<string> $fields */
    private function matchSearchTerms(Builder $query, string $search, array $fields, bool $includeCategoryAndCity = false): Builder
    {
        $columns = $fields;
        foreach ($fields as $field) {
            $columns[] = $field . '_ar';
            $columns[] = $field . '_en';
        }

        $terms = preg_split('/[\s,،]+/u', trim($search), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        foreach ($terms as $term) {
            $pattern = '%' . strtr(mb_strtolower($term), ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
            $query->where(function (Builder $matches) use ($columns, $pattern, $term, $includeCategoryAndCity): void {
                foreach ($columns as $column) {
                    $matches->orWhereRaw("LOWER(properties.{$column}) LIKE ? ESCAPE '!'", [$pattern]);
                }

                if ($includeCategoryAndCity) {
                    $categories = array_keys(array_filter(self::CATEGORIES, fn(string $label): bool => mb_stripos($label, $term) !== false || mb_stripos(__($label, [], 'ar'), $term) !== false));
                    $matches->orWhereRaw("LOWER(properties.category) LIKE ? ESCAPE '!'", [$pattern])
                        ->orWhereIn('properties.category', $categories)
                        ->orWhereIn('properties.city', self::cityVariants($term));
                }
            });
        }

        return $query;
    }

    public function scopeInCity(Builder $query, string $city): Builder
    {
        $variants = self::cityVariants($city);

        return $query->where(fn(Builder $matches) => $matches->whereIn('properties.city', $variants)
            ->orWhereIn('properties.city_ar', $variants)->orWhereIn('properties.city_en', $variants));
    }

    /** @return list<string> */
    private static function cityVariants(string $city): array
    {
        foreach (self::CITY_NAMES as $english => $arabic) {
            if (in_array(mb_strtolower($city), [mb_strtolower($english), ...$arabic], true)) {
                return [$city, $english, ...$arabic];
            }
        }

        return [$city];
    }

    public function scopeDiscoverable($query)
    {
        return $query->where('verification_status', 'verified')
            ->whereIn('status', ['available', 'partially_rented']);
    }

    // الوكيل المسؤول عن العقار
    public function agent(): BelongsTo
    {
        return $this->belongsTo(
            RealEstateUser::class,
            'agent_id'
        );
    }

    // المستخدم الذي راجع العقار
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(
            RealEstateUser::class,
            'reviewed_by'
        );
    }

    // غرف العقار
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class, 'property_id');
    }

    // صور العقار، بما فيها صور غرفه
    public function images(): HasMany
    {
        return $this->hasMany(PropertyImage::class, 'property_id');
    }
    public function coverImage(): ?PropertyImage
    {
        $images = $this->images
            ->whereNull('room_id')
            ->sortBy('id');

        return $images->firstWhere('is_primary', true)
            ?? $images->first(fn($image) => $image->isDemoDefaultImage())
            ?? $images->first();
    }

    /**
     * Return property gallery images with the cover image first.
     *
     * @return Collection<int, PropertyImage>
     */
    public function galleryImages(): Collection
    {
        $cover = $this->coverImage();

        return $this->images
            ->whereNull('room_id')
            ->sortBy([
                fn(PropertyImage $a, PropertyImage $b) => ($a->id !== $cover?->id)
                    <=> ($b->id !== $cover?->id),

                ['id', 'asc'],
            ])
            ->values();
    }

    public function dailyViews(): HasMany
    {
        return $this->hasMany(PropertyDailyView::class);
    }

    // الخدمات المتوفرة في العقار
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(
            Service::class,
            'property_services',
            'property_id',
            'service_id'
        );
    }

    // حجوزات العقار، بما فيها حجوزات غرفه
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'property_id');
    }

    // المحادثات المرتبطة بالعقار
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'property_id');
    }

    // تقييمات العقار
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'property_id');
    }

    // المستخدمون الذين أضافوا العقار للمفضلة
    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(
            RealEstateUser::class,
            'favorites',
            'property_id',
            'tenant_id'
        )->withPivot('created_at');
    }
}
