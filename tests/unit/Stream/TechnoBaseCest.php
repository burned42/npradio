<?php

declare(strict_types=1);

namespace App\Tests\Unit\Stream;

use App\DataFetcher\HttpDomFetcher;
use App\Stream\TechnoBase;
use Codeception\Exception\TestRuntimeException;
use Codeception\Util\Stub;
use DOMDocument;
use Exception;
use InvalidArgumentException;
use RuntimeException;
use UnitTester;

class TechnoBaseCest
{
    private $domFetcher;

    /**
     * @throws Exception
     */
    public function _before(): void
    {
        $this->domFetcher = Stub::makeEmpty(HttpDomFetcher::class, ['getXmlDom' => static function () {
            $dom = new DOMDocument();
            $xml = file_get_contents(__DIR__.'/../TestSamples/TechnoBaseSample.xml');
            @$dom->loadXML($xml);

            return $dom;
        }]);
    }

    public function canInstantiate(UnitTester $I): void
    {
        $tb = new TechnoBase($this->domFetcher, TechnoBase::getAvailableStreams()[0]);

        $I->assertInstanceOf(TechnoBase::class, $tb);
    }

    public function testRadioNameSet(UnitTester $I): void
    {
        $I->assertNotNull(TechnoBase::getRadioName());
    }

    public function testStreamsSet(UnitTester $I): void
    {
        $I->assertNotEmpty(TechnoBase::getAvailableStreams());
    }

    /**
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function testUpdateInfo(UnitTester $I): void
    {
        foreach (TechnoBase::getAvailableStreams() as $availableStream) {
            new TechnoBase($this->domFetcher, $availableStream);
        }

        // dummy assertion, updateInfo() just shall not throw an exception so
        // if we get here everything is ok
        $I->assertTrue(true);
    }

    /**
     * @throws Exception
     */
    public function testDomFetcherException(UnitTester $I): void
    {
        /** @var HttpDomFetcher $domFetcher */
        $domFetcher = Stub::makeEmpty(HttpDomFetcher::class, ['getXmlDom' => static function () {
            throw new TestRuntimeException('test');
        }]);

        $I->expectThrowable(
            new RuntimeException('could not get xml dom: test'),
            static function () use ($domFetcher) {
                new TechnoBase($domFetcher, TechnoBase::getAvailableStreams()[0]);
            }
        );
    }

    public function testProtectedMethods(UnitTester $I): void
    {
        $tb = new TechnoBase($this->domFetcher, TechnoBase::getAvailableStreams()[0]);
        $info = $tb->getAsArray();

        $I->assertNotEmpty($info['homepage']);
        $I->assertInternalType('string', $info['homepage']);

        $I->assertNotEmpty($info['stream_url']);
        $I->assertInternalType('string', $info['stream_url']);
    }
}
