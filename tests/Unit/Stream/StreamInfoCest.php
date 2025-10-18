<?php

declare(strict_types=1);

namespace Tests\Unit\Stream;

use App\Stream\StreamInfo;
use DateTimeImmutable;
use JsonSerializable;
use Tests\Support\UnitTester;

final class StreamInfoCest
{
    public function testConstructor(UnitTester $I): void
    {
        $streamInfo = new StreamInfo(
            'foo',
            'bar',
            'baz',
            'blub',
        );

        $I->assertEquals('foo', $streamInfo->radioName);
        $I->assertEquals('bar', $streamInfo->streamName);
        $I->assertEquals('baz', $streamInfo->homepageUrl);
        $I->assertEquals('blub', $streamInfo->streamUrl);
    }

    public function testJsonSerialize(UnitTester $I): void
    {
        $radioName = 'fake_radio';
        $streamName = 'fake_stream';
        $homepageUrl = 'fake_url';
        $streamUrl = 'fake_stream_url';

        $show = 'test_show';
        $genre = 'test_genre';
        $moderator = 'test_moderator';
        $showStartTime = '16:00';
        $showStartTimeDateTime = new DateTimeImmutable($showStartTime);
        $showEndTime = '18:00';
        $showEndTimeDateTime = new DateTimeImmutable($showEndTime);
        $track = 'test_track';
        $artist = 'test_artist';

        $streamInfo = new StreamInfo(
            $radioName,
            $streamName,
            $homepageUrl,
            $streamUrl,
        );

        $streamInfo->show = $show;
        $streamInfo->genre = $genre;
        $streamInfo->moderator = $moderator;
        $streamInfo->showStartTime = $showStartTimeDateTime;
        $streamInfo->showEndTime = $showEndTimeDateTime;
        $streamInfo->track = $track;
        $streamInfo->artist = $artist;

        $I->assertInstanceOf(JsonSerializable::class, $streamInfo);
        $array = $streamInfo->jsonSerialize();

        $expected = [
            'radio_name' => $radioName,
            'stream_name' => $streamName,
            'homepage' => $homepageUrl,
            'stream_url' => $streamUrl,
            'show' => [
                'name' => $show,
                'genre' => $genre,
                'moderator' => $moderator,
                'start_time' => $showStartTime,
                'end_time' => $showEndTime,
            ],
            'track' => $track,
            'artist' => $artist,
        ];
        $I->assertEquals($expected, $array);
        $I->assertEquals(
            json_encode($expected, JSON_THROW_ON_ERROR),
            json_encode($streamInfo, JSON_THROW_ON_ERROR)
        );
    }
}
