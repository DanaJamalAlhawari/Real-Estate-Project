<?php

namespace App\Http\Controllers;

use App\Events\BookingActivity;
use App\Models\Booking;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class BookingRequestController extends Controller
{
    public function index(Request $request)
    {
        $bookings = $this->forAgent($request)->with(['property', 'tenant.tenantProfile'])->latest()->get();

        return view('owner.requests', compact('bookings'));
    }

    public function tenantBookings(Request $request)
    {
        $bookings = $request->user()->bookings()->with(['property', 'payments'])->latest()->get();

        return view('seeker.bookings', compact('bookings'));
    }

    public function store(Request $request, Property $property)
    {
        abort_unless(Property::discoverable()->whereKey($property->id)->exists(), 404);
        abort_if($property->price === null, 422, 'This listing has no monthly price.');

        $data = $request->validate([
            'moveIn' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'duration' => ['required', 'integer', 'in:3,6,12'],
        ]);

        $booking = new Booking();
        $booking->property()->associate($property);
        $booking->tenant()->associate($request->user());
        $booking->start_date = $data['moveIn'];
        $booking->end_date = Carbon::parse($data['moveIn'])->addMonthsNoOverflow((int) $data['duration']);
        $booking->duration = $data['duration'];
        $booking->duration_unit = 'monthly';
        $booking->total_price = (int) round((float) $property->price * 100) * (int) $data['duration'] / 100;
        $booking->status = 'pending';
        $booking->save();
        rescue(fn () => BookingActivity::dispatch(
            (int) $property->agent_id,
            'New booking request for '.$property->title,
            route('owner.requests', [], false),
        ));

        return redirect()->route('seeker.bookings')->with('success', 'Booking request sent to the property owner.');
    }

    public function accept(Request $request, Booking $booking)
    {
        return $this->decide($request, $booking, 'confirmed', 'Booking accepted successfully.');
    }

    public function reject(Request $request, Booking $booking)
    {
        return $this->decide($request, $booking, 'cancelled', 'Booking rejected successfully.');
    }

    private function decide(Request $request, Booking $booking, string $status, string $message)
    {
        $owned = $this->forAgent($request)->whereKey($booking->id);
        abort_unless((clone $owned)->exists(), 404);

        if (!$owned->where('status', 'pending')->update(['status' => $status])) {
            return redirect()->route('owner.requests')->with('error', 'This booking is no longer pending.');
        }

        rescue(fn () => BookingActivity::dispatch(
            (int) $booking->tenant_id,
            $status === 'confirmed' ? 'Your booking request was accepted.' : 'Your booking request was declined.',
            route('seeker.bookings', [], false),
        ));

        return redirect()->route('owner.requests')->with('success', $message);
    }

    private function forAgent(Request $request)
    {
        return Booking::whereHas('property', fn ($query) => $query->where('agent_id', $request->user()->id));
    }
}
