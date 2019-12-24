<?php

declare(strict_types=1);

namespace Rarst\PHPCS\CognitiveComplexity\Tests;

use PHP_CodeSniffer\Files\File;
use PHPUnit\Framework\TestCase;
use Rarst\PHPCS\CognitiveComplexity\Sniffs\Complexity\MaximumComplexitySniff;

final class SniffTest extends TestCase
{
    public function testMaximumComplexity(): void
    {
        $file = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getTokens', 'addError'])
            ->getMock();

        $file->expects($this->once())
            ->method('getTokens')
            ->willReturn(
                [
                    [
                        'scope_opener' => 2,
                        'scope_closer' => 5,
                        'level' => 0,
                    ],
                    [],
                    [
                        'content' => 'function_name',
                        'level' => 0,
                    ],
                    [
                        'type'  => 'T_IF',
                        'code'  => T_IF,
                        'level' => 1,
                    ],
                    [
                        'type'  => 'T_BOOLEAN_AND',
                        'code'  => T_BOOLEAN_AND,
                        'level' => 2,
                    ],
                ]
            );

        $file->expects($this->once())
            ->method('addError')
            ->with(
                $this->isType('string'),
                $this->anything(),
                $this->equalTo('TooHigh'),
                $this->equalTo(['function_name', 2, 1])
            );

        $sniff                         = new MaximumComplexitySniff();
        $sniff->maxCognitiveComplexity = 1;

        $this->assertEquals([T_FUNCTION], $sniff->register());

        $sniff->process($file, 0);
    }
}
