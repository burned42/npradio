<?php

namespace NPRadio\Controller;

use Codeception\Util\Stub;
use Psr\Container\ContainerInterface;
use Slim\Container;
use Slim\Views\Twig;
use \UnitTester;

class IndexControllerCest
{
    /** @var ContainerInterface */
    protected $container;

    public function _before(UnitTester $I)
    {
        $this->container = Stub::make(Container::class);
        $this->container['view'] = null;
    }

    // tests
    public function testIsInstantiable(UnitTester $I)
    {
        $I->assertInstanceOf(
            IndexController::class,
            new IndexController($this->container)
        );
    }
}
