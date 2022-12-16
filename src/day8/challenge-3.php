<?php

$grid = readTreeGrid();

$nunRows = count($grid);
$numColumns = count($grid[0]);
$maxScore = 0;

foreach($grid as $rowNo => $row) {
    for ($columnNo = 0; $columnNo < $numColumns; $columnNo++) {
        $score = calculateScenicScore($grid, $nunRows, $numColumns, $rowNo, $columnNo);
        if ($score > $maxScore) {
            $maxScore = $score;
        }
    }
}

print "Max scenic score: $maxScore\n";

function countNumVisible(array $trees, int $treeHeight): int
{
    $numVisible = 0;

    foreach ($trees as $tree)
    {
        $numVisible++;

        if ($tree >= $treeHeight) {
            return $numVisible;
        }
    }

    return $numVisible;
}

function calculateScenicScore(array $grid, int $numRows, int $numCols, int $rowNo, int $colNo): int
{
    $treeHeight = $grid[$rowNo][$colNo];
    $numVisibleUp = 0;
    $numVisibleDown = 0;

    // RIGHT
    $slice = array_slice($grid[$rowNo], $colNo + 1);
    $numVisibleRight = countNumVisible($slice, $treeHeight);

    // LEFT
    $numVisibleLeft = 0;
    if ($colNo > 0) {
        $slice = array_reverse(array_slice($grid[$rowNo], 0, $colNo));
        $numVisibleLeft = countNumVisible($slice, $treeHeight);
    }

    // DOWN
    $currentRow = $rowNo + 1;
    while ($currentRow <= $numRows - 1) {
        $xTree = $grid[$currentRow][$colNo];
        $numVisibleDown++;

        if ($xTree >= $treeHeight) {
            break;
        }

        $currentRow++;
    }

    // UP
    $currentRow = $rowNo - 1;
    while ($currentRow >= 0) {
        $xTree = $grid[$currentRow][$colNo];
        $numVisibleUp++;

        if ($xTree >= $treeHeight) {
            break;
        }

        $currentRow--;
    }

    //print "TH: $treeHeight, NVR: $numVisibleRight, NVL: $numVisibleLeft, NVD: $numVisibleDown, NVU: $numVisibleUp\n";

    return $numVisibleRight * $numVisibleLeft * $numVisibleDown * $numVisibleUp;
}


die("OK");

function readTreeGrid(): array
{
    $grid = [];

    $fp = fopen('input2.txt', 'r');
    if ($fp === false) {
        throw new Exception('Unable to load input data');
    }

    while ($line = fgets($fp)) {
        $line = trim($line);
        if (empty($line)) {
            break;
        }

        $elements = str_split($line);
        $grid[] = $elements;
    }

    fclose($fp);

    return $grid;
}