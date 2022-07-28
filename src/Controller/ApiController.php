<?php

declare(strict_types=1);

namespace App\Controller;

use App\Stream\AbstractRadioStream;
use InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

use function Sentry\captureException;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Traversable;

#[Route('/api', methods: ['GET'], format: 'json')]
final class ApiController extends AbstractController
{
    /** @var array<string, AbstractRadioStream> */
    private readonly array $radios;

    /**
     * @param Traversable<AbstractRadioStream> $radios
     */
    public function __construct(
        #[TaggedIterator(AbstractRadioStream::class, defaultIndexMethod: 'getRadioName')]
        Traversable $radios
    ) {
        $this->radios = iterator_to_array($radios);
    }

    #[Route('/radios')]
    public function getRadioNames(): JsonResponse
    {
        return $this->json(array_keys($this->radios));
    }

    #[Route('/radios/{radioName}/streams')]
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

    #[Route('/radios/{radioName}/streams/{streamName}')]
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
