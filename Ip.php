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
     * Ensures an ip address is both a valid IP and does not fall within
     * a private network range.
     * @param $ip
     * @return bool
     */
    public static function validateIp($ip)
    {
        if (strtolower($ip) === 'unknown') {
            return false;
        }
        // generate ipv4 network address
        $ip = ip2long($ip);

        // if the ip is set and not equivalent to 255.255.255.255
        if (($ip !== false) && ($ip !== -1)) {
            // make sure to get unsigned long representation of ip
            // due to discrepancies between 32 and 64 bit OSes and
            // signed numbers (ints default to signed in PHP)
            $ip = sprintf('%u', $ip);
            // do private network range checking
            if ($ip >= 0 && $ip <= 50331647) {
                return false;
            }
            if ($ip >= 167772160 && $ip <= 184549375) {
                return false;
            }
            if ($ip >= 2130706432 && $ip <= 2147483647) {
                return false;
            }
            if ($ip >= 2851995648 && $ip <= 2852061183) {
                return false;
            }
            if ($ip >= 2886729728 && $ip <= 2887778303) {
                return false;
            }
            if ($ip >= 3221225984 && $ip <= 3221226239) {
                return false;
            }
            if ($ip >= 3232235520 && $ip <= 3232301055) {
                return false;
            }
            if ($ip >= 4294967040) {
                return false;
            }
        }
        return true;
    }


    public static function getLongIp()
    {
        $ipAddress = self::getIpAddress();
        if (self::validateIp($ipAddress)) {
            return ip2long($ipAddress);
        } else {
            return null;
        }
    }
}