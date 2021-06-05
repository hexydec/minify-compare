# PHP Minify Compare

A program to compare PHP minifiers.

## Description

To use this software, you must input an array of callbacks that can accept source code, they should return the minified code. You also specify a number of URLs to test the minfiers against, or a URL to scrape links from. The program will then minify each URL with each minifier and output the results in a table. It will note:

- Input Size
- Input Size (Gzipped)
- Input Validation Errors (If a  validator was specified)
- Time Taken
- Output Size
- Diff Bytes
- Compression Ratio
- Output Size (gzipped)
- Diff Bytes (gzipped)
- Compression Ratio (gzipped)
- Output Validation Errors (If a  validator was specified)

You can also see the output source code of each minifier. The input and validation results can also be cached, but if the validator is connected to an external service, the first run will be slower, especially if the service is rate limited.

## Runners

In the runners folder there are some scripts to setup some HTML, CSS, and Javascript minifiers, these are included using composer.

## Installation

Clone this repository and then run:

```
$ composer install
```
