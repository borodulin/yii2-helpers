<?php

namespace tests\unit;

use conquer\helpers\Array2Xml;

class Array2XmlTest extends \Codeception\Test\Unit
{
    public function testEncode()
    {
        $this->assertSame(
            '<root><a>1</a><list><element>x</element><element>&lt;y&gt; &amp; &quot;z&quot;</element></list><empty/></root>',
            Array2Xml::encodeXml(['a' => 1, 'list' => ['x', '<y> & "z"'], 'empty' => null])
        );
    }

    public function testKeysCannotInjectMarkup()
    {
        $this->expectException(\InvalidArgumentException::class);
        Array2Xml::encodeXml(['a><admin>1</admin><b' => 'x']);
    }

    public function testRootTagIsValidated()
    {
        $this->expectException(\InvalidArgumentException::class);
        Array2Xml::encodeXml([], 'root><x');
    }

    public function testInvalidUtf8IsNotDropped()
    {
        $xml = Array2Xml::encodeXml(['n' => "\xff text"]);
        $this->assertStringContainsString('text', $xml);
        $this->assertNotFalse(simplexml_load_string($xml));
    }
}
