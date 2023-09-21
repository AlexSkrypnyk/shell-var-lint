<p align="center">
  <a href="" rel="noopener">
  <img width=200px height=200px src="https://placehold.jp/000000/ffffff/200x200.png?text=Shell%20var%20lint&css=%7B%22border-radius%22%3A%22%20100px%22%7D" alt="Project logo"></a>
</p>

<h1 align="center">shell-var-lint</h1>

<div align="center">

  [![GitHub Issues](https://img.shields.io/github/issues/AlexSkrypnyk/shell-var-lint.svg)](https://github.com/AlexSkrypnyk/shell-var-lint/issues)
  [![GitHub Pull Requests](https://img.shields.io/github/issues-pr/AlexSkrypnyk/shell-var-lint.svg)](https://github.com/AlexSkrypnyk/shell-var-lint/pulls)
  [![Test](https://github.com/AlexSkrypnyk/shell-var-lint/actions/workflows/test.yml/badge.svg)](https://github.com/AlexSkrypnyk/shell-var-lint/actions/workflows/test.yml)
  [![codecov](https://codecov.io/gh/AlexSkrypnyk/shell-var-lint/graph/badge.svg?token=OAERD0PS3T)](https://codecov.io/gh/AlexSkrypnyk/shell-var-lint)
  ![GitHub release (latest by date)](https://img.shields.io/github/v/release/AlexSkrypnyk/shell-var-lint)
  ![LICENSE](https://img.shields.io/github/license/AlexSkrypnyk/shell-var-lint)

</div>

---

<p align="center"> Lint and fix shell vars to ${VAR} format.
    <br>
</p>

## Features

- Report on shell variables that are not in `${VAR}` format.
- Fix shell variables that are not in `${VAR}` format.

## Installation


    composer require --dev alexskrypnyk/shell-var-lint



## Usage

    # Lint file.
    vendor/bin/shell-var-lint <file>

    # Fix file.
    vendor/bin/shell-var-lint <file> --fix



## Maintenance


    composer install
    composer lint
    composer test


