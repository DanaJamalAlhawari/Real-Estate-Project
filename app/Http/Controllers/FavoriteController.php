<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    public function index(Request $request): View
    {

        abort_unless($request->user()->role === 'tenant', 403);

        $favorites = $request->user()->favoriteProperties()
            ->discoverable()
            ->with('images')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->get();

        return view('seeker.favorites', [
            'favorites' => $favorites,
        ]);
    }

    public function store(Request $request, Property $property)
    {
        $user = $request->user();

        abort_unless($user->role === 'tenant', 403);

        abort_unless(
            Property::discoverable()->whereKey($property->id)->exists(),
            404
        );

        $user->favoriteProperties()->syncWithoutDetaching([$property->id]);

        return back();
    }

    public function destroy(Request $request, Property $property)
    {
        $user = $request->user();

        abort_unless($user->role === 'tenant', 403);

        $user->favoriteProperties()->detach($property->id);

        return back();
    }
}
