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

function calculateScenicScore(array $grid, int $numRows, int $numCols, int $rowNo, int $colNo): int
{
    $treeHeight = $grid[$rowNo][$colNo];

    $numVisibleRight = 0;
    $numVisibleLeft = 0;
    $numVisibleUp = 0;
    $numVisibleDown = 0;

    // RIGHT
    $currentCol = $colNo + 1;
    while($currentCol <= $numCols - 1) {
        $xTree = $grid[$rowNo][$currentCol];
        $numVisibleRight++;

        if ($xTree >= $treeHeight) {
            break;
        }

        $currentCol++;
    }

    // LEFT
    $currentCol = $colNo - 1;
    while($currentCol >= 0) {
        $xTree = $grid[$rowNo][$currentCol];
        $numVisibleLeft++;

        if ($xTree >= $treeHeight) {
            break;
        }

        $currentCol--;
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

    $fp = fopen('input.txt', 'r');
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