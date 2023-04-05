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
     */
    public Cart $cart;

    /**
     * {@inheritDoc}
     */
    protected $listeners = [
        'cardDetailsSubmitted',
    ];

    protected $queryString = [
        'resultIndicator',
    ];

    public ?string $resultIndicator = null;

    public function mount()
    {
        if ($this->resultIndicator) {
            return $this->checkoutSuccess();
        }
    }

    public function checkout()
    {
        $this->cart->calculate();
        $intent = MpgsFacade::createIntent($this->cart);

        if ($intent === false) {
            return false;
        }

        return [
            'session' => $intent,
        ];
    }

    protected function checkoutSuccess()
    {
        $mpgs = (new MpgsPaymentType());

        $payment = $mpgs->cart($this->cart)->withData([
            'resultIndicator' => $this->resultIndicator,
        ])->authorize();

        if ($payment->success) {
            $this->emit('mpgsPaymentSuccess');

            if (config('lunar-mpgs.route.payment-success')) {
                $this->shouldSkipRender = false; //# workaround with an issue with livewire

                return $this->redirectRoute(config('lunar-mpgs.route.payment-success'));
            }

            return;
        }

        $this->emit('mpgsPaymentFailed');

        if (config('lunar-mpgs.route.payment-failed')) {
            $this->shouldSkipRender = false; //# workaround with an issue with livewire

            return $this->redirectRoute(config('lunar-mpgs.route.payment-failed'));
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
