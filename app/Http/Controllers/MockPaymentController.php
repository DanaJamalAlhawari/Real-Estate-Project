<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MockPaymentController extends Controller
{
    private const METHODS = [
        'credit_card' => 'Credit card',
        'paypal' => 'PayPal',
        'bank_transfer' => 'Bank transfer',
        'cash' => 'Cash',
    ];

    public function cart(Request $request): View
    {
        $bookings = $request->user()->bookings()->where('status', 'confirmed')
            ->whereDoesntHave('payments', fn ($query) => $query->whereIn('status', ['successful', 'refunded']))
            ->orderBy('id');
        $checkoutBookings = (clone $bookings)->pluck('total_price', 'id');

        return view('seeker.cart', [
            'bookings' => $bookings->with('property')->paginate(config('app.pagination_per_page'))->withQueryString(),
            'checkoutBookingIds' => $checkoutBookings->keys(),
            'total' => $checkoutBookings->sum(fn ($price) => (int) round((float) $price * 100)) / 100,
        ]);
    }

    public function payCart(Request $request): RedirectResponse
    {
        $bookingIds = $this->validateCheckout($request, [
            'bookings' => ['required', 'array', 'list'],
            'bookings.*' => ['required', 'integer', 'distinct'],
        ])['bookings'];

        DB::transaction(function () use ($request, $bookingIds) {
            $bookings = $request->user()->bookings()->whereKey(array_map('intval', $bookingIds))
                ->with('property')->orderBy('id')->lockForUpdate()->get();
            abort_unless($bookings->count() === count($bookingIds), 404);

            foreach ($bookings as $booking) {
                if ($booking->status !== 'confirmed' && ! $booking->payments()->where('status', 'successful')->exists()) {
                    throw ValidationException::withMessages(['payment' => __('A booking in your cart changed. Review the updated cart and try again.')]);
                }

                $this->recordPayment($booking);
            }
        }, 3);

        return to_route('seeker.bookings')->with('success', __('Demo checkout complete. Your receipts are available below.'));
    }

    public function show(Request $request, Booking $booking): View
    {
        $this->authorizeTenant($request, $booking);
        $booking->load(['property', 'commission']);
        $payment = $booking->payments()->where('tenant_id', $booking->tenant_id)
            ->where('status', 'successful')->latest('id')->first();
        abort_unless($booking->status === 'confirmed' || $payment, 403);

        return view('seeker.payment', ['booking' => $booking, 'payment' => $payment, 'methods' => self::METHODS]);
    }

    public function pay(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorizeTenant($request, $booking);

        return $this->complete($request, $booking);
    }

    public function confirm(Request $request, Payment $payment): RedirectResponse
    {
        $booking = $payment->booking()->firstOrFail();
        $this->authorizeTenant($request, $booking);
        abort_unless((int) $payment->tenant_id === (int) $booking->tenant_id, 403);
        abort_unless(in_array($payment->status, ['pending', 'successful'], true), 409);

        return $this->complete($request, $booking);
    }

    private function validateCheckout(Request $request, array $rules = []): array
    {
        return $request->validate($rules + [
            'card_number' => ['bail', 'required', 'string', 'regex:/\A4242(?:[ -]?4242){3}\z/'],
            'card_expiry' => ['bail', 'required', 'string', 'in:12/30'],
            'card_cvv' => ['bail', 'required', 'string', 'in:123'],
        ], [
            'card_number.regex' => __('Enter the demo card number 4242 4242 4242 4242.'),
            'card_expiry.in' => __('Enter the demo expiration date 12/30.'),
            'card_cvv.in' => __('Enter the demo security code 123.'),
        ], [
            'card_number' => 'card number',
            'card_expiry' => 'expiration date',
            'card_cvv' => 'security code',
        ]);
    }

    private function complete(Request $request, Booking $booking): RedirectResponse
    {
        $this->validateCheckout($request);

        DB::transaction(function () use ($request, $booking) {
            $booking = Booking::with('property')->lockForUpdate()->findOrFail($booking->id);
            $this->authorizeTenant($request, $booking);

            $this->recordPayment($booking);
        }, 3);

        return redirect()->route('payments.mock.show', $booking);
    }

    private function recordPayment(Booking $booking): void
    {
        $successfulPayment = $booking->payments()->where('status', 'successful')->first();
        if ($successfulPayment) {
            abort_unless((int) $successfulPayment->tenant_id === (int) $booking->tenant_id, 409);

            return;
        }
        abort_unless($booking->status === 'confirmed', 403);

        if ($booking->commission()->exists() || $booking->payments()->where('status', 'refunded')->exists()) {
            throw ValidationException::withMessages(['payment' => __('An existing payment record needs review before checkout.')]);
        }

        $rate = $booking->approved_commission_rate
            ?? config('commissions.category_rates.'.$booking->property->category);

        if ($rate === null) {
            throw ValidationException::withMessages(['payment' => __('No commission rate is available for this property.')]);
        }

        $paidCents = (int) round((float) $booking->total_price * 100);
        $rateBasisPoints = (int) round((float) $rate * 100);
        $commissionCents = intdiv($paidCents * $rateBasisPoints + 5000, 10000);

        $payment = $booking->payments()->where('tenant_id', $booking->tenant_id)
            ->where('status', 'pending')->oldest('id')->first() ?? $booking->payments()->make();
        $payment->fill([
            'tenant_id' => $booking->tenant_id,
            'amount' => $paidCents / 100,
            'payment_method' => 'credit_card',
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
    }

    private function authorizeTenant(Request $request, Booking $booking): void
    {
        abort_unless($request->user()?->role === 'tenant', 403);
        abort_unless((int) $booking->tenant_id === (int) $request->user()->id, 404);
    }
}
