<?php
$grid = getTopologyGrid();
$grid->traverse();
$grid->dumpGrid();
$grid->printEndNumMoves();

class Position
{
    private int $x;
    private int $y;

    /**
     * @param int $x
     * @param int $y
     */
    public function __construct(int $x, int $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

    /**
     * @return int
     */
    public function getX(): int
    {
        return $this->x;
    }

    /**
     * @return int
     */
    public function getY(): int
    {
        return $this->y;
    }
}

class GridCell
{
    private int $numMovesRequired = 0;

    public function __construct(
        public readonly string $letter,
    ) {}

    public function canMoveToMe(string $letter): bool
    {
        // We should never be moving from an 'E' to any other square since E is the END of traversal.
        if ($letter === 'E') {
            throw new Exception('Current letter is E - probably should not be?');
        }

        // The end letter E iz actually a 'z' - don't forget this.
        $thisLetter = $this->letter === 'E' ? 'z' : $this->letter;

        // We can move to the letter if the difference is less than 2, e.g. a to b, a to a, or j to a.
        $diff = ord($thisLetter) - ord($letter);

        return $diff <= 1;
    }

    /**
     * @return int
     */
    public function getNumMovesRequired(): int
    {
        return $this->numMovesRequired;
    }

    public function setNumMovesRequired(int $numMoves): void
    {
        $this->numMovesRequired = $numMoves;
    }

    public function dump()
    {
        print "Letter $this->letter\n";
    }
}

enum Direction: int
{
    case UP = 1;
    case DOWN = 2;
    CASE LEFT = 3;
    CASE RIGHT = 4;
}

class Grid
{
    private readonly int $numRows;
    private readonly int $numCols;

    /**
     * @throws Exception
     */
    public function __construct(
        public readonly array $startCoords,
        public readonly array $goalCoords,
        public readonly array $grid,
    ){
        if ((empty($grid)) || (!is_array($grid[0]))) {
            throw new Exception('Invalid grid');
        }
        $this->numRows = count($grid);
        $this->numCols = count($grid[0]);

        if (($this->numRows <= 0) || ($this->numCols <= 0)) {
            throw new Exception('Invalid grid');
        }

        if (count($startCoords) !== 2) {
            throw new Exception('Start pos not found!');
        }

        if (count($this->goalCoords) !== 2) {
            throw new Exception('Start pos not found!');
        }
    }

    public function traverse(?Position $currentPosition = null, $numMoves = 0): void
    {
        if ($currentPosition === null) {
            // Start at the start coords
            $currentPosition = new Position($this->startCoords[1], $this->startCoords[0]);
        }

        $currentGridCell = $this->getGridCell($currentPosition->getX(), $currentPosition->getY());


        // Try go LEFT
        $newX = $currentPosition->getX() - 1;
        $newY = $currentPosition->getY();

        // Don't allow us to go out of bounds of the array (i.e. less than 0 values)
        if ($newX >= 0) {
            /** @var GridCell $gridCell */
            $targetGridCell = $this->getGridCell($newX, $newY);
            $this->move($currentGridCell, $targetGridCell, $numMoves, new Position($newX, $newY));
        }

        // Try go RIGHT
        $newX = $currentPosition->getX() + 1;
        $newY = $currentPosition->getY();

        // Don't allow us to go out of bounds of the array (i.e. greater than the total number of columns)
        if ($newX < $this->numCols) {
            /** @var GridCell $gridCell */
            $targetGridCell = $this->getGridCell($newX, $newY);
            $this->move($currentGridCell, $targetGridCell, $numMoves, new Position($newX, $newY));
        }

        // Try go UP
        $newX = $currentPosition->getX();
        $newY = $currentPosition->getY() - 1;

        // Don't allow us to go out of bounds of the array (i.e. less than the total number of rows)
        if ($newY >= 0) {
            /** @var GridCell $gridCell */
            $targetGridCell = $this->getGridCell($newX, $newY);
            $this->move($currentGridCell, $targetGridCell, $numMoves, new Position($newX, $newY));
        }

        // Try go DOWN
        $newX = $currentPosition->getX();
        $newY = $currentPosition->getY() + 1;

        // Don't allow us to go out of bounds of the array (i.e. greater than the total number of rows)
        if ($newY < $this->numRows) {
            /** @var GridCell $gridCell */
            $targetGridCell = $this->getGridCell($newX, $newY);
            $this->move($currentGridCell, $targetGridCell, $numMoves, new Position($newX, $newY));
        }
    }

    public function printEndNumMoves(): void
    {
        $gridCell = $this->getGridCell($this->goalCoords[1], $this->goalCoords[0]);
        print "End cell reached in: {$gridCell->getNumMovesRequired()}\n";
    }

    private function move(GridCell $currentGridCell, GridCell $targetGridCell, int $numMoves, Position $newPosition): void
    {
        if (!$targetGridCell->canMoveToMe($currentGridCell->letter)) {
            return;
        }

        $numMoves++;

        if (($targetGridCell->getNumMovesRequired() === 0) || ($targetGridCell->getNumMovesRequired() > $numMoves)) {
            $targetGridCell->setNumMovesRequired($numMoves);

            if ($targetGridCell->letter === 'E') {
                // Don't actually traverse after reaching the end.
                return;
            }

            $this->traverse($newPosition, $numMoves);
        }
    }

    //public function findMinimum

    private function getGridCell(int $x, int $y): GridCell
    {
        return $this->grid[$y][$x];
    }

    public function dumpGrid(): void
    {
        print "\n----------------\n";

        /** @var GridCell[] $row */
        foreach ($this->grid as $rowNo => $row) {
            foreach ($row as $cellNo => $gridCell) {
                if ($this->goalCoords == [$rowNo, $cellNo]) {
                    print "E   ";
                } elseif ($this->startCoords == [$rowNo, $cellNo]) {
                    print "S   ";
                } else {
                    print str_pad((string)$gridCell->getNumMovesRequired(), 4);
                }
            }

            print "\n";
        }

        print "----------------\n";
    }
}

/**
 * @return Grid
 * @throws Exception
 */
function getTopologyGrid(): Grid
{
    $fp = fopen('input-2.txt', 'r');
    if ($fp === false) {
        throw new Exception('Unable to load input data');
    }

    $grid = [];

    $currentRowNo = 0;
    $startCoords = [];
    $endCoords = [];

    while ($line = fgets($fp)) {
        $line = trim($line);

        if (empty($line)) {
            continue;
        }

        $elements = str_split($line);
        $row = [];
        foreach ($elements as $letter) {
            $row[] = new GridCell($letter);
        }

        $grid[] = $row;

        $pos = array_search('S', $elements);
        if ($pos !== false) {
            $startCoords = [$currentRowNo, $pos];
        }

        $pos = array_search('E', $elements);
        if ($pos !== false) {
            $endCoords = [$currentRowNo, $pos];
        }

        $currentRowNo++;
    }

    fclose($fp);

    $grid = new Grid($startCoords, $endCoords, $grid);

    return $grid;
}
