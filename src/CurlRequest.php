<?php

namespace kidol\curl;

class CurlRequest
{
    
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    
    protected $config;
    protected $handle;
    
    public function __construct($config = null)
    {
        $this->config = $this->normalizeConfig($config);
    }
    
    /**
     * Sends a request defined by the given configuration.
     * @param array|CurlConfig $config
     * @return CurlResponse the response object.
     * @throws RequestFailedException
     * @throws RequestTimedOutException
     * @throws RequestAbortedException
     * @throws TooManyRedirectsException
     * @throws UnexpectedHttpCodeException
     */
    public function send($config = null)
    {
        $config = $this->normalizeConfig($config);
        $config->merge($this->config);
        return $this->sendInternal($config);
    }
    
    protected function sendInternal(CurlConfig $config, $startTime = null, $retryCount = 1)
    {
        if (empty($config->url)) {
            throw new InvalidConfigException("You can't send a request without setting the url first.");
        }
        
        if ($startTime === null) {
            $startTime = time();
        }
        
        if (!is_resource($this->handle)) {
            $this->handle = curl_init();
        } else {
            curl_reset($this->handle);
        }

        if (!empty($config->cookies)) {
            
            curl_setopt($this->handle, CURLOPT_COOKIE, http_build_query($config->cookies, '', '; ', PHP_QUERY_RFC3986));
        }

        if (!empty($config->headers)) {
            $headers = [];

            foreach ($config->headers as $name => $value) {
                $headers[] = "{$name}: {$value}";
            }

            curl_setopt($this->handle, CURLOPT_HTTPHEADER, $headers);
        }

        if (!empty($config->referer)) {
            curl_setopt($this->handle, CURLOPT_REFERER, $config->referer);
        }

        curl_setopt_array($this->handle, [

            CURLOPT_URL => $config->url,
            
            CURLOPT_POST => $config->method === self::METHOD_POST,
            
            CURLOPT_USERAGENT => $config->userAgent,
            
            CURLOPT_AUTOREFERER => $config->autoReferer,

            CURLOPT_FOLLOWLOCATION => $config->followRedirects,
            CURLOPT_MAXREDIRS => $config->maxRedirects,
            
            CURLOPT_CONNECTTIMEOUT => (int)$config->connectTimeout,
            CURLOPT_TIMEOUT => (int)$config->globalTimeout,
            
            CURLOPT_HEADER => true,
            CURLOPT_NOBODY => $config->headersOnly,
            CURLOPT_RETURNTRANSFER => true,
            
            CURLOPT_FORBID_REUSE => !$config->reuseConnection,
            
            CURLOPT_MAX_RECV_SPEED_LARGE => $config->maxReceiveSpeed,
            CURLOPT_MAX_SEND_SPEED_LARGE => $config->maxSendSpeed,
            
            CURLOPT_LOW_SPEED_LIMIT => $config->lowSpeedLimit,
            CURLOPT_LOW_SPEED_TIME => $config->lowSpeedTime,
            
            CURLOPT_SSL_VERIFYHOST => $config->sslVerifyHost,
            CURLOPT_SSL_VERIFYPEER => $config->sslVerifyPeer,
            
            CURLOPT_FAILONERROR => false,
            
            CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
        ]);
        
        if ($config->proxy !== null) {
            $proxy = $config->getProxyInfo();
            curl_setopt_array($this->handle, [
                CURLOPT_PROXY		 => $proxy['ip'],
                CURLOPT_PROXYPORT	 => $proxy['port'],
                CURLOPT_PROXYTYPE    => $proxy['type'],
                CURLOPT_PROXYUSERPWD => $proxy['login'],
            ]);
        }
        
        if ($config->progressHandler !== null && !$config->headersOnly) {
            curl_setopt_array($this->handle, [
                CURLOPT_NOPROGRESS => false,
                CURLOPT_PROGRESSFUNCTION => function($curl, $dltotal, $dlnow, $ultotal, $ulnow) use ($config) {
                    return call_user_func_array($config->progressHandler, [$dltotal, $dlnow, $ultotal, $ulnow]);
                },
            ]);
        }

        $response = new CurlResponse($this->handle, clone $config, curl_exec($this->handle));

        if ($response->getFailed()) {
            if ($config->throwException && $response->getTooManyRedirects()) {
                throw new TooManyRedirectsException($response, "The request failed due to too many redirects.", $response->errorNumber);
            } elseif ($config->throwException && $response->aborted) {
                throw new RequestAbortedException($response, "The request was aborted.", $response->errorNumber);
            } elseif ($config->retryHandler !== null && call_user_func_array($config->retryHandler, [$config, $response, $retryCount, time() - $startTime])) {
                $this->sendInternal($config, $startTime, ++$retryCount);
            } elseif ($config->throwException) {
                if ($response->getTimedOut()) {
                    throw new RequestTimedOutException($response, "The request timed out.", $response->errorNumber);
                } else {
                    throw new RequestFailedException($response, sprintf("The request failed: %s (#%d)", $response->error, $response->errorNumber), $response->errorNumber);
                }
            }
        } elseif (!empty($config->expectedHttpCodes) && !in_array($response->getHttpCode(), $config->expectedHttpCodes)) {
            throw new UnexpectedHttpCodeException($response, "Received an unexpected http code: {$response->getHttpCode()}", $response->errorNumber);
        }
        
        return $response;
    }

    protected function normalizeConfig($config)
    {
        if ($config !== null) {
            if (is_array($config)) {
                $config = new CurlConfig($config);
            } elseif (!$config instanceof CurlConfig) {
                throw new CurlException("\$config is not an instance of CurlConfig or does not");
            }
        } else {
            $config = new CurlConfig;
        }
        return $config;
    }
    
    public function getHandle()
    {
        return $this->handle;
    }
    
    public function getConfig()
    {
        return $this->config;
    }

}