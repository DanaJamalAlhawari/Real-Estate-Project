<?php

namespace App\Http\Controllers;

use App\Models\DemoHome;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PageController extends Controller
{
    public function show(string $page): View
    {
        $homes = in_array($page, ['home.index', 'seeker.search', 'seeker.property'], true)
            ? DemoHome::all()
            : [];

        $data = ['homes' => $homes];
        if ($page === 'seeker.search') {
            $ordered = collect($homes)->groupBy(fn (array $home) => (int) $home['featured']);
            $data['featuredHomes'] = $ordered->get(1, collect())->concat($ordered->get(0, collect()));
            $data['defaultFavorites'] = $data['featuredHomes']->whereIn('id', [1, 4]);
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
