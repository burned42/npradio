<?php

declare(strict_types=1);

namespace NPRadio\Stream;

use Codeception\Example;
use NPRadio\DataFetcher\HttpDomFetcher;
use UnitTester;

class AbstractRadioStreamCest
{
    private function getDummy()
    {
        return new DummyRadioStream(new HttpDomFetcher(), 'fake_stream');
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testConstructor(UnitTester $I)
    {
        $I->assertInstanceOf(
            AbstractRadioStream::class,
            $this->getDummy()
        );
    }

    public function testConstructorException(UnitTester $I)
    {
        $I->expectException(
            new \InvalidArgumentException('Invalid stream name given'),
            function () {
                new DummyRadioStream(new HttpDomFetcher(), 'foobar');
            }
        );
    }

    public function testGetStreamName(UnitTester $I)
    {
        $I->assertEquals('fake_stream', $this->getDummy()->getStreamName());
    }

    /**
     * @param UnitTester $I
     * @param Example    $example
     *
     * @example ["Show"]
     * @example ["Genre"]
     * @example ["Moderator"]
     * @example ["Track"]
     * @example ["Artist"]
     */
    public function testStringPropertySettersAndGetters(UnitTester $I, Example $example)
    {
        $value = 'foobar';

        $setter = 'set'.$example[0];
        $getter = 'get'.$example[0];

        $dummy = $this->getDummy();
        $dummy->$setter($value);

        $I->assertEquals($value, $dummy->$getter());
    }

    /**
     * @param UnitTester $I
     * @param Example    $example
     *
     * @example ["ShowStartTime"]
     * @example ["ShowEndTime"]
     */
    public function testDateTimePropertySettersAndGetters(UnitTester $I, Example $example)
    {
        $value = new \DateTime();

        $setter = 'set'.$example[0];
        $getter = 'get'.$example[0];

        $dummy = $this->getDummy();
        $dummy->$setter($value);

        $I->assertEquals($value, $dummy->$getter());
    }

    public function testGetAsArray(UnitTester $I)
    {
        $radioName = 'fake_radio';
        $streamName = 'fake_stream';
        $homepageUrl = 'fake_url';
        $streamUrl = 'fake_stream_url';

        $show = 'test_show';
        $genre = 'test_genre';
        $moderator = 'test_moderator';
        $showStartTime = '16:00';
        $showStartTimeDateTime = new \DateTime($showStartTime);
        $showEndTime = '18:00';
        $showEndTimeDateTime = new \DateTime($showEndTime);
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
