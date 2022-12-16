<?php
declare(strict_types=1);

namespace Andyc\AdventOfCode\day14;

class Cave
{
    private int $currentStepNo = 0;
    private array $cave = [];

    /** @var Sand[] */
    private array $sand = [];

    private array $dimensions = [0, 0, 0, 0];

    /**
     * @param Line[] $lines
     */
    public function __construct(array $lines)
    {
        $this->initCave($lines);
    }

    public function startSand(): void
    {
        $this->currentStepNo++;

        // If there is no sand at all yet, add the first sand item.
        if (empty($this->sand)) {
            $this->sand[] = $this->getNewSandItem();
        }

        $canMoveSand = true;

        while (true) {
            // Get the sand item at the top of the stack
            $sand = $this->sand[0];

            // If the top sand item is blocked, there's nothing to do.
            if ($sand->isBlocked()) {
                break;
            }

            /**
             * Can we move the sand straight down?  If so, do it.
             */
            $newPoint = $sand->goStraightDown();
            if ($this->isPointValid($newPoint)) {
                $sand->setNewLocation($newPoint);
                print "Moved sand down\n";
                echo $newPoint->dump();
                continue;
            }

            /**
             * Can we move the sand diagonally down left?  If so, do it.
             * In order to go down left, we must first be able to go left.
             */
            $newPoint = $sand->goLeft();
            if ($this->isPointValid($newPoint)) {
                // Left is OK - now try diagonal left
                $newPoint = $sand->goDownLeft();
                if ($this->isPointValid($newPoint)) {
                    $sand->setNewLocation($newPoint);
                    print "Moved sand down left\n";
                    echo $newPoint->dump();
                    continue;
                }
            }

            /**
             * Can we move the sand diagonally down right?  If so, do it.
             * In order to go down left, we must first be able to go right.
             */
            $newPoint = $sand->goRight();
            if ($this->isPointValid($newPoint)) {
                $newPoint = $sand->goDownRight();
                if ($this->isPointValid($newPoint)) {
                    $sand->setNewLocation($newPoint);
                    print "Moved sand down right\n";
                    echo $newPoint->dump();
                    continue;
                }
            }

            // The sand is blocked
            $sand->setIsBlocked();
            $this->drawFinalSandPosition($sand->getPoint());

            if (count($this->sand) === 22) {
                $this->drawCave();
                die("STOP");
            }

            /*
             * If the sand final Y pos is > 0, push a new sand item to
             * the start of the array
             */
            if ($sand->getPoint()->y > 0) {
                array_unshift($this->sand, $this->getNewSandItem());
            } else {
                // If we could not add more sand to the stack - we're done.
                break;
            }
        }
    }

    private function getNewSandItem(): Sand
    {
        return new Sand(500, 0);
    }

    private function isPointValid(Point $p): bool
    {
        [$minX, $maxX, $minY, $maxY] = $this->dimensions;

        if ($p->y < $minY) {
            return false;
        }

        if ($p->y > $maxY) {
            return false;
        }

        if ($p->x < $minX) {
            return false;
        }

        if ($p->x > $maxX) {
            return false;
        }

        return $this->cave[$p->y][$p->x] === '.';
    }

    private function drawFinalSandPosition(Point $p): void
    {
        $this->cave[$p->y][$p->x] = 'o';
    }

    /**
     * @param Line[] $lines
     * @return void
     */
    private function initCave(array $lines): void
    {
        // First scan the maximum dimensions of the cave
        $minX = 9999999;
        $minY = 0;
        $maxX = -1;
        $maxY = -1;

        foreach ($lines as $line) {
            // Point1
            if ($line->point1->x < $minX) {
                $minX = $line->point1->x;
            }

            if ($line->point1->x > $maxX) {
                $maxX = $line->point1->x;
            }

            if ($line->point1->y < $minY) {
                $minY = $line->point1->y;
            }

            if ($line->point1->y > $maxY) {
                $maxY = $line->point1->y;
            }

            // Point2
            if ($line->point2->x < $minX) {
                $minX = $line->point2->x;
            }

            if ($line->point2->x > $maxX) {
                $maxX = $line->point2->x;
            }

            if ($line->point2->y < $minY) {
                $minY = $line->point2->y;
            }

            if ($line->point2->y > $maxY) {
                $maxY = $line->point2->y;
            }
        }

        $this->dimensions = [$minX, $maxX, $minY, $maxY];

//        print "X: $minX to $maxX\n";
//        print "Y: $minY to $maxY\n";

        // Create the cave array according to the found dimensions, filling it with air "."
        for ($y = 0; $y <= $maxY; $y++) {
            $line = [];
            for ($x = $minX; $x <= $maxX; $x++) {
                $line[$x] = ".";
            }
            $this->cave[$y] = $line;
        }

        // Draw the lines
        foreach ($lines as $line) {
            if ($line->point1->y <= $line->point2->y) {
                for ($y = $line->point1->y; $y <= $line->point2->y; $y++) {
                    if ($line->point1->x <= $line->point2->x) {
                        for ($x = $line->point1->x; $x <= $line->point2->x; $x++) {
                            $this->cave[$y][$x] = "#";
                        }
                    } else {
                        for ($x = $line->point1->x; $x >= $line->point2->x; $x--) {
                            $this->cave[$y][$x] = "#";
                        }
                    }
                }
            } else {
                for ($y = $line->point1->y; $y >= $line->point2->y; $y--) {
                    if ($line->point1->x <= $line->point2->x) {
                        for ($x = $line->point1->x; $x <= $line->point2->x; $x++) {
                            $this->cave[$y][$x] = "#";
                        }
                    } else {
                        for ($x = $line->point1->x; $x >= $line->point2->x; $x--) {
                            $this->cave[$y][$x] = "#";
                        }
                    }
                }
            }
        }

        $this->drawCave();
    }

    public function drawCave(): void
    {
        foreach ($this->cave as $rowNo => $line) {
            foreach ($line as $lineIdx => $value) {
                print $value;
            }
            print "\n";
        }
    }
}
