<?php

namespace WyChoong\Mpgs\Managers;

use Closure;
use Illuminate\Support\Facades\Log;
use Lunar\Models\Cart;
use WyChoong\Mpgs\Clients\Mpgs;

/**
 *
 * here to have Lunar logic
 */

class MpgsManager
{
    protected static Closure $initiateCheckoutUsing;

    protected static ?Closure $notifyUsing = null;

    public function setupClientUsing(Closure | array $setupUsing)
    {
        Mpgs::setupClientUsing($setupUsing);
    }

    public static function notifyUsing(Closure $notifyUsing)
    {
        static::$notifyUsing = $notifyUsing;
    }

    public static function notify($message, $error = false, $data = null)
    {
        if (blank(static::$notifyUsing)) {
            if ($error) {
                Log::error($message, ['data' => $data]);
            } else {
                Log::info($message, ['data' => $data]);
            }

            return;
        }

        app()->call(static::$notifyUsing, [
            'message' => $message,
            'data' => $data,
            'error' => $error,
        ]);
    }

    public function createIntent(Cart $cart): string
    {
        $meta = (array) $cart->meta;

        $result = $this->buildIntent(
            $cart->total->value / $cart->currency->factor,
            $cart->currency->code,
            $cart
        );

        $paymentIntent = $result['intent'];
        $orderId = $result['order_id'];

        if (!$meta) {
            $cart->update([
                'meta' => [
                    'payment_intent' => $paymentIntent->session->id,
                    'order_id' => $orderId,
                ],
            ]);
        } else {
            $meta['payment_intent'] = $paymentIntent->session->id;
            $meta['order_id'] = $orderId;
            $cart->meta = $meta;
            $cart->save();
        }

        return $paymentIntent->session->id;
    }

    public static function initiateCheckoutUsing(Closure $initiateCheckoutUsing)
    {
        static::$initiateCheckoutUsing = $initiateCheckoutUsing;
    }

    protected function buildIntent($value, $currencyCode, $cart)
    {
        $data = app()->call(static::$initiateCheckoutUsing, [
            'amount' => $value,
            'currency' => $currencyCode,
            'cart' => $cart,
        ]);

        return [
            'order_id' => $data['order']['id'] ?? null,
            'intent' => Mpgs::initiateCheckout($data),
        ];
    }
}
