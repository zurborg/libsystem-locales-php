<?php

use System\Locales;

class SystemLocalesTest extends PHPUnit_Framework_TestCase
{
    public function testNormalize()
    {
        $this->assertSame('xx_XX', Locales::normalize('xx_XX'));
        $this->assertSame('xx_XX', Locales::normalize('xx-xx'));
    }

    public function testIconv()
    {
        $this->assertSame('ÿ', Locales::iconv('C', 'ISO-8859-1', 'UTF-8', chr(255)));
    }
}
