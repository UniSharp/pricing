<?php

namespace UniSharp\Pricing\Tests;

use Closure;
use Mockery as m;
use UniSharp\Cart\CartItem;
use UniSharp\Pricing\Pricing;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Container\Container;
use UniSharp\Pricing\Tests\TestCase;
use UniSharp\Cart\CartItemCollection;
use UniSharp\Pricing\Tests\Fixtures\TestModule;
use UniSharp\Pricing\Tests\Fixtures\TestModule2;
use UniSharp\Pricing\Exceptions\InvalidModuleException;

class PricingTest extends TestCase
{
    protected $modules = [
        TestModule::class,
        TestModule2::class
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
            ->with(m::type('callable'))
            ->andReturn($total = 10);

        $pricing = $this->getPricing($items);

        $this->assertEquals($total, $pricing->getOriginalTotal());
    }

    public function testInvalidModuleException()
    {
        $this->expectException(InvalidModuleException::class);

        $pricing = $this->getPricing()->setModules([]);
        $pricing->apply(TestModule::class);
    }

    public function testInvalidModuleSequenceException()
    {
        $this->expectException(InvalidModuleException::class);

        $pricing = $this->getPricing()
            ->apply(TestModule2::class)
            ->apply(TestModule::class);
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

    public function testGetAppliedModules()
    {
        $pricing = $this->getPricing();
        $pricing = $pricing->apply(TestModule::class);

        $this->assertTrue(in_array(TestModule::class, $pricing->getAppliedModules()));
    }

    public function testGetTotal()
    {
        $items = m::mock(CartItemCollection::class);
        $items->shouldReceive('sum')
            ->with(m::type('callable'))
            ->andReturn($total = 100);

        $pricing = $this->getPricing($items);
        $pricing = $pricing->apply(TestModule::class);

        $result = $total - TestModule::DEDUCTION + TestModule::FEE;

        $this->assertEquals($result, $pricing->getTotal());
    }

    public function testApply()
    {
        $pricing = $this->getPricing();
        $pricing = $pricing->apply(TestModule::class);

        $this->assertEquals(TestModule::DEDUCTION, $pricing->getDeduction(TestModule::class));
        $this->assertEquals(TestModule::FEE, $pricing->getFee(TestModule::class));
        $this->assertEquals(TestModule::LOG, $pricing->getModuleLog(TestModule::class));
    }

    public function testModuleInfo()
    {
        $pricing = $this->getPricing();
        $pricing = $pricing->with([
            TestModule::class => $foo = 'bar'
        ]);

        $this->assertEquals($foo, $pricing->getModuleInfo(TestModule::class));
    }

    public function testExecute()
    {
        $module = m::mock(TestModule::class);

        $module->shouldReceive('handle')
            ->once()
            ->with(m::type(Pricing::class), m::type(Closure::class))
            ->andReturnSelf();

        $module->shouldReceive('finish')
            ->once()
            ->with(m::type(Pricing::class))
            ->andReturnSelf();

        $pricing = $this->getPricing();
        $pricing->apply(TestModule::class)->execute();
    }

    protected function getPricing($items = null)
    {
        $container = new Container;
        $pipeline = new Pipeline($container);
        $pricing = new Pricing($container, $pipeline, $this->modules);

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
