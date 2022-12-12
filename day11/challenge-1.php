<?php
declare(strict_types=1);

class Monkey
{
    /** @var int[] */
    private array $items;

    private $inspectionCount = 0;

    public function __construct(
        /** @var int[] $initialItems */
        array $initialItems,
        public readonly array $operation,
        public readonly array $test,
        public readonly int $trueMonkey,
        public readonly int $falseMonkey,
    ) {
        $this->items = $initialItems;

        if (empty($this->items)) {
            throw new DomainException('Invalid monkey - items are empty');
        }

        if (count($this->operation) !== 5) {
            throw new DomainException('Invalid monkey - operation is invalid');
        }

        if (count($this->test) !== 3) {
            throw new DomainException('Invalid monkey - test is invalid');
        }

        if ($this->trueMonkey < 0) {
            throw new DomainException('Invalid true monkey');
        }

        if ($this->falseMonkey < 0) {
            throw new DomainException('Invalid false monkey');
        }
    }

    public function receiveNewItem(int $worryLevel): void
    {
        $this->items[] = $worryLevel;
    }

    /**
     * @param Monkey[] $allMonkeys
     * @return void
     */
    public function processItems(array &$allMonkeys): void
    {
        foreach ($this->items as $index => $worryLevel) {
            $this->inspectionCount++;
            $initialWorryLevel = $worryLevel;

            print "Item $index, Initial worry level: $worryLevel\n";

            // Apply operation
            $operator = $this->operation[3];

            $value1 = $worryLevel;

            if ($this->operation[2] === 'old') {
                $value1 = $worryLevel;
            }

            if ($this->operation[4] === 'old') {
                print "Operator value 2 was old, setting value 2 to $worryLevel\n";
                $value2 = $worryLevel;
            } else {
                $value2 = (int)$this->operation[4];
            }

            switch ($operator) {
                case '+':
                    $worryLevel  = (int)$value1 + $value2;
                    print "Added value $value2 to $value1.  Result is: $worryLevel\n";
                    break;

                case '*':
                    $worryLevel = (int)$value1 * $value2;
                    print "Multiplied $value1 by $value2.  Result is: $worryLevel\n";
                    break;

                default:
                    throw new DomainException("Invalid operator: $operator");
            }

            // Process relief
            $worryLevel = (int)floor($worryLevel / 3);
            print "After relief: $worryLevel\n";

            // Evaluate test
            $divisibleBy = (int)$this->test[2];

            if (($worryLevel % $divisibleBy) === 0) {
                $allMonkeys[$this->trueMonkey]->receiveNewItem($worryLevel);
                print "Worry level $worryLevel was divisible by $divisibleBy.  Throwing to monkey {$this->trueMonkey}\n";
            } else {
                $allMonkeys[$this->falseMonkey]->receiveNewItem($worryLevel);
                print "Worry level $worryLevel was NOT divisible by $divisibleBy.  Throwing to monkey {$this->falseMonkey}\n";
            }
        }

        $this->items = [];
        print "Monkey finished processing.\n";
    }

    /**
     * @return int
     */
    public function getInspectionCount(): int
    {
        return $this->inspectionCount;
    }
}

$allMonkeys = getMonkeys();
$numMonkeys = count($allMonkeys);
print "Found $numMonkeys monkeys\n";

$maxRounds = 20;

for ($roundNo = 0; $roundNo < $maxRounds; $roundNo++) {
    foreach ($allMonkeys as $monkeyIdx => $monkey) {
        print "--------------------\n";
        print "Processing monkey $monkeyIdx\n";
        $allMonkeys[$monkeyIdx]->processItems($allMonkeys);
        print "--------------------\n";
    }
}

$monkeyInspectionCounts = [];
foreach ($allMonkeys as $monkeyIdx => $monkey) {
    print "Inpection count for monkey $monkeyIdx: {$monkey->getInspectionCount()}\n";
    $monkeyInspectionCounts[] = $monkey->getInspectionCount();
}

sort($monkeyInspectionCounts);
print_r($monkeyInspectionCounts);

$highest = $monkeyInspectionCounts[$numMonkeys - 1];
$nextHighest = $monkeyInspectionCounts[$numMonkeys - 2];

print "The two highest are $highest and $nextHighest\n";

$monkeyBusinessScore = $highest * $nextHighest;

print "Monkey business score: $monkeyBusinessScore\n";

/**
 * @return MOnkey[]
 * @throws Exception
 */
function getMonkeys(): array
{
    /** @var Monkey[] $monkeys */
    $monkeys = [];

    $fp = fopen('input.txt', 'r');
    if ($fp === false) {
        throw new Exception('Unable to load input data');
    }

    $items = [];
    $operation = [];
    $test = [];
    $trueMonkey = -1;
    $falseMonkey = -1;

    while ($line = fgets($fp)) {
        $line = trim($line);

        if (empty($line)) {
            continue;
        }

        if (str_starts_with($line, 'Monkey')) {
            $items = [];
            $operation = [];
            $test = [];
            $trueMonkey = 0;
            $falseMonkey = 0;
            continue;
        }

        if (str_starts_with($line, 'Starting items')) {
            $suffix = substr($line, 15);
            $elements = explode(',', $suffix);
            $items = array_map(static function($thisItem) {
                return (int)$thisItem;
            }, $elements);
        } elseif (str_starts_with($line, 'Operation')) {
            $suffix = substr($line, 11);
            $operation = explode(' ', $suffix);
        } elseif (str_starts_with($line, 'Test')) {
            $suffix = substr($line, 6);
            $test = explode(' ', $suffix);
        } elseif (str_starts_with($line, 'If true')) {
            $lastSpacePos = strrpos($line, ' ');
            $trueMonkey = (int)substr($line, $lastSpacePos + 1);
        } elseif (str_starts_with($line, 'If false')) {
            $lastSpacePos = strrpos($line, ' ');
            $falseMonkey = (int)substr($line, $lastSpacePos + 1);

            $monkey = new Monkey($items, $operation, $test, $trueMonkey, $falseMonkey);
            $monkeys[] = $monkey;
        }
    }

    fclose($fp);

    return $monkeys;
}
