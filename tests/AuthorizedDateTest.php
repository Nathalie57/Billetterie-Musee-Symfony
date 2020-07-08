<?php

namespace Tests;

use App\Services\AuthorizedDate;
use PHPUnit\Framework\TestCase;
use \DateTime;

class AuthorizedDateTest extends TestCase
{
    private $age;
    private $reduction;
    private $visit_duration;

    public function offDayOrder()
    {
        $offDayOrder = new AuthorizedDate();
        $this->assertSame(false, $dayBeforeOrder->authorizedOrderDate(new \Datetime('2020-04-13'), 1));
    }
    
    public function testSundayOrder()
    {
        $sundayOrder = new AuthorizedDate();
        $this->assertSame(false, $sundayOrder->authorizedOrderDate(new \Datetime('2021-05-16'), 1));
    }

    public function testOkDayOrder()
    {
        $okDayOrder = new AuthorizedDate();
        $this->assertSame(true, $okDayOrder->authorizedOrderDate(new \Datetime('2020-04-08'), 1));
    }
}
