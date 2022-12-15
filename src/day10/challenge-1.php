<?php
enum Operator: string {
    case ADD_X = 'addx';
    case NOOP = 'noop';
}

class Instruction
{
    public function __construct(
        public readonly Operator $operator,
        public readonly int $qty,
    ){}

    public function printInstruction(): void
    {
        print "{$this->operator->value} $this->qty\n";
    }
}

class CPU
{
    private int $x = 1;
    private int $cycle = 0;

    private int $totalSignal = 0;

    private $measureSignalPoints = [20, 60, 100, 140, 180, 220];

    /**
     * @param Instruction[] $instructions
     * @return void
     */
    public function processInstructions(array $instructions): void
    {
        foreach ($instructions as $instruction) {
            $numCyclesToAdd = 0;

            if ($instruction->operator === Operator::NOOP) {
                $this->cycle++;
                $this->processCycle();
            } else {
                $this->cycle++;
                $this->processCycle();

                $this->cycle++;
                $this->processCycle();
                $this->x += $instruction->qty;
            }
        }
    }

    private function processCycle(): void
    {
        if (in_array($this->cycle, $this->measureSignalPoints)) {
            $signalStrength = $this->cycle * $this->x;
            print "Cycle: {$this->cycle}: $this->x, Signal strength $signalStrength\n";
            $this->totalSignal += $signalStrength;
        }
    }

    /**
     * @return int
     */
    public function getTotalSignal(): int
    {
        return $this->totalSignal;
    }
}

/**
 * @return Instruction[]
 * @throws Exception
 */
function getInstructions(): array
{
    $instructions = [];

    $fp = fopen('input.txt', 'r');
    if ($fp === false) {
        throw new Exception('Unable to load input data');
    }

    while ($line = fgets($fp)) {
        $line = trim($line);
        if (empty($line)) {
            break;
        }

        $instruction = explode(' ', $line);

        $operator = Operator::from($instruction[0]);
        $value = 0;

        if ($operator === Operator::ADD_X) {
            if (count($instruction) !== 2) {
                throw new \RuntimeException("Invalid instruction: $line");
            }

            $value = (int)$instruction[1];
        }

        $instruction = new Instruction(
            $operator,
            $value,
        );

        $instructions[] = $instruction;
    }

    fclose($fp);

    return $instructions;
}

$cpu = new CPU();

$instructions = getInstructions();
$cpu->processInstructions($instructions);

$totalSignal = $cpu->getTotalSignal();
print "Total signal was: $totalSignal\n";
