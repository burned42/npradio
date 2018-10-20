<?php

declare(strict_types=1);

namespace App\Controller;

use App\DataFetcher\HttpDomFetcher;
use App\Stream\AbstractRadioStream;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api", methods={"GET"}, defaults={"_format": "json"})
 */
class ApiController extends AbstractController
{
    /** @var array */
    private $radios = [];

    public function addRadio(string $radio)
    {
        $this->radios[$radio::getRadioName()] = $radio;
    }

    /**
     * @Route("/radios")
     */
    public function getRadioNames(): JsonResponse
    {
        return $this->json(array_keys($this->radios));
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
        if (!array_key_exists($radioName, $this->radios)) {
            throw new \InvalidArgumentException('Invalid radio name given');
        }

        return $this->radios[$radioName];
    }
}
