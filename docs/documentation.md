Documentation
=============

* [Known issues](#known-issues)
* [Creating a request object](#creating-a-request-object)
* [Sending a request](#sending-a-request)
* [Handling failed requests](#handling-failed-requests)

Known issues
------------

- No POST support at the moment.
- Exposes the underlying curl handle, but will currently reset any option set with `curl_setopt()`.
- Needs more documentation and code comment.

Creating a request object
-------------------------

Without any explicit config, the default config values of the `CurlConfig` class will be used:
```php
$request = new CurlRequest;
```

Using `CurlConfig`:
```php
$config = new CurlConfig;
$config->url = 'http://example.com/';
$request = new CurlRequest($config);
```

Since `CurlConfig` can be initialized with an array, it can be simplified to:

```php
$config = new CurlConfig(['url' => 'http://example.com/']);
$request = new CurlRequest($config);
```

For `CurlRequest` this works too, so it gets even simpler:

```php
$request = new CurlRequest(['url' => 'http://example.com/']);
```

Don't worry about possible problems with the array approach - the property names and values you set will be validated in either case. Only downside is missing code-completion.

The config can always be accessed later via `$request->config`.

Sending a request
-----------------

```php
$response = $request->send();
```

The `send()` method supports a config as argument and is able to **merge** the request's defaut config (`$request->config`) with a per-request config.

Example:

```php
// Create a request object with a default config we want to use for all further requests.
$request = new CurlRequest([
    'connectTimeout' => 30,
    'userAgent' => 'example',
]);

// This single request might take a long time, so we increase the connect timeout.
$response = $request->send([
    'connectTimeout' => 100,
    'url' => 'http://example.com/',
]);
```

The request was now send with the user-agent `example`, a connect timeout of `100` and the url `http://example.com/`.

Handling failed requests
------------------------

### With exceptions

If a requests fails, by default one the following exceptions will be thrown:

- `RequestFailedException` - In case the target url is unreachable or the request exceeded the connect timeout.
- `RequestTimedOutException` - In case the request exceeded the global timeout.
- `RequestAbortedException` - In case the request was aborted by a configured progress handler.
- `TooManyRedirectsException` - In case the amount of redirects exceeded the configured maximum.
- `UnexpectedHttpCodeException` - In case the returned http code does not match any of the expected http codes.

Each exception extends from `CurlRequestException` which provides a `$response` property that holds the related `CurlResponse` object. You can use it for further processing. For example, even if a request timed out, you might still be able to get the returned http code.

Also, the `$code` property of the first two exceptions holds the underlying curl error code as returned by `curl_errno()`.

Example:

```php
try {
    $response = $request->send(['url' => 'http://example.com/']);
} catch (CurlRequestException $e) {
    // Your custom error handling
}
```

### Without exceptions

You can disable all `send()` related exceptions by setting `throwException` to `false`. You can then use one of the following properties of the `CurlResponse` object to check whether the request failed:

- `$failed` - Same as `RequestFailedException`.
- `$timedOut` - Same as `RequestTimedOutException`.
- `$aborted` - Same as `RequestAbortedException`.
- `$tooManyRedirects` - Same as `TooManyRedirectsException`.

There is no equivalent property for the `UnexpectedHttpCodeException`. Means you have to check the http code manually if needed.

Example:

```php
$response = $request->send([
    'url' => 'http://example.com/',
    'throwException' => false,
]);

if ($reponse->failed) {
    // Your custom error handling
}
```

### Optional: With a retry handler

You can configure `$retryHandler` to retry a failed request. It works for the `RequestFailedException` & `RequestTimedOutException` scenario (note: `throwException` does not have to be enabled).

The retry handler must be a `callable` and have the following signature:

```php
function(CurlConfig $config, CurlResponse $response, int $retryCount, int $timeSpend)
```

Parameters explained:

- `$config` - The `CurlConfig` object of the current request. You can modify it, for example to increase timeouts.
- `$response` - The related `CurlResponse` object.
- `$retryCount` - The current retry count (starts at 1).
- `$timeSpend` - The amount of seconds spend on all the requests (initial request + retry-requests).

If the retry handler returns `true`, the request will be send again. This can lead to an infinite loop, so make sure the retry handler does **not** return `true` at some point.

Example:

```php
$request = new CurlRequest([
    'http://example.com/very-unreliable-host-we-access-here',
    'retryHandler' => function($config, $response, $retryCount, $timeSpend) {
        // Retry up to 3 times
        if ($retryCount <= 3) {
            return true;
        }
    },
]);

$request->send();
```