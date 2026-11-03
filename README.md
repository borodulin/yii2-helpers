# Helpers for Yii2 framework

[![Latest Version](https://img.shields.io/packagist/v/conquer/helpers.svg)](https://packagist.org/packages/conquer/helpers)
[![Total Downloads](https://img.shields.io/packagist/dt/conquer/helpers.svg)](https://packagist.org/packages/conquer/helpers)
[![CI](https://github.com/borodulin/yii2-helpers/actions/workflows/ci.yml/badge.svg)](https://github.com/borodulin/yii2-helpers/actions/workflows/ci.yml)
[![License](https://img.shields.io/packagist/l/conquer/helpers.svg)](LICENSE)

## Requirements

- PHP 7.4 or 8.x
- Yii 2.0.14 or newer (2.0.x)

## Installation

Install the extension via [Composer](https://getcomposer.org):

```bash
composer require conquer/helpers:^2.1
```

## Security notes (2.2.0)

- `CurlTrait` / `Curl` verify TLS certificates by default (`CURLOPT_SSL_VERIFYPEER`, `CURLOPT_SSL_VERIFYHOST`).
  Earlier versions disabled verification.
- `Ip::getIpAddress()` no longer trusts `Client-IP` / `X-Forwarded-For` headers from arbitrary clients.
  Behind a reverse proxy, list it in `Ip::$trustedProxies`:

  ```php
  \conquer\helpers\Ip::$trustedProxies = ['10.0.0.0/8'];
  ```
- `Temporary::dir()` creates directories with unpredictable names and `0700` permissions.

## License

**conquer/helpers** is released under the MIT License. See the bundled [`LICENSE`](LICENSE) for details.
