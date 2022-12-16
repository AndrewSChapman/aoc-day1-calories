<?php
declare(strict_types=1);

namespace Andyc\AdventOfCode\day14;

require_once '../../vendor/autoload.php';

$lines = LineLoader::loadLines();

$cave = new Cave($lines);
$cave->startSand();

$numSandUnits = $cave->getNumSandUnits();

$cave->drawCave();

print "Num sand units: $numSandUnits\n";

