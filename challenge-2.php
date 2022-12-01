<?php
/**
 * We have a data file "data.txt" which contains calorie data for Elves.
 * Each Elf has an inventory of food, where each food item contains a certain number of calories.
 * Each line is a new food item for that Elf - the value is always an integer.
 * A blank line separates the data for each elf.
 *
 * This script scans the input data and calculates the total calories consumed for each Elf.
 * The result for each elf is returned in an array.
 *
 * We then sort the array (in ascending order, such that the items at the end
 * represent the Elves with the highest calories), and pop 3 items off the end of the array
 * and add those calories together to get the total calories for those Elves.
 *
 * Not over engineering this - keeping the logic simple and straight to the point.
 */
try {
    $totalCaloriesForEachElf = findTotalCaloriesForEachElf();
    sort($totalCaloriesForEachElf, SORT_NUMERIC);

    $topThreeCalories = 0;
    for ($counter = 0; $counter < 3; $counter++) {
        $topThreeCalories += array_pop($totalCaloriesForEachElf);
    }

    print "The total calories for the top 3 elves is: $topThreeCalories\n";

} catch (Exception $exception) {
    print "Caught exception whilst processing data: {$exception->getMessage()}\n";
}


/**
 * Reads the input file line by line, adding the total number of calories
 * together for each elf. The total for each elf is appended to an array, which
 * is then returned.
 * @return int[]
 * @throws Exception
 */
function findTotalCaloriesForEachElf(): array
{
    $fp = fopen('data.txt', 'r');
    if ($fp === false) {
        throw new Exception('Unable to load elf calorie data');
    }

    $elfTotalCalories = [];
    $totalElfCalories = 0;

    while ($line = fgets($fp)) {
        $line = trim($line);
        if (empty($line)) {
            $elfTotalCalories[] = $totalElfCalories;
            $totalElfCalories = 0;
            continue;
        }

        $totalElfCalories += (int)$line;
    }

    fclose($fp);

    return $elfTotalCalories;
}
