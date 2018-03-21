<?php

namespace UniSharp\Pricing\Tests\Fixtures;

use Closure;
use Illuminate\Contracts\Pipeline\Pipeline;

class TestModule
{
    const FEE = 99;
    const DEDUCTION = 88;
    const LOG = 'log';

    public function handle($pricing, Closure $next)
    {
        $pricing->addFee(static::FEE);
        $pricing->addDeduction(static::DEDUCTION);
        $pricing->writeModuleLog(static::LOG);

        return $next($pricing);
    }
}