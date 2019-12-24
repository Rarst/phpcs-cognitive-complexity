<?php

declare(strict_types=1);

namespace Rarst\PHPCS\CognitiveComplexity\Sniffs\Complexity;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use Rarst\PHPCS\CognitiveComplexity\Analyzer;

final class MaximumComplexitySniff implements Sniff
{
    /** @var int */
    public $maxCognitiveComplexity = 15;

    /** @var Analyzer */
    private $analyzer;

    public function __construct()
    {
        $this->analyzer = new Analyzer();
    }

    /**
     * @return int[]
     */
    public function register(): array
    {
        return [T_FUNCTION];
    }

    /**
     * @param int $stackPtr
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();

        $cognitiveComplexity = $this->analyzer->computeForFunctionFromTokensAndPosition(
            $tokens,
            $stackPtr
        );

        if ($cognitiveComplexity <= $this->maxCognitiveComplexity) {
            return;
        }

        $name = $tokens[$stackPtr + 2]['content'];

        $phpcsFile->addError(
            'Cognitive complexity for "%s" is %d but has to be less than or equal to %d.',
            $stackPtr,
            'TooHigh',
            [
                $name,
                $cognitiveComplexity,
                $this->maxCognitiveComplexity
            ]
        );
    }
}