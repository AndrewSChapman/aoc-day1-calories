<?php

declare(strict_types=1);

namespace Andyc\AdventOfCode\day14;

use DomainException;

class LineLoader
{
    /**
     * @return Line[]
     */
    public static function loadLines(): array
    {
        $fp = fopen('input-2.txt', 'r');
        if ($fp === false) {
            throw new DomainException('Unable to load input data');
        }

        $lines = [];

        while ($input = fgets($fp)) {
            $input = trim($input);

            if (empty($input)) {
                continue;
            }

            $parts = explode('->', $input);
            if (count($parts) === 0) {
                throw new DomainException('Invalid line');
            }

            /** @var ?Point $lastPoint */
            $lastPoint = null;

            foreach ($parts as $part) {
                $part = trim($part);
                $pointElements = explode(',', $part);
                if (count($pointElements) !== 2) {
                    throw new DomainException('Invalid point');
                }

                $point = new Point((int)$pointElements[0], (int)$pointElements[1]);

                if ($lastPoint !== null) {
                    $line = new Line($lastPoint, $point);
                    $lines[] = $line;
                    echo $line->dump();
                }

                $lastPoint = $point;
            }
        }

        return $lines;
    }
}
