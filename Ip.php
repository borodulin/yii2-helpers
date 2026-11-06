<?php
/**
 * @link https://github.com/borodulin/yii2-helpers
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-helpers/blob/master/LICENSE
 */

namespace conquer\helpers;

use yii\helpers\IpHelper;

/**
 * Class Ip
 * @package conquer\helpers
 * @link http://stackoverflow.com/questions/15699101/get-the-client-ip-address-using-php
 */
class Ip
{
    /**
     * IP addresses or CIDR ranges of reverse proxies whose X-Forwarded-For header is trusted,
     * e.g. ['10.0.0.0/8', '127.0.0.1'].
     * By default no proxy is trusted and REMOTE_ADDR is returned: client-supplied headers such as
     * Client-IP or X-Forwarded-For can be spoofed by anyone.
     * @var string[]
     */
    public static $trustedProxies = [];

    /**
     * Returns the client IP address.
     * X-Forwarded-For is used only when the request comes from a trusted proxy;
     * the right-most address that is not a trusted proxy is returned.
     * @return string
     */
    public static function getIpAddress()
    {
        $remoteAddr = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
        if (!static::isTrustedProxy($remoteAddr) || empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $remoteAddr;
        }
        $chain = array_reverse(array_map('trim', explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])));
        foreach ($chain as $ip) {
            if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
                break;
            }
            if (!static::isTrustedProxy($ip)) {
                return $ip;
            }
        }
        return $remoteAddr;
    }

    /**
     * @param string $ip
     * @return bool
     */
    public static function isTrustedProxy($ip)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return false;
        }
        foreach (static::$trustedProxies as $range) {
            if (IpHelper::inRange($ip, strpos($range, '/') === false ? $range . (strpos($range, ':') === false ? '/32' : '/128') : $range)) {
                return true;
            }
        }
        return false;
    }

    /**
     * IPv4 ranges that are not covered by FILTER_FLAG_NO_PRIV_RANGE / FILTER_FLAG_NO_RES_RANGE
     * on all supported PHP versions, but are not public unicast addresses either.
     * @var string[]
     */
    private static $_nonPublicRanges = [
        '100.64.0.0/10',   // carrier-grade NAT
        '192.0.0.0/24',    // IETF protocol assignments
        '192.0.2.0/24',    // TEST-NET-1
        '198.18.0.0/15',   // benchmarking
        '198.51.100.0/24', // TEST-NET-2
        '203.0.113.0/24',  // TEST-NET-3
        '224.0.0.0/4',     // multicast
        '2001:db8::/32',   // IPv6 documentation
        'ff00::/8',        // IPv6 multicast
    ];

    /**
     * Ensures an ip address is both a valid IP (v4 or v6) and a public one:
     * private, loopback, link-local, reserved, multicast and documentation ranges are rejected.
     * @param string $ip
     * @return bool
     */
    public static function validateIp($ip)
    {
        if (!is_string($ip) || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return false;
        }
        foreach (self::$_nonPublicRanges as $range) {
            if (IpHelper::inRange($ip, $range)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Returns the client IPv4 address as an integer, or null when it is not a public IPv4 address.
     * @return int|null
     */
    public static function getLongIp()
    {
        $ipAddress = self::getIpAddress();
        if (self::validateIp($ipAddress) && filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
            return ip2long($ipAddress);
        }
        return null;
    }
}