<?php

function getWords($number) {
    return match ($number) { // +1
        1       => "one",
        2       => "a couple",
        3       => "a few",
        default => "lots",
    };
}

// Cognitive Complexity 1
