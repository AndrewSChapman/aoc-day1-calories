<?php
enum CompareResult: int {
    case SUCCESS = -1;
    case CONTINUE = 0;
    case FAILED = 1;
}

// Get all the packets
$packets = getAllPackets();

// Add the decoder lines
$packets[] = '[[2]]';
$packets[] = '[[6]]';

// Sort them all, using the logic from the puzzle
usort($packets, function(string $lineA, string $lineB) {
   if (Comparison::inCorrectOrder($lineA, $lineB)) {
       return -1;
   } else {
       return 1;
   }
});

$divider1Pos = 0;
$divider2Pos = 0;

foreach ($packets as $idx => $line) {
    if ($line === '[[2]]') {
        $divider1Pos = $idx + 1;
    } elseif ($line === '[[6]]') {
        $divider2Pos = $idx + 1;
    }
    //print $line . "\n";
}

$decoderKey = $divider1Pos * $divider2Pos;

print "Decoder key is: $decoderKey\n";

class Comparison
{
    private const DEBUG = true;

    public static function inCorrectOrder(string $a, string $b): bool
    {
        if (empty($a)) {
            throw new Exception('Line A is empty');
        }

        if (empty($b)) {
            throw new Exception('Line B is empty');
        }

        $aDecoded = json_decode($a);
        $bDecoded = json_decode($b);

        if (!is_array($aDecoded)) {
            throw new Exception('Line A is NOT an array');
        }

        if (!is_array($bDecoded)) {
            throw new Exception('Line B is NOT an array');
        }

        $aLen = count($aDecoded);
        $bLen = count($bDecoded);

        for ($itemNo = 0; $itemNo < $aLen; $itemNo++) {
            if (self::DEBUG) {
                //print "\n---------------Item number is: $itemNo--------\n\n";
            }

            // If B has now run out of items, the lists ARE NOT in the correct order
            if ($itemNo >= $bLen) {
                if (self::DEBUG) {
                    print "B has run out of items\n";
                }
                return false;
            }

            $aItem = $aDecoded[$itemNo];
            $bItem = $bDecoded[$itemNo];

            $result = self::compare($aItem, $bItem);

            if ($result == CompareResult::FAILED) {
                return false;
            } elseif ($result == CompareResult::SUCCESS) {
                return true;
            }

            // We only continue to the next item if the compare result is CONTINUE;
        }

        return true;
    }

    private static function compare($aItem, $bItem): CompareResult
    {
        // Is this a straight out integer comparison
        if ((is_int($aItem)) && (is_int($bItem))) {
            if (self::DEBUG) {
                print "Int comparison A: $aItem, B: $bItem\n";
            }

            if ($aItem < $bItem) {
                return CompareResult::SUCCESS;
            } else if ($bItem < $aItem) {
                return CompareResult::FAILED;
            } else {
                return CompareResult::CONTINUE;
            }
        }

        // Test for a straight array comparison
        if ((is_array($aItem)) && (is_array($bItem))) {
            $result = self::compareArrays($aItem, $bItem);

            if ($result !== CompareResult::CONTINUE) {
                return $result;
            }
        }

        // Is one item an integer, and one an array
        if ((is_int($aItem)) && (is_array($bItem))) {
            if (self::DEBUG) {
                print "Convert A $aItem to array\n";
            }

            $aItem = [$aItem];
        }

        if ((is_array($aItem)) && (is_int($bItem))) {
            if (self::DEBUG) {
                print "Convert B $bItem to array\n";
            }

            $bItem = [$bItem];
        }

        return self::compareArrays($aItem, $bItem);
    }

    private static function compareArrays(array $aArray, array $bArray): CompareResult
    {
        if (self::DEBUG) {
            print "Array comparison\n";
        }

        $aLen = count($aArray);
        $bLen = count($bArray);

        if ($aLen === 0) {
            if (self::DEBUG) {
                print "AArray is empty\n";
                print "BArray is: " . json_encode($bArray) . "\n";
                print "Because AArray is empty we return success\n";
                print "----\n";
            }

            return CompareResult::SUCCESS;
        }

        if ($bLen === 0) {
            if (self::DEBUG) {
                print "BArray is empty\n";
                print "Because BArray is empty we return failed\n";
                print "----\n";
            }

            return CompareResult::FAILED;
        }

        if (self::DEBUG) {
            print "Alen is $aLen, bLen is $bLen\n";
            print "A Array: " . json_encode($aArray) . "\n";
            print "B Array: " . json_encode($bArray) . "\n";
        }

        foreach ($aArray as $aIdx => $aItem) {
            if ($aIdx >= $bLen) {
                if (self::DEBUG) {
                    print "B has run out of items.\n";
                }

                return CompareResult::FAILED;
            }

            $bItem = $bArray[$aIdx];
            $result = self::compare($aItem, $bItem);

            if ($result !== CompareResult::CONTINUE) {
                return $result;
            }
        }

        if ($aLen < $bLen) {
            if (self::DEBUG) {
                print "A has run out of items.\n";
            }
            return CompareResult::SUCCESS;
        }

        return CompareResult::CONTINUE;
    }
}

function getAllPackets(): array
{
    $packets = [];

    $fp = fopen('input.txt', 'r');
    if ($fp === false) {
        throw new Exception('Unable to load input data');
    }

    while ($line = fgets($fp)) {
        $line = trim($line);

        if (empty($line)) {
            continue;
        }

        $packets[] = $line;
    }

    fclose($fp);

    return $packets;
}