<?php

declare(strict_types = 1);

use PHPUnit\Framework\TestCase;
use Webmasterskaya\X501\StringPrep\NormalizeStep;

/**
 * @group string-prep
 *
 * @internal
 */
class NormalizeStepTest extends TestCase
{
    public function testApply()
    {
        $source = 'ฉันกินกระจกได้ แต่มันไม่ทำให้ฉันเจ็บ';
        $step = new NormalizeStep();
        $expected = normalizer_normalize($source, \Normalizer::FORM_KC);
        $this->assertEquals($expected, $step->apply($source));
    }
}
