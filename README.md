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
composer require conquer/helpers:^2.2
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

## Security notes (2.2.1)

- `Temporary::clear()` no longer follows symbolic links: earlier versions deleted the contents
  of directories that a symlink inside the temporary directory pointed to.
- `Array2Xml::encodeXml()` throws `InvalidArgumentException` for keys that are not valid XML element names
  instead of producing injectable markup.
- `CurlTrait` / `Curl` allow only HTTP and HTTPS, also for redirects. Override `CURLOPT_PROTOCOLS`
  and `CURLOPT_REDIR_PROTOCOLS` if you need other protocols.
- `Json::encode()` escapes `<` and `>` (`JSON_HEX_TAG`). Keep in mind that it turns **every** string
  starting with `js:` into executable JavaScript: never pass user data through it, use
  `yii\helpers\Json::htmlEncode()` for data, or disable the prefix with `Json::$jsPrefix = null`.

Other fixes in 2.2.1: `Ip::validateIp()` uses `filter_var()` (public addresses such as `1.1.1.1` were rejected,
garbage strings and IPv6 loopback were accepted); `Curl::multiExec()` reports transfer errors;
the result of the previous request (headers, error message) is reset before each execution;
`XPath` update methods escape `&` and `<`, `XPath::evaluate()` returns numbers as is
(`evalute()` is kept as a deprecated alias), `xmlToArray()` skips comments; no `curl_close()`
deprecation on PHP 8.5.

## License

**conquer/helpers** is released under the MIT License. See the bundled [`LICENSE`](LICENSE) for details.
