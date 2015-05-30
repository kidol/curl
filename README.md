kidol\curl
==========

Object orientated wrapper for the PHP cURL extension.

Links
-----

* [Installation](docs/installation.md)
* [Documentation](docs/documentation.md)
	* [Known limitations](docs/documentation#known-limitations)
	* [Creating a request object](docs/documentation#creating-a-request-object)
	* [Sending a request](docs/documentation#sending-a-request)
	* [Handling failed requests](docs/documentation#handling-failed-requests)
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