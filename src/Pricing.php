<?php

namespace UniSharp\Pricing;

use UniSharp\Cart\CartItem;
use Illuminate\Pipeline\Pipeline;
use UniSharp\Pricing\Tests\Fixtures\TestModule;
use UniSharp\Cart\CartItemCollection as Collection;
use UniSharp\Pricing\Exceptions\InvalidModuleException;

class Pricing
{
    protected $items = null;
    protected $modules = [];
    protected $pipeline;
    protected $fees = [];
    protected $deductions = [];
    protected $infos = [];
    protected $logs = [];

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
        $this->checkModule($module);

        if ($params) {
            $this->applyModuleInfo($module, $params);
        }

        return $this->pipeline
            ->send($this)
            ->through($module)
            ->then(function ($pricing) {
                return $pricing;
            });
    }

    public function with(array $params)
    {
        foreach ($params as $key => $value) {
            $this->checkModule($key);
            $this->applyModuleInfo($key, $value);
        }

        return $this;
    }

    protected function applyModuleInfo($module, $params)
    {
        $this->infos[$module] = $params;

        return $this;
    }

    protected function checkModule($module)
    {
        if (! in_array($module, $this->modules)) {
            throw new InvalidModuleException('module not found in whitelist.');
        }
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
        $moduleName = $moduleName ?: $this->getCallerClass();
        $this->fees[$moduleName] = $value;

        return $this;
    }

    public function addDeduction($value, $moduleName = null)
    {
        $moduleName = $moduleName ?: $this->getCallerClass();
        $this->deductions[$moduleName] = $value;

        return $this;
    }

    public function writeModuleLog($value, $moduleName = null)
    {
        $moduleName = $moduleName ?: $this->getCallerClass();
        $this->logs[$moduleName] = $value;

        return $this;
    }

    public function getDeduction($module)
    {
        return $this->deductions[$module] ?? null;
    }

    public function getDeductions()
    {
        return $this->deductions;
    }

    public function getFee($module)
    {
        return $this->fees[$module] ?? null;
    }

    public function getFees()
    {
        return $this->fees;
    }

    public function getModuleInfo($module = null)
    {
        return $this->infos[$module ?? $this->getCallerClass()] ?? null;
    }

    public function getModuleLog($module = null)
    {
        return $this->logs[$module ?? $this->getCallerClass()] ?? null;
    }

    protected function getCallerClass()
    {
        return debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[2]['class'];
    }
}