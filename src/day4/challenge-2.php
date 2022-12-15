<?php
$assignmentData = loadAssignmentData();

$count = findPartiallyOverlappingItemCount($assignmentData);

print "Partially overlapping count: $count\n";

function findPartiallyOverlappingItemCount(array $assignmentData): int
{
    $numPartiallyOverlappingItems = 0;

    foreach ($assignmentData as $rangeData) {
        $arrayA = range($rangeData[0], $rangeData[1]);
        $arrayB = range($rangeData[2], $rangeData[3]);
        $intersection = array_intersect($arrayA, $arrayB);

        if (!empty($intersection)) {
            $numPartiallyOverlappingItems++;
        }
    }

    return $numPartiallyOverlappingItems;
}

function loadAssignmentData(): array
{
    $data = [];

    $fp = fopen('input.txt', 'r');
    if ($fp === false) {
        throw new Exception('Unable to load input data');
    }

    while ($line = fgets($fp)) {
        $line = trim($line);
        if (empty($line)) {
            continue;
        }

        $elements = explode(',', $line);
        $numElements = count($elements);
        if ($numElements !== 2) {
            throw new Exception('Invalid line element count');
        }

        $range1Elements = explode('-', $elements[0]);
        $range2Elements = explode('-', $elements[1]);
        if ((count($range1Elements) !== 2) || (count($range2Elements) !== 2)) {
            throw new Exception('Invalid range element count');
        }

        $data[] = [
            (int)$range1Elements[0], (int)$range1Elements[1],
            (int)$range2Elements[0], (int)$range2Elements[1],
        ];
    }

    fclose($fp);

    return $data;
}
