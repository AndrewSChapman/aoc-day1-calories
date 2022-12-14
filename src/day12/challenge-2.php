<?php
$grid = getTopologyGrid();
$grid->traverse();
//$grid->dumpGrid();

$allAs = $grid->getAllAPositions();
$numAs = count($allAs);

$bestNumEndMoves = 99999;


foreach ($allAs as $aIdx => $aPosition) {
    $perc = ($aIdx / $numAs) * 100;
    print "Processing $aIdx of $numAs - $perc % - $bestNumEndMoves\n";
    $grid = getTopologyGrid();
    $grid->setStartTo($aPosition->getY(), $aPosition->getX());
    //$grid->dumpGrid();
    $grid->traverse();
    $numEndMoves = $grid->getEndNumMoves();

    if (($numEndMoves > 0) && ($numEndMoves < $bestNumEndMoves)) {
        $bestNumEndMoves = $numEndMoves;
    }
}

print "Best num end moves for an 'A' start pos is $bestNumEndMoves\n";

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
        // A Start or End position can always move to this letter.
        if ($letter === 'S') {
            $letter = 'a';
        }

        if ($letter === 'E') {
            throw new Exception('Current letter is E - probably should not be?');
        }

        $thisLetter = $this->letter === 'E' ? 'z' : $this->letter;

        $diff = ord($thisLetter) - ord($letter);
        //print "Current letter $letter, Target letter: {$thisLetter} - Diff: $diff\n";

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
        public array $startCoords,
        public readonly array $goalCoords,
        public array $grid,
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

        if ($newX >= 0) {
            /** @var GridCell $gridCell */
            $targetGridCell = $this->getGridCell($newX, $newY);
            $this->move($currentGridCell, $targetGridCell, $numMoves, new Position($newX, $newY));
        }

        // Try go RIGHT
        $newX = $currentPosition->getX() + 1;
        $newY = $currentPosition->getY();

        if ($newX < $this->numCols) {
            /** @var GridCell $gridCell */
            $targetGridCell = $this->getGridCell($newX, $newY);
            $this->move($currentGridCell, $targetGridCell, $numMoves, new Position($newX, $newY));
        }

        // Try go UP
        $newX = $currentPosition->getX();
        $newY = $currentPosition->getY() - 1;

        if ($newY >= 0) {
            /** @var GridCell $gridCell */
            $targetGridCell = $this->getGridCell($newX, $newY);
            $this->move($currentGridCell, $targetGridCell, $numMoves, new Position($newX, $newY));
        }

        // Try go DOWN
        $newX = $currentPosition->getX();
        $newY = $currentPosition->getY() + 1;

        if ($newY < $this->numRows) {
            /** @var GridCell $gridCell */
            $targetGridCell = $this->getGridCell($newX, $newY);
            $this->move($currentGridCell, $targetGridCell, $numMoves, new Position($newX, $newY));
        }
    }

    public function getEndNumMoves(): int
    {
        $gridCell = $this->getGridCell($this->goalCoords[1], $this->goalCoords[0]);
        return $gridCell->getNumMovesRequired();
    }

    public function setStartTo(int $y, int $x): void
    {
        $this->grid[$this->startCoords[0]][$this->startCoords[1]] = new GridCell('z');
        $this->startCoords = [$y, $x];
        $this->grid[$this->startCoords[0]][$this->startCoords[1]] = new GridCell('S');
    }

    /**
     * @return Position[]
     * @throws Exception
     */
    public function getAllAPositions(): array
    {
        $allAPositions = [];

        foreach ($this->grid as $rowNo => $row) {
            /**
             * @var int $colNo
             * @var GridCell $gridCell
             */
            foreach ($row as $colNo => $gridCell) {
                if ($gridCell->letter === 'a') {
                    $allAPositions[] = new Position($colNo, $rowNo);
                }
            }
        }

        return $allAPositions;
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
    $fp = fopen('input.txt', 'r');
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
