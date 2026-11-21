<?php

namespace App\Http\Controllers;

use App\Models\DemoHome;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\RealEstateUser;
use Illuminate\Support\Facades\Auth;
use App\Models\Property;

final class PageController extends Controller
{
    public function show(Request $request, string $page): View
    {
        $homes = in_array($page, ['home.index', 'seeker.search', 'seeker.property'], true)
            ? DemoHome::all()
            : [];

        $data = ['homes' => $homes];

        if ($page === 'seeker.search') {

            // Dana's code: get the signed-in user
            /** @var RealEstateUser|null $user */
            $user = Auth::user();

            $ordered = collect($homes)->groupBy(fn (array $home) => (int) $home['featured']);
            $data['featuredHomes'] = $ordered->get(1, collect())->concat($ordered->get(0, collect()));
            $data['defaultFavorites'] = $data['featuredHomes']->whereIn('id', [1, 4]);

            // Dana's code: get available, verified properties
            $data['properties'] = Property::where('status', 'available')
                ->where('verification_status', 'verified')
                ->with('images')
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->get();

            // Dana's code: get this user's favorites and favorite IDs
            $data['favorites'] = $user?->favoriteProperties()
                ->with('images')
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->get() ?? collect();

            $data['favoriteIds'] = $data['favorites']->pluck('id')->all();
            $data['favoritesCount'] = $data['favorites']->count();
        }

//dana added code 
        if ($page === 'seeker.property' && $request->filled('property_id')) {
    $property = Property::where('verification_status', 'verified')
        ->where('status', '!=', 'hidden')
        ->findOrFail($request->query('property_id'));

    $data['property'] = $property;
    //
}

        return view($page, $data);
    }

    public function legacy(Request $request, string $target): RedirectResponse
    {
        return redirect()->route($target, $request->query());
    }

    public function demoNotice(): RedirectResponse
    {
        return back();
    }
}
