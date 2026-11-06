<?php

namespace tests\unit;

use conquer\helpers\Json;

class JsonTest extends \Codeception\Test\Unit
{
    protected function _after()
    {
        Json::$jsPrefix = 'js:';
    }

    public function testJsPrefix()
    {
        $this->assertSame('{"f":function(){return 1;},"s":"x"}', Json::encode(['f' => 'js:function(){return 1;}', 's' => 'x']));
    }

    public function testScriptTagIsEscaped()
    {
        $json = Json::encode(['s' => '</script><script>alert(1)</script>']);
        $this->assertStringNotContainsString('</script>', $json);
        $this->assertSame(['s' => '</script><script>alert(1)</script>'], json_decode($json, true));
    }

    public function testPrefixCanBeDisabled()
    {
        Json::$jsPrefix = null;
        $this->assertSame('{"s":"js:alert(1)"}', Json::encode(['s' => 'js:alert(1)']));
    }
}
