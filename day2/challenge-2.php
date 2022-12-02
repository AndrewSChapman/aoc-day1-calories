<?php
enum Shape: int {
    case ROCK = 1;
    case PAPER = 2;
    case SCISSORS = 3;
}

enum DesiredRoundOutcome {
    case WIN;
    case LOSE;
    case DRAW;
}

$victoryMatrix = [
    Shape::ROCK->value => Shape::SCISSORS->value,
    Shape::SCISSORS->value => Shape::PAPER->value,
    Shape::PAPER->value => Shape::ROCK->value,
];

$letterMatrix = [
    'A' => Shape::ROCK,
    'B' => Shape::PAPER,
    'C' => Shape::SCISSORS,
    'X' => DesiredRoundOutcome::LOSE,
    'Y' => DesiredRoundOutcome::DRAW,
    'Z' => DesiredRoundOutcome::WIN,
];

function calculateRoundScore(array $victoryMatrix, Shape $shape1, Shape $shape2): int
{
    $roundScore = $shape2->value;

    // If the round was a draw, the score is +3
    if ($shape1 === $shape2) {
        $roundScore += 3;
        return $roundScore;
    }

    $didWin = $victoryMatrix[$shape2->value] === $shape1->value;

    if ($didWin) {
        $roundScore += 6;
    }

    return $roundScore;
}

function loadRoundData(): array
{
    $rounds = [];

    $fp = fopen('input.txt', 'r');
    if ($fp === false) {
        throw new Exception('Unable to load round input data');
    }

    while ($line = fgets($fp)) {
        $line = trim($line);
        if (empty($line)) {
            continue;
        }

        $shapeLetters = explode(' ', $line);
        if (count($shapeLetters) !== 2) {
            throw new Exception("Invalid line: $line");
        }

        $rounds[] = $shapeLetters;
    }

    fclose($fp);

    return $rounds;
}

/**
 * @throws Exception
 */
function getShapeForValue(int $shapeValue): Shape
{
    return match ($shapeValue) {
        Shape::SCISSORS->value => Shape::SCISSORS,
        Shape::PAPER->value => Shape::PAPER,
        Shape::ROCK->value => Shape::ROCK,
        default => throw new Exception("getShapeForValue - invalid shape value: $shapeValue"),
    };
}

/**
 * @throws Exception
 */
function determineCorrectShape(DesiredRoundOutcome $desiredRoundOutcome, array $victoryMatrix, Shape $shape): Shape
{
    // If we want a draw, return the same shape as what came in
    if ($desiredRoundOutcome === DesiredRoundOutcome::DRAW) {
        return $shape;
    }

    // If we want to lose, return the shape indicated by the victory matrix
    if ($desiredRoundOutcome === DesiredRoundOutcome::LOSE) {
        return getShapeForValue($victoryMatrix[$shape->value]);
    }

    // We want to win - return the inverse of the victory matrix.
    $matrixFlipped = array_flip($victoryMatrix);
    return getShapeForValue($matrixFlipped[$shape->value]);
}

$rounds = loadRoundData();
//$rounds = [
//    ['A', 'Y'],
//    ['B', 'X'],
//    ['C', 'Z'],
//];

$totalScore = 0;

foreach($rounds as $round) {
    $shape1 = $letterMatrix[$round[0]];

    /** @var DesiredRoundOutcome $desiredRoundOutcome */
    $desiredRoundOutcome = $letterMatrix[$round[1]];

    //print "DRO: {$desiredRoundOutcome->name}\n";

    $shape2 = determineCorrectShape($desiredRoundOutcome, $victoryMatrix, $shape1);
    $roundScore = calculateRoundScore($victoryMatrix, $shape1, $shape2);
    $totalScore += $roundScore;
}

print "Total score: $totalScore\n";

