<?php

namespace NPRadio\Controller;

use NPRadio\DataFetcher\HttpDomFetcher;
use NPRadio\Stream\MetalOnly;
use NPRadio\Stream\RadioContainer;
use NPRadio\Stream\RauteMusik;
use NPRadio\Stream\TechnoBase;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;

class ApiController implements ControllerProviderInterface
{
    /** @var RadioContainer */
    protected $radioContainer;

    public function __construct()
    {
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

    /**
     * Returns routes to connect to the given application.
     *
     * @param Application $app An Application instance
     *
     * @return ControllerCollection A ControllerCollection instance
     */
    public function connect(Application $app)
    {
        $radioContainer = $this->radioContainer;

        /** @var ControllerCollection $controller */
        $controller = $app['controllers_factory'];

        $controller->get(
            '/radios',
            function (Application $app) use ($radioContainer) {
                return $app->json($radioContainer->getRadioNames());
            }
        );
        $controller->get(
            '/radios/{radioName}/streams',
            function (Application $app, string $radioName) use ($radioContainer) {
                return $app->json(
                    $this->radioContainer->getStreamNames($radioName)
                );
            }
        );
        $controller->get(
            'radios/{radioName}/streams/{streamName}',
            function (Application $app, string $radioName, string $streamName) use ($radioContainer) {
                return $app->json(
                    $radioContainer->getInfo($radioName, $streamName)->getAsArray(),
                    200,
                    [
                        'Cache-Control' => 's-maxage=60',
                        'ETag' => uniqid()
                    ]
                );
            }
        );

        return $controller;
    }
}
