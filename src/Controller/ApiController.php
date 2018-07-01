<?php

declare(strict_types=1);

namespace NPRadio\Controller;

use NPRadio\DataFetcher\HttpDomFetcher;
use NPRadio\Stream\AbstractRadioStream;
use NPRadio\Stream\MetalOnly;
use NPRadio\Stream\RauteMusik;
use NPRadio\Stream\StarFm;
use NPRadio\Stream\TechnoBase;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\HttpCache\CacheProvider;

class ApiController
{
    /** @var ContainerInterface */
    private $container;

    const RADIO_CLASSES = [
        MetalOnly::class,
        RauteMusik::class,
        StarFm::class,
        TechnoBase::class,
    ];

    /** @var array */
    private static $radios;

    /** @var HttpDomFetcher */
    private static $httpDomFetcher;

    /**
     * ApiController constructor.
     *
     * @param ContainerInterface $container
     *
     * @throws \RuntimeException
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        if (!static::$radios) {
            /** @var AbstractRadioStream $radioClass */
            foreach (self::RADIO_CLASSES as $radioClass) {
                static::$radios[$radioClass::getRadioName()] = $radioClass;
            }
        }
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @return Response
     *
     * @throws \RuntimeException
     */
    public function getRadioNames(Request $request, Response $response, array $args): Response
    {
        return $response->withJson(array_keys(static::$radios));
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @return Response
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function getStreams(Request $request, Response $response, array $args): Response
    {
        /** @var AbstractRadioStream $radioClass */
        $radioClass = $this->getRadioClass($args['radioName']);

        return $response->withJson($radioClass::getAvailableStreams());
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @return Response
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getStreamInfo(Request $request, Response $response, array $args): Response
    {
        /** @var CacheProvider $cache */
        $cache = $this->container->get('cache');

        $eTagPrefix = 'NPRadio-'.$args['radioName'].'-'.$args['streamName'].'_';
        /** @var Response $newResponse */
        $newResponse = $cache->withEtag($response, uniqid($eTagPrefix, true));
        $newResponse = $cache->withExpires($newResponse, time() + 30);
        $newResponse = $cache->withLastModified($newResponse, time());

        $radioClass = $this->getRadioClass($args['radioName']);
        /** @var AbstractRadioStream $stream */
        $stream = new $radioClass($this->getHttpDomFetcher(), $args['streamName']);

        return $newResponse->withJson($stream->getAsArray());
    }

    private function getRadioClass(string $radioName): string
    {
        if (!array_key_exists($radioName, static::$radios)) {
            throw new \InvalidArgumentException('Invalid radio name given');
        }

        return static::$radios[$radioName];
    }

    private function getHttpDomFetcher(): HttpDomFetcher
    {
        if (!(static::$httpDomFetcher instanceof HttpDomFetcher)) {
            static::$httpDomFetcher = new HttpDomFetcher();
        }

        return static::$httpDomFetcher;
    }
}
