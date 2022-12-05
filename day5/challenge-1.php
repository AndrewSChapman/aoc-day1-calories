<?php
$initialStacks = readInitialStacks();

print_r($initialStacks);

function readInitialStacks(): array
{
    $initialStacks = [];

    $fp = fopen('input.txt', 'r');
    if ($fp === false) {
        throw new Exception('Unable to load input data');
    }

    while ($line = fgets($fp)) {
        $line = trim($line);
        if (empty($line)) {
            print "End of initial stacks";
            break;
        }
    }

    return $initialStacks;
}
