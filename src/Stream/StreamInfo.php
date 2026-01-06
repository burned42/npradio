<?php

declare(strict_types=1);

namespace App\Stream;

use DateTimeInterface;
use JsonSerializable;
use Override;

final class StreamInfo implements JsonSerializable
{
    public ?string $track = null;
    public ?string $artist = null;
    public ?string $show = null;
    public ?string $genre = null;
    public ?string $moderator = null;
    public ?DateTimeInterface $showStartTime = null;
    public ?DateTimeInterface $showEndTime = null;

    public function __construct(
        public readonly string $radioName,
        public readonly string $streamName,
        public readonly string $homepageUrl,
        public readonly string $streamUrl,
    ) {
    }

    /**
     * @return array<string, string|array<string|null>|null>
     */
    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'radio_name' => $this->radioName,
            'stream_name' => $this->streamName,
            'homepage' => $this->homepageUrl,
            'stream_url' => $this->streamUrl,
            'show' => [
                'name' => $this->show,
                'genre' => $this->genre,
                'moderator' => $this->moderator,
                'start_time' => $this->showStartTime?->format('H:i'),
                'end_time' => $this->showEndTime?->format('H:i'),
            ],
            'track' => $this->track,
            'artist' => $this->artist,
        ];
    }
}
