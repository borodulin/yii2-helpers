<?php

namespace tests\unit;

use conquer\helpers\Curl;

class CurlTest extends \Codeception\Test\Unit
{
    private static $server;
    private static $baseUrl;

    public static function setUpBeforeClass(): void
    {
        $socket = stream_socket_server('tcp://127.0.0.1:0');
        $port = (int)substr(strrchr(stream_socket_get_name($socket, false), ':'), 1);
        fclose($socket);
        self::$server = proc_open(
            [PHP_BINARY, '-S', "127.0.0.1:$port", codecept_data_dir('server.php')],
            [1 => ['file', '/dev/null', 'w'], 2 => ['file', '/dev/null', 'w']],
            $pipes
        );
        self::$baseUrl = "http://127.0.0.1:$port";
        for ($i = 0; $i < 100; $i++) {
            if ($fp = @fsockopen('127.0.0.1', $port)) {
                fclose($fp);
                return;
            }
            usleep(50000);
        }
        throw new \RuntimeException('Built-in server did not start');
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$server) {
            proc_terminate(self::$server);
            proc_close(self::$server);
        }
    }

    public function testResultIsResetBetweenExecutions()
    {
        $curl = new Curl('http://127.0.0.1:1/');
        $this->assertFalse($curl->execute());
        $this->assertSame(CURLE_COULDNT_CONNECT, $curl->getErrorCode());
        $this->assertNotEmpty($curl->getErrorMessage());

        $this->assertTrue($curl->execute(self::$baseUrl . '/cookie'));
        $this->assertSame(0, $curl->getErrorCode());
        $this->assertNull($curl->getErrorMessage());

        $curl->execute();
        $this->assertSame(1, substr_count($curl->getHeader(), 'Set-Cookie'), 'headers of the previous request are dropped');
        $this->assertStringStartsWith('n=', $curl->getCookies());
    }

    public function testAutoCookieKeepsCookiesAcrossRedirects()
    {
        $curl = new Curl(self::$baseUrl . '/redirect');
        $curl->execute();
        $this->assertSame('no-session', $curl->getContent());

        $curl = new Curl(self::$baseUrl . '/redirect');
        $curl->setAutoCookie(true);
        $curl->execute();
        $this->assertSame('session=abc', $curl->getContent());
    }

    public function testMultiExecReportsErrors()
    {
        $ok = new Curl(self::$baseUrl . '/');
        $failed = new Curl('http://127.0.0.1:1/');
        Curl::multiExec([$ok, $failed]);

        $this->assertSame(0, $ok->getErrorCode());
        $this->assertSame('ok', $ok->getContent());
        $this->assertSame(CURLE_COULDNT_CONNECT, $failed->getErrorCode());
        $this->assertNotEmpty($failed->getErrorMessage());
    }

    public function testOnlyHttpProtocolsAreAllowed()
    {
        $curl = new Curl('file:///etc/passwd');
        $this->assertFalse($curl->execute());
        $this->assertSame(CURLE_UNSUPPORTED_PROTOCOL, $curl->getErrorCode());
        $this->assertEmpty($curl->getContent());

        $curl = new Curl(self::$baseUrl . '/file-redirect');
        $this->assertFalse($curl->execute());
        $this->assertSame(CURLE_UNSUPPORTED_PROTOCOL, $curl->getErrorCode());
        $this->assertStringNotContainsString('root:', (string)$curl->getContent());
    }
}
