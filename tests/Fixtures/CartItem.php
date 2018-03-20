<?php

namespace UniSharp\Pricing\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['id', 'price', 'quantity'];
}