<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;

class BookingRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bookings = Booking::whereHas('property', function ($query) {
            $query->where('agent_id', 1);
        })->with('property', 'tenant')->get();
        return view('owner.requests', ['bookings' => $bookings]);
    }

    public function accept(Booking $booking)
    {
        $booking = Booking::whereHas('property', function ($query) {
            $query->where('agent_id', 1);
        })->where('id', $booking->id)->firstOrFail();

        if ($booking->status !== 'pending') {
            return redirect()->route('owner.requests')->with('error', 'This booking cannot be accepted.');
        }
        $booking->status = 'confirmed';
        $booking->save();
        return redirect()->route('owner.requests')->with('success', 'Booking accepted successfully.');
    }

    public function reject(Booking $booking)
    {
        $booking = Booking::whereHas('property', function ($query) {
            $query->where('agent_id', 1);
        })->where('id', $booking->id)->firstOrFail();

        if ($booking->status !== 'pending') {
            return redirect()->route('owner.requests')->with('error', 'This booking cannot be rejected.');
        }

        $booking->status = 'cancelled';
        $booking->save();
        return redirect()->route('owner.requests')->with('success', 'Booking rejected successfully.');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
