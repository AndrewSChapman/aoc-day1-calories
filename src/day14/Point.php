<?php
declare(strict_types=1);

namespace Andyc\AdventOfCode\day14;

class Point
{
    public function __construct(
        public readonly int $x,
        public readonly int $y,
    ){}

    public function dump(): string
    {
        return "Sand X pos: {$this->x}, Y pos {$this->y}\n";
    }
}
