# PHP Minify Compare

A program to compare PHP minifiers.

## Description

In the runners folder there are some scripts to setup each minifier in a callback to be tested against a number of URL's, the program will then minify each URL with each minifier and output the results in a table. It will note:

- Input Size
- Time Taken
- Output Size
- Diff Bytes
- Compression Ratio
- Output Size (gzipped)
- Diff Bytes (gzipped)
- Compression Ratio (gzipped)

You an also see the output source code of each minifier.
