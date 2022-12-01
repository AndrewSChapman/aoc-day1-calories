<?php
/**
 * We have a data file "data.txt" which contains calorie data for Elves.
 * Each Elf has an inventory of food, where each food item contains a certain number of calories.
 * Each line is a new food item for that Elf - the value is always an integer.
 * A blank line separates the data for each elf.
 *
 * This script scans the input data and finds the elf with the highest number of calories
 * and reports back the Elf number and total calories for that Elf.
 *
 * Not over engineering this - keeping the logic simple and straight to the point.
 */
try {
    [$elfNo, $highestElfCalories] = findHighestElfCalories();
    print "The elf with the highest calories was elf number $elfNo, with $highestElfCalories calories.\n";
} catch (Exception $exception) {
    print "Caught exception whilst processing data: {$exception->getMessage()}\n";
}


/**
 * Reads the input file line by line, adding the total number of calories
 * together and keeping a track of which Elf number we're up to.
 * When a new line is encountered, we reset the calorie counter and increment
 * the Elf counter.
 * @return int[]
 * @throws Exception
 */
function findHighestElfCalories(): array
{
    $fp = fopen('data.txt', 'r');
    if ($fp === false) {
        throw new Exception('Unable to load elf calorie data');
    }

    $elfNo = 1;
    $elfNoWithHighestCalories = 0;

    $highestElfCalories = 0;
    $totalElfCalories = 0;

    while ($line = fgets($fp)) {
        $line = trim($line);
        if (empty($line)) {
            if ($totalElfCalories > $highestElfCalories) {
                $highestElfCalories = $totalElfCalories;
                $elfNoWithHighestCalories = $elfNo;
            }

            $totalElfCalories = 0;
            $elfNo++;
            continue;
        }

        $totalElfCalories += (int)$line;
    }

    fclose($fp);

    return [$elfNoWithHighestCalories, $highestElfCalories];
}
