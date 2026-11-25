<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class VerificationController extends Controller
{
    public function ownerProperties(Request $request)
    {
        abort_unless($request->user()?->role === 'agent', 403);

        return app(PropertyController::class)->index($request);
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

        $filters = $request->validate([
            'q' => 'nullable|string|max:255',
            'status' => 'nullable|in:pending,verified,rejected',
        ]);
        $properties = Property::where('ownership_deed_url', 'like', 'verifications/%')
            ->matchingSearch($filters['q'] ?? '')->latest()->orderByDesc('id')->get()
            ->filter(fn (Property $property) => Storage::disk('local')->exists($property->ownership_deed_url));

        $selectedProperty = $properties->find($request->integer('property'));
        $activeStatus = $filters['status'] ?? $selectedProperty?->verification_status ?? 'pending';
        $pendingCount = $properties->where('verification_status', 'pending')->count();
        $activeProperties = $properties->where('verification_status', $activeStatus);
        $page = Paginator::resolveCurrentPage();
        $perPage = config('app.properties_per_page');
        $queue = new LengthAwarePaginator($activeProperties->forPage($page, $perPage), $activeProperties->count(), $perPage, $page, ['path' => $request->url()]);
        $queue->appends(['status' => $activeStatus] + $request->only('q'));
        $selectedProperty = $selectedProperty?->verification_status === $activeStatus ? $selectedProperty : $queue->first();

        return view('admin.verifications', compact('queue', 'activeStatus', 'pendingCount', 'selectedProperty'));
    }

    public function store(Request $request)
    {
        abort_unless($request->user()?->role === 'agent', 403);

        $request->validate([
            'property_id' => 'required|integer',
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $property = $request->user()->properties()->findOrFail($request->integer('property_id'));

        $path = $request->file('document')->store('verifications', 'local');
        abort_if($path === false, 500, __('Could not store the ownership document.'));

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

        $data = $request->validate([
            'decision' => 'required|in:approve,reject',
            'note' => 'nullable|string',
            'commission_rate' => 'exclude_unless:decision,approve|nullable|numeric|decimal:0,2|min:0|max:100',
        ]);

        DB::transaction(function () use ($request, $verification, $data) {
            $property = Property::lockForUpdate()->findOrFail($verification);

            abort_unless($property->verification_status === 'pending', 403);
            abort_unless(
                str_starts_with($property->ownership_deed_url ?? '', 'verifications/')
                    && Storage::disk('local')->exists($property->ownership_deed_url),
                403,
            );

            $approved = $data['decision'] === 'approve';
            $property->approved_commission_rate = $approved
                ? ($data['commission_rate'] ?? config('commissions.category_rates.'.$property->category))
                : null;
            $property->verification_status = $approved ? 'verified' : 'rejected';
            $property->verified_at = $approved ? now() : null;
            $property->reviewed_by = $request->user()->id;
            $property->admin_notes = $data['note'] ?? null;
            $property->save();
        });

        return redirect()->route('admin.verifications')
            ->with('success', 'Verification updated');
    }
}
