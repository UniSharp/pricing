# Pricing

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

A modularized pricing package for buyalbe.

## Installation

```
composer require unisharp/pricing dev-master
```

## Configuration

```
php artisan vendor:publish --tag pricing
```

Set available pricing modules in `config/pricing.php`

```php
return [
    'modules' => [
        UniSharp\Pricing\Tests\Fixtures\TestModule::class,
    ]
];
```

## Module Principles

* Module must implement `UniSharp\Pricing\ModuleContract` which needs `handle` and `finish` functions.
* Modules will be processed by the sequence in `config/pricing.php`, the first module will handle the pricing logic and pass pricing instance to the next module (Pipeline Pattern).
* There are some APIs a pricing module can call in the handle function:
    * $pricing->addFee(int $fee);
    * $pricing->addDeduction(int $deduction);
    * $pricing->writeModuleLog(mix $log);
    * $pricing->getModuleInfo();

> `addFee`, `addDeduction`, `writeModuleLog` will only change pricing instance's properties. `getModuleInfo` can get extra info of that module.

* Finish functions will be called after pricing execute. The logic after module successfully applied can be implemented here.

```php
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

        $info = $pricing->getModuleInfo();

        return $next($pricing);
    }

    public function finish(Pricing $pricing)
    {
        //
    }
}
```

## Pricing Usages

```php
use UniSharp\Pricing\Facades\Pricing;

class Foo {
    // set items and get original price
    Pricing::setItems(UniSharp\Cart\CartItemCollection $items)
        ->getOriginalTotal();

    // apply some modules
    Pricing::apply(ModuleA::class)
        ->apply(ModuleB::class);
    
    // apply some modules with some extra info
    Pricing::apply(ModuleA::class)
        ->apply(ModuleB::class)
        ->with([
            ModuleA::class => 'extra info A',
            ModuleB::class => 'extra info B',
        ]);
    
    // apply modules and get final total
    Pricing::apply(ModuleA::class)
        ->apply(ModuleB::class)
        ->getTotal();

    // apply modules and execute them
    Pricing::apply(ModuleA::class)
        ->apply(ModuleB::class)
        ->execute();
    
    // get all applied modules
    Pricing::getAppliedModules();

    // get all fees
    Pricing::getFees();

    // get fee of a specific module
    Pricing::getFee(ModuleA::class);
    
    // get all deductions
    Pricing::getDeductions();

    // get deduction of a specific module
    Pricing::getDeduction(ModuleA::class);

    // get items
    Pricing::getItems();
}
```

[ico-version]: https://img.shields.io/packagist/v/UniSharp/pricing.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/UniSharp/pricing/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/UniSharp/pricing.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/UniSharp/pricing.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/UniSharp/pricing.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/unisharp/pricing
[link-travis]: https://travis-ci.org/UniSharp/pricing
[link-scrutinizer]: https://scrutinizer-ci.com/g/UniSharp/pricing/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/UniSharp/pricing
[link-downloads]: https://packagist.org/packages/UniSharp/pricing
[link-author]: https://github.com/UniSharp
[link-contributors]: ../../contributors
