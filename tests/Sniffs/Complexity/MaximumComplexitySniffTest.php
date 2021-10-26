<?php

declare(strict_types=1);

namespace Rarst\PHPCS\CognitiveComplexity\Tests\Sniffs\Complexity;

use Rarst\PHPCS\CognitiveComplexity\Tests\TestCase;

final class MaximumComplexitySniffTest extends TestCase
{

    public function testNoErrors(): void
    {
        $report = self::checkFile(__DIR__ . '/../../Data/function.php.inc');
        self::assertNoSniffErrorInFile($report);
    }

    public function testErrors(): void
    {
        $report = self::checkFile(__DIR__ . '/../../Data/function5.php.inc');
        self::assertSame(1, $report->getErrorCount());
        self::assertSniffError($report, 3, 'TooHigh');
    }
}
