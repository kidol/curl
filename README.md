kidol\curl
==========

Object orientated wrapper for the PHP cURL extension.

Links
-----

* [Installation](docs/installation.md)
* [Documentation](docs/documentation.md)
	* [Known issues](docs/documentation.md#known-issues)
	* [Creating a request object](docs/documentation.md#creating-a-request-object)
	* [Sending a request](docs/documentation.md#sending-a-request)
	* [Handling failed requests](docs/documentation.md#handling-failed-requests)
* [Yii 2 integration](docs/yii2-integration.md)

Small usage example
-------------------

```php
$response = (new CurlRequest())->send([
    'url' => 'http://example.com/',
	'connectTimeout' => 10,
	'userAgent' => 'curl',
]);

echo $response->content;
```