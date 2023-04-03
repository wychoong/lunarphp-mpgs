<?php

namespace WyChoong\Mpgs\Components;

use Livewire\Component;
use Lunar\Models\Cart;
use WyChoong\Mpgs\Facades\Mpgs as MpgsFacade;
use WyChoong\Mpgs\MpgsPaymentType;

class PaymentForm extends Component
{
    /**
     * The instance of the order.
     *
     * @var Order
     */
    public Cart $cart;

    /**
     * {@inheritDoc}
     */
    protected $listeners = [
        'cardDetailsSubmitted',
    ];

    public function checkout()
    {
        $this->cart->calculate();
        $intent = MpgsFacade::createIntent($this->cart);

        return [
            'session' => $intent,
        ];
    }

    public function checkoutSuccess()
    {
        $mpgs = (new MpgsPaymentType());
        $payment = $mpgs->cart($this->cart)->authorize();

        if ($payment->success) {
            $this->emit('mpgsPaymentSuccess');

            if (config('lunar-mpgs.route.payment-success')) {
                return redirect()->route(config('lunar-mpgs.route.payment-success'));
            }

            return;
        }

        $this->emit('mpgsPaymentFailed');

        if (config('lunar-mpgs.route.payment-failed')) {
            return redirect()->route(config('lunar-mpgs.route.payment-failed'));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function render()
    {
        return view('lunar-mpgs::components.payment-form');
    }
}
