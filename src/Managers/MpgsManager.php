<?php

namespace WyChoong\Mpgs\Managers;

use Closure;
use Illuminate\Support\Facades\Log;
use Lunar\Models\Cart;
use WyChoong\Mpgs\Clients\Mpgs;

/**
 * here to have Lunar logic
 */
class MpgsManager
{
    protected static Closure $initiateCheckoutUsing;

    protected static ?Closure $notifyUsing = null;

    public function setupClientUsing(Closure|array $setupUsing)
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

    public function createIntent(Cart $cart)
    {
        $meta = (array) $cart->meta;

        $result = $this->buildIntent(
            $cart->total->value / $cart->currency->factor,
            $cart->currency->code,
            $cart
        );

        $response = $result['response'];
        $orderId = $result['order_id'];

        if ($response->result !== 'SUCCESS') {
            return [
                'error' => true,
            ];
        }

        if (!$meta) {
            $cart->update([
                'meta' => [
                    'checkout_session' => $response->session->id,
                    'success_indicator' => $response->successIndicator,
                    'order_id' => $orderId,
                ],
            ]);
        } else {
            $meta['checkout_session'] = $response->session->id;
            $meta['success_indicator'] = $response->successIndicator;
            $meta['order_id'] = $orderId;
            $cart->meta = $meta;
            $cart->save();
        }

        return $response->session;
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
            'response' => Mpgs::initiateCheckout($data),
        ];
    }
}
