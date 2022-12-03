<?php
$groupData = loadGroupData();

$totalPriority = 0;

foreach ($groupData as $groups) {
    $group1 = $groups[0];
    $group2 = $groups[1];

    $letterPriorityGroup1 = getGroupPriority($group1);
    $letterPriorityGroup2 = getGroupPriority($group2);

    $totalPriority += $letterPriorityGroup1 + $letterPriorityGroup2;
}

print "Total priority is: $totalPriority\n";

function getGroupPriority(array $group): int
{
    $commonLetterIntersect = array_intersect(...$group);

    if (count($commonLetterIntersect) < 1) {
        throw new Exception('Invalid array intersection for group');
    }

    $firstKey = array_keys($commonLetterIntersect)[0];
    $commonLetter = $commonLetterIntersect[$firstKey];
    return getLetterPriority($commonLetter);
}

function getLetterPriority(string $letter): int
{
    $asciiCode = ord($letter);

    // A - Z
    if (($asciiCode >= 65) && ($asciiCode <= 90)) {
        // A - Z must have a priority value range of  27 - 52
        // Converting an ascii value of 65 to a priority value of 27
        // requires a deduction of 38.
        return $asciiCode - 38;
    }

    // a-z
    if (($asciiCode >= 97) && ($asciiCode <= 122)) {
        // A - Z must have a priority value range of  1 - 26
        // Converting an ascii value of 97 to a priority value of 1
        // requires a deduction of 96.
        return $asciiCode - 96;
    }

    throw new Exception("Invalid letter $letter");
}

function loadGroupData(): array
{
    $data = [];

    $fp = fopen('input.txt', 'r');
    if ($fp === false) {
        throw new Exception('Unable to load input data');
    }

    $group1 = [];
    $group2 = [];

    $groupIndex = 0;

    while ($line = fgets($fp)) {
        $line = trim($line);
        if (empty($line)) {
            continue;
        }

        $groupIndex++;

        $elements = str_split($line);

        if ($groupIndex <= 3) {
            $group1[] = $elements;
        } else {
            $group2[] = $elements;
        }

        if ($groupIndex === 6) {
            if (count($group1) !== count($group2)) {
                throw new Exception('Invalid group counts');
            }

            $data[] = [$group1, $group2];

            $groupIndex = 0;
            $group1 = [];
            $group2 = [];
        }
    }

    fclose($fp);

    return $data;
}
