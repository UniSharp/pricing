<?php

namespace UniSharp\Pricing;

use UniSharp\Cart\CartItem;
use Illuminate\Pipeline\Pipeline;
use UniSharp\Pricing\Tests\Fixtures\TestModule;
use UniSharp\Cart\CartItemCollection as Collection;

class Pricing
{
    protected $items = null;
    protected $modules = [];
    protected $pipeline;
    protected $fees = [];
    protected $deductions = [];
    protected $infos = [];

    public function __construct(Pipeline $pipeline, array $modules)
    {
        $this->pipeline = $pipeline;
        $this->modules = $modules;
    }

    public function setItems(Collection $items)
    {
        $this->items = $items;

        return $this;
    }

    public function getItems()
    {
        return $this->items;
    }

    public function getOriginalTotal()
    {
        return $this->items->sum('price');
    }

    public function apply($module, $params = null)
    {
        if ($params) {
            $this->infos[$module] = $params;
        }

        return $this->pipeline
            ->send($this)
            ->through($module)
            ->then(function ($pricing) {
                return $pricing;
            });
    }

    public function setModules(array $modules)
    {
        $this->modules = $modules;

        return $this;
    }

    public function getModules()
    {
        return $this->modules;
    }


    public function addFee($value, $moduleName = null)
    {
        $moduleName = $moduleName ?: debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[1]['class'];
        $this->fees[$moduleName] = $value;

        return $this;
    }

    public function addDeduction($value, $moduleName = null)
    {
        $moduleName = $moduleName ?: debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[1]['class'];
        $this->deductions[$moduleName] = $value;

        return $this;
    }
}