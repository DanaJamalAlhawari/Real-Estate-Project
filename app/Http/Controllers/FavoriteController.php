<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Property;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {

    abort_unless($request->user()->role === 'tenant', 403);

        $favorites = $request->user()->favoriteProperties()->get([
                'properties.id',
                'properties.title',
                'properties.price',
                'properties.area',
            ]);

        return view('seeker.favorites', [
            'favorites' => $favorites,
        ]);
    }

public function store(Request $request, Property $property)
{
    $user = $request->user();

    abort_unless($user->role === 'tenant', 403);

    abort_unless(
        $property->status !== 'hidden'
        && $property->verification_status === 'verified',
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