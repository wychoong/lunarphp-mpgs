<?php

namespace WyChoong\Mpgs\Commands;

use Illuminate\Console\Command;

class MpgsPaymentTypeCommand extends Command
{
    public $signature = 'lunarphp-mpgs';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
