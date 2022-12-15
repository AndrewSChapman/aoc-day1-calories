<?php

$grid = readTreeGrid();

$numOuterTrees = countOuterTrees($grid);
print "Outer tree count: $numOuterTrees\n";

$numInnerTrees = countVisibleInnerTrees($grid);
print "Inner tree count: $numInnerTrees\n";

$totalVisible = $numOuterTrees + $numInnerTrees;

print "Total visible: $totalVisible\n";

function countOuterTrees(array $grid): int
{
    $width = count($grid[0]) - 2; // - 2 because we don't want to count the same trees in the height calc
    $height = count($grid);

    return ($width * 2) + ($height * 2);
}

function countVisibleInnerTrees(array $grid): int {
    $numRows = count($grid);
    $maxWidth = count($grid[0]) - 1;
    $rowNo = 0;
    $numVisibleTrees = 0;

    foreach ($grid as $row) {
        $rowNo++;

        // Skip the outer rows - we've already counted these trees
        if (($rowNo === 1) || ($rowNo === $numRows)) {
            continue;
        }

        for ($x = 1; $x < $maxWidth; $x++) {
            $treeHeight = ($row[$x]);
            $isHigherInRow = isHigherThanTheRest($treeHeight, $x, $row);
            if ($isHigherInRow) {
                $numVisibleTrees++;
                continue;
            }

            // If the tree wasn't visible in the row, maybe it is in the column
            $columnTrees = [];
            for ($i = 0; $i < $numRows; $i++) {
                $columnTrees[] = $grid[$i][$x];
            }

            $isHigherInColumn = isHigherThanTheRest($treeHeight, $rowNo - 1, $columnTrees);
            if ($isHigherInColumn) {
                $numVisibleTrees++;
                continue;
            }
        }
    }

    return $numVisibleTrees;
}

function isHigherThanTheRest(int $height, int $pos, array $allTrees): bool
{
    $numTrees = count($allTrees);

    // Scan from left to right
    $isVisible = true;

    for ($x = 0; $x < $numTrees; $x++) {
        if ($x === $pos) {
            return true;
        }

        if ($height === (int)$allTrees[$x]) {
            if ($pos > $x) {
                $isVisible = false;
                break;
            }
        }

        if ($height < $allTrees[$x]) {
            $isVisible = false;
            break;
        }
    };

    if ($isVisible) {
        return true;
    }

    $loopStart = $numTrees - 1;

    // Also check from right to left
    for ($x = $loopStart; $x >= 0; $x--) {
        if ($x === $pos) {
            return true;
        }

        if ($height === (int)$allTrees[$x]) {
            if ($pos < $x) {
                return false;
            }
        }

        if ($height < (int)$allTrees[$x]) {
            return false;
        }
    }

    return true;
}

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