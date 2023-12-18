<?php

declare(strict_types=1);

namespace App\Stream;

use App\DataFetcher\HttpDataFetcherInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag]
abstract class AbstractRadioStream
{
    final public function __construct(
        private readonly HttpDataFetcherInterface $httpDataFetcher
    ) {
    }

    final protected function getHttpDataFetcher(): HttpDataFetcherInterface
    {
        return $this->httpDataFetcher;
    }

    abstract public static function getRadioName(): string;

    /**
     * @return array<string>
     */
    abstract public function getAvailableStreams(): array;

    abstract public function getStreamInfo(string $streamName): StreamInfo;
}
