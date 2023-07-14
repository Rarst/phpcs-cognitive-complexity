<?php

function matchIncrementsNesting($number) {
    $a = true;
    $b = true;

    if ($a) { // +1 if
        match ($number) { // +1 match +1 nesting
            1       => $b ? "one" : "uno", // +1 ternary +2 nesting
            2       => "a couple",
            3       => "a few",
            default => "lots",
        };
    }
}

// Cognitive Complexity 6
