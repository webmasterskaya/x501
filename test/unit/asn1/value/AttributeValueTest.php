<?php

declare(strict_types = 1);

use PHPUnit\Framework\TestCase;
use Sop\ASN1\Type\Primitive\NullType;
use Sop\ASN1\Type\UnspecifiedType;
use Webmasterskaya\X501\ASN1\Attribute;
use Webmasterskaya\X501\ASN1\AttributeTypeAndValue;
use Webmasterskaya\X501\ASN1\AttributeValue\AttributeValue;
use Webmasterskaya\X501\ASN1\AttributeValue\CommonNameValue;

/**
 * @group asn1
 * @group value
 *
 * @internal
 */
class AttributeValueTest extends TestCase
{
    public function testFromASN1BadCall()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('must be implemented in a concrete class');
        AttributeValue::fromASN1(new UnspecifiedType(new NullType()));
    }

    public function testToAttribute()
    {
        $val = new CommonNameValue('name');
        $this->assertInstanceOf(Attribute::class, $val->toAttribute());
    }

    public function testToAttributeTypeAndValue()
    {
        $val = new CommonNameValue('name');
        $this->assertInstanceOf(AttributeTypeAndValue::class,
            $val->toAttributeTypeAndValue());
    }
}
