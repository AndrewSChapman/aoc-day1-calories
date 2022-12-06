<?php
$input = file_get_contents('input.txt');
//$input = 'mjqjpqmgbljsphdztnvjfqwrcgsmlb';

$signalStart = getSignalStartPosition($input);

print "Signal start: $signalStart\n";


function getSignalStartPosition(string $input): int
{
    $inputLen = strlen($input);
    $headerLength = 4;

    for ($offset = 0; $offset <= ($inputLen - $headerLength); $offset++) {
        $possibleHeader = substr($input, $offset, $headerLength);
        if (allCharsAreUnique($possibleHeader)) {
            return $offset + $headerLength;
        }
    }

    throw new Exception('Valid header not found');
}

function allCharsAreUnique(string $header): bool
{
    $headerLen = strlen($header);
    $headerElements = str_split($header);
    $uniqueElements = array_unique($headerElements);

    return count($uniqueElements) === $headerLen;
}