<?php

namespace UniSharp\Pricing\Tests;

use Mockery as m;
use UniSharp\Cart\CartItem;
use UniSharp\Pricing\Pricing;
use PHPUnit\Framework\TestCase;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Container\Container;
use UniSharp\Cart\CartItemCollection;
use UniSharp\Pricing\Tests\Fixtures\TestModule;
use UniSharp\Pricing\Exceptions\InvalidModuleException;

class PricingTest extends TestCase
{
    protected $modules = [
        TestModule::class
    ];

    public function testSetItems()
    {
        $items = m::mock(CartItemCollection::class);

        $pricing = $this->getPricing();
        $pricing->setItems($items);

        $this->assertEquals($items, $pricing->getItems());
    }

    public function testGetOriginalTotal()
    {
        $items = m::mock(CartItemCollection::class);
        $items->shouldReceive('sum')
            ->with('price')
            ->andReturn($total = 10);

        $pricing = $this->getPricing($items);
        $pricing->getOriginalTotal();

        $this->assertEquals($total, $pricing->getOriginalTotal());
    }

    public function testInvalidModuleException()
    {
        $this->expectException(InvalidModuleException::class);

        $pricing = $this->getPricing()->setModules([]);
        $pricing->apply(TestModule::class);
    }

    public function testAddFee()
    {
        $pricing = $this->getPricing();
        $pricing = $pricing->addFee($fee = 10, TestModule::class);

        $this->assertEquals($fee, $pricing->getFee(TestModule::class));
    }

    public function testAddDeduction()
    {
        $pricing = $this->getPricing();
        $pricing = $pricing->addDeduction($fee = 10, TestModule::class);

        $this->assertEquals($fee, $pricing->getDeduction(TestModule::class));
    }

    public function testWriteModuleLog()
    {
        $pricing = $this->getPricing();
        $pricing = $pricing->writeModuleLog($log = 'log', TestModule::class);

        $this->assertEquals($log, $pricing->getModuleLog(TestModule::class));
    }

    public function testApply()
    {
        $items = m::mock(CartItemCollection::class);

        $pricing = $this->getPricing($items);
        $pricing = $pricing->apply(TestModule::class);

        $this->assertEquals(TestModule::DEDUCTION, $pricing->getDeduction(TestModule::class));
        $this->assertEquals(TestModule::FEE, $pricing->getFee(TestModule::class));
        $this->assertEquals(TestModule::LOG, $pricing->getModuleLog(TestModule::class));
    }

    public function testModuleInfo()
    {
        $items = m::mock(CartItemCollection::class);

        $pricing = $this->getPricing($items);
        $pricing = $pricing->with([
            TestModule::class => $foo = 'bar'
        ]);

        $this->assertEquals($foo, $pricing->getModuleInfo(TestModule::class));
    }

    protected function getPricing($items = null)
    {
        $pipeline = new Pipeline(new Container);
        $pricing = new Pricing($pipeline, $this->modules);

        if ($items) {
            $pricing = $pricing->setItems($items);
        }

        return $pricing;
    }

    // protected function getItems($number = 1)
    // {
    //     $collection = new CartItemCollection([]);

    //     for ($i = 0; $i < $number; $i++) {
    //         $collection->push(new CartItem([
    //             'id' => uniqid(),
    //             'price' => random_int(1, 100),
    //             'quantity' => random_int(1, 5)
    //         ]));
    //     }

    //     return $collection;
    // }
}