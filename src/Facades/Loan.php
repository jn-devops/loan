<?php

namespace Homeful\Loan\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Homeful\Loan\Loan2
 */
class Loan extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Homeful\Loan\Loan2::class;
    }
}
