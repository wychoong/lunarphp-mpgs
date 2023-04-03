<?php

namespace WyChoong\Mpgs\Facades;

use Illuminate\Support\Facades\Facade;
use WyChoong\Mpgs\Contracts\LunarMpgsInterface;

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
