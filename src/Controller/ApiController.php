<?php

declare(strict_types=1);

namespace App\Controller;

use App\DataFetcher\HttpDomFetcher;
use App\Stream\AbstractRadioStream;
use InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api", methods={"GET"}, defaults={"_format": "json"})
 */
class ApiController extends AbstractController
{
    /** @var array<string, string> */
    private array $radios = [];

    public function addRadio(string $radio): void
    {
        /* @var $radio AbstractRadioStream */
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
     */
    public function getStreams(string $radioName): JsonResponse
    {
        try {
            /** @var AbstractRadioStream $radioClass */
            $radioClass = $this->getRadioClass($radioName);
        } catch (InvalidArgumentException $e) {
            return $this->json($e->getMessage(), 404);
        }

        return $this->json($radioClass::getAvailableStreams());
    }

    /**
     * @Route("/radios/{radioName}/streams/{streamName}")
     * @Cache(smaxage="30", mustRevalidate=true)
     */
    public function getStreamInfo(string $radioName, string $streamName, HttpDomFetcher $httpDomFetcher): JsonResponse
    {
        try {
            $radioClass = $this->getRadioClass($radioName);
            /** @var AbstractRadioStream $stream */
            $stream = new $radioClass($httpDomFetcher, $streamName);
        } catch (InvalidArgumentException $e) {
            return $this->json($e->getMessage(), 404);
        }

        return $this->json($stream->getAsArray());
    }

    private function getRadioClass(string $radioName): string
    {
        if (!array_key_exists($radioName, $this->radios)) {
            throw new InvalidArgumentException('Invalid radio name given');
        }

        return $this->radios[$radioName];
    }
}
