<?php

declare(strict_types=1);

namespace App\Controller;

use App\DataFetcher\HttpDomFetcher;
use App\Stream\AbstractRadioStream;
use App\Stream\MetalOnly;
use App\Stream\RadioGalaxy;
use App\Stream\RauteMusik;
use App\Stream\StarFm;
use App\Stream\TechnoBase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api", methods={"GET"}, defaults={"_format": "json"})
 */
class ApiController extends AbstractController
{
    const RADIO_CLASSES = [
        MetalOnly::class,
        RadioGalaxy::class,
        RauteMusik::class,
        StarFm::class,
        TechnoBase::class,
    ];

    /** @var array */
    private static $radios;

    public function __construct()
    {
        if (!static::$radios) {
            /** @var AbstractRadioStream $radioClass */
            foreach (self::RADIO_CLASSES as $radioClass) {
                static::$radios[$radioClass::getRadioName()] = $radioClass;
            }
        }
    }

    /**
     * @Route("/radios")
     */
    public function getRadioNames(): JsonResponse
    {
        return $this->json(array_keys(static::$radios));
    }

    /**
     * @Route("/radios/{radioName}/streams")
     *
     * @param string $radioName
     *
     * @return JsonResponse
     */
    public function getStreams(string $radioName): JsonResponse
    {
        try {
            /** @var AbstractRadioStream $radioClass */
            $radioClass = $this->getRadioClass($radioName);
        } catch (\InvalidArgumentException $e) {
            return $this->json($e->getMessage(), 404);
        }

        return $this->json($radioClass::getAvailableStreams());
    }

    /**
     * @Route("/radios/{radioName}/streams/{streamName}")
     *
     * @param string         $radioName
     * @param string         $streamName
     * @param HttpDomFetcher $httpDomFetcher
     *
     * @return JsonResponse
     */
    public function getStreamInfo(string $radioName, string $streamName, HttpDomFetcher $httpDomFetcher): JsonResponse
    {
        try {
            $radioClass = $this->getRadioClass($radioName);
            /** @var AbstractRadioStream $stream */
            $stream = new $radioClass($httpDomFetcher, $streamName);
        } catch (\InvalidArgumentException $e) {
            return $this->json($e->getMessage(), 404);
        }

        $response = $this->json($stream->getAsArray());

        $response->setSharedMaxAge(30);
        $response->headers->addCacheControlDirective('must-revalidate', true);

        return $response;
    }

    private function getRadioClass(string $radioName): string
    {
        if (!array_key_exists($radioName, static::$radios)) {
            throw new \InvalidArgumentException('Invalid radio name given');
        }

        return static::$radios[$radioName];
    }
}
