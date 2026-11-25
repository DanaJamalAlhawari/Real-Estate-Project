<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Property;
use App\Models\PropertyDailyView;
use App\Models\RealEstateUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SeekerPropertyController extends Controller
{
    public function show(Request $request): View
    {
        $propertyId = filter_var(
            $request->query('property_id', $request->query('home')),
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]],
        );

        abort_if($propertyId === false, 404);

        $property = Property::discoverable()
            ->with([
                'images' => fn ($query) => $query
                    ->whereNull('room_id')
                    ->orderByDesc('is_primary')
                    ->orderBy('id'),
                'services',
                'agent.agentProfile',
                'rooms',
            ])
            ->findOrFail($propertyId);

        PropertyDailyView::incrementOrCreate([
            'property_id' => $property->id,
            'viewed_on' => today('Asia/Amman')->toDateString(),
        ], 'views');

        return view('seeker.property', [
            'property' => $property,
            'saved' => $request->user()->favoriteProperties()->whereKey($property->id)->exists(),
            'homes' => [],
            'reservedDates' => $property->bookings()->reserved()
                ->where('end_date', '>', today()->toDateString())
                ->orderBy('start_date')->get(['start_date', 'end_date'])
                ->map(fn ($booking) => [
                    $booking->start_date->toDateString(),
                    $booking->end_date->toDateString(),
                ]),
        ]);
    }

    public function startConversation(Request $request, Property $property): RedirectResponse
    {
        $tenant = $request->user();

        abort_unless($tenant instanceof RealEstateUser && $tenant->role === 'tenant', 403);

        abort_unless(
            $property->verification_status === 'verified'
                && in_array($property->status, ['available', 'partially_rented'], true),
            404,
        );

        $conversation = Conversation::unguarded(
            fn () => Conversation::firstOrCreate([
                'property_id' => $property->getKey(),
                'tenant_id' => $tenant->getKey(),
                'agent_id' => $property->agent_id,
            ]),
        );

        return redirect()->route('conversations.show', ['conversation' => $conversation]);
    }
}
