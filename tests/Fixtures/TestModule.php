<?php

namespace UniSharp\Pricing\Tests\Fixtures;

use Closure;
use Illuminate\Contracts\Pipeline\Pipeline;

class TestModule
{
    const FEE = 99;
    const DEDUCTION = 88;

    public function handle($pricing, Closure $next)
    {
        $pricing->addFee(99);
        $pricing->addDeduction(99);

        return $next($pricing);
    }
}