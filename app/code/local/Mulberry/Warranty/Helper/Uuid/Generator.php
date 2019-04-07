<?php

/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @version 1.0.0
 * @copyright Copyright (c) 2019 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */
class Mulberry_Warranty_Helper_Uuid_Generator extends Mage_Core_Helper_Abstract
{
    /**
     * For testing, openssl_random_pseudo_bytes() override; if true, treat as
     * if openssl_random_pseudo_bytes() is not available
     *
     * @var bool
     */
    public static $forceNoOpensslRandomPseudoBytes = false;

    /**
     * The fields that make up this UUID
     *
     * This is initialized to the nil value.
     *
     * @var array
     */
    protected $fields = array(
        'time_low' => '00000000',
        'time_mid' => '0000',
        'time_hi_and_version' => '0000',
        'clock_seq_hi_and_reserved' => '00',
        'clock_seq_low' => '00',
        'node' => '000000000000',
    );

    /**
     * Mulberry_Warranty_Helper_Uuid_Generator constructor.
     * Creates a universally unique identifier (UUID) from an array of fields.
     *
     * @param array $fields
     */
    public function __construct(array $fields = array())
    {
        $this->fields = $fields;
    }

    /**
     * Converts this UUID object to a string when the object is used in any
     * string context
     *
     * @return string
     * @link http://www.php.net/manual/en/language.oop5.magic.php#object.tostring
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Generate a version 4 (random) UUID.
     *
     * @return $this
     */
    public function uuid4()
    {
        $bytes = self::generateBytes(16);

        // When converting the bytes to hex, it turns into a 32-character
        // hexadecimal string that looks a lot like an MD5 hash, so at this
        // point, we can just pass it to uuidFromHashedName.
        $hex = bin2hex($bytes);

        return self::uuidFromHashedName($hex, 4);
    }

    /**
     * Returns true if the system has openssl_random_pseudo_bytes()
     *
     * @return bool
     */
    protected static function hasOpensslRandomPseudoBytes()
    {
        return (function_exists('openssl_random_pseudo_bytes') && !self::$forceNoOpensslRandomPseudoBytes);
    }

    /**
     * Returns a version 3 or 5 UUID based on the hash (md5 or sha1) of a
     * namespace identifier (which is a UUID) and a name (which is a string)
     *
     * @param string $hash The hash to use when creating the UUID
     * @param int $version The UUID version to be generated
     *
     * @return $this
     */
    protected static function uuidFromHashedName($hash, $version)
    {
        // Set the version number
        $timeHi = hexdec(substr($hash, 12, 4)) & 0x0fff;
        $timeHi &= ~(0xf000);
        $timeHi |= $version << 12;

        // Set the variant to RFC 4122
        $clockSeqHi = hexdec(substr($hash, 16, 2)) & 0x3f;
        $clockSeqHi &= ~(0xc0);
        $clockSeqHi |= 0x80;

        $fields = array(
            'time_low' => substr($hash, 0, 8),
            'time_mid' => substr($hash, 8, 4),
            'time_hi_and_version' => sprintf('%04x', $timeHi),
            'clock_seq_hi_and_reserved' => sprintf('%02x', $clockSeqHi),
            'clock_seq_low' => substr($hash, 18, 2),
            'node' => substr($hash, 20, 12),
        );

        return new self($fields);
    }

    /**
     * Generates random bytes for use in version 4 UUIDs
     *
     * @param int $length
     *
     * @return string
     */
    private static function generateBytes($length)
    {
        if (self::hasOpensslRandomPseudoBytes()) {
            return openssl_random_pseudo_bytes($length);
        }

        $bytes = '';
        for ($i = 1; $i <= $length; $i++) {
            $bytes = chr(mt_rand(0, 255)) . $bytes;
        }

        return $bytes;
    }

    /**
     * Converts this UUID into a string representation
     *
     * @return string
     */
    public function toString()
    {
        return vsprintf(
            '%08s-%04s-%04s-%02s%02s-%012s',
            $this->fields
        );
    }
}
