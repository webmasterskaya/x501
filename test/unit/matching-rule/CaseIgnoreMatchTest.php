<?php

declare(strict_types = 1);

use PHPUnit\Framework\TestCase;
use Sop\ASN1\Element;
use Webmasterskaya\X501\MatchingRule\CaseIgnoreMatch;

/**
 * @group matching-rule
 *
 * @internal
 */
class CaseIgnoreMatchTest extends TestCase
{
    /**
     * @dataProvider provideMatch
     *
     * @param string $assertion
     * @param string $value
     * @param bool   $expected
     */
    public function testMatch($assertion, $value, $expected)
    {
        $rule = new CaseIgnoreMatch(Element::TYPE_UTF8_STRING);
        $this->assertEquals($expected, $rule->compare($assertion, $value));
    }

    public function provideMatch()
    {
        return [
            ['abc', 'abc', true],
            ['ABC', 'abc', true],
            [' abc ', 'abc', true],
            ['abc', ' abc ', true],
            ['a b c', 'a  b  c', true],
            ['abc', 'abcd', false],
            ['', '', true],
            ['', ' ', true],
        ];
    }
}
