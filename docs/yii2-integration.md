Yii 2 integration
-----------------

### Installation

Add the component to your configuration:

```php
'components' => [
    ...
    'curl' => [
        'class' => 'kidol\curl\yii2\Curl',
    ],
    ...
],
```

You can now get a `CurlRequest` object via `Yii::$app->curl->getRequest()`.

### Features

The component allows you to define a default request config. For example, assume you want to set `connectTimeout` to a certain value for all your requests. Instead of doing this within your code in different places, you can simply define a default config like so:


```php
'curl' => [
    'class' => 'kidol\curl\yii2\Curl',
    'configs' => [
        'default' => [
            'connectTimeout' => 10,
        ],
    ],
]
```

All request objects returned by `Yii::$app->curl->getRequest()` will now have a connect timeout of 10 seconds.

**Config merging**

Other than the default config, you can create unlimited named configs. All these configs will then merge with the default config.

Example:

```php
'curl' => [
    'class' => 'kidol\curl\yii2\Curl',
    'configs' => [
        'default' => [
            'globalTimeout' => 30,
            'userAgent' => 'curl',
        ],
        'crawler' => [
            'globalTimeout' => 600,
        ],
    ],
]
```

```php
$request = Yii::$app->curl->getRequest('crawler');
```

The returned request object will now use the user agent `curl` and a global timeout of `600` seconds.