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

                    if(resp.error){
                        console.log('error')

                        return
                    }

                    if(resp.session){
                        this.processing = true

                        sessionStorage.removeItem('HostedCheckout_sessionId')

                        session = resp.session

                        Checkout.configure({
                            session:{
                                id: session.id,
                                version: session.version,
                            }
                        });

                        Checkout.showEmbeddedPage('#mpgs-target');
                    }
                })
        }
    }"
    @checkout='checkout'
>
    <x-lunar-mpgs::embed-container />
    <x-lunar-mpgs::checkout-button />
</div>
