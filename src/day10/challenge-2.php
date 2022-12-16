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

class CRT
{
    private const CYCLES_PER_ROW = 40;

    private array $row = [];

    private int $rowsDrawn = 0;

    public function __construct()
    {
        $this->initRow();
    }

    private function initRow(): void
    {
        $this->row = array_fill(0, self::CYCLES_PER_ROW, ".");
    }

    public function render(int $cycle, int $x): void
    {
        $spriteX = [$x, $x + 1, $x + 2];

        $offset = (int)($this->rowsDrawn * self::CYCLES_PER_ROW);
        $cycle -= $offset;

        if (in_array($cycle, $spriteX)) {
            $this->row[$cycle - 1] = "#";
        }

        if (($cycle % self::CYCLES_PER_ROW) === 0) {
            $this->renderRow();
            $this->initRow();
            $this->rowsDrawn++;
        }
    }

    private function renderRow(): void
    {
        for ($count = 0; $count < self::CYCLES_PER_ROW; $count++) {
            print $this->row[$count];
        }

        print "\n";
    }
}

class CPU
{
    private int $x = 1;
    private int $cycle = 0;

    public function __construct(private readonly CRT $crt)
    {}

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
        $this->crt->render($this->cycle, $this->x);
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

$cpu = new CPU(new CRT());

$instructions = getInstructions();
$cpu->processInstructions($instructions);
