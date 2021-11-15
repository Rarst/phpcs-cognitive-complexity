<?php declare(strict_types=1);

namespace Rarst\PHPCS\CognitiveComplexity\Tests;

use Iterator;
use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Ruleset;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Tokenizers\PHP;
use PHPUnit\Framework\TestCase;
use Rarst\PHPCS\CognitiveComplexity\Analyzer;
use stdClass;

final class AnalyzerTest extends TestCase
{
    /** @var Analyzer */
    private $analyzer;

    protected function setUp(): void
    {
        $this->analyzer = new Analyzer();
    }

    /**
     * @dataProvider provideTokensAndExpectedCognitiveComplexity()
     */
    public function test(string $filePath, int $expectedCognitiveComplexity): void
    {
        $file = $this->fileFactory($filePath);
        $functionTokenPos = $file->findNext(T_FUNCTION, 0);
        $cognitiveComplexity = $this->analyzer->computeForFunctionFromTokensAndPosition(
            $file,
            $functionTokenPos
        );
        $this->assertSame($expectedCognitiveComplexity, $cognitiveComplexity);
    }

    /**
     * Here are tested all examples from https://www.sonarsource.com/docs/CognitiveComplexity.pdf
     */
    public function provideTokensAndExpectedCognitiveComplexity(): Iterator
    {
        yield [__DIR__ . '/Data/function.php.inc', 9];
        yield [__DIR__ . '/Data/function2.php.inc', 6];
        yield [__DIR__ . '/Data/function3.php.inc', 9];
        yield [__DIR__ . '/Data/function4.php.inc', 2];
        yield [__DIR__ . '/Data/function5.php.inc', 19];
        yield [__DIR__ . '/Data/function6.php.inc', 0];
        yield [__DIR__ . '/Data/function7.php.inc', 3];
        yield [__DIR__ . '/Data/function8.php.inc', 7];
        yield [__DIR__ . '/Data/function9.php.inc', 5];
        yield [__DIR__ . '/Data/function10.php.inc', 19];
        yield [__DIR__ . '/Data/function11.php.inc', 5];
        yield [__DIR__ . '/Data/function12.php.inc', 8];
    }

    /**
     * @param string $filePath
     *
     * @return File
     */
    private function fileFactory($filePath)
    {
        $config = new Config();
        $ruleset = new Ruleset($config);
        $file = new File($filePath, $ruleset, $config);
        $file->setContent(\file_get_contents($filePath));
        $file->parse();
        return $file;
    }
}
