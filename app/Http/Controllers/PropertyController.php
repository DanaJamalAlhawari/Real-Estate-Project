<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Property;
use App\Models\Service;
use App\Models\PropertyImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PropertyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $properties = Property::with('images')->where('agent_id', 1)->get();
        return view('owner.properties', ['properties' => $properties]);
    }

    // Hide or show a property
    public function toggleVisibility($id)
    {
        $property = Property::where('agent_id', 1)->findOrFail($id);
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
        $services = Service::all();
        return view('owner.property-form', ['services' => $services]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'category'    => 'required|string|max:255',
            'price'       => 'required|numeric|min:0',
            'city'        => 'required|string|max:255',
            'area'        => 'nullable|string|max:255',
            'address'     => 'required|string|max:255',
            'latitude'    => 'nullable|numeric|between:-90,90',
            'longitude'   => 'nullable|numeric|between:-180,180',
            'bedrooms'    => 'required|integer|min:0',
            'bathrooms'   => 'required|integer|min:0',
            'services'    => 'sometimes|array',
            'services.*'  => 'integer|distinct|exists:services,id',
            'photos'      => 'sometimes|array|max:10',
            'photos.*'    => 'image|mimes:jpg,jpeg,png|max:5120',
            'action'      => 'required|in:draft,publish',
        ]);

        // 2. Get selected services and uploaded photos.
        $services = $validated['services'] ?? [];
        $photos = $request->file('photos', []);

        // 3. Keep track of uploaded files for cleanup.
        $uploadedPaths = [];

        try {

            // 4. Upload property photos.
            foreach ($photos as $photo) {
                $uploadedPaths[] = $photo->store('properties', 'public');
            }

            // 5. Save property, services, and image records together.
            DB::transaction(function () use (
                $validated,
                $services,
                $uploadedPaths
            ) {

                // Create property.
                $property = Property::create([
                    'agent_id'    => 1,
                    'title'       => $validated['title'],
                    'description' => $validated['description'],
                    'category'    => $validated['category'],
                    'price'       => $validated['price'],
                    'city'        => $validated['city'],
                    'area'        => $validated['area'] ?? null,
                    'address'     => $validated['address'],
                    'latitude'    => $validated['latitude'] ?? null,
                    'longitude'   => $validated['longitude'] ?? null,
                    'bedrooms'    => $validated['bedrooms'],
                    'bathrooms'   => $validated['bathrooms'],
                    'ownership_deed_url' => 'NULL',
                ]);

                // Temporary agent until authentication is connected.
                $property->agent_id = 1;


                $property->save();

                $property->services()->sync($services);

                // Save uploaded images.
                foreach ($uploadedPaths as $index => $path) {

                    $property->images()->create([
                        'image_url' => $path,
                        'is_primary' => $index === 0,
                    ]);
                }
            });
        } catch (\Throwable $e) {

            // Delete uploaded files if database saving fails.
            Storage::disk('public')->delete($uploadedPaths);

            throw $e;
        }

        // 6. Redirect after successful creation.
        return redirect()
            ->route('owner.properties')
            ->with('success', 'Property created successfully.');
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
        $property = Property::with(['services', 'images'])->where('agent_id', 1)->findOrFail($id);
        $services = Service::all();
        return view('owner.property-form', ['property' => $property, 'services' => $services]);
    }

    /**
     * Update the specified property in storage.
     */
    public function update(Request $request, string $id)
    {
        // 1. Find the property belonging to the agent.
        $property = Property::where('agent_id', 1)->findOrFail($id);

        // 2. Validate property details and selected services.
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'category'    => 'required|string|max:255',
            'price'       => 'required|numeric|min:0',
            'city'        => 'required|string|max:255',
            'area'        => 'nullable|string|max:255',
            'address'     => 'required|string|max:255',
            'latitude'    => 'nullable|numeric|between:-90,90',
            'longitude'   => 'nullable|numeric|between:-180,180',
            'bedrooms'    => 'nullable|integer|min:0',
            'bathrooms'   => 'nullable|integer|min:0',
            // Validate selected services.
            'services'    => 'sometimes|array',
            'services.*'  => 'integer|distinct|exists:services,id',
            // Validate newly uploaded photos.
            'photos'          => 'sometimes|array',
            'photos.*'        => 'image|mimes:jpg,jpeg,png|max:5120',

            // Validate existing images selected for deletion.
            'deleted_images'   => 'sometimes|array',
            'deleted_images.*' => 'integer|distinct|exists:property_images,id',
        ]);

        // 3. Update basic property information.
        $property->title = $validated['title'];
        $property->description = $validated['description'];
        $property->category = $validated['category'];
        $property->price = $validated['price'];

        // 4. Update property location.
        $property->city = $validated['city'];
        $property->area = $validated['area'] ?? null;
        $property->address = $validated['address'];
        $property->latitude = $validated['latitude'] ?? null;
        $property->longitude = $validated['longitude'] ?? null;

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
            throw \Illuminate\Validation\ValidationException::withMessages([
                'deleted_images' => 'One or more selected images do not belong to this property.',
            ]);
        }

        // Check the maximum number of photos after updating.
        $remainingPhotos = $property->images()->count() - $imagesToDelete->count();
        $newPhotos = $request->file('photos', []);

        if ($remainingPhotos + count($newPhotos) > 10) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'photos' => 'A property can have a maximum of 10 photos.',
            ]);
        }

        // Upload new photos before saving the database changes.
        $uploadedPaths = [];

        try {
            foreach ($newPhotos as $photo) {
                $uploadedPaths[] = $photo->store('properties', 'public');
            }

            \Illuminate\Support\Facades\DB::transaction(function () use (
                $property,
                $validated,
                $imagesToDelete,
                $uploadedPaths
            ) {
                // Save property details.
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
        };

        // 7. Redirect after successful update.
        return redirect()
            ->route('owner.properties')
            ->with('success', 'Property updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //Find the property belonging to this owner
        $property = Property::where('agent_id', 1)
            ->findOrFail($id);
        $property->delete();
        return redirect()
            ->route('owner.properties')
            ->with('success', 'Property deleted successfully.');
    }
}
