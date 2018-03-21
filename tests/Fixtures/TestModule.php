<?php

namespace UniSharp\Pricing\Tests\Fixtures;

use Closure;
use UniSharp\Pricing\Pricing;
use UniSharp\Pricing\ModuleContract;
use Illuminate\Contracts\Pipeline\Pipeline;

class TestModule implements ModuleContract
{
    const FEE = 99;
    const DEDUCTION = 88;
    const LOG = 'log';

    public function handle(Pricing $pricing, Closure $next)
    {
        $pricing->addFee(static::FEE);
        $pricing->addDeduction(static::DEDUCTION);
        $pricing->writeModuleLog(static::LOG);

        return $next($pricing);
    }

    public function finish(Pricing $pricing)
    {
        //
    }
}