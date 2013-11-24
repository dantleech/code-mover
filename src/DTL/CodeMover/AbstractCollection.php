<?php

namespace DTL\CodeMover;

abstract class AbstractCollection implements \Countable, \ArrayAccess, \Iterator
{
    protected $position = 0;
    protected $elements;
    protected $bomb = true;

    public function __construct($elements = array())
    {
        $this->elements = $elements;
    }

    public function getRaw()
    {
        throw new \Exception('Implement me!');
    }

    abstract public function dump();

    protected function throwException(\Exception $e)
    {
        if ($this->bomb) {
            throw $e;
        }
    }

    /**
     * Increment the array pointer
     */
    public function next()
    {
        $this->position++;
        return $this;
    }

    /**
     * Deincrement the array pointer
     */
    public function prev()
    {
        $this->position--;
        return $this;
    }

    /**
     * Set to false to disable non-critical exceptions
     *
     * e.g. ->bomb(false)->seekType('ARRAY')->apply(function () {});
     *
     * Will not throw an exception if ARRAY is not found.
     *
     * @param boolean $boolean
     *
     * @return AbstractCollection
     */
    public function bomb($boolean)
    {
        $this->bomb = $boolean;
        return $this;
    }

    /**
     * Throw an exception if bomb is true
     *
     * @param string $message
     */
    public function explode($message)
    {
        $this->throwException(new \RuntimeException($message));
        return $this;
    }

    public function assertSingleElement()
    {
        $trace = debug_backtrace();
        $caller = array_shift($trace);
        $method = $caller['function'];

        if ($this->count() > 1) {
            throw new \RuntimeException(sprintf('Method "%s" requires a single element, this collection of lines contains "%s"',
                $method, $this->count()
            ));
        }
    }

    /**
     * Returns the values of the array as a string
     * joined with the given delimiter.
     *
     * @param string $delimiter
     *
     * @return string
     */
    public function join($delimiter = '')
    {
        $raw = array();
        return implode($delimiter, $this->toArray());
    }

    /**
     * Add an element
     *
     * @param mixed
     *
     * @return self
     */
    public function add($values)
    {
        if (!is_array($values) && !$values instanceof AbstractCollection) {
            $values = array($values);
        }

        foreach ($values as $value) {
            $this->elements[] = $value;
        }

        return $this;
    }

    /**
     * Clear the elements
     *
     * @return self
     */
    public function clear()
    {
        $this->elements = array();

        return $this;
    }

    /**
     * Return true if the collection has the target element
     *
     * @param mixed
     *
     * @return boolean
     */
    public function has($targetElement)
    {
        return in_array($targetElement, $this->elements, true);
    }

    /**
     * Remove the target element
     *
     * @param mixed
     *
     * @return self
     */
    public function remove($targetElement)
    {
        $elements = array();
        foreach ($this as $element) {
            if ($element!== $targetElement) {
                $elements[] = $element;
            }
        }

        $this->clear();

        foreach ($elements as $el) {
            $this->add($el);
        }

        return $this;
    }

    /**
     * Trim the collection by the given offsets
     *
     * @param integer $leftOffset
     * @param integer $rightOffset
     *
     * @return AbstractCollection
     */
    public function trim($leftOffset, $rightAmount)
    {
        $tokenList = new static();

        $i = 0;
        $rightLimit = $this->count() - $rightAmount;

        foreach ($this as $token) {
            if ($i >= $leftOffset && $i < $rightLimit) {
                $tokenList->add($token);
            }
            $i++;
        }

        return $tokenList;
    }

    /**
     * Subtrcat the given values from the collection
     *
     * @param array|AbstractCollection $targetCollection
     *
     * @return AbstractCollection
     */
    public function subtract($targetCollection)
    {
        if (!$targetCollection instanceof AbstractCollection) {
            $targetCollection = new static($targetCollection);
        }

        $collection = new static();

        foreach ($this->elements as $el) {
            if (false === $targetCollection->has($el)) {
                $collection->add($el);
            }
        }

        return $collection;
    }

    /**
     * Return THE single element and lay a bomb if there is more
     * than one element.
     *
     * @return mixed
     */
    public function getSingle()
    {
        $this->assertSingleElement();
        return $this->first();
    }

    /**
     * Return the underlying array for this collection
     *
     * @return array
     */
    public function toArray()
    {
        return $this->elements;
    }

    /**
     * Return the element at the given offset from the current
     * position without changing the position.
     *
     * @param integer $offset - default 1
     *
     * @return self
     */
    public function checkAhead($offset = 1)
    {
        return $this->offsetGet($this->position + $offset);
    }

    /**
     * Iterator
     */
    public function rewind()
    {
        $this->position = 0;
        return $this;
    }

    /**
     * Iterator
     */
    public function current() 
    {
        return $this->elements[$this->position];
    }

    /**
     * Iterator
     */
    function key() 
    {
        return $this->position;
    }

    /**
     * Iterator
     */
    public function valid() 
    {
        return isset($this->elements[$this->position]);
    }

    /**
     * Array Access
     */
    public function offsetSet($offset, $value) 
    {
        if (is_null($offset)) {
            $this->elements[] = $value;
        } else {
            $this->elements[$offset] = $value;
        }
    }

    /**
     * Array Access
     */
    public function offsetExists($offset) 
    {
        return isset($this->elements[$offset]);
    }

    /**
     * Array Access
     */
    public function offsetUnset($offset) 
    {
        unset($this->elements[$offset]);
    }

    /**
     * Array Access
     */
    public function offsetGet($offset) 
    {
        return isset($this->elements[$offset]) ? $this->elements[$offset] : null;
    }

    /**
     * Countable
     */
    public function count()
    {
        return count($this->elements);
    }

    /**
     * Return the key of the given target element
     */
    public function keyOf($targetElement)
    {
        foreach ($this->elements as $key => $el) {
            if ($el === $targetElement) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Return the first element (without modifying the position)
     *
     * @return mixed
     */
    public function first()
    {
        $values = array_values($this->toArray());
        return array_shift($values);
    }

    /**
     * Return the last element (without modifying the position)
     *
     * @return mixed
     */
    public function last()
    {
        $values = array_values($this->toArray());
        return array_pop($values);
    }

    /**
     * Apply a closure to each element
     *
     * @param \Closure $closure
     *
     * @return AbstractCollection
     */
    public function each(\Closure $closure)
    {
        foreach ($this->elements as $element) {
            $closure($element);
        }

        return $this;
    }

    /**
     * Apply a closure to this object
     *
     * @param \Closure $closure
     *
     * return AbstractCollection
     */
    public function apply(\Closure $closure)
    {
        if ($this->count()) {
            $closure($this);
        }

        return $this;
    }

    /**
     * Use closure to filter collection
     *
     * @param \Closure $closure
     *
     * @return AbstractCollection
     */
    public function filter(\Closure $closure)
    {
        return new static(array_values(array_filter($this->elements, $closure)));
    }
}
