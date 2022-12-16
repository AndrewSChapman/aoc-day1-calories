<?php
declare(strict_types=1);

namespace Andyc\AdventOfCode\day14;

class Sand
{
    private bool $blocked = false;

    private Point $point;

    public function __construct(int $startX, int $startY)
    {
        $this->point = new Point($startX, $startY);
    }

    public function setIsBlocked(): void
    {
        $this->blocked = true;
    }

    public function isBlocked(): bool
    {
        return $this->blocked;
    }

    public function isNotBlocked(): bool
    {
        return !$this->isBlocked();
    }

    public function goStraightDown(): Point
    {
        return new Point($this->point->x, $this->point->y + 1);
    }

    public function goLeft(): Point
    {
        return new Point($this->point->x - 1, $this->point->y);
    }

    public function goDownLeft(): Point
    {
        return new Point($this->point->x - 1, $this->point->y + 1);
    }

    public function goRight(): Point
    {
        return new Point($this->point->x + 1, $this->point->y);
    }

    public function goDownRight(): Point
    {
        return new Point($this->point->x + 1, $this->point->y + 1);
    }

    public function setNewLocation(Point $p): void
    {
        $this->point = $p;
    }

    /**
     * @return Point
     */
    public function getPoint(): Point
    {
        return $this->point;
    }
}
