<?php

declare(strict_types = 1);

namespace Sop\X501\MatchingRule;

use Sop\X501\StringPrep\StringPreparer;

/**
 * Implements 'caseExactMatch' matching rule.
 *
 * @see https://tools.ietf.org/html/rfc4517#section-4.2.4
 */
class CaseExactMatch extends StringPrepMatchingRule
{
    /**
     * Constructor.
     *
     * @param int $string_type ASN.1 string type tag
     */
    public function __construct(int $string_type)
    {
        parent::__construct(StringPreparer::forStringType($string_type));
    }
}
