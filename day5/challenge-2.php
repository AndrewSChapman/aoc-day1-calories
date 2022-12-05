<?php
$stackManager = readInitialStacks();
executeInstructions($stackManager);
//$stackManager->dumpStacks();
$stackManager->printStackTops();

function executeInstructions(StackManager $stackManager): void
{
    $re = '/move (\d+) from (\d+) to (\d+)/m';

    $fp = fopen('input.txt', 'r');
    if ($fp === false) {
        throw new Exception('Unable to load input data');
    }

    while ($line = fgets($fp)) {
        $line = trim($line);
        if (empty($line)) {
            continue;
        }

        if (substr($line, 0, 4) !== 'move') {
            continue;
        }

        preg_match_all($re, $line, $matches, PREG_SET_ORDER, 0);

        $moveQty = (int)$matches[0][1];
        $moveFrom = (int)$matches[0][2];
        $moveTo = (int)$matches[0][3];

        $stackManager->move($moveFrom, $moveTo, $moveQty);
    }

    fclose($fp);
}

function readInitialStacks(): StackManager
{
    $stackManager = new StackManager();

    $fp = fopen('input.txt', 'r');
    if ($fp === false) {
        throw new Exception('Unable to load input data');
    }

    while ($line = fgets($fp)) {
        $line = trim($line);
        if (empty($line)) {
            break;
        }

        $pos = strpos($line, '[');
        if ($pos === false) {
            continue;
        }

        // Parse the line
        $chars = str_split($line);
        $value = '';
        $stackNo = 1;
        $inValue = false;

        $spaceCounter = 0;

        foreach ($chars as $char) {
            if ($char === ']') {
                $stackManager->addToStack($stackNo, $value);
                $inValue = false;
                $value = '';
                continue;
            }

            if ($char === ' ') {
                $spaceCounter++;

                // If it's the first space in a group of 4 spaces, increment the stack count
                if ($spaceCounter === 1) {
                    $stackNo++;
                }

                // If it's the 4th space, reset the space counter
                if ($spaceCounter === 4) {
                    $spaceCounter = 0;
                }
            }

            if ($char === '[') {
                $inValue = true;
                $spaceCounter = 0;
                continue;
            }

            if ($inValue) {
                $value .= $char;
            }
        }
    }

    $stackManager->flipAllStacks();

    return $stackManager;
}

class StackManager
{
    private array $stacks = [];

    public function addToStack(int $stackNo, string $stackValue): void
    {
        if (!isset($this->stacks[$stackNo])) {
            $this->stacks[$stackNo] = [];
        }

        $this->stacks[$stackNo][] = $stackValue;
    }

    public function flipAllStacks(): void
    {
        foreach ($this->stacks as $stackNo => $stack) {
            $stack = array_reverse($stack);
            $this->stacks[$stackNo] = $stack;
        }
    }

    public function move(int $stackFrom, int $stackTo, int $qty): void
    {
        if (!isset($this->stacks[$stackFrom])) {
            throw new Exception('Invalid stack FROM number');
        }

        if (count($this->stacks[$stackFrom]) < $qty) {
            throw new Exception("Invalid instruction - stack $stackFrom does not have $qty elements");
        }

        if (!isset($this->stacks[$stackTo])) {
            throw new Exception('Invalid stack TO number');
        }

//        print "Moving $qty from $stackFrom to $stackTo\n";
//        print_r($this->stacks[$stackFrom]);
//        print_r($this->stacks[$stackTo]);
//        print "-----------------\n";

        $values = [];
        for ($counter = 0; $counter < $qty; $counter++) {
            $values[] = array_pop($this->stacks[$stackFrom]);
        }

        if ($qty > 1) {
            $values = array_reverse($values);
        }

        foreach ($values as $value) {
            $this->stacks[$stackTo][] = $value;
        }

//        print_r($this->stacks[$stackFrom]);
//        print_r($this->stacks[$stackTo]);
//
//        die("\nDONE");
    }

    public function printStackTops(): void
    {
        $keys = array_keys($this->stacks);
        sort($keys);

        foreach($keys as $stackNo) {
            $stack = $this->stacks[$stackNo];

            if (empty($stack)) {
                $top = '';
            } else {
                $top = $stack[count($stack) - 1];
            }

            print "Stack: $stackNo, $top\n";
        }
    }

    public function dumpStacks(): void
    {
        print_r($this->stacks);
    }
}