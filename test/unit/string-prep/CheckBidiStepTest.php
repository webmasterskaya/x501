<?php

declare(strict_types = 1);

use PHPUnit\Framework\TestCase;
use Webmasterskaya\X501\StringPrep\CheckBidiStep;

/**
 * @group string-prep
 *
 * @internal
 */
class CheckBidiStepTest extends TestCase
{
    public function testApply()
    {
        $str = 'Test';
        $step = new CheckBidiStep();
        $this->assertEquals($str, $step->apply($str));
    }
}
