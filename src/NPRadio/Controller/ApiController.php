<?php

namespace NPRadio\Controller;

use NPRadio\DataFetcher\HttpDomFetcher;
use NPRadio\Stream\MetalOnly;
use NPRadio\Stream\RadioContainer;
use NPRadio\Stream\RauteMusik;
use NPRadio\Stream\TechnoBase;
use Psr\Container\ContainerInterface;
use \Slim\Http\Request as Request;
use \Slim\Http\Response as Response;

class ApiController
{
    /** @var RadioContainer */
    protected $radioContainer;

    /** @var ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $this->radioContainer = new RadioContainer();
        $domFetcher = new HttpDomFetcher();
        $radioStreams = [
            MetalOnly::class,
            RauteMusik::class,
            TechnoBase::class
        ];

        foreach ($radioStreams as $radioStream) {
            $this->radioContainer->addRadio(new $radioStream($domFetcher));
        }
    }

    public function getRadios(Request $request, Response $response, $args)
    {
        return $response->withJson($this->radioContainer->getRadioNames());
    }

    public function getStreams(Request $request, Response $response, $args)
    {
        return $response->withJson($this->radioContainer->getStreamNames($args['radioName']));
    }

    public function getStreamInfo(Request $request, Response $response, $args)
    {
        // old:
        // 'Cache-Control' => 's-maxage=60',
        // 'ETag' => uniqid()


        // $resWithEtag = $this->cache->withEtag($res, 'abc');
        // $resWithExpires = $this->cache->withExpires($res, time() + 3600);
        // $resWithLastMod = $this->cache->withLastModified($res, time() - 3600);

        return $response->withJson(
            $this->radioContainer
                ->getInfo($args['radioName'], $args['streamName'])
                ->getAsArray()
        );
    }
}
