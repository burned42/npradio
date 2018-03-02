<?php

declare(strict_types=1);

namespace NPRadio\Controller;

use Codeception\Util\Stub;
use Psr\Container\ContainerInterface;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Views\Twig;
use UnitTester;

class IndexControllerCest
{
    /** @var ContainerInterface */
    protected $container;

    public function _before(UnitTester $I)
    {
        $this->container = Stub::make(Container::class);
        $this->container['view'] = Stub::make(
            Twig::class,
            ['render' => new Response()]
        );
    }

    // tests
    public function testIsInstantiable(UnitTester $I)
    {
        $I->assertInstanceOf(
            IndexController::class,
            new IndexController($this->container)
        );
    }

    public function testGetIndex(UnitTester $I)
    {
        $controller = new IndexController($this->container);

        /** @var Request $request */
        $request = Stub::make(Request::class);
        $response = new Response();

        $I->assertInstanceOf(
            Response::class,
            $controller->getIndex($request, $response, [])
        );
    }
}
