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
}
