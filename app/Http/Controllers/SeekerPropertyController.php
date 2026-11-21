<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\RealEstateUser;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Conversation;
use Illuminate\Http\RedirectResponse;


class SeekerPropertyController extends Controller
{
    public function show(Request $request): View
    {
        $propertyId = filter_var(
            $request->query('home'),
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]],
        );

        abort_if($propertyId === false, 404);

        $property = Property::query()
            ->with([
                'images' => fn($query) => $query
                    ->whereNull('room_id')
                    ->orderByDesc('is_primary')
                    ->orderBy('id'),
                'services',
                'agent.agentProfile',
                'rooms',
            ])
            ->whereIn('status', ['available', 'partially_rented'])
            ->where('verification_status', 'verified')
            ->findOrFail($propertyId);

        return view('seeker.property', [
            'property' => $property,
            'homes' => [],
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
            fn() => Conversation::firstOrCreate([
                'property_id' => $property->getKey(),
                'tenant_id' => $tenant->getKey(),
                'agent_id' => $property->agent_id,
            ]),
        );

        return redirect()->route('conversations.show', ['conversation' => $conversation]);
    }
}
