<?php

namespace tests\unit;

use conquer\helpers\Ip;

class IpTest extends \Codeception\Test\Unit
{
    protected function _after()
    {
        Ip::$trustedProxies = [];
        unset($_SERVER['HTTP_X_FORWARDED_FOR'], $_SERVER['HTTP_CLIENT_IP']);
    }

    public function testSpoofedHeadersAreIgnoredByDefault()
    {
        $_SERVER['REMOTE_ADDR'] = '203.0.113.5';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.1';
        $_SERVER['HTTP_CLIENT_IP'] = '198.51.100.2';
        $this->assertSame('203.0.113.5', Ip::getIpAddress());
    }

    public function testForwardedForIsUsedBehindTrustedProxy()
    {
        Ip::$trustedProxies = ['10.0.0.0/8'];
        $_SERVER['REMOTE_ADDR'] = '10.0.0.2';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.9, 203.0.113.7, 10.0.0.1';
        $this->assertSame('203.0.113.7', Ip::getIpAddress(), 'right-most untrusted address');
    }

    public function testGarbageInForwardedForFallsBackToRemoteAddr()
    {
        Ip::$trustedProxies = ['127.0.0.1'];
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = 'not-an-ip';
        $this->assertSame('127.0.0.1', Ip::getIpAddress());
    }
}
