<?php

namespace UniSharp\Pricing;

use Closure;
use UniSharp\Pricing\Pricing;

interface ModuleContract
{
    /**
     * Handle the main logic of module (addFee/addDeduction/writeLog).
     *
     * @param  Pricing  $pricing
     * @param  Closure  $closure
     */
    public function handle(Pricing $pricing, Closure $closure);

    /**
     * Handle logics after executing the module.
     *
     * @param  Pricing  $pricing
     */
    public function finish(Pricing $pricing);
}