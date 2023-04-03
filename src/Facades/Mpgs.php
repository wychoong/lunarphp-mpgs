<?php

namespace WyChoong\Mpgs\Facades;

use WyChoong\Mpgs\Contracts\LunarMpgsInterface;
use Illuminate\Support\Facades\Facade;

/**
 * @see \WyChoong\Mpgs\MpgsPaymentType
 */
class Mpgs extends Facade
{
    protected static function getFacadeAccessor()
    {
        return LunarMpgsInterface::class;
    }
}
