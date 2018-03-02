<?php

namespace NPRadio\Stream;

use Codeception\Example;
use UnitTester;

class StreamInfoCest
{
    /** @var StreamInfo */
    private $streamInfo;

    public function _before(UnitTester $I)
    {
        $this->streamInfo = new StreamInfo();
    }

    public function _after(UnitTester $I)
    {
    }

    // tests
    public function canInstantiate(UnitTester $I)
    {
        $I->assertInstanceOf(StreamInfo::class, $this->streamInfo);
    }

    /**
     * @param UnitTester $I
     * @param Example    $example
     *
     * @example ["RadioName"]
     * @example ["StreamName"]
     * @example ["HomepageUrl"]
     * @example ["StreamUrl"]
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

        $this->streamInfo->$setter($value);

        $I->assertEquals($value, $this->streamInfo->$getter());
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

        $this->streamInfo->$setter($value);

        $I->assertEquals($value, $this->streamInfo->$getter());
    }

    public function testGetAsArray(UnitTester $I)
    {
        $radioName = 'test_radio';
        $streamName = 'test_stream';
        $homepageUrl = 'test_homepage';
        $streamUrl = 'test_stream_url';
        $show = 'test_show';
        $genre = 'test_genre';
        $moderator = 'test_moderator';
        $showStartTime = '16:00';
        $showStartTimeDateTime = new \DateTime($showStartTime);
        $showEndTime = '18:00';
        $showEndTimeDateTime = new \DateTime($showEndTime);
        $track = 'test_track';
        $artist = 'test_artist';

        $this->streamInfo->setRadioName($radioName)
            ->setStreamName($streamName)
            ->setHomepageUrl($homepageUrl)
            ->setStreamUrl($streamUrl)
            ->setShow($show)
            ->setGenre($genre)
            ->setModerator($moderator)
            ->setShowStartTime($showStartTimeDateTime)
            ->setShowEndTime($showEndTimeDateTime)
            ->setTrack($track)
            ->setArtist($artist);

        $array = $this->streamInfo->getAsArray();

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
