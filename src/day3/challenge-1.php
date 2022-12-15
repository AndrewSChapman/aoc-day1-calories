<?php
$compartmentData = loadCompartmentData();

$totalPriority = 0;

foreach ($compartmentData as $data) {
    $compartmentA = $data[0];
    $compartmentB = $data[1];

    $commonLetterIntersect = array_intersect($compartmentA, $compartmentB);

    if (count($commonLetterIntersect) < 1) {
        throw new Exception('Invalid array intersection');
    }

    $firstKey = array_keys($commonLetterIntersect)[0];
    $commonLetter = $commonLetterIntersect[$firstKey];
    $letterPriority = getLetterPriority($commonLetter);

    $totalPriority += $letterPriority;
}

print "Total priority is: $totalPriority\n";

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

function loadCompartmentData(): array
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

        $elements = str_split($line);
        $numElements = count($elements);
        $numElementsPerCompartment = $numElements / 2;

        $compartment1 = array_slice($elements, 0, $numElementsPerCompartment);
        $compartment2 = array_slice($elements, $numElementsPerCompartment);

        $data[] = [$compartment1, $compartment2];
    }

    fclose($fp);

    return $data;
}
