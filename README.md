# PHPCS Cognitive Complexity

_Make it maintainable or else._

[![Tests Status](https://github.com/rarst/phpcs-cognitive-complexity/actions/workflows/tests.yml/badge.svg)](https://github.com/Rarst/phpcs-cognitive-complexity/actions/workflows/tests.yml)
[![Latest Stable Version](https://img.shields.io/packagist/v/rarst/phpcs-cognitive-complexity.svg?label=version)](https://packagist.org/packages/rarst/phpcs-cognitive-complexity)
[![PHP from Packagist](https://img.shields.io/packagist/php-v/rarst/phpcs-cognitive-complexity.svg)](https://packagist.org/packages/rarst/phpcs-cognitive-complexity)
[![PDS Skeleton](https://img.shields.io/badge/pds-skeleton-blue.svg)](https://github.com/php-pds/skeleton)

The project implements [Cognitive Complexity metric by SonarSource](https://www.sonarsource.com/resources/white-papers/cognitive-complexity.html) as [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) standard.

## Install

### Composer

```bash
composer init --no-interaction
composer require rarst/phpcs-cognitive-complexity squizlabs/php_codesniffer dealerdirect/phpcodesniffer-composer-installer
vendor/bin/phpcs --config-set installed_paths vendor/rarst/phpcs-cognitive-complexity/src/CognitiveComplexity
vendor/bin/phpcs --standard=CognitiveComplexity /path/to/scan
```

### Standalone

```bash
git clone https://github.com/Rarst/phpcs-cognitive-complexity
phpcs --standard=phpcs-cognitive-complexity/src/CognitiveComplexity /path/to/scan
```

## Limitations

- detection of boolean operator chains is not perfect, due to complexity of many possible cases.

## Credits

Initial code forked from [Symplify Coding Standard](https://github.com/Symplify/Symplify).

## License

MIT
