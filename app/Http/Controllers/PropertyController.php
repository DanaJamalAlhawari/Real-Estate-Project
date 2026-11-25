<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PropertyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->validate(['q' => 'nullable|string|max:255']);
        $properties = $request->user()->properties()->with('images')
            ->matchingSearch($filters['q'] ?? '')
            ->latest()->orderByDesc('id')->paginate(config('app.properties_per_page'))->withQueryString();

        return view('owner.properties', ['properties' => $properties]);
    }

    // Hide or show a property
    public function toggleVisibility($id)
    {
        $property = Property::where('agent_id', request()->user()->id)->findOrFail($id);
        if ($property->status == 'available') {
            $property->status = 'hidden';
            $property->save();
        } elseif ($property->status == 'hidden') {
            $property->status = 'available';
            $property->save();
        }

        return redirect()->route('owner.properties');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->ensureCanSubmitProperty(request());
        $services = Service::all();

        return view('owner.property-form', ['services' => $services]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $translationRules = $this->translationRules();
        $this->ensureCanSubmitProperty($request);
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            ...$translationRules,
            'category' => ['required', Rule::in(array_keys(Property::CATEGORIES))],
            'price' => 'required|numeric|min:0',
            'city' => 'required|string|max:255',
            'area' => 'nullable|string|max:255',
            'address' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:29.18,33.4',
            'longitude' => 'required|numeric|between:34.9,39.32',
            'location_is_approximate' => 'sometimes|boolean',
            'bedrooms' => 'required|integer|min:0',
            'bathrooms' => 'required|integer|min:0',
            'services' => 'sometimes|array',
            'services.*' => 'integer|distinct|exists:services,id',
            'photos' => 'sometimes|array|max:10',
            'photos.*' => 'image|mimes:jpg,jpeg,png|max:5120',
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'action' => 'required|in:publish',
        ]);

        $document = $validated['document'] ?? null;
        $agentId = $request->user()->id;

        // 2. Get selected services and uploaded photos.
        $services = $validated['services'] ?? [];
        $photos = $request->file('photos', []);

        // 3. Keep track of uploaded files for cleanup.
        $uploadedPaths = [];
        $ownershipPath = null;

        try {

            // 4. Upload property photos.
            foreach ($photos as $photo) {
                $path = $photo->store('properties', 'public');
                abort_if($path === false, 500, __('Could not store the property photo.'));
                $uploadedPaths[] = $path;
            }

            // 5. Save property, services, and image records together.
            $property = DB::transaction(function () use (
                $validated,
                $translationRules,
                $services,
                $uploadedPaths,
                $agentId,
                $document,
                &$ownershipPath
            ) {

                // Create property.
                $property = Property::create([
                    'agent_id' => $agentId,
                    ...Arr::only($validated, array_keys($translationRules)),
                    'title' => $validated['title'],
                    'description' => $validated['description'],
                    'category' => $validated['category'],
                    'price' => $validated['price'],
                    'city' => $validated['city'],
                    'area' => $validated['area'] ?? null,
                    'address' => $validated['address'],
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude'],
                    'location_is_approximate' => (bool) ($validated['location_is_approximate'] ?? true),
                    'bedrooms' => $validated['bedrooms'],
                    'bathrooms' => $validated['bathrooms'],
                    'ownership_deed_url' => 'NULL',
                ]);

                $property->services()->sync($services);

                // Save uploaded images.
                foreach ($uploadedPaths as $index => $path) {

                    $property->images()->create([
                        'image_url' => $path,
                        'is_primary' => $index === 0,
                    ]);
                }

                if ($document) {
                    $path = $document->store('verifications', 'local');
                    abort_if($path === false, 500, __('Could not store the ownership document.'));
                    $ownershipPath = $path;
                    $property->ownership_deed_url = $path;
                    $property->verification_status = 'pending';
                    abort_unless($property->save(), 500, __('Could not save the ownership document.'));
                }

                return $property;
            });
        } catch (\Throwable $e) {

            // Delete uploaded files if database saving fails.
            Storage::disk('public')->delete($uploadedPaths);
            if ($ownershipPath !== null) {
                Storage::disk('local')->delete($ownershipPath);
            }

            throw $e;
        }

        if ($document) {
            return redirect()->route('owner.properties.edit', $property)
                ->with('success', __('Property created and ownership document uploaded.'));
        }

        // 6. Redirect after successful creation.
        return redirect()
            ->route('owner.properties')
            ->with('success', __('Property created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $property = Property::with(['services', 'images'])->where('agent_id', request()->user()->id)->findOrFail($id);
        $services = Service::all();

        return view('owner.property-form', ['property' => $property, 'services' => $services]);
    }

    /**
     * Update the specified property in storage.
     */
    public function update(Request $request, string $id)
    {
        $translationRules = $this->translationRules();
        // 1. Find the property belonging to the agent.
        $property = Property::where('agent_id', $request->user()->id)->findOrFail($id);

        // 2. Validate property details and selected services.
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            ...$translationRules,
            'category' => ['required', Rule::in(array_keys(Property::CATEGORIES))],
            'price' => 'required|numeric|min:0',
            'city' => 'required|string|max:255',
            'area' => 'nullable|string|max:255',
            'address' => 'required|string|max:255',
            'latitude' => 'nullable|required_with:longitude|numeric|between:-90,90',
            'longitude' => 'nullable|required_with:latitude|numeric|between:-180,180',
            'location_is_approximate' => 'sometimes|boolean',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            // Validate selected services.
            'services' => 'sometimes|array',
            'services.*' => 'integer|distinct|exists:services,id',
            // Validate newly uploaded photos.
            'photos' => 'sometimes|array',
            'photos.*' => 'image|mimes:jpg,jpeg,png|max:5120',

            // Validate existing images selected for deletion.
            'deleted_images' => 'sometimes|array',
            'deleted_images.*' => 'integer|distinct|exists:property_images,id',
        ]);

        // 3. Update basic property information.
        $property->fill(Arr::only($validated, array_keys($translationRules)));
        $property->title = $validated['title'];
        $property->description = $validated['description'];
        $property->category = $validated['category'];
        $property->price = $validated['price'];

        // 4. Update property location.
        $property->city = $validated['city'];
        $property->area = $validated['area'] ?? null;
        $property->address = $validated['address'];
        $property->latitude = $validated['latitude'] ?? $property->latitude;
        $property->longitude = $validated['longitude'] ?? $property->longitude;
        if ($property->isDirty(['latitude', 'longitude'])) {
            $property->location_is_approximate = (bool) ($validated['location_is_approximate'] ?? true);
        }

        // 5. Update property details.
        // Keep existing values if bedrooms or bathrooms are not submitted.
        $property->bedrooms = $validated['bedrooms'] ?? $property->bedrooms;
        $property->bathrooms = $validated['bathrooms'] ?? $property->bathrooms;

        // Get the IDs of images selected for deletion.
        $deletedImageIds = $validated['deleted_images'] ?? [];

        // Ensure that the selected images belong to this property.
        $imagesToDelete = $property->images()
            ->whereIn('id', $deletedImageIds)
            ->get();

        if ($imagesToDelete->count() !== count($deletedImageIds)) {
            throw ValidationException::withMessages([
                'deleted_images' => __('One or more selected images do not belong to this property.'),
            ]);
        }

        // Check the maximum number of photos after updating.
        $remainingPhotos = $property->images()->count() - $imagesToDelete->count();
        $newPhotos = $request->file('photos', []);

        if ($remainingPhotos + count($newPhotos) > 10) {
            throw ValidationException::withMessages([
                'photos' => __('A property can have a maximum of 10 photos.'),
            ]);
        }

        // Upload new photos before saving the database changes.
        $uploadedPaths = [];

        try {
            foreach ($newPhotos as $photo) {
                $path = $photo->store('properties', 'public');
                abort_if($path === false, 500, __('Could not store the property photo.'));
                $uploadedPaths[] = $path;
            }

            DB::transaction(function () use (
                $property,
                $validated,
                $imagesToDelete,
                $uploadedPaths
            ) {
                // Save property details.
                $current = Property::lockForUpdate()->findOrFail($property->id);
                $current->forceFill($property->getDirty());
                if ($current->getOriginal('category') !== $validated['category']) {
                    $current->category = $validated['category'];
                    $current->approved_commission_rate = null;
                    if ($current->verification_status === 'verified') {
                        $current->verification_status = 'pending';
                        $current->reviewed_by = null;
                        $current->verified_at = null;
                        $current->admin_notes = null;
                    }
                }
                $property = $current;
                $property->save();

                // Update property services.
                $property->services()->sync($validated['services'] ?? []);

                // Delete selected image records.
                foreach ($imagesToDelete as $image) {
                    $image->delete();
                }

                // Add newly uploaded images.
                foreach ($uploadedPaths as $path) {
                    $property->images()->create([
                        'image_url' => $path,
                    ]);
                }

                // Ensure exactly one remaining image is the cover.
                $images = $property->images()->orderBy('id')->get();

                if ($images->isNotEmpty()) {
                    $primary = $images->firstWhere('is_primary', true)
                        ?? $images->first();

                    $property->images()
                        ->where('id', '!=', $primary->id)
                        ->update(['is_primary' => false]);

                    $primary->is_primary = true;
                    $primary->save();
                }
            });
        } catch (\Throwable $e) {
            // Remove newly uploaded files if saving fails.
            Storage::disk('public')->delete($uploadedPaths);

            throw $e;
        }

        // Delete old image files only after the database update succeeds.
        foreach ($imagesToDelete as $image) {
            Storage::disk('public')->delete($image->image_url);
        }

        // 7. Redirect after successful update.
        return redirect()
            ->route('owner.properties')
            ->with('success', __('Property updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id): RedirectResponse
    {
        DB::transaction(function () use ($request, $id) {
            $property = $request->user()->properties()->lockForUpdate()->findOrFail($id);
            $property->bookings()->where('status', 'pending')->update(['status' => 'cancelled']);
            $property->delete();
        });

        return redirect()
            ->route('owner.properties')
            ->with('success', __('Property deleted successfully.'));
    }

    /** @return array<string, string> */
    private function translationRules(): array
    {
        $rules = [];

        foreach (Property::TRANSLATABLE_FIELDS as $field) {
            $rule = $field === 'description' ? 'nullable|string' : 'nullable|string|max:255';
            $rules[$field.'_ar'] = $rule;
            $rules[$field.'_en'] = $rule;
        }

        return $rules;
    }

    private function ensureCanSubmitProperty(Request $request): void
    {
        $properties = $request->user()->properties();
        abort_if((clone $properties)->exists() && ! $properties->where('verification_status', 'verified')->exists(),
            403, 'An admin must approve your first property before you add another.');
    }
}
