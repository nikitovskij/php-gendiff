# PHP GenDiff
[![PHP GenDiff](https://github.com/nikitovskij/php-gendiff/workflows/PHP%20GenDiff/badge.svg?branch=master)](https://github.com/nikitovskij/php-gendiff/actions)
[![Project Status: Active – The project has reached a stable, usable state and is being actively developed.](https://www.repostatus.org/badges/latest/active.svg)](https://www.repostatus.org/#active)
[![Maintainability](https://api.codeclimate.com/v1/badges/49b443ed2897d6babf08/maintainability)](https://codeclimate.com/github/nikitovskij/php-gendiff/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/49b443ed2897d6babf08/test_coverage)](https://codeclimate.com/github/nikitovskij/php-gendiff/test_coverage)

This is a program that determines the difference between two data structures.

Utility features:
- Support for different input formats: yaml and json
- Generating a report in plain text, stylish and json format

## Requirements
* PHP >= 7.4
* <a href="https://github.com/phpfunct/funct">funct library</a>
* <a href="http://docopt.org/">CLI parser (docopt)</a>

## Setup
```
$ git clone https://github.com/nikitovskij/php-gendiff.git

$ make install
```
### Composer
```
$ composer require nikitovskij/php-gendiff
```

## Testing
```
$ make test
```
## Lint
```
$ make lint 
```

## Examples

#### php-gendiff: json
```
$ gendiff /path/to/file/first.json /path/to/file/second.json
```
Link to <a href="https://asciinema.org/a/bguI3dSGS0Oucj41LmGyWx6aC" target="_blank">asciinema</a>

#### php-gendiff: yml
```
$ gendiff /path/to/file/first.yml /path/to/file/second.yml
```
Link to <a href="https://asciinema.org/a/lQ5YZJ1YkaQFT37Y1NADuiFwh" target="_blank">asciinema</a>

#### php-gendiff: pretty format output
The `pretty` output format is set by default.
```
$ gendiff /path/to/file/first.json /path/to/file/second.json

or

$ gendiff --format pretty /path/to/file/first.json /path/to/file/second.json
```
Link to asciinema:<a href="https://asciinema.org/a/ZSWbl6MQVHGAqB3IQzgeONthe" target="_blank">asciinema</a>

#### php-gendiff: plain format output
```
$ gendiff --format plain /path/to/file/first.json /path/to/file/second.json
```
Link to <a href="https://asciinema.org/a/3gqXaadTxO0lnZWhxAn38RHsk" target="_blank">asciinema</a>

#### php-gendiff: json format output
```
$ gendiff --format json /path/to/file/first.json /path/to/file/second.json
```
Link to <a href="https://asciinema.org/a/rmD6GkIOUQvSiN0P5Z82ZhLbW" target="_blank">asciinema</a>