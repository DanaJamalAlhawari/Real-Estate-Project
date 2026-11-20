<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\CommissionRate;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MockPaymentController extends Controller
{
    public function confirm(Request $request, Payment $payment)
    {
        abort_unless($request->user()?->role === 'tenant', 403);
        abort_unless((int) $payment->tenant_id === (int) $request->user()->id, 403);

        DB::transaction(function () use ($payment) {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            $booking = $payment->booking()->with('property')->firstOrFail();

            if ($booking->status !== 'confirmed') {
                throw ValidationException::withMessages([
                    'payment' => 'The booking must be confirmed before payment.',
                ]);
            }

            if ($payment->status !== 'successful') {
                $payment->update(['status' => 'successful', 'paid_at' => now()]);
            }

            // A room booking gets one commission, even if payment is retried.
            if (Commission::where('booking_id', $booking->id)->exists()) {
                return;
            }

            $agentId = $booking->property->agent_id;
            $rate = CommissionRate::where('agent_id', $agentId)
                ->where('active', true)
                ->latest('id')
                ->first();

            if (! $rate) {
                throw ValidationException::withMessages([
                    'payment' => 'The admin has not set an active commission rate.',
                ]);
            }

            $commissionAmount = round(
                (float) $payment->amount * (float) $rate->percentage / 100,
                2
            );

            Commission::create([
                'booking_id' => $booking->id,
                'payment_id' => $payment->id,
                'agent_id' => $agentId,
                'commission_rate' => $rate->percentage,
                'commission_amount' => $commissionAmount,
                'agent_net_amount' => round((float) $payment->amount - $commissionAmount, 2),
            ]);
        });

        return back()->with('success', 'Mock payment confirmed.');
    }
}
