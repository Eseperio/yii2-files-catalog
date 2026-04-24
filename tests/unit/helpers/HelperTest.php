<?php

namespace unit\helpers;

use Codeception\Test\Unit;
use eseperio\filescatalog\helpers\Helper;
use UnitTester;

class HelperTest extends Unit
{
    protected UnitTester $tester;

    public function testHumanizeCamelCasesAndEncodesHtml(): void
    {
        $this->assertSame('My sample name', Helper::humanize('mySampleName'));
        $this->assertSame('File name &lt;safe&gt;', Helper::humanize('fileName <safe>'));
    }
}
