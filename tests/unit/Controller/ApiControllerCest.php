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

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function _before(UnitTester $I)
    {
        $this->container = Stub::make(Container::class);
        $this->container['cache'] = Stub::make(CacheProvider::class);
    }

    // tests

    /**
     * @param UnitTester $I
     *
     * @throws \RuntimeException
     */
    public function testIsInstantiable(UnitTester $I)
    {
        $I->assertInstanceOf(
            ApiController::class,
            new ApiController($this->container)
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testGetRadios(UnitTester $I)
    {
        $controller = new ApiController($this->container);

        /** @var Request $request */
        $request = Stub::make(Request::class);
        $response = new Response();

        $I->assertInstanceOf(
            Response::class,
            $controller->getRadioNames($request, $response, [])
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
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

    public function testGetStreamsWithInvalidStreamName(UnitTester $I)
    {
        $controller = new ApiController($this->container);

        /** @var Request $request */
        $request = Stub::make(Request::class);
        $response = new Response();

        $I->expectException(
            new \InvalidArgumentException('Invalid radio name given'),
            function () use ($controller, $request, $response) {
                $controller->getStreams(
                    $request,
                    $response,
                    ['radioName' => 'foobar']
                );
            }
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws \RuntimeException
     * @throws \Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \InvalidArgumentException
     */
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
