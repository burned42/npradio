<?php

declare(strict_types=1);

namespace App\Controller;

use App\Stream\AbstractRadioStream;
use App\Stream\StreamInfo;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\HttpKernel\Attribute\Serialize;
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

    /**
     * @return string[]
     */
    #[Route('/api/radios', methods: ['GET'], format: 'json')]
    #[Cache(smaxage: 300, mustRevalidate: true)]
    #[Serialize]
    public function getRadioNames(): array
    {
        return array_keys($this->radios);
    }

    /**
     * @return string[]
     */
    #[Route('/api/radios/{radioName}/streams', methods: ['GET'], format: 'json')]
    #[Cache(smaxage: 300, mustRevalidate: true)]
    #[Serialize]
    public function getStreams(string $radioName): array
    {
        try {
            $radioClass = $this->getRadioClass($radioName);
        } catch (InvalidArgumentException $e) {
            captureException($e);

            throw $this->createNotFoundException($e->getMessage());
        }

        return $radioClass->getAvailableStreams();
    }

    #[Route('/api/radios/{radioName}/streams/{streamName}', methods: ['GET'], format: 'json')]
    #[Cache(smaxage: 30, mustRevalidate: true)]
    #[Serialize]
    public function getStreamInfo(string $radioName, string $streamName): StreamInfo
    {
        try {
            return $this->getRadioClass($radioName)
                ->getStreamInfo($streamName);
        } catch (InvalidArgumentException $e) {
            captureException($e);

            throw $this->createNotFoundException($e->getMessage());
        }
    }

    private function getRadioClass(string $radioName): AbstractRadioStream
    {
        return $this->radios[$radioName]
            ?? throw new InvalidArgumentException('Invalid radio name given: '.$radioName);
    }
}
