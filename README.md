# PHPCS Cognitive Complexity

_Make it maintainable or else._

[![PDS Skeleton](https://img.shields.io/badge/pds-skeleton-blue.svg)](https://github.com/php-pds/skeleton)

The project implements [Cognitive Complexity metric by SonarSource](https://www.sonarsource.com/resources/white-papers/cognitive-complexity.html) as [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) standard.

## Install

### Composer

```bash
composer init --no-interaction
composer require rarst/phpcs-cognitive-complexity squizlabs/php_codesniffer dealerdirect/phpcodesniffer-composer-installer
vendor/bin/phpcs --standard=CognitiveComplexity /path/to/scan
```

### Standalone

```bash
git clone https://github.com/Rarst/phpcs-cognitive-complexity
phpcs --standard=phpcs-cognitive-complexity/src/CognitiveComplexity /path/to/scan
```

## Credits

Initial code forked from [Simplify Coding Standard](https://github.com/Symplify/Symplify).