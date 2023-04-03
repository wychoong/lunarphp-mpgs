<?php

namespace WyChoong\Mpgs;

use Exception;
use Illuminate\Support\Facades\DB;
use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Base\DataTransferObjects\PaymentCapture;
use Lunar\Base\DataTransferObjects\PaymentRefund;
use Lunar\Models\Currency;
use Lunar\Models\Transaction;
use Lunar\PaymentTypes\AbstractPayment;
use WyChoong\Mpgs\Clients\Mpgs;

class MpgsPaymentType extends AbstractPayment
{
    protected $orderResponse;

    /**
     * Authorize the payment for processing.
     */
    public function authorize(): PaymentAuthorize
    {
        if (! $this->order) {
            if (! $this->order = $this->cart->order) {
                $this->order = $this->cart->createOrder();
            }
        }

        if ($this->order->placed_at) {
            // Somethings gone wrong!
            return new PaymentAuthorize(
                success: false,
                message: 'This order has already been placed',
            );
        }

        $meta = (array) $this->cart->meta;

        $orderId = $meta['order_id'];
        $this->orderResponse = Mpgs::retrieveOrder($orderId);

        if ($this->orderResponse->result == 'SUCCESS' && $this->orderResponse->status == 'CAPTURED') {
            return $this->releaseSuccess();
        } else {
            return new PaymentAuthorize(
                success: false,
                message: 'Unable to verify payment',
            );
        }
    }

    /**
     * Capture a payment for a transaction.
     *
     * @param  int  $amount
     */
    public function capture(Transaction $transaction, $amount = 0): PaymentCapture
    {
        throw new Exception('MPGS Payment Capture not supported.');
    }

    /**
     * Refund a captured transaction
     *
     * @param  string|null  $notes
     */
    public function refund(Transaction $transaction, int $amount = 0, $notes = null): PaymentRefund
    {
        $transaction->order->transactions()->create([
            'success' => true,
            'type' => 'refund',
            'driver' => 'mpgs',
            'amount' => $amount,
            'reference' => 'offline',
            'status' => 'refund',
            'notes' => $notes,
            'card_type' => $transaction->card_type,
            'last_four' => $transaction->last_four,
        ]);

        return new PaymentRefund(
            success: true
        );
    }

    /**
     * Return a successfully released payment.
     */
    private function releaseSuccess(): PaymentAuthorize
    {
        DB::transaction(function () {
            $transactions = collect($this->orderResponse->transaction);
            $charge = $transactions->where('transaction.type', 'PAYMENT')->first() ?? $transactions->first();

            $this->order->update([
                'status' => $this->config['authorized'] ?? 'payment-received',
                'placed_at' => now()->parse($charge->timeOfRecord),
            ]);

            $card = $charge->sourceOfFunds->provided->card;

            $currency = Currency::whereCode($charge->transaction->currency)->firstOrFail();
            $amount = bcmul($charge->transaction->amount, $currency->factor);

            $transaction = [
                'success' => true,
                'type' => 'capture',
                'driver' => 'mpgs',
                'amount' => $amount,
                'reference' => $this->orderResponse->reference,
                'status' => $charge->transaction->authenticationStatus,
                'card_type' => $card->brand,
                'last_four' => substr($card->number, -4),
                'captured_at' => $charge->timeOfLastUpdate ? now()->parse($charge->timeOfLastUpdate) : null,
            ];

            $this->order->transactions()->create($transaction);
        });

        return new PaymentAuthorize(success: true);
    }
}
