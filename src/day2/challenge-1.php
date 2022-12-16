<?php
enum Shape: int {
    case ROCK = 1;
    case PAPER = 2;
    case SCISSORS = 3;
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
    'X' => Shape::ROCK,
    'Y' => Shape::PAPER,
    'Z' => Shape::SCISSORS,
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

$rounds = loadRoundData();

$totalScore = 0;

foreach($rounds as $round) {
    $shape1 = $letterMatrix[$round[0]];
    $shape2 = $letterMatrix[$round[1]];

    $roundScore = calculateRoundScore($victoryMatrix, $shape1, $shape2);
    $totalScore += $roundScore;
}

print "Total score: $totalScore\n";

