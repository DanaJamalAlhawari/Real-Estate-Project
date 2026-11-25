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
        $filters = $request->validate([
            'category' => ['nullable', Rule::in(array_keys(Property::CATEGORIES))],
            'q' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'search_scope' => 'nullable|in:title_area',
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

        if (($filters['search_scope'] ?? null) === 'title_area') {
            $query->matchingTitleOrArea($filters['q'] ?? '');
        } else {
            $query->matchingSearch($filters['q'] ?? '');
        }
        $query->matchingTitle($filters['name'] ?? '');

        if ($request->filled('max')) {
            $query->where('price', '<=', $request->max);
        }

        if ($request->filled('city')) {
            $query->inCity($request->city);
        }

        $properties = $query->latest()->orderByDesc('properties.id')->paginate(config('app.properties_per_page'))->withQueryString();

        $favoriteIds = $request->user()->favoriteProperties()
            ->whereKey($properties->getCollection()->modelKeys())
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
