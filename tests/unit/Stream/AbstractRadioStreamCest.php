<?php

declare(strict_types=1);

namespace App\Tests\unit\Stream;

use App\DataFetcher\HttpDomFetcher;
use App\Stream\AbstractRadioStream;
use App\Tests\UnitTester;
use Codeception\Example;
use DateTime;
use Exception;
use InvalidArgumentException;

final class DummyRadioStream extends AbstractRadioStream
{
    protected function getHomepageUrl(): string
    {
        return 'fake_url';
    }

    public function getStreamUrl(): string
    {
        return 'fake_stream_url';
    }

    public function updateInfo(): void
    {
    }

    public static function getAvailableStreams(): array
    {
        return ['fake_stream'];
    }

    public static function getRadioName(): string
    {
        return 'fake_radio';
    }
}

class AbstractRadioStreamCest
{
    private function getDummy(): DummyRadioStream
    {
        return new DummyRadioStream(new HttpDomFetcher(), 'fake_stream');
    }

    /**
     * @throws Exception
     */
    public function testConstructor(UnitTester $I): void
    {
        $this->getDummy();

        // dummy assertion, updateInfo() just shall not throw an exception so
        // if we get here everything is ok
        $I->assertTrue(true);
    }

    public function testConstructorException(UnitTester $I): void
    {
        $I->expectThrowable(
            new InvalidArgumentException('Invalid stream name given'),
            static function () {
                new DummyRadioStream(new HttpDomFetcher(), 'foobar');
            }
        );
    }

    public function testGetStreamName(UnitTester $I): void
    {
        $I->assertEquals('fake_stream', $this->getDummy()->getStreamName());
    }

    public function testGetStreamUrl(UnitTester $I): void
    {
        $I->assertEquals('fake_stream_url', $this->getDummy()->getStreamUrl());
    }

    /**
     * @example ["Show"]
     * @example ["Genre"]
     * @example ["Moderator"]
     * @example ["Track"]
     * @example ["Artist"]
     */
    public function testStringPropertySettersAndGetters(UnitTester $I, Example $example): void
    {
        $value = 'foobar';

        $setter = 'set'.$example[0];
        $getter = 'get'.$example[0];

        $dummy = $this->getDummy();
        $dummy->$setter($value);

        $I->assertEquals($value, $dummy->$getter());
    }

    /**
     * @example ["ShowStartTime"]
     * @example ["ShowEndTime"]
     *
     * @throws Exception
     */
    public function testDateTimePropertySettersAndGetters(UnitTester $I, Example $example): void
    {
        $value = new DateTime();

        $setter = 'set'.$example[0];
        $getter = 'get'.$example[0];

        $dummy = $this->getDummy();
        $dummy->$setter($value);

        $I->assertEquals($value, $dummy->$getter());
    }

    /**
     * @throws Exception
     */
    public function testGetAsArray(UnitTester $I): void
    {
        $radioName = 'fake_radio';
        $streamName = 'fake_stream';
        $homepageUrl = 'fake_url';
        $streamUrl = 'fake_stream_url';

        $show = 'test_show';
        $genre = 'test_genre';
        $moderator = 'test_moderator';
        $showStartTime = '16:00';
        $showStartTimeDateTime = new DateTime($showStartTime);
        $showEndTime = '18:00';
        $showEndTimeDateTime = new DateTime($showEndTime);
        $track = 'test_track';
        $artist = 'test_artist';

        $dummy = $this->getDummy();
        $dummy->setShow($show)
            ->setGenre($genre)
            ->setModerator($moderator)
            ->setShowStartTime($showStartTimeDateTime)
            ->setShowEndTime($showEndTimeDateTime)
            ->setTrack($track)
            ->setArtist($artist);

        $array = $dummy->getAsArray();

        $I->assertEquals([
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
        ], $array);
    }
}
