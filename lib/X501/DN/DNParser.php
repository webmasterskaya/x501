<?php

namespace X501\DN;

use ASN1\Element;


/**
 * Distinguished Name parsing conforming to RFC 2253 and RFC 1779.
 *
 * @link https://tools.ietf.org/html/rfc1779
 * @link https://tools.ietf.org/html/rfc2253
 */
class DNParser
{
	/**
	 * DN string.
	 *
	 * @var string
	 */
	private $_dn;
	
	/**
	 * DN string length.
	 *
	 * @var int
	 */
	private $_len;
	
	/**
	 * RFC 2253 special characters.
	 *
	 * @var string
	 */
	const SPECIAL_CHARS = ",=+<>#;";
	
	/**
	 * Parse distinguished name string to name-components.
	 *
	 * @param string $dn
	 * @return array
	 */
	public static function parseString($dn) {
		$parser = new self($dn);
		return $parser->parse();
	}
	
	/**
	 * Constructor
	 *
	 * @param string $dn Distinguised name
	 */
	protected function __construct($dn) {
		$this->_dn = $dn;
		$this->_len = strlen($dn);
	}
	
	/**
	 * Parse DN to name-components.
	 *
	 * @throws \RuntimeException
	 * @return array
	 */
	protected function parse() {
		$offset = 0;
		$name = $this->_parseName($offset);
		if ($offset < $this->_len) {
			throw new \RuntimeException(
				"Parser finished before the end of string");
		}
		return $name;
	}
	
	/**
	 * Parse 'name'.
	 *
	 * name-component *("," name-component)
	 *
	 * @param int $offset
	 * @return array Array of name-components
	 */
	private function _parseName(&$offset) {
		$idx = $offset;
		$names = array();
		while ($idx < $this->_len) {
			$name = $this->_parseNameComponent($idx);
			if (null === $name) {
				break;
			}
			$names[] = $name;
			if ($idx >= $this->_len) {
				break;
			}
			$this->_skipWs($idx);
			if ($this->_dn[$idx] !== "," && $this->_dn[$idx] !== ";") {
				break;
			}
			$idx++;
			$this->_skipWs($idx);
		}
		$offset = $idx;
		return array_reverse($names);
	}
	
	/**
	 * Parse 'name-component'.
	 *
	 * attributeTypeAndValue *("+" attributeTypeAndValue)
	 *
	 * @param int $offset
	 * @return array Array of [type, value] tuples
	 */
	private function _parseNameComponent(&$offset) {
		$idx = $offset;
		$tvpairs = array();
		while ($idx < $this->_len) {
			$tvpair = $this->_parseAttrTypeAndValue($idx);
			if (null === $tvpair) {
				break;
			}
			$tvpairs[] = $tvpair;
			$this->_skipWs($idx);
			if ($idx >= $this->_len || $this->_dn[$idx] !== "+") {
				break;
			}
			++$idx;
			$this->_skipWs($idx);
		}
		$offset = $idx;
		return $tvpairs;
	}
	
	/**
	 * Parse 'attributeTypeAndValue'.
	 *
	 * attributeType "=" attributeValue
	 *
	 * @param int $offset
	 * @throws \UnexpectedValueException
	 * @return array A tuple of [type, value]. Value may be either a string or
	 *         an Element, if it's encoded as hexstring.
	 */
	private function _parseAttrTypeAndValue(&$offset) {
		$idx = $offset;
		$type = $this->_parseAttrType($idx);
		$this->_skipWs($idx);
		if ($idx >= $this->_len || $this->_dn[$idx++] !== "=") {
			throw new \UnexpectedValueException("Invalid type and value pair");
		}
		$this->_skipWs($idx);
		// hexstring
		if ($idx < $this->_len && $this->_dn[$idx] === "#") {
			++$idx;
			$data = $this->_parseAttrHexValue($idx);
			$value = Element::fromDER($data);
		} else {
			$value = $this->_parseAttrStringValue($idx);
		}
		$offset = $idx;
		return array($type, $value);
	}
	
	/**
	 * Parse 'attributeType'.
	 *
	 * (ALPHA 1*keychar) / oid
	 *
	 * @param int $offset
	 * @return string
	 */
	private function _parseAttrType(&$offset) {
		$idx = $offset;
		// dotted OID
		$type = $this->_regexMatch('/^(?:oid\.)?([0-9]+(?:\.[0-9]+)*)/i', $idx);
		if (null === $type) {
			// name
			$type = $this->_regexMatch('/^[a-z][a-z0-9\-]*/i', $idx);
			if (null === $type) {
				throw new \UnexpectedValueException("Invalid attribute type");
			}
		}
		$offset = $idx;
		return $type;
	}
	
	/**
	 * Parse 'attributeValue' of string type.
	 *
	 * @param int $offset
	 * @throws \UnexpectedValueException
	 * @return string
	 */
	private function _parseAttrStringValue(&$offset) {
		$idx = $offset;
		$val = "";
		if ($idx >= $this->_len) {
			return $val;
		}
		if ($this->_dn[$idx] === '"') { // quoted string
			++$idx;
			while ($idx < $this->_len) {
				$c = $this->_dn[$idx];
				if ($c === "\\") { // pair
					++$idx;
					$val .= $this->_parsePairAfterSlash($idx);
					continue;
				} else if ($c === '"') {
					++$idx;
					break;
				}
				$val .= $c;
				++$idx;
			}
		} else { // string
			$wsidx = null;
			while ($idx < $this->_len) {
				$c = $this->_dn[$idx];
				// pair (escape sequence)
				if ($c === "\\") {
					++$idx;
					$val .= $this->_parsePairAfterSlash($idx);
					$wsidx = null;
					continue;
				} else if ($c === '"') {
					throw new \UnexpectedValueException("Unexpected quotation");
				} else if (false !== strpos(self::SPECIAL_CHARS, $c)) {
					break;
				}
				// keep track of first consecutive whitespace
				if ($c === ' ') {
					if (null === $wsidx) {
						$wsidx = $idx;
					}
				} else {
					$wsidx = null;
				}
				// stringchar
				$val .= $c;
				++$idx;
			}
			// if there was non-escaped whitespace in the end of value
			if (null !== $wsidx) {
				$val = substr($val, 0, -($idx - $wsidx));
			}
		}
		$offset = $idx;
		return $val;
	}
	
	/**
	 * Parse 'attributeValue' of binary type.
	 *
	 * @param int $offset
	 * @throws \UnexpectedValueException
	 * @return string
	 */
	private function _parseAttrHexValue(&$offset) {
		$idx = $offset;
		$hexstr = $this->_regexMatch('/^(?:[0-9a-f]{2})+/i', $idx);
		if (null == $hexstr) {
			throw new \UnexpectedValueException("Invalid hexstring");
		}
		$data = hex2bin($hexstr);
		$offset = $idx;
		return $data;
	}
	
	/**
	 * Parse 'pair' after leading slash.
	 *
	 * @param int $offset
	 * @throws \UnexpectedValueException
	 * @return string
	 */
	private function _parsePairAfterSlash(&$offset) {
		$idx = $offset;
		if ($idx >= $this->_len) {
			throw new \UnexpectedValueException(
				"Unexpected end of escape sequence");
		}
		$c = $this->_dn[$idx++];
		// special | \ | " | SPACE
		if (false !== strpos(self::SPECIAL_CHARS . '\\" ', $c)) {
			$val = $c;
		} else if ($idx < $this->_len) { // hexpair
			$val = @hex2bin($c . $this->_dn[$idx++]);
			if (false === $val) {
				throw new \UnexpectedValueException("Invalid hexpair");
			}
		}
		$offset = $idx;
		return $val;
	}
	
	/**
	 * Match DN to pattern and extract the last capture group.
	 *
	 * Updates offset to fully matched pattern.
	 *
	 * @param string $pattern
	 * @param int $offset
	 * @return string
	 */
	private function _regexMatch($pattern, &$offset) {
		$idx = $offset;
		if (!preg_match($pattern, substr($this->_dn, $idx), $match)) {
			return null;
		}
		$idx += strlen($match[0]);
		$offset = $idx;
		return end($match);
	}
	
	/**
	 * Skip consecutive spaces.
	 *
	 * @param int $offset
	 */
	private function _skipWs(&$offset) {
		$idx = $offset;
		while ($idx < $this->_len) {
			if ($this->_dn[$idx] !== " ") {
				break;
			}
			++$idx;
		}
		$offset = $idx;
	}
}
