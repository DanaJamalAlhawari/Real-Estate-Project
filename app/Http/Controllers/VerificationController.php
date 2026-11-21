<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VerificationController extends Controller
{
    public function ownerProperties(Request $request)
    {
        abort_unless($request->user()?->role === 'agent', 403);

        $ownedProperties = Property::where('agent_id', $request->user()->id)->get();

        return view('owner.properties', compact('ownedProperties'));
    }

    public function ownerEdit(Request $request, $property)
    {
        abort_unless($request->user()?->role === 'agent', 403);

        $property = Property::where('agent_id', $request->user()->id)->findOrFail($property);

        return view('owner.property-form', compact('property'));
    }

    public function index(Request $request)
    {
        abort_unless($request->user()?->role === 'admin', 403);

        $properties = Property::whereNotNull('ownership_deed_url')->get();

        $queues = $properties->groupBy('verification_status');
        $selectedProperty = $properties->find($request->integer('property'))
            ?? $queues->get('pending')?->first();

        return view('admin.verifications', compact('queues', 'selectedProperty'));
    }

    public function store(Request $request)
    {
        abort_unless($request->user()?->role === 'agent', 403);

        $request->validate([
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $property = Property::findOrFail($request->property_id);

        $path = $request->file('document')->store('verifications', 'local');
        abort_if($path === false, 500, 'Could not store the ownership document.');

        $property->ownership_deed_url = $path;
        $property->verification_status = 'pending';
        $property->reviewed_by = null;
        $property->verified_at = null;
        $property->admin_notes = null;

        $property->save();

        return redirect()->route('owner.properties.edit', $property)
            ->with('success', 'Document uploaded');
    }
    public function document(Request $request, $verification)
    {
        abort_unless($request->user()?->role === 'admin', 403);

        $property = Property::findOrFail($verification);
        $path = $property->ownership_deed_url;

        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path));
    }
    public function update(Request $request, $verification)
    {
        abort_unless($request->user()?->role === 'admin', 403);

        $request->validate([
            'decision' => 'required|in:approve,reject',
            'note' => 'nullable|string',
        ]);

        $property = Property::findOrFail($verification);

        abort_unless(
            $property->verification_status === 'pending'
            && $property->ownership_deed_url
            && Storage::disk('local')->exists($property->ownership_deed_url),
            403
        );

        $approved = $request->decision === 'approve';

        $property->verification_status = $approved ? 'verified' : 'rejected';
        $property->verified_at = $approved ? now() : null;
        $property->reviewed_by = $request->user()->id;
        $property->admin_notes = $request->note;
        $property->save();

        return redirect()->route('admin.verifications')
            ->with('success', 'Verification updated');
    }
}
