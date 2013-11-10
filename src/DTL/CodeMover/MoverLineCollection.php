<?php

namespace DTL\CodeMover;

use Doctrine\Common\Collections\ArrayCollection;

class MoverLineCollection extends ArrayCollection
{
    public function match($pattern)
    {
        foreach ($this as $line) {
            $patterns = (array) $patterns;
            foreach ($patterns as $pattern) {
                $pattern = $this->delimitRegex($pattern);
                $match = preg_match($pattern, $this->line);

                if ($match) {
                    return true;
                }
            }
        }

        return false;
    }

    public function replace($pattern, $replacement)
    {
        foreach ($this as $line) {
            $line->replace($pattern, $replacement);
        }

        return $this;
    }

    public function delete()
    {
        foreach ($this as $line) {
            $line->delete();
        }

        return $this;
    }

    public function dump()
    {
        echo "Dumping ".$this->count()." lines\n";
        foreach ($this as $line) {
            echo $line."\n";
        }
        echo "Finished dumping\n";
        die(1);
    }

    public function findLine($pattern)
    {
        // note we return a collection even for a singular method because
        // we want not to crash when lines do not exist.
        $lines = new MoverLineCollection();
        foreach ($this as $line) {
            if ($line->match($pattern)) {
                $lines->add($line);
                break;
            }
        }

        return $lines;
    }

    public function findLines($patterns)
    {
        $patterns = (array) $patterns;

        $lineCollection = new MoverLineCollection();
        foreach($this as $line) {
            if ($line->match($patterns)) {
                $lineCollection->add($line);
            }
        }

        return $lineCollection;
    }

    public function tokenize()
    {
        $tokenList = new PhpTokenList();
        foreach ($this as $line) {
            foreach ($line->tokenize() as $token) {
                $tokenList->add($token);
            }
        }

        return $tokenList;
    }

    public function tokenizeStatement()
    {
        $this->assertSingleElement(__METHOD__);
        foreach ($this as $line) {
            return $line->tokenizeStatement();
        }
    }

    public function getLineNo()
    {
        $this->assertSingleElement(__METHOD__);

        foreach ($this as $line) {
            return $line->getLineNo();
        }

        return $this;
    }

    protected function assertSingleElement($method)
    {
        if ($this->count() > 1) {
            throw new \InvalidArgumentException(sprintf('Method "%s" requires a single element, this collection of lines contains "%s"',
                $method, $this->count()
            ));
        }
    }

    public function unwrap()
    {
        $this->assertSingleElement(__METHOD__);
        foreach ($this as $line) {
            return $line;
        }
    }

    public function nextLine()
    {
        $this->assertSingleElement(__METHOD__);
        foreach ($this as $line) {
            return $line->nextLine();
        }

        return null;
    }

    public function prevLine()
    {
        $this->assertSingleElement(__METHOD__);
        foreach ($this as $line) {
            return $line->prevLine();
        }

        return null;
    }
}
