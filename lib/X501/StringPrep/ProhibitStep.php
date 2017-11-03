<?php

declare(strict_types = 1);

namespace X501\StringPrep;

/**
 * Implements 'Prohibit' step of the Internationalized String Preparation
 * as specified by RFC 4518.
 *
 * @link https://tools.ietf.org/html/rfc4518#section-2.4
 */
class ProhibitStep implements PrepareStep
{
    /**
     *
     * @throws \UnexpectedValueException If string contains prohibited
     *         characters
     * @param string $string UTF-8 encoded string
     * @return string
     */
    public function apply(string $string): string
    {
        // @todo Implement
        return $string;
    }
}
