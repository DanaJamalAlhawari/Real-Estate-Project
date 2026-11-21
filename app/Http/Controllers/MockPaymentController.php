<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MockPaymentController extends Controller
{
    private const METHODS = [
        'credit_card' => 'Credit card',
        'paypal' => 'PayPal',
        'bank_transfer' => 'Bank transfer',
        'cash' => 'Cash',
    ];

    public function show(Request $request, Booking $booking)
    {
        $this->authorizeTenant($request, $booking);
        $booking->load(['property', 'commission']);
        $payment = $booking->payments()->where('tenant_id', $booking->tenant_id)
            ->where('status', 'successful')->latest('id')->first();
        abort_unless($booking->status === 'confirmed' || $payment, 403);

        return view('seeker.payment', ['booking' => $booking, 'payment' => $payment, 'methods' => self::METHODS]);
    }

    public function pay(Request $request, Booking $booking)
    {
        $this->authorizeTenant($request, $booking);
        $data = $request->validate([
            'payment_method' => ['required', Rule::in(array_keys(self::METHODS))],
            'outcome' => ['required', 'in:success,failure'],
        ]);

        return $this->complete($request, $booking, $data['payment_method'], $data['outcome']);
    }

    public function confirm(Request $request, Payment $payment)
    {
        $booking = $payment->booking()->firstOrFail();
        $this->authorizeTenant($request, $booking);
        abort_unless((int) $payment->tenant_id === (int) $booking->tenant_id, 403);
        abort_unless(in_array($payment->status, ['pending', 'successful'], true), 409);

        return $this->complete($request, $booking, $payment->payment_method, 'success');
    }

    private function complete(Request $request, Booking $booking, string $method, string $outcome)
    {
        DB::transaction(function () use ($request, $booking, $method, $outcome) {
            $booking = Booking::with('property')->lockForUpdate()->findOrFail($booking->id);
            $this->authorizeTenant($request, $booking);

            $successfulPayment = $booking->payments()->where('status', 'successful')->first();
            if ($successfulPayment) {
                abort_unless((int) $successfulPayment->tenant_id === (int) $booking->tenant_id, 409);

                return;
            }
            abort_unless($booking->status === 'confirmed', 403);

            if ($outcome === 'failure') {
                throw ValidationException::withMessages(['payment' => 'Mock payment failed. You can try again.']);
            }
            if ($booking->commission()->exists() || $booking->payments()->where('status', 'refunded')->exists()) {
                throw ValidationException::withMessages(['payment' => 'An existing payment record needs review before checkout.']);
            }

            $rate = $booking->approved_commission_rate
                ?? config('commissions.category_rates.'.$booking->property->category);

            if ($rate === null) {
                throw ValidationException::withMessages(['payment' => 'No commission rate is available for this property.']);
            }

            $paidCents = (int) round((float) $booking->total_price * 100);
            $rateBasisPoints = (int) round((float) $rate * 100);
            $commissionCents = intdiv($paidCents * $rateBasisPoints + 5000, 10000);

            $payment = $booking->payments()->where('tenant_id', $booking->tenant_id)
                ->where('status', 'pending')->oldest('id')->first() ?? $booking->payments()->make();
            $payment->fill([
                'tenant_id' => $booking->tenant_id,
                'amount' => $paidCents / 100,
                'payment_method' => $method,
                'transaction_id' => 'mock-'.Str::uuid(),
                'status' => 'successful',
                'paid_at' => now(),
            ])->save();

            $booking->commission()->create([
                'payment_id' => $payment->id,
                'agent_id' => $booking->property->agent_id,
                'commission_rate' => $rate,
                'commission_amount' => $commissionCents / 100,
                'agent_net_amount' => ($paidCents - $commissionCents) / 100,
                'status' => 'pending',
            ]);
        });

        return redirect()->route('payments.mock.show', $booking);
    }

    private function authorizeTenant(Request $request, Booking $booking): void
    {
        abort_unless($request->user()?->role === 'tenant', 403);
        abort_unless((int) $booking->tenant_id === (int) $request->user()->id, 404);
    }
}
