<?php

declare(strict_types=1);

namespace NPRadio\Controller;

use NPRadio\DataFetcher\HttpDomFetcher;
use NPRadio\Stream\MetalOnly;
use NPRadio\Stream\RadioContainer;
use NPRadio\Stream\RauteMusik;
use NPRadio\Stream\TechnoBase;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\HttpCache\CacheProvider;

class ApiController
{
    /** @var RadioContainer */
    protected $radioContainer;

    /** @var ContainerInterface */
    private $container;

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

        $this->radioContainer = new RadioContainer();
        $domFetcher = new HttpDomFetcher();
        $radioStreams = [
            MetalOnly::class,
            RauteMusik::class,
            TechnoBase::class,
        ];

        foreach ($radioStreams as $radioStream) {
            $this->radioContainer->addRadio(new $radioStream($domFetcher));
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
    public function getRadios(Request $request, Response $response, array $args): Response
    {
        return $response->withJson($this->radioContainer->getRadioNames());
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
        return $response->withJson($this->radioContainer->getStreamNames($args['radioName']));
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

        /** @var Response $response */
        $response = $cache->withEtag($response, uniqid());
        $response = $cache->withExpires($response, time() + 30);
        $response = $cache->withLastModified($response, time());

        return $response->withJson(
            $this->radioContainer
                ->getInfo($args['radioName'], $args['streamName'])
                ->getAsArray()
        );
    }
}
