<?php

namespace Tests;

use App\Services\CalculatePrice;
use PHPUnit\Framework\TestCase;

class CalculatePriceTest extends TestCase
{
    public function testReduction()
    {
        $calculatePrice = new CalculatePrice();
        $this->assertSame(10, $calculatePrice->calculatePrice(new \Datetime('1990-10-14'), true, 1));
    }
    
    public function testPriceBaby()
    {
        $calculatePrice = new CalculatePrice();
        $this->assertSame(0, $calculatePrice->calculatePrice(new \Datetime('2018-04-03'), false, 1));
    }

    public function testPriceChild()
    {
        $calculatePrice = new CalculatePrice();
        $this->assertSame(4, $calculatePrice->calculatePrice(new \Datetime('2012-12-18'), false, 0.5));
    }

    public function testPriceNormal()
    {
        $calculatePrice = new CalculatePrice();
        $this->assertSame(16, $calculatePrice->calculatePrice(new \Datetime('1982-01-14'), false, 1));
    }

    public function testPriceSenior()
    {
        $calculatePrice = new CalculatePrice();
        $this->assertSame(6, $calculatePrice->calculatePrice(new \Datetime('1950-10-27'), false, 0.5));
    }
}
