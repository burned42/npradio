<?php

declare(strict_types=1);

namespace Tests\Functional\Controller;

use Tests\Support\FunctionalTester;

final class HomepageControllerCest
{
    public function testHomepage(FunctionalTester $I): void
    {
        $I->amOnPage('/');
        $I->seeResponseCodeIs(200);
        $I->see('NPRadio');
    }
}
