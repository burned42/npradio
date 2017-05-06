<?php

namespace NPRadio\Stream;

use NPRadio\DataFetcher\HttpDomFetcher;
use \UnitTester;

class RadioContainerCest
{
    /** @var  RadioContainer */
    private $radioContainer;

    /** @var  RadioStream */
    private $fakeRadio;

    public function _before(UnitTester $I)
    {
        $this->radioContainer = new RadioContainer();
        $this->fakeRadio = new DummyRadioStream(new HttpDomFetcher());
    }

    public function _after(UnitTester $I)
    {
    }

    // tests
    public function canInstantiate(UnitTester $I)
    {
        $I->assertInstanceOf(RadioContainer::class, $this->radioContainer);
    }

    public function canAddRadio(UnitTester $I)
    {
        $this->radioContainer->addRadio($this->fakeRadio);
    }

    public function canNotAddRadioTwice(UnitTester $I)
    {
        $this->radioContainer->addRadio($this->fakeRadio);

        $I->expectException(
            new \RuntimeException('radio stream with this radio name already exists'),
            function () {
                $this->radioContainer->addRadio($this->fakeRadio);
            }
        );
    }

    public function containerContainsAddedRadio(UnitTester $I)
    {
        $this->radioContainer->addRadio($this->fakeRadio);

        $I->assertTrue($this->radioContainer->containsRadio('fake_radio'));
    }

    public function containerDoesNotContainNonAddedRadio(UnitTester $I)
    {
        $I->assertFalse($this->radioContainer->containsRadio('fake_radio2'));
    }

    public function containerContainsAddedStream(UnitTester $I)
    {
        $this->radioContainer->addRadio($this->fakeRadio);

        $I->assertTrue($this->radioContainer->containsStream('fake_radio', 'fake_stream'));
    }

    public function containerDoesNotContainNotAddedStream(UnitTester $I)
    {
        $I->expectException(
            new \InvalidArgumentException('invalid radio name given: fake_radio'),
            function () {
                $this->radioContainer->containsStream('fake_radio', 'fake_stream2');
            }
        );

        $this->radioContainer->addRadio($this->fakeRadio);
        $I->assertFalse($this->radioContainer->containsStream('fake_radio', 'fake_stream2'));
    }

    public function canGetInfoOfExistingStream(UnitTester $I)
    {
        $this->radioContainer->addRadio($this->fakeRadio);

        $info = $this->radioContainer->getInfo('fake_radio', 'fake_stream');

        $I->assertInstanceOf(StreamInfo::class, $info);
    }

    public function canNotGetInfoOfNonExistingRadio(UnitTester $I)
    {
        $I->expectException(
            new \InvalidArgumentException('invalid radio name given: fake_radio2'),
            function () {
                $this->radioContainer->getInfo('fake_radio2', 'fake_stream2');
            }
        );
    }

    public function canNotGetInfoOfNonExistingStream(UnitTester $I)
    {
        $this->radioContainer->addRadio($this->fakeRadio);

        $I->expectException(
            new \InvalidArgumentException('no radio info object created for stream: fake_stream2'),
            function () {
                $this->radioContainer->getInfo('fake_radio', 'fake_stream2');
            }
        );
    }

    public function canGetRadioNames(UnitTester $I)
    {
        $this->radioContainer->addRadio($this->fakeRadio);

        $I->assertEquals(
            ['fake_radio'],
            $this->radioContainer->getRadioNames()
        );
    }

    public function canNotGetRadioNamesIfThereAreNone(UnitTester $I)
    {
        $I->assertEquals(
            [],
            $this->radioContainer->getRadioNames()
        );
    }

    public function canGetStreamNames(UnitTester $I)
    {
        $this->radioContainer->addRadio($this->fakeRadio);

        $I->assertEquals(
            ['fake_stream'],
            $this->radioContainer->getStreamNames('fake_radio')
        );
    }

    public function canNotGetStreamNamesIfNoRadioAdded(UnitTester $I)
    {
        $I->expectException(
            new \InvalidArgumentException('invalid radio name given: fake_radio'),
            function () {
                $this->radioContainer->getStreamNames('fake_radio');
            }
        );
    }
}
