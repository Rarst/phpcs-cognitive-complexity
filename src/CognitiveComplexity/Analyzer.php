<?php

declare(strict_types=1);

namespace Rarst\PHPCS\CognitiveComplexity;

/**
 * Based on https://www.sonarsource.com/docs/CognitiveComplexity.pdf
 *
 * A Cognitive Complexity score has 3 rules:
 * - B1. Ignore structures that allow multiple statements to be readably shorthanded into one
 * - B2. Increment (add one) for each break in the linear flow of the code
 * - B3. Increment when flow-breaking structures are nested
 */
final class Analyzer
{
    /** @var int */
    private $cognitiveComplexity = 0;

    /** @var bool */
    private $isInTryConstruction = false;

    /** @var int */
    private $lastBooleanOperator = 0;

    /**
     * B1. Increments
     *
     * Boolean operators are handled separately due to their chain logic.
     *
     * @var int[]|string[]
     */
    private $increments = [
        T_IF,
        T_ELSE,
        T_ELSEIF,
        T_SWITCH,
        T_FOR,
        T_FOREACH,
        T_WHILE,
        T_DO,
        T_CATCH,
    ];

    /** @var int[]|string[] */
    private $booleanOperators = [
        T_BOOLEAN_AND, // &&
        T_BOOLEAN_OR, // ||
    ];

    /** @var int[]|string[] */
    private $operatorChainBreaks = [
        T_OPEN_PARENTHESIS,
        T_CLOSE_PARENTHESIS,
        T_SEMICOLON,
        T_INLINE_THEN,
        T_INLINE_ELSE,
    ];

    /**
     * B3. Nesting increments
     * @var int[]|string[]
     */
    private $nestingIncrements = [
        T_IF,
        T_INLINE_THEN,
        T_SWITCH,
        T_FOR,
        T_FOREACH,
        T_WHILE,
        T_DO,
        T_CATCH,
    ];

    /**
     * B1. Increments
     * @var int[]
     */
    private $breakingTokens = [T_CONTINUE, T_GOTO, T_BREAK];

    /**
     * @param mixed[] $tokens
     */
    public function computeForFunctionFromTokensAndPosition(array $tokens, int $position): int
    {
        // function without body, e.g. in interface
        if (!isset($tokens[$position]['scope_opener'])) {
            return 0;
        }

        // Detect start and end of this function definition
        $functionStartPosition = $tokens[$position]['scope_opener'];
        $functionEndPosition = $tokens[$position]['scope_closer'];

        $this->isInTryConstruction = false;
        $this->lastBooleanOperator = 0;
        $this->cognitiveComplexity = 0;

        for ($i = $functionStartPosition + 1; $i < $functionEndPosition; ++$i) {
            $currentToken = $tokens[$i];

            $this->resolveTryControlStructure($currentToken);
            $this->resolveBooleanOperatorChain($currentToken);

            if (!$this->isIncrementingToken($currentToken, $tokens, $i)) {
                continue;
            }

            ++$this->cognitiveComplexity;

            if (in_array($currentToken['code'], $this->breakingTokens, true)) {
                continue;
            }

            $isNestingIncrement = in_array($currentToken['code'], $this->nestingIncrements, true);
            $measuredNestingLevel = $this->getMeasuredNestingLevel($currentToken, $tokens, $position);

            // B3. Nesting increment
            if ($isNestingIncrement && $measuredNestingLevel > 1) {
                $this->cognitiveComplexity += $measuredNestingLevel - 1;
            }
        }

        return $this->cognitiveComplexity;
    }

    /**
     * Keep track of consecutive matching boolean operators, that don't receive increment.
     *
     * @param mixed[] $token
     */
    private function resolveBooleanOperatorChain(array $token): void
    {
        // Whenever we cross anything that interrupts possible condition we reset chain.
        if ($this->lastBooleanOperator && in_array($token['code'], $this->operatorChainBreaks, true)) {
            $this->lastBooleanOperator = 0;

            return;
        }

        if (!in_array($token['code'], $this->booleanOperators, true)) {
            return;
        }

        // If we match last operator, there is no increment added for current one.
        if ($this->lastBooleanOperator === $token['code']) {
            return;
        }

        ++$this->cognitiveComplexity;
        $this->lastBooleanOperator = $token['code'];
    }

    /**
     * @param mixed[] $token
     */
    private function resolveTryControlStructure(array $token): void
    {
        // code entered "try { }"
        if ($token['code'] === T_TRY) {
            $this->isInTryConstruction = true;
            return;
        }

        // code left "try { }"
        if ($token['code'] === T_CATCH) {
            $this->isInTryConstruction = false;
        }
    }

    /**
     * @param mixed[] $token
     * @param mixed[] $tokens
     */
    private function isIncrementingToken(array $token, array $tokens, int $position): bool
    {
        if (in_array($token['code'], $this->increments, true)) {
            return true;
        }

        // B1. ternary operator
        if ($token['code'] === T_INLINE_THEN) {
            return true;
        }

        // B1. goto LABEL, break LABEL, continue LABEL
        if (in_array($token['code'], $this->breakingTokens, true)) {
            $nextToken = $tokens[$position + 1]['code'];
            if ($nextToken !== T_SEMICOLON) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param mixed[] $currentToken
     * @param mixed[] $tokens
     */
    private function getMeasuredNestingLevel(array $currentToken, array $tokens, int $functionTokenPosition): int
    {
        $functionNestingLevel = $tokens[$functionTokenPosition]['level'];

        $measuredNestingLevel = $currentToken['level'] - $functionNestingLevel;

        if ($this->isInTryConstruction) {
            return --$measuredNestingLevel;
        }

        return $measuredNestingLevel;
    }
}
