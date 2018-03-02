<?php

declare(strict_types=1);

namespace NPRadio\Controller;

use Codeception\Util\Stub;
use NPRadio\Stream\MetalOnly;
use Psr\Container\ContainerInterface;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\HttpCache\CacheProvider;
use UnitTester;

class ApiControllerCest
{
    /** @var ContainerInterface */
    protected $container;

    public function _before(UnitTester $I)
    {
        $this->container = Stub::make(Container::class);
        $this->container['cache'] = Stub::make(CacheProvider::class);
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

    public function testGetStreamInfo(UnitTester $I)
    {
        $controller = new ApiController($this->container);

        /** @var Request $request */
        $request = Stub::make(Request::class);
        $response = new Response();

        $I->assertInstanceOf(
            Response::class,
            $controller->getStreamInfo(
                $request,
                $response,
                [
                    'radioName' => MetalOnly::RADIO_NAME,
                    'streamName' => MetalOnly::METAL_ONLY,
                ]
            )
        );
    }
}
