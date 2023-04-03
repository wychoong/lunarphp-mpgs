<div
    wire:key="mpgs-form"
    x-data="{
        checkout(){
            $wire.checkout()
                .then(resp => {
                    if(resp.session){
                        Checkout.configure({
                            session:{
                                id: resp.session
                            }
                        });

                        Checkout.showEmbeddedPage('#mpgs-target');
                    }
                })
        }
    }"
    @checkout='checkout'
    @mpgs-complete='$wire.checkoutSuccess()'
>
    <x-lunar-mpgs::embed-container />
    <x-lunar-mpgs::checkout-button />
</div>
