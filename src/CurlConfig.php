<?php

namespace kidol\curl;

class CurlConfig extends Object
{
    
    /**
     * @var type @property string $url The url to request. Defaults to null. Setting this manually is mandatory.
     * @property string $method The request method to use. Defaults to 'GET'.
     * Supported methods are defined by the CurlRequest::METHOD_* constants.
     * @property array $cookies The cookies to send with the request. Defaults to an empty array.
     * @property array $headers The headers to send with the request. Defaults to an empty array.
     * @property string $userAgent The user agent to send with the request. Default to 'curl'.
     * @property boolean $autoReferer Whether to automatically set the referer when following a redirect. Defaults to true.
     * @property string $referer The referer to send with the request. Default to an empty string.
     * @property integer $connectTimeout The connect timeout in seconds. Default to 30.
     * This timeout does only affect the connect-operation of a request.
     * @property integer $globalTimeout The global timeout in seconds. Defaults to 600.
     * This timeout does affect the whole request lifetime, inclusive the connect-operation.
     * @property boolean $followRedirects Whether to follow http redirects. Defaults to true.
     * @property integer $maxRedirects How many redirects to follow when $followRedirects is enabled. Defaults to 3.
     * If set to -1, then there is no limit (use with caution).
     * @property boolean $headersOnly Whether to finish the request as soon as the http headers are available. Defaults to false.
     * If set to true, a configured $progressHandler will not be called.
     * @property boolean $reuseConnection Whether to reuse the underlying http connection. Defaults to true, which means curl will
     * try to keep a connection open for as long as possible to use it for subsequent requests. If set to false, curl will open a new
     * connection for every request.
     * @property array $expectedHttpCodes A list of expected http response codes. Defaults to [200, 301, 302].
     * If the request does return a response code not in the list, a UnexpectedHttpCodeException will be thrown, but only if $throwException
     * is set to true (the default).
     * @property null|string $interface The interface to bind to. Defaults to null.
     * @property null|string $proxy The proxy to use for the request. Defaults to null.
     * @property integer $sslVerifyHost Whether to verify the ssl host. Defaults to 2.
     * See http://curl.haxx.se/libcurl/c/CURLOPT_SSL_VERIFYHOST.html for allowed values.
     * @property integer $sslVerifyPeer Whether to verify the ssl peer. Defaults to 1.
     * See http://curl.haxx.se/libcurl/c/CURLOPT_SSL_VERIFYPEER.html for allowed values.
     * @property integer $maxReceiveSpeed The max receive (download) speed in bytes per second. Default to 0, meaning no limit.
     * @property integer $maxSendSpeed The max send (upload) speed in bytes per second. Default to 0, meaning no limit.
     * @property integer $lowSpeedLimit The min speed limit in bytes per second. Default to 0, meaning no limit.
     * @property integer $lowSpeedTime After how many seconds the request should fail when the receive or send
     * speed is continuously less than $lowSpeedLimit. Defaults to 0, meaning this feature is disabled.
     * @property boolean $throwException Whether to throw an exception in case the request failed. Default to true.
     * @property null|callable $progressHandler A progress handler function. Defaults to null.
     * @property null|callable $retryHandler A retry handler function. Defaults to null.
     */
    
    private $_url;
    private $_method = CurlRequest::METHOD_GET;
    private $_cookies = [];
    private $_headers = [];  
    private $_userAgent = 'curl';
    private $_autoReferer = true;
    private $_referer;
    private $_connectTimeout = 30;
    private $_globalTimeout = 600;
    private $_followRedirects = true;
    private $_maxRedirects = 3;
    private $_headersOnly = false;
    private $_reuseConnection = true;
    private $_expectedHttpCodes = [200, 301, 302];
    private $_interface;
    private $_proxy;
    private $_sslVerifyHost = 2;
    private $_sslVerifyPeer = 1;
    private $_maxReceiveSpeed = 0;
    private $_maxSendSpeed = 0;
    private $_lowSpeedLimit = 0;
    private $_lowSpeedTime = 0;
    private $_throwException = true;
    private $_progressHandler;    
    private $_retryHandler;
    
    private $_modifiedOptions = [];
    private $_proxyInfo;
    
    public function __construct(array $config = [])
    {
        foreach ($config as $name => $value) {
            $this->$name = $value;
        }
    }
    
    public function setUrl($value)
    {
        $this->_url = $value;
        $this->_modifiedOptions['url'] = true;
    }
    
    public function getUrl()
    {
        return $this->_url;
    }
    
    public function setMethod($value)
    {
        if (!in_array($value, [CurlRequest::METHOD_GET, CurlRequest::METHOD_POST])) {
            throw new InvalidConfigException("Option 'method' is invalid.");
        }
        $this->_modifiedOptions['method'] = true;
        $this->_method = $value;
    }
    
    public function getMethod()
    {
        return $this->_method;
    }
    
    public function setCookies(array $value)
    {
        $this->_cookies = $value;
        $this->_modifiedOptions['cookies'] = true;
    }
    
    public function getCookies()
    {
        return $this->_cookies;
    }
    
    public function setHeaders(array $value)
    {
        $this->_headers = $value;
        $this->_modifiedOptions['headers'] = true;
    }
    
    public function getHeaders()
    {
        return $this->_headers;
    }
    
    public function setUserAgent($value)
    {
        $this->_userAgent = $value;
        $this->_modifiedOptions['useragent'] = true;
    }
    
    public function getUserAgent()
    {
        return $this->_userAgent;
    }
    
    public function setAutoReferer($value)
    {
        if (!is_bool($value)) {
            throw new InvalidConfigException("CurlConfig::\$autoReferer must be of type boolean.");
        }
        $this->_autoReferer = $value;
        $this->_modifiedOptions['autoreferer'] = true;
    }
    
    public function getAutoReferer()
    {
        return $this->_autoReferer;
    }
    
    public function setReferer($value)
    {
        $this->_referer = $value;
        $this->_modifiedOptions['referer'] = true;
    }
    
    public function getReferer()
    {
        return $this->_referer;
    }
    
    public function setConnectTimeout($value)
    {
        $this->_connectTimeout = $value;
        $this->_modifiedOptions['connecttimeout'] = true;
    }
    
    public function getConnectTimeout()
    {
        return $this->_connectTimeout;
    }
    
    public function setGlobalTimeout($value)
    {
        $this->_globalTimeout = $value;
        $this->_modifiedOptions['globaltimeout'] = true;
    }
    
    public function getGlobalTimeout()
    {
        return $this->_globalTimeout;
    }
    
    public function setFollowRedirects($value)
    {
        if (!is_bool($value)) {
            throw new InvalidConfigException("CurlConfig::\$followRedirects must be of type boolean.");
        }
        $this->_followRedirects = $value;
        $this->_modifiedOptions['followredirects'] = true;
    }
    
    public function getFollowRedirects()
    {
        return $this->_followRedirects;
    }
    
    public function setMaxRedirects($value)
    {
        if (!is_int($value)) {
            throw new InvalidConfigException("CurlConfig::\$maxRedirects must be of type integer.");
        }
        $this->_maxRedirects = $value;
        $this->_modifiedOptions['maxredirects'] = true;
    }
    
    public function getMaxRedirects()
    {
        return $this->_maxRedirects;
    }
    
    public function setHeadersOnly($value)
    {
        if (!is_bool($value)) {
            throw new InvalidConfigException("CurlConfig::\$headersOnly must be of type boolean.");
        }
        $this->_headersOnly = $value;
        $this->_modifiedOptions['headersonly'] = true;
    }
    
    public function getHeadersOnly()
    {
        return $this->_headersOnly;
    }
    
    public function setReuseConnection($value)
    {
        if (!is_bool($value)) {
            throw new InvalidConfigException("CurlConfig::\$reuseConnection must be of type boolean.");
        }
        $this->_headersOnly = $value;
        $this->_modifiedOptions['reuseconnection'] = true;
    }
    
    public function getReuseConnection()
    {
        return $this->_reuseConnection;
    }
    
    public function setExpectedHttpCodes(array $value)
    {
        $this->_expectedHttpCodes = $value;
        $this->_modifiedOptions['expectedhttpcodes'] = true;
    }
    
    public function getExpectedHttpCodes()
    {
        return $this->_expectedHttpCodes;
    }
    
    public function setInterface($value)
    {
        $this->_interface = $value;
        $this->_modifiedOptions['interface'] = true;
    }
    
    public function getInterface()
    {
        return $this->_interface;
    }
    
    public function setProxy($value)
    {
        if ($value === null) {
            $this->_proxyInfo = null;
        } else {
            if (!preg_match("#^(?<protocol>https?|socks)://((?<login>[^:]+:[^:]+)@)?(?<ip>[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}):(?<port>[0-9]{1,5})/?$#", $value, $match)) {
                throw new InvalidConfigException("CurlConfig::\$proxy should have the following format: <protocol>://[<username>:<password>@]<ip>:<port>");
            }
            $this->_proxyInfo = [
                'ip' => $match['ip'],
                'port' => $match['port'],
                'type' => $match['protocol'] === 'socks' ? CURLPROXY_SOCKS5 : CURLPROXY_HTTP,
                'login' => isset($match['login']) ? $match['login'] : null,
            ];
        }
        $this->_proxy = $value;
        $this->_modifiedOptions['proxy'] = true;
    }
    
    public function getProxy()
    {
        return $this->_proxy;
    }
    
    public function getProxyInfo()
    {
        return $this->_proxyInfo;
    }
    
    public function setSslVerifyHost($value)
    {
        if (!in_array($value, [0, 1, 2])) {
            throw new InvalidConfigException("CurlConfig::\$sslVerifyHost does not allow any other value than 0, 1 or 2. For details: http://curl.haxx.se/libcurl/c/CURLOPT_SSL_VERIFYHOST.html");
        }
        $this->_sslVerifyHost = $value;
        $this->_modifiedOptions['sslverifyhost'] = true;
    }
    
    public function getSslVerifyHost()
    {
        return $this->_sslVerifyHost;
    }
    
    public function setSslVerifyPeer($value)
    {
        if (!in_array($value, [0, 1])) {
            throw new InvalidConfigException("CurlConfig::\$sslVerifyPeer does not allow any other value than 0 or 1. For details: http://curl.haxx.se/libcurl/c/CURLOPT_SSL_VERIFYPEER.html");
        }
        $this->_sslVerifyPeer = $value;
        $this->_modifiedOptions['sslverifypeer'] = true;
    }
    
    public function getSslVerifyPeer()
    {
        return $this->_sslVerifyPeer;
    }
    
    public function setMaxReceiveSpeed($value)
    {
        if (!is_int($value)) {
            throw new InvalidConfigException("CurlConfig::\$maxReceiveSpeed must be of type integer.");
        }
        $this->_maxReceiveSpeed = $value;
        $this->_modifiedOptions['maxreceivespeed'] = true;
    }
    
    public function getMaxReceiveSpeed()
    {
        return $this->_maxReceiveSpeed;
    }
    
    public function setMaxSendSpeed($value)
    {
        if (!is_int($value)) {
            throw new InvalidConfigException("CurlConfig::\$maxSendSpeed must be of type integer.");
        }
        $this->_maxSendSpeed = $value;
        $this->_modifiedOptions['maxsendspeed'] = true;
    }
    
    public function getMaxSendSpeed()
    {
        return $this->_maxSendSpeed;
    }
    
    public function setLowSpeedLimit($value)
    {
        if (!is_int($value)) {
            throw new InvalidConfigException("CurlConfig::\$lowSpeedLimit must be of type integer.");
        }
        $this->_lowSpeedLimit = $value;
        $this->_modifiedOptions['lowspeedlimit'] = true;
    }
    
    public function getLowSpeedLimit()
    {
        return $this->_lowSpeedLimit;
    }
    
    public function setLowSpeedTime($value)
    {
        if (!is_int($value)) {
            throw new InvalidConfigException("CurlConfig::\$lowSpeedTime must be of type integer.");
        }
        $this->_lowSpeedTime = $value;
        $this->_modifiedOptions['lowspeedtime'] = true;
    }
    
    public function getLowSpeedTime()
    {
        return $this->_lowSpeedTime;
    }
    
    public function setThrowException($value)
    {
        if (!is_bool($value)) {
            throw new InvalidConfigException("CurlConfig::\$throwException must be of type boolean.");
        }
        $this->_throwException = $value;
        $this->_modifiedOptions['throwexception'] = true;
    }
    
    public function getThrowException()
    {
        return $this->_throwException;
    }
    
    public function setProgressHandler($value)
    {
        if ($value !== null && !is_callable($value)) {
            throw new InvalidConfigException("CurlConfig::\$progressHandler must be callable.");
        }
        $this->_progressHandler = $value;
        $this->_modifiedOptions['progresshandler'] = true;
    }
    
    public function getProgressHandler()
    {
        return $this->_progressHandler;
    }
    
    public function setRetryHandler($value)
    {
        if ($value !== null && !is_callable($value)) {
            throw new InvalidConfigException("CurlConfig::\$retryHandler must be callable.");
        }
        $this->_retryHandler = $value;
        $this->_modifiedOptions['retryhandler'] = true;
    }
    
    public function getRetryHandler()
    {
        return $this->_retryHandler;
    }

    public function isOptionSet($name)
    {
        return isset($this->_modifiedOptions[strtolower($name)]);
    }
    
    public function merge(CurlConfig $config)
    {
        $reflect = new \ReflectionClass($this);
        $properties = $reflect->getProperties(\ReflectionProperty::IS_PRIVATE);

        foreach ($properties as $property) {
            $name = ltrim($property->getName(), '_');
            if ($name !== 'proxyInfo' && $name !== 'modifiedOptions') {
                if (!$this->isOptionSet($name) && $config->isOptionSet($name)) {
                    $this->$name = $config->$name;
                }
            }
        }
    }
}