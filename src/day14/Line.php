<?php
declare(strict_types=1);

namespace Andyc\AdventOfCode\day14;

class Line
{
    public function __construct(
       public readonly Point $point1,
        public readonly Point $point2,
    ) {}

    public function dump(): string
    {
        return "{$this->point1->x},{$this->point1->y} -> {$this->point2->x},{$this->point2->y}\n";
    }
}
