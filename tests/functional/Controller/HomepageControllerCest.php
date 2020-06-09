<?php

declare(strict_types=1);

namespace App\Tests\functional\Controller;

use FunctionalTester;

class HomepageControllerCest
{
    public function testHomepage(FunctionalTester $I): void
    {
        $I->amOnPage('/');
        $I->seeResponseCodeIs(200);
        $I->see('NPRadio');
    }
}
