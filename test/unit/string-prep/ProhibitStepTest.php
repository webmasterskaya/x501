<?php

declare(strict_types = 1);

use PHPUnit\Framework\TestCase;
use Webmasterskaya\X501\StringPrep\ProhibitStep;

/**
 * @group string-prep
 *
 * @internal
 */
class ProhibitStepTest extends TestCase
{
    public function testApply()
    {
        $str = 'Test';
        $step = new ProhibitStep();
        $this->assertEquals($str, $step->apply($str));
    }
}
