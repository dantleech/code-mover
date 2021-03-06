<?php

namespace DTL\CodeMover;

use DTL\CodeMover\Tokenizer\Php\PhpToken;
use DTL\CodeMover\Tokenizer\Php\PhpTokenList;
use DTL\CodeMover\LineCollection;

class PhpTokenListTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->line = $this->getMockBuilder('DTL\CodeMover\Line')
            ->disableOriginalConstructor()->getMock();
        $this->t1 = new PhpToken('FOOBAR', 'arf');
        $this->t1->setLine($this->line);
        $this->t2 = new PhpToken('WHITESPACE', 'barf');
        $this->t2->setLine($this->line);
        $this->t3 = new PhpToken('FOOBAR', 'garf');
        $this->t3->setLine($this->line);

        $this->threeTokenList = new PhpTokenList(array($this->t1, $this->t2, $this->t3));
    }

    public function testFilterByType()
    {
        $tokenList2 = $this->threeTokenList->filterByType('FOOBAR');

        $this->assertCount(2, $tokenList2);
        $this->assertEquals(array($this->t1, $this->t3), $tokenList2->toArray());

        // invert the filter
        $tokenList3 = $this->threeTokenList->filterByType('FOOBAR', true);
        $this->assertCount(1, $tokenList3);
        $this->assertEquals(array($this->t2), $tokenList3->toArray());
    }

    public function testFilterByValue()
    {
        $tokenList2 = $this->threeTokenList->filterByType('FOOBAR');

        $this->assertCount(2, $tokenList2);
        $this->assertEquals(array($this->t1, $this->t3), $tokenList2->toArray());

        // invert the filter
        $tokenList3 = $this->threeTokenList->filterByType('FOOBAR', true);
        $this->assertCount(1, $tokenList3);
        $this->assertEquals(array($this->t2), $tokenList3->toArray());
    }


    public function testSeekType()
    {
        $token = $this->threeTokenList->seekType(T_WHITESPACE)->getToken();
        $this->assertEquals('barf', $token->getValue());

        // If bomb is false then the position should be reset when
        // a token is not found.
        $currentToken = $this->threeTokenList->getToken();
        $this->threeTokenList->bomb(false)->seekType('ASD');
        $this->assertSame($currentToken, $this->threeTokenList->getToken());

    }

    public function testSeekValue()
    {
        $token = $this->threeTokenList->seekValue('barf')->getToken();
        $this->assertEquals('WHITESPACE', $token->getType());

        // If bomb is false then the position should be reset when
        // a token is not found.
        $currentToken = $this->threeTokenList->getToken();
        $this->threeTokenList->bomb(false)->seekValue('ASD');
        $this->assertSame($currentToken, $this->threeTokenList->getToken());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Could not find token
     */
    public function testSeekValueNotFound()
    {
        $this->threeTokenList->seekValue('NOTKNOWN');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Could not find token
     */
    public function testSeekTypeNotFound()
    {
        $this->threeTokenList->seekType('NOTKNOWN');
    }

    public function testLines()
    {
        $lines = $this->threeTokenList->getLines();
        $this->assertNotNull($lines);
        $this->assertCount(1, $lines);
        $this->assertSame($this->line, $lines->first());
    }

    public function testToken()
    {
        $token = $this->threeTokenList->getToken();
        $this->assertSame($token, $this->t1);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage No token found at offset
     */
    public function testTokenNotFound()
    {
        $tokenList = new PhpTokenList();
        $token = $tokenList->getToken();
    }

    public function testGetValue()
    {
        $value = $this->threeTokenList->seekType(T_WHITESPACE)->getValue();
        $this->assertEquals('barf', $value);
    }

    public function testGetType()
    {
        $type = $this->threeTokenList->getType();
        $this->assertEquals('FOOBAR', $type);
    }

    public function provideBomb()
    {
        return array(
            array(true),
            array(false),
        );
    }

    /**
     * @dataProvider provideBomb
     */
    public function testBomb($bomb)
    {
        $tokenList = new PhpTokenList();
        if ($bomb) {
            $this->setExpectedException('RuntimeException');
        }

        $tokenList->bomb($bomb)->seekValue('asdasdasd');
    }

    public function testCastArray()
    {
        $list = new PhpTokenList();
        $list->addToken('ARRAY', 'array');
        $res = $list->bomb(false)->castArray();
        $this->assertInstanceOf('DTL\CodeMover\Tokenizer\Php\PhpArray', $res);
    }

    public function testAddRawToken()
    {
        $tokenList = new PhpTokenList;
        $tokenList->addRawToken('foobar');
        $tokenList->addRawToken('barfoo');

        $this->assertEquals('foobarbarfoo', (string) $tokenList);
    }

    public function testAddRawTokenAfter()
    {
        $this->threeTokenList->addRawTokenAfter(
            $this->t1, 'This IS Raw'
        );

        $this->assertEquals('This IS Raw', $this->threeTokenList->offsetGet(1)->getValue());
    }

    public function testAddToken()
    {
        // add real token
        $l = new PhpTokenList;
        $l->addToken($this->t1);

        $this->assertSame($this->t1, $l->getToken());

        // test adding from string valuees
        $l = new PhpTokenList;
        $l->addToken(T_WHITESPACE, ' ');

        $this->assertSame(' ', $l->getToken()->getValue());
    }

    public function testWith()
    {
        $tList = new PhpTokenList;
        $me = $this;
        $res = $tList->apply(function ($arg) use ($me, $tList) {
            $me->assertSame($tList, $arg);
        });

        $this->assertSame($res, $tList);
    }
}







