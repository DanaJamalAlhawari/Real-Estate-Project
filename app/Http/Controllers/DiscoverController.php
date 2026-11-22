<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DiscoverController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $request->validate([
            'category' => ['nullable', Rule::in(array_keys(Property::CATEGORIES))],
            'q' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'max' => 'nullable|numeric|min:0',
        ]);

        $query = Property::discoverable()->with(['images' => function ($q) {
            $q->orderByDesc('is_primary');
        }, 'rooms', 'services'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');

        // Apply filters Button
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('q')) {
            foreach (preg_split('/[\s,]+/u', trim($request->q), -1, PREG_SPLIT_NO_EMPTY) as $term) {
                $query->where(function ($q) use ($term) {
                    $q->where('area', 'like', '%'.$term.'%')
                        ->orWhere('city', 'like', '%'.$term.'%');
                });
            }
        }

        if ($request->filled('max')) {
            $query->where('price', '<=', $request->max);
        }

        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        $properties = $query->get();

        $favoriteIds = $request->user()->favoriteProperties()
            ->whereKey($properties->modelKeys())
            ->pluck('properties.id')->all();

        return view('seeker.search', compact('properties', 'favoriteIds'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
