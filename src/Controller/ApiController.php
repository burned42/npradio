<?php

declare(strict_types=1);

namespace App\Controller;

use App\Stream\AbstractRadioStream;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;
use Traversable;

use function Sentry\captureException;

final class ApiController extends AbstractController
{
    /** @var array<string, AbstractRadioStream> */
    private readonly array $radios;

    /**
     * @param Traversable<AbstractRadioStream> $radios
     */
    public function __construct(
        #[AutowireIterator(AbstractRadioStream::class, defaultIndexMethod: 'getRadioName')]
        Traversable $radios,
    ) {
        /** @var array<string, AbstractRadioStream> $radioArray */
        $radioArray = iterator_to_array($radios);
        $this->radios = $radioArray;
    }

    #[Route('/api/radios', methods: ['GET'], format: 'json')]
    #[Cache(smaxage: 300, mustRevalidate: true)]
    public function getRadioNames(): JsonResponse
    {
        return $this->json(array_keys($this->radios));
    }

    #[Route('/api/radios/{radioName}/streams', methods: ['GET'], format: 'json')]
    #[Cache(smaxage: 300, mustRevalidate: true)]
    public function getStreams(string $radioName): JsonResponse
    {
        try {
            $radioClass = $this->getRadioClass($radioName);
        } catch (InvalidArgumentException $e) {
            captureException($e);

            return $this->json($e->getMessage(), 404);
        }

        return $this->json($radioClass->getAvailableStreams());
    }

    #[Route('/api/radios/{radioName}/streams/{streamName}', methods: ['GET'], format: 'json')]
    #[Cache(smaxage: 30, mustRevalidate: true)]
    public function getStreamInfo(string $radioName, string $streamName): JsonResponse
    {
        try {
            $radioClass = $this->getRadioClass($radioName);
            $streamInfo = $radioClass->getStreamInfo($streamName);
        } catch (InvalidArgumentException $e) {
            captureException($e);

            return $this->json($e->getMessage(), 404);
        }

        return $this->json($streamInfo);
    }

    private function getRadioClass(string $radioName): AbstractRadioStream
    {
        if (!array_key_exists($radioName, $this->radios)) {
            throw new InvalidArgumentException('Invalid radio name given');
        }

        return $this->radios[$radioName];
    }
}
