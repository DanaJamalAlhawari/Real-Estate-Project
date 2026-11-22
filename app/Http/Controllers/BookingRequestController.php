<?php

namespace App\Http\Controllers;

use App\Events\BookingActivity;
use App\Models\Booking;
use App\Models\Property;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
        $data = $request->validate([
            'moveIn' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'moveOut' => ['required', 'date_format:Y-m-d', 'after:moveIn'],
        ]);

        $start = CarbonImmutable::parse($data['moveIn']);
        $end = CarbonImmutable::parse($data['moveOut']);

        $property = DB::transaction(function () use ($request, $property, $start, $end) {
            $property = Property::discoverable()->lockForUpdate()->findOrFail($property->id);
            abort_if($property->price === null, 422, 'This listing has no monthly price.');

            if ($property->bookings()->reserved()->overlapping($start->toDateString(), $end->toDateString())->exists()) {
                throw ValidationException::withMessages(['moveIn' => 'These dates overlap an existing booking.']);
            }

            $total = 0;
            for ($date = $start; $date < $end; $date = $segmentEnd) {
                $nextMonth = $date->startOfMonth()->addMonth();
                $segmentEnd = $nextMonth < $end ? $nextMonth : $end;
                $total += (float) $property->price * $date->diffInDays($segmentEnd) / $date->daysInMonth;
            }

            if ($total > 9999999999.99) {
                throw ValidationException::withMessages(['moveOut' => 'Choose a shorter stay.']);
            }

            $booking = new Booking;
            $booking->property()->associate($property);
            $booking->tenant()->associate($request->user());
            $booking->start_date = $start;
            $booking->end_date = $end;
            $booking->duration = $start->diffInDays($end);
            $booking->duration_unit = 'daily';
            $booking->total_price = round($total, 2);
            $booking->status = 'pending';
            $booking->save();

            return $property;
        });
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

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        $cancelled = DB::transaction(function () use ($request, $booking) {
            $booking = $request->user()->bookings()->lockForUpdate()->findOrFail($booking->id);

            if (! in_array($booking->status, ['pending', 'confirmed'], true)
                || $booking->payments()->whereIn('status', ['successful', 'refunded'])->exists()) {
                return false;
            }

            $booking->status = 'cancelled';

            return $booking->save();
        });

        if (! $cancelled) {
            return to_route('seeker.bookings')->with('error', 'Only unpaid booking requests can be cancelled online.');
        }

        rescue(fn () => BookingActivity::dispatch(
            (int) $booking->property->agent_id,
            'A booking for '.$booking->property->title.' was cancelled by the tenant.',
            route('owner.requests', [], false),
        ));

        return to_route('seeker.bookings')->with('success', 'Booking cancelled.');
    }

    public function reject(Request $request, Booking $booking)
    {
        return $this->decide($request, $booking, 'cancelled', 'Booking rejected successfully.');
    }

    private function decide(Request $request, Booking $booking, string $status, string $message)
    {
        $error = DB::transaction(function () use ($request, $booking, $status) {
            $property = $request->user()->properties()->lockForUpdate()->findOrFail($booking->property_id);
            $booking = $property->bookings()->lockForUpdate()->findOrFail($booking->id);

            if ($booking->status !== 'pending') {
                return 'This booking is no longer pending.';
            }
            if ($status === 'confirmed') {
                if ($property->verification_status !== 'verified') {
                    return 'This property needs admin approval before accepting bookings.';
                }
                if ($property->bookings()->reserved()->overlapping(
                    $booking->start_date->toDateString(), $booking->end_date->toDateString()
                )->exists()) {
                    return 'These dates overlap an existing booking.';
                }
                $booking->approved_commission_rate = $property->approved_commission_rate
                    ?? config('commissions.category_rates.'.$property->category);
            }

            $booking->status = $status;
            $booking->save();

            return null;
        });

        if ($error !== null) {
            return redirect()->route('owner.requests')->with('error', $error);
        }

        rescue(fn () => BookingActivity::dispatch(
            (int) $booking->tenant_id,
            $status === 'confirmed'
                ? 'Your booking for '.$booking->property->title.' was accepted. You can now pay.'
                : 'Your booking request was declined.',
            $status === 'confirmed'
                ? route('payments.mock.show', $booking, false)
                : route('seeker.bookings', [], false),
        ));

        return redirect()->route('owner.requests')->with('success', $message);
    }

    private function forAgent(Request $request)
    {
        return Booking::whereHas('property', fn ($query) => $query->where('agent_id', $request->user()->id));
    }
}
