<?php

declare(strict_types=1);

namespace App\Tests\api;

use ApiTester;
use App\Stream\TechnoBase;
use Codeception\Util\HttpCode;

class ApiCest
{
    public function testGetRadioNames(ApiTester $I): void
    {
        $I->sendGET('radios');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType(['string']);
    }

    public function testGetStreams(ApiTester $I): void
    {
        $radioName = TechnoBase::getRadioName();

        $I->sendGET('radios/'.$radioName.'/streams');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType(['string']);
    }

    public function testGetStreamsWithInvalidRadioName(ApiTester $I): void
    {
        $I->sendGET('radios/foobar/streams');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['Invalid radio name given']);
    }

    public function testGetStreamInfo(ApiTester $I): void
    {
        $radioName = TechnoBase::getRadioName();
        $streamName = TechnoBase::getAvailableStreams()[0];

        $I->sendGET('radios/'.$radioName.'/streams/'.$streamName);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'radio_name' => 'string',
            'stream_name' => 'string',
            'homepage' => 'string',
            'stream_url' => 'string',
            'show' => [
                'name' => 'string|null',
                'genre' => 'string|null',
                'moderator' => 'string|null',
                'start_time' => 'string|null',
                'end_time' => 'string|null',
            ],
            'track' => 'string|null',
            'artist' => 'string|null',
        ]);
    }

    public function testGetStreamInfoWithInvalidRadioName(ApiTester $I): void
    {
        $I->sendGET('radios/foo/streams/bar');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['Invalid radio name given']);
    }

    public function testGetStreamInfoWithInvalidStreamName(ApiTester $I): void
    {
        $radioName = TechnoBase::getRadioName();

        $I->sendGET('radios/'.$radioName.'/streams/bar');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['Invalid stream name given']);
    }
}
