<?php

declare(strict_types=1);

namespace Rarst\PHPCS\CognitiveComplexity\Sniffs\Complexity;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use Rarst\Phpcs\CognitiveComplexity\Analyzer;

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
     * @param int $position
     */
    public function process(File $file, $position): void
    {
        $tokens = $file->getTokens();

        $cognitiveComplexity = $this->analyzer->computeForFunctionFromTokensAndPosition(
            $tokens,
            $position
        );

        if ($cognitiveComplexity <= $this->maxCognitiveComplexity) {
            return;
        }

        $method = $tokens[$position + 2]['content'];

        $file->addError(
            sprintf(
                'Cognitive complexity for method "%s" is %d but has to be less than or equal to %d.',
                $method,
                $cognitiveComplexity,
                $this->maxCognitiveComplexity
            ),
            $position,
            self::class
        );
    }
}