<?php

namespace NPRadio\Controller;

use Codeception\Util\Stub;
use NPRadio\Stream\MetalOnly;
use Psr\Container\ContainerInterface;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use \UnitTester;

class ApiControllerCest
{
    /** @var ContainerInterface */
    protected $container;

    public function _before(UnitTester $I)
    {
        $this->container = Stub::make(Container::class);
    }

    // tests
    public function testIsInstantiable(UnitTester $I)
    {
        $I->assertInstanceOf(
            ApiController::class,
            new ApiController($this->container)
        );
    }

    public function testGetRadios(UnitTester $I)
    {
        $controller = new ApiController($this->container);

        /** @var Request $request */
        $request = Stub::make(Request::class);
        $response = new Response();

        $I->assertInstanceOf(
            Response::class,
            $controller->getRadios($request, $response, [])
        );
    }

    public function testGetStreams(UnitTester $I)
    {
        $controller = new ApiController($this->container);

        /** @var Request $request */
        $request = Stub::make(Request::class);
        $response = new Response();

        $I->assertInstanceOf(
            Response::class,
            $controller->getStreams(
                $request,
                $response,
                ['radioName' => MetalOnly::RADIO_NAME]
            )
        );
    }
}
