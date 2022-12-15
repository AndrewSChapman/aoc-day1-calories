<?php
class Vector {
    public function __construct(
        public readonly int $xPos,
        public readonly int $yPos,
    ) {}

    /**
     * @throws Exception
     */
    public function moveUp(): Vector
    {
        return new self(
            $this->xPos,
            $this->yPos - 1,
        );
    }

    /**
     * @throws Exception
     */
    public function moveDown(): Vector
    {
        return new self(
            $this->xPos,
            $this->yPos + 1,
        );
    }

    /**
     * @throws Exception
     */
    public function moveLeft(): Vector
    {
        return new self(
            $this->xPos - 1,
            $this->yPos,
        );
    }

    /**
     * @throws Exception
     */
    public function moveRight(): Vector
    {
        return new self(
            $this->xPos + 1,
            $this->yPos,
        );
    }

    public function isHere(int $x, $y): bool
    {
        return (($this->xPos === $x) && ($this->yPos === $y));
    }

    public function isNotHere(int $x, $y): bool
    {
        return !$this->isHere($x, $y);
    }

    public function clone(): Vector
    {
        return new self($this->xPos, $this->yPos);
    }

    public function calculateNewTail(Vector $head, bool &$tailMoved = false): Vector
    {
        $newX = $this->xPos;
        $newY = $this->yPos;

        /*
         * Directive 1
         * If the head is ever two steps directly up, down, left, or right from the tail, the tail must
         * also move one step in that direction so it remains close enough:
         */

        // Handle X
        $diffX = $head->xPos - $this->xPos;
        $diffY = $head->yPos - $this->yPos;

        if (((abs($diffX) === 2) && (abs($diffY) === 0)) ||
            ((abs($diffX) === 0) && (abs($diffY) === 2))) {
            // Directive 1 applies
            if ($diffX == 2) {
                $newX++;
            } else {
                if ($diffX == -2) {
                    $newX--;
                }
            }

            // Handle Y

            if ($diffY == 2) {
                $newY++;
            } else {
                if ($diffY == -2) {
                    $newY--;
                }
            }

            $tailMoved = true;

            return new Vector($newX, $newY);
        }

        /*
         * Directive 2
         * Otherwise, if the head and tail aren't touching and aren't in the same row or column, the tail always moves
         * one step diagonally to keep up.
         */
        if (((abs($diffX) === 2) && (abs($diffY) === 1)) ||
            ((abs($diffX) === 1) && (abs($diffY) === 2))) {
            // Directive 2 applies - it's a diagonal movement.  Move the tail to meet it.
            if ($diffX > 0) {
                $newX++;
            } else {
                $newX--;
            }

            if ($diffY > 0) {
                $newY++;
            } else {
                $newY--;
            }

            $tailMoved = true;

            return new Vector($newX, $newY);
        }

        // It's also possible for the snake to have a difference of two in both X and Y axis.
        if ((abs($diffX) === 2) && (abs($diffY) === 2)) {
            if ($diffX > 0) {
                $newX++;
            } else {
                $newX--;
            }

            if ($diffY > 0) {
                $newY++;
            } else {
                $newY--;
            }

            $tailMoved = true;
        }

        return new Vector($newX, $newY);
    }
}

enum Direction: string
{
    case UP = 'U';
    case DOWN = 'D';
    case RIGHT = 'R';
    case LEFT = 'L';
}

class Instruction
{
    public function __construct(
        public readonly Direction $direction,
        public readonly int $moveAmount,
    ){}

    public function printInstruction(): void
    {
        print "{$this->direction->value} $this->moveAmount\n";
    }
}

$manager = new Manager();
$instructions = getInstructions();
$manager->executeInstructions($instructions);


/*************************
 * MAIN CLASS
 * *************************/



class Manager {
    private const SNAKE_SIZE = 10;

    /**
     * @var Vector[]
     */
    private $snake = [];

    /**
     * @var Vector[]
     */
    private array $tailHistory = [];

    public function __construct() {
        for ($s = 0; $s < self::SNAKE_SIZE; $s++) {
            $this->snake[] = new Vector(0, 0);
        }
    }

    private function getHead(): Vector
    {
        return $this->snake[0];
    }

    private function getTail(): Vector
    {
        return $this->snake[self::SNAKE_SIZE - 1];
    }

    /**
     * @param Instruction[] $instructions
     * @throws Exception
     */
    public function executeInstructions(array $instructions): void
    {
        $this->tailHistory[] = $this->getTail()->clone();

        foreach ($instructions as $instruction) {
            for ($moveCount = 0; $moveCount < $instruction->moveAmount; $moveCount++) {
                // Move the head of the snake first
                $this->snake[0] = match ($instruction->direction) {
                    Direction::UP => $this->getHead()->moveUp(),
                    Direction::DOWN => $this->getHead()->moveDown(),
                    Direction::LEFT => $this->getHead()->moveLeft(),
                    Direction::RIGHT => $this->getHead()->moveRight(),
                    default => throw new Exception('Invalid instruction direction'),
                };

                $prevVector = $this->getHead();

                $tailMoved = false;

                // Loop through the snake, moving all pieces
                for ($s = 1; $s < self::SNAKE_SIZE; $s++) {
                    $thisVector = $this->snake[$s];
                    $tailMoved = false;
                    $this->snake[$s] = $thisVector->calculateNewTail($prevVector, $tailMoved);
                    $prevVector = $this->snake[$s];
                }

                if ($tailMoved) {
                    $this->tailHistory[] = $this->getTail()->clone();
                }
            }
        }

        $totalTailPositions = $this->countTailPositions();

        print "Total tail positions: $totalTailPositions\n";
    }

    private function countTailPositions(): int
    {
        $uniquePosHash = [];
        $numTailPositions = 0;

        foreach ($this->tailHistory as $vector) {
            $hash = "{$vector->xPos}_{$vector->yPos}";
            if (!isset($uniquePosHash[$hash])) {
                $uniquePosHash[$hash] = true;
                $numTailPositions++;
            }
        }

        return $numTailPositions;
    }

    public function dumpGrid(int $xFrom, int $xTo, int $yFrom, int $yTo): void
    {
        $grid = [];

        for ($rowNo = $yFrom; $rowNo <= $yTo; $rowNo++) {
            for ($colNo = $xFrom; $colNo <= $xTo; $colNo++) {
                $grid[$rowNo][$colNo] = ".";
            }
        }

        foreach ($this->snake as $snakeIdx => $vector) {
            if ($snakeIdx === 0) {
                $grid[$vector->yPos][$vector->xPos] = "H";
            } else if ($snakeIdx === (self::SNAKE_SIZE - 1)) {
                if ($grid[$vector->yPos][$vector->xPos] === ".") {
                    $grid[$vector->yPos][$vector->xPos] = "T";
                }
            } else {
                if ($grid[$vector->yPos][$vector->xPos] === ".") {
                    $grid[$vector->yPos][$vector->xPos] = $snakeIdx;
                }
            }
        }

        for ($rowNo = $yFrom; $rowNo <= $yTo; $rowNo++) {
            for ($colNo = $xFrom; $colNo <= $xTo; $colNo++) {
                print $grid[$rowNo][$colNo];
            }
            print "\n";
        }
    }
}
//executeInstructions();


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
        if (count($instruction) !== 2) {
            throw new Exception("Invalid instruction: $line");
        }

        $instruction = new Instruction(
            Direction::from($instruction[0]),
            (int)$instruction[1],
        );

        $instructions[] = $instruction;
    }

    fclose($fp);

    return $instructions;
}

