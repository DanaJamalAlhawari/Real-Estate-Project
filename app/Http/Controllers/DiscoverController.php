<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Property;
use App\Models\RealEstateUser;

class DiscoverController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        $request->validate([
            'category' => 'nullable|in:student_apartment,apartment,vacation',
            'q' => 'nullable|string|max:255',
            'max' => 'nullable|numeric|min:0',
        ]);

        $query = Property::with(['images' => function ($q) {
            $q->orderByDesc('is_primary');
        }, 'rooms', 'services']);

        // Apply filters Button
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('area', 'like', '%' . $request->q . '%')
                    ->orWhere('city', 'like', '%' . $request->q . '%');
            });
        }

        if ($request->filled('max')) {
            $query->where('price', '<=', $request->max);
        }

        $properties = $query->get();

        $user = $request->user();

        $favorites = $user instanceof RealEstateUser && $user->role === 'tenant'
            ? $user->favoriteProperties()->with(['images', 'rooms', 'services'])->get()
            : collect();

        return view('seeker.search', [
            'properties' => $properties,
            'favorites' => $favorites,
            'favoritesCount' => $favorites->count(),
            'favoriteIds' => $favorites->pluck('id')->all(),
        ]);
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
