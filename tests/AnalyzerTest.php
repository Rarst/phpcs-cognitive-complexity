<?php declare(strict_types=1);

namespace Rarst\PHPCS\CognitiveComplexity\Tests;

use Iterator;
use PHP_CodeSniffer\Config;
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
        $fileContent = file_get_contents($filePath);
        $tokens = $this->fileToTokens($fileContent);
        $functionTokenPosition = null;
        foreach ($tokens as $position => $token) {
            if ($token['code'] === T_FUNCTION) {
                $functionTokenPosition = $position;
                break;
            }
        }

        $cognitiveComplexity = $this->analyzer->computeForFunctionFromTokensAndPosition(
            $tokens,
            $functionTokenPosition
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
        yield [__DIR__ . '/Data/function3.php.inc', 1];
        yield [__DIR__ . '/Data/function4.php.inc', 2];
        yield [__DIR__ . '/Data/function5.php.inc', 19];
        yield [__DIR__ . '/Data/function6.php.inc', 0];
        yield [__DIR__ . '/Data/function7.php.inc', 3];
        yield [__DIR__ . '/Data/function8.php.inc', 7];
        yield [__DIR__ . '/Data/function9.php.inc', 5];
        yield [__DIR__ . '/Data/function10.php.inc', 19];
    }

    /**
     * @return mixed[]
     */
    private function fileToTokens(string $fileContent): array
    {
        return (new PHP($fileContent, $this->getLegacyConfig()))->getTokens();
    }

    /**
     * @return Config|stdClass
     */
    private function getLegacyConfig()
    {
        $config = new stdClass();
        $config->tabWidth = 4;
        $config->annotations = false;
        $config->encoding = 'UTF-8';

        return $config;
    }
}
