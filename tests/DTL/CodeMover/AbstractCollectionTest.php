<?php

namespace DTL\CodeMover;

class AbstractCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->col = $this->getMockForAbstractClass(
            'DTL\CodeMover\AbstractCollection'
        );
    }

    public function testArrayAccess()
    {
        $this->col['foobar'] = 'barfoo';
        $this->assertEquals('barfoo', $this->col['foobar']);

        unset($this->col['foobar']);
        $this->assertFalse(isset($this->col['foobar']));
    }

    public function testCountable()
    {
        $this->col[] = 'Hello';
        $this->col[] = 'Goodbye';

        $this->assertCount(2, $this->col);
    }

    public function testIterator()
    {
        $this->col[] = 'Hello';
        $this->col[] = 'Goodbye';
        $els = array();

        foreach ($this->col as $el) {
            $els[] = $el;
        }

        $this->assertCount(2, $els);
    }

    public function testKeyOf()
    {
        $this->col['foobar'] = 'barfoo';
        $this->assertEquals('foobar', $this->col->keyOf('barfoo'));
    }

    public function testFirstLast()
    {
        $this->col[] = 'Hello';
        $this->col[] = 'Goodbye';
        $this->col[] = 'Hello Again';
        $this->col->next(); // move the pointer to 1

        $this->assertEquals('Hello', $this->col->first());
        $this->assertEquals(1, $this->col->key());

        $this->assertEquals('Hello Again', $this->col->last());
        $this->assertEquals(1, $this->col->key());
    }

    public function testEach()
    {
        $elements = new \ArrayObject;
        $this->col->each(function ($el) use ($elements) {
            $elements[] = $el;
        });

        $this->assertSame($this->col->toArray(), (array) $elements);
    }

    public function testApply()
    {
        $col = $this->col;
        $me = $this;
        $this->col->apply(function ($arg) use ($me, $col) {
            $me->assertSame($col, $arg);
        });
    }

    public function testFilter()
    {
        $this->col[] = 'Foobar';
        $this->col[] = 'Boobar';
        $this->col[] = 'Foobar';

        $res = $this->col->filter(function ($value) {
            if ($value == 'Boobar') {
                return true;
            }

            return false;
        });

        $this->assertCount(1, $res);
        $this->assertEquals('Boobar', $res->current());
    }

    public function testNextPrevKey()
    {
        $this->col[] = 'Foobar';
        $this->col[] = 'Boobar';
        $this->col[] = 'Foobar';

        $this->assertEquals(0, $this->col->key());
        $this->col->next();
        $this->assertEquals(1, $this->col->key());
        $this->col->prev();
        $this->assertEquals(0, $this->col->key());
    }

    public function testBombOff()
    {
        $this->col->bomb(false);
        $this->col->explode('This will not explode');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage This will explode
     */
    public function testBombOn()
    {
        $this->col->bomb(true);
        $this->col->explode('This will explode');
    }

    public function testJoin()
    {
        $this->col[] = 'foo';
        $this->col[] = 'bar';
        $this->assertEquals('foobar', $this->col->join());
        $this->assertEquals('foo-bar', $this->col->join('-'));
    }

    public function testAssertSingleElementOff()
    {
        $this->col[] = 'foobar';
        $this->col->assertSingleElement();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Method "assertSingleElement" requires a single element
     */
    public function testAssertSingleElementOn()
    {
        $this->col[] = 'foobar';
        $this->col[] = 'foobar';
        $this->col->assertSingleElement();
    }

    public function testAddRemoveClearHas()
    {
        $this->col->add('foobar');
        $this->assertEquals(array('foobar'), $this->col->toArray());
        $this->col->clear();

        $this->col->add(array('foobar', 'barfoo'));
        $this->assertEquals(array('foobar', 'barfoo'), $this->col->toArray());

        $this->col->remove('foobar');
        $this->assertCount(1, $this->col->toArray());

        $this->assertTrue($this->col->has('barfoo'));
        $this->assertFalse($this->col->has('barbar'));
    }

    public function testTrim()
    {
        $this->col[] = 'one';
        $this->col[] = 'two';
        $this->col[] = 'three';
        $this->col[] = 'four';

        $res = $this->col->trim(1, 1);
        $this->assertCount(2, $res);
        $this->assertEquals(array('two', 'three'), $res->toArray());

        $res = $this->col->trim(1, 2);
        $this->assertCount(1, $res);
        $this->assertEquals(array('two'), $res->toArray());
    }

    public function testSubtract()
    {
        $this->col[] = 'one';
        $this->col[] = 'two';
        $this->col[] = 'three';
        $this->col[] = 'four';

        $res = $this->col->subtract(array('two', 'four'));
        $this->assertCount(2, $res);
        $this->assertEquals(array('one', 'three'), $res->toArray());
    }
}
