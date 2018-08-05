<?php

declare(strict_types=1);

namespace App\Controller;

use FunctionalTester;

class HomepageControllerCest
{
    public function testHomepage(FunctionalTester $I)
    {
        $I->amOnPage('/');
        $I->seeResponseCodeIs(200);
        $I->see('NPRadio');
    }
}
