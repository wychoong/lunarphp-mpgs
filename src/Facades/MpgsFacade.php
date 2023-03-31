<?php

namespace WyChoong\Mpgs\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \WyChoong\Mpgs\MpgsPaymentType
 */
class MpgsFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \WyChoong\Mpgs\MpgsPaymentType::class;
    }
}
