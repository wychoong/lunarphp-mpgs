<div
    wire:key="mpgs-form"
    x-data="{
        initializing: false,
        processing: false,
        checkout(){
            this.initializing = true

            $wire.checkout()
                .then(resp => {
                    this.initializing = false

                    if(resp.session){
                        this.processing = true

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
    @mpgs-complete.window='$wire.checkoutSuccess()'
>
    <x-lunar-mpgs::embed-container />
    <x-lunar-mpgs::checkout-button />
</div>
