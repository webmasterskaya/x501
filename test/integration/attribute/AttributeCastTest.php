<?php

declare(strict_types = 1);

use PHPUnit\Framework\TestCase;
use Sop\ASN1\Type\Primitive\UTF8String;
use Sop\X501\ASN1\Attribute;
use Sop\X501\ASN1\AttributeType;
use Sop\X501\ASN1\AttributeValue\CommonNameValue;
use Sop\X501\ASN1\AttributeValue\DescriptionValue;
use Sop\X501\ASN1\AttributeValue\UnknownAttributeValue;

/**
 * @group attribute
 *
 * @internal
 */
class AttributeCastTest extends TestCase
{
    private static $_attr;

    public static function setUpBeforeClass(): void
    {
        self::$_attr = new Attribute(
            new AttributeType(AttributeType::OID_COMMON_NAME),
            new UnknownAttributeValue(AttributeType::OID_COMMON_NAME,
                new UTF8String('name')));
    }

    public static function tearDownAfterClass(): void
    {
        self::$_attr = null;
    }

    public function testCast()
    {
        $attr = self::$_attr->castValues(CommonNameValue::class);
        $this->assertInstanceOf(CommonNameValue::class, $attr->first());
    }

    public function testInvalidClass()
    {
        $this->expectException(\LogicException::class);
        self::$_attr->castValues(stdClass::class);
    }

    public function testOIDMismatch()
    {
        $this->expectException(\LogicException::class);
        self::$_attr->castValues(DescriptionValue::class);
    }
}
