<?php

namespace tests\unit;

use conquer\helpers\XPath;

class XPathTest extends \Codeception\Test\Unit
{
    public function testUpdateEscapesValues()
    {
        $x = new XPath('<r a="1"><b>x</b><c>y</c></r>');
        $this->assertTrue($x->updateOne('//b', 'Tom & <Jerry>'));
        $this->assertTrue($x->update('//@a', 'a & "b"'));
        $this->assertSame(1, $x->updateAll(['//c' => '1 < 2']));
        $doc = $x->getDoc();
        $this->assertSame('Tom & <Jerry>', $x->query('//b'));
        $this->assertSame('a & "b"', $doc->documentElement->getAttribute('a'));
        $this->assertSame('1 < 2', $x->query('//c'));
    }

    public function testEvaluate()
    {
        $x = new XPath('<r><a>1</a><a>2</a></r>');
        $this->assertSame(0.0, $x->evaluate('count(//z)'));
        $this->assertSame(2.0, $x->evaluate('count(//a)'));
        $this->assertSame('1', $x->evaluate('//a'));
        $this->assertNull($x->evaluate('//z'));
        $this->assertNull($x->evaluate('string(//z)'));
        $this->assertSame(0.0, $x->evalute('count(//z)'), 'deprecated alias');
    }

    public function testXmlToArray()
    {
        $x = new XPath('<r><!-- note --><a>1</a><a>2</a><b><![CDATA[text]]></b><c><d>3</d></c></r>');
        $this->assertSame(
            ['a' => ['1', '2'], 'b' => 'text', 'c' => ['d' => '3']],
            XPath::xmlToArray($x->getDoc()->documentElement)
        );
    }

    public function testClearTextConcat()
    {
        $this->assertSame('a b c', XPath::clearTextConcat(["  a\n", ['b', null, ''], " c "]));
    }
}
