# PHP GenDiff
[![PHP GenDiff](https://github.com/nikitovskij/php-gendiff/workflows/PHP%20GenDiff/badge.svg?branch=master)](https://github.com/nikitovskij/php-gendiff/actions)
[![Project Status: Active – The project has reached a stable, usable state and is being actively developed.](https://www.repostatus.org/badges/latest/active.svg)](https://www.repostatus.org/#active)
[![Maintainability](https://api.codeclimate.com/v1/badges/49b443ed2897d6babf08/maintainability)](https://codeclimate.com/github/nikitovskij/php-gendiff/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/49b443ed2897d6babf08/test_coverage)](https://codeclimate.com/github/nikitovskij/php-gendiff/test_coverage)

This is a program that determines the difference between two data structures.

Utility features:
- Support for different input formats: yaml and json
- Generating a report in plain text, stylish and json format

## Setup
```
$ git clone https://github.com/nikitovskij/php-gendiff.git

$ make install
```
### Composer
```
$ composer require nikitovskij/php-gendiff
```
## Examples

### php-gendiff: json
[![asciicast](https://asciinema.org/a/bguI3dSGS0Oucj41LmGyWx6aC.svg)](https://asciinema.org/a/bguI3dSGS0Oucj41LmGyWx6aC)

### php-gendiff: yml
[![asciicast](https://asciinema.org/a/lQ5YZJ1YkaQFT37Y1NADuiFwh.svg)](https://asciinema.org/a/lQ5YZJ1YkaQFT37Y1NADuiFwh)

### php-gendiff: nested structure
[![asciicast](https://asciinema.org/a/ZSWbl6MQVHGAqB3IQzgeONthe.svg)](https://asciinema.org/a/ZSWbl6MQVHGAqB3IQzgeONthe)

### php-gendiff: plain formatter
[![asciicast](https://asciinema.org/a/3gqXaadTxO0lnZWhxAn38RHsk.svg)](https://asciinema.org/a/3gqXaadTxO0lnZWhxAn38RHsk)

### php-gendiff: json formatter
[![asciicast](https://asciinema.org/a/rmD6GkIOUQvSiN0P5Z82ZhLbW.svg)](https://asciinema.org/a/rmD6GkIOUQvSiN0P5Z82ZhLbW)