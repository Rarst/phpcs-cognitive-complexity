<?php

declare(strict_types=1);

use PHP_CodeSniffer\Util\Tokens;

require_once __DIR__ . '/../vendor/squizlabs/php_codesniffer/autoload.php';
require_once __DIR__ . '/../src/CognitiveComplexity/Analyzer.php';
require_once __DIR__ . '/../src/CognitiveComplexity/Sniffs/Complexity/MaximumComplexitySniff.php';

if (! defined('PHP_CODESNIFFER_VERBOSITY')) {
    define('PHP_CODESNIFFER_VERBOSITY', 0);
    // initialize custom T_* token constants used by PHP_CodeSniffer parser
    new Tokens();
}