<?php

namespace tests\unit;

use conquer\helpers\Temporary;

class TemporaryTest extends \Codeception\Test\Unit
{
    public function testDirIsPrivateAndUnpredictable()
    {
        $a = (new Temporary())->dir();
        $b = (new Temporary())->dir();
        $this->assertNotSame((string)$a, (string)$b);
        $this->assertDirectoryExists((string)$a);
        $this->assertSame('0700', substr(sprintf('%o', fileperms((string)$a)), -4));
        $a->clear();
        $b->clear();
        $this->assertFalse(is_dir((string)$a));
    }

    public function testClearDoesNotFollowSymlinks()
    {
        $outside = (new Temporary('outside'))->dir();
        file_put_contents($outside . '/precious.txt', 'x');

        $tmp = (new Temporary())->dir();
        mkdir($tmp . '/sub');
        file_put_contents($tmp . '/sub/file.txt', 'x');
        symlink((string)$outside, $tmp . '/sub/link-to-dir');
        symlink($outside . '/precious.txt', $tmp . '/link-to-file');

        $tmp->clear();

        $this->assertFalse(file_exists((string)$tmp), 'temporary directory is removed');
        $this->assertFileExists($outside . '/precious.txt', 'link targets are not touched');
        $outside->clear();
    }

    public function testFile()
    {
        $file = (new Temporary())->file();
        $this->assertFileExists((string)$file);
        $file->clear();
        $this->assertFileDoesNotExist((string)$file);
    }

    public function testUnusedInstanceIsHarmless()
    {
        $t = new Temporary();
        $this->assertSame('', (string)$t);
        $t->clear();
    }
}
