<?php

namespace NPRadio\Controller;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;
use \UnitTester;

class ApiControllerCest
{
    /** @var  ControllerProviderInterface */
    protected $controller;

    public function _before(UnitTester $I)
    {
        $this->controller = new ApiController();
    }

    public function _after(UnitTester $I)
    {
    }

    // tests
    public function testIsInstantiable(UnitTester $I)
    {
        $I->assertInstanceOf(ControllerProviderInterface::class, $this->controller);
    }

    public function testCanConnect(UnitTester $I)
    {
        $controller = $this->controller->connect(new Application());

        $I->assertInstanceOf(ControllerCollection::class, $controller);
    }
}
