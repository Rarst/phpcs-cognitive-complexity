<?php

declare(strict_types=1);

namespace Rarst\PHPCS\CognitiveComplexity;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;

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
    /**
     * B1. Increments
     *
     * Boolean operators are handled separately due to their chain logic.
     *
     * @var int[]|string[]
     */
    private const increments = [
        T_IF      => T_IF,
        T_ELSE    => T_ELSE,
        T_ELSEIF  => T_ELSEIF,
        T_SWITCH  => T_SWITCH,
        T_FOR     => T_FOR,
        T_FOREACH => T_FOREACH,
        T_WHILE   => T_WHILE,
        T_DO      => T_DO,
        T_CATCH   => T_CATCH,
    ];

    /** @var int[]|string[] */
    private const booleanOperators = [
        T_BOOLEAN_AND => T_BOOLEAN_AND, // &&
        T_BOOLEAN_OR  => T_BOOLEAN_OR, // ||
    ];

    /** @var int[]|string[] */
    private const operatorChainBreaks = [
        T_OPEN_PARENTHESIS  => T_OPEN_PARENTHESIS,
        T_CLOSE_PARENTHESIS => T_CLOSE_PARENTHESIS,
        T_SEMICOLON         => T_SEMICOLON,
        T_INLINE_THEN       => T_INLINE_THEN,
        T_INLINE_ELSE       => T_INLINE_ELSE,
    ];

    /**
     * B3. Nesting increments
     * @var int[]|string[]
     */
    private const nestingIncrements = [
        T_CLOSURE     => T_CLOSURE,
        T_IF          => T_IF,
        T_INLINE_THEN => T_INLINE_THEN,
        T_SWITCH      => T_SWITCH,
        T_FOR         => T_FOR,
        T_FOREACH     => T_FOREACH,
        T_WHILE       => T_WHILE,
        T_DO          => T_DO,
        T_CATCH       => T_CATCH,
    ];

    /**
     * B1. Increments
     * @var int[]
     */
    private const breakingTokens = [
        T_CONTINUE => T_CONTINUE,
        T_GOTO     => T_GOTO,
        T_BREAK    => T_BREAK,
    ];

    /** @var int */
    private $cognitiveComplexity = 0;

    /** @var int */
    private $lastBooleanOperator = 0;

    private $phpcsFile;

    /**
     * @param File $phpcsFile phpcs File instance
     * @param int  $position  current index
     */
    public function computeForFunctionFromTokensAndPosition(File $phpcsFile, int $position): int
    {
        $this->phpcsFile = $phpcsFile;
        $tokens = $phpcsFile->getTokens();

        // function without body, e.g. in interface
        if (!isset($tokens[$position]['scope_opener'])) {
            return 0;
        }

        // Detect start and end of this function definition
        $functionStartPosition = $tokens[$position]['scope_opener'];
        $functionEndPosition = $tokens[$position]['scope_closer'];

        $this->lastBooleanOperator = 0;
        $this->cognitiveComplexity = 0;

        /*
            Keep track of parser's level stack
            We push to this stak whenever we encounter a Tokens::$scopeOpeners
        */
        $levelStack = array();
        /*
            We look for changes in token[level] to know when to remove from the stack
            however ['level'] only increases when there are tokens inside {}
            after pushing to the stack watch for a level change
        */
        $levelIncreased = false;

        for ($i = $functionStartPosition + 1; $i < $functionEndPosition; ++$i) {
            $currentToken = $tokens[$i];

            $isNestingToken = false;
            if (\in_array($currentToken['code'], Tokens::$scopeOpeners)) {
                $isNestingToken = true;
                if ($levelIncreased === false && \count($levelStack)) {
                    // parser's level never increased
                    // caused by empty condition such as `if ($x) { }`
                    \array_pop($levelStack);
                }
                $levelStack[] = $currentToken;
                $levelIncreased = false;
            } elseif (isset($tokens[$i - 1]) && $currentToken['level'] < $tokens[$i - 1]['level']) {
                \array_pop($levelStack);
            } elseif (isset($tokens[$i - 1]) && $currentToken['level'] > $tokens[$i - 1]['level']) {
                $levelIncreased = true;
            }

            $this->resolveBooleanOperatorChain($currentToken);

            if (!$this->isIncrementingToken($currentToken, $tokens, $i)) {
                continue;
            }

            ++$this->cognitiveComplexity;

            $addNestingIncrement = isset(self::nestingIncrements[$currentToken['code']]);
            if (!$addNestingIncrement) {
                continue;
            }
            $measuredNestingLevel = \count(\array_filter($levelStack, function ($token) {
                return \in_array($token['code'], self::nestingIncrements);
            }));
            if ($isNestingToken) {
                $measuredNestingLevel--;
            }
            // B3. Nesting increment
            if ($measuredNestingLevel > 0) {
                $this->cognitiveComplexity += $measuredNestingLevel;
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
        if ($this->lastBooleanOperator && isset(self::operatorChainBreaks[$token['code']])) {
            $this->lastBooleanOperator = 0;

            return;
        }

        if (!isset(self::booleanOperators[$token['code']])) {
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
     * @param mixed[] $tokens
     */
    private function isIncrementingToken(array $token, array $tokens, int $position): bool
    {
        if (isset(self::increments[$token['code']])) {
            return true;
        }

        // B1. ternary operator
        if ($token['code'] === T_INLINE_THEN) {
            return true;
        }

        // B1. goto LABEL, break LABEL, continue LABEL
        if (isset(self::breakingTokens[$token['code']])) {
            $nextToken = $this->phpcsFile->findNext(Tokens::$emptyTokens, $position + 1, null, true);
            if ($nextToken === false || $tokens[$nextToken]['code'] !== T_SEMICOLON) {
                return true;
            }
        }

        return false;
    }
}
