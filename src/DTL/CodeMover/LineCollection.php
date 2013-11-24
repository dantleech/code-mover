<?php

namespace DTL\CodeMover;

use Doctrine\Common\Collections\ArrayCollection;
use DTL\CodeMover\Tokenizer\Php\PhpTokenList;
use DTL\CodeMover\AbstractCollection;
use DTL\CodeMover\Line;

class LineCollection extends AbstractCollection implements LineInterface
{
    public function match($patterns)
    {
        foreach ($this as $line) {
            if ($matches = $line->match($patterns)) {
                return $matches;
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

    public function removeElement($element)
    {
        $newLines = array();
        foreach ($this as $line) {
            if ($line !== $element) {
                $newLines[] = $line;
            }
        }

        $this->clear();

        foreach ($newLines as $newLine) {
            $this->add($newLine);
        }

        return true;
    }

    public function delete()
    {
        foreach ($this->elements as $el) {
            $el->delete();
        }

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function dump()
    {
        echo "Dumping ".$this->count()." lines\n";
        echo $this->getRaw()."\n";
        echo "Finished dumping\n";
        die(1);
    }

    public function getRaw()
    {
        $raw = array();
        return implode("\n", $this->toArray());
    }

    public function findLine($pattern)
    {
        // note we return a collection even for a singular method because
        // we want not to crash when lines do not exist.
        $lines = new LineCollection();
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

        $lineCollection = new LineCollection();
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

    /**
     * Tokenize until the number of $leftString equals the number of $rightString
     */
    public function tokenizeBetween($leftString, $rightString)
    {
        foreach ($this->elements as $line) {
            return $line->tokenizeBetween($leftString, $rightString);
        }

        return new PhpTokenList();
    }

    public function getLineNo()
    {
        $this->assertSingleElement(__METHOD__);

        foreach ($this as $line) {
            return $line->getLineNo();
        }

        return $this;
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

    public function addLine($line, $offset = null)
    {
        return $this->addLines(array($line), $offset);
    }

    public function addLines($lines, $offset = null)
    {
        $offset = $offset === null ? -1 : $offset;

        $newLines = array();

        foreach ($this as $i => $existingLine) {
            if ($i == $offset) {

                foreach ($lines as $line) {
                    if ($line instanceof Line) {
                        $newLines[] = $line;
                    } else {
                        $newLines[] = new Line($this, $line);
                    }
                }
            }

            $newLines[] = $existingLine;
        }

        if ($offset == -1) {
            foreach ($lines as $line) {
                $newLines[] = new Line($this, $line);
            }
        }

        $this->clear();

        foreach ($newLines as $newLine) {
            $this->elements[] = $newLine;
        }

        return $this;
    }

    public function addLinesAfter(LineInterface $targetLine, $lines)
    {
        $offset = $this->keyOf($targetLine);
        $this->addLines($lines, $offset + 1);
    }

    public function addLinesBefore(LineInterface $targetLine, $lines)
    {
        $offset = $this->keyOf($targetLine->getSingle());
        $this->addLines($lines, $offset);
    }

    public function addLineAfter(LineInterface $targetLine, $line)
    {
        $offset = $this->keyOf($targetLine->getSingle());
        $this->addLinesAfter($targetLine, array($line), $offset + 1);
    }

    public function addLineBefore(LineInterface $targetLine, $line)
    {
        $offset = $this->keyOf($targetLine->getSingle());
        $this->addLinesBefore($targetLine, array($line));
    }

    public function getLineNeighbor(Line $line, $before = false)
    {
        $index = $this->keyOf($line);
        if ($before) {
            --$index;
        } else {
            ++$index;
        }

        if ($this->offsetExists($index)) {
            return $this->offsetGet($index);
        }

        return null;
    }
}
