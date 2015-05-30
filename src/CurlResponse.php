<?php

namespace kidol\curl;

class CurlResponse extends Object
{
    
    /**
     * @property resource $handle The underlying curl resource handle.
     * @property CurlConfig $config The config used for the request.
     * @property boolean|string The result of the underlying curl_exec() call.
     * @property boolean $failed Whether the request failed.
     * @property boolean $aborted Whether the request was aborted by a configured progress handler.
     * @property boolean $timedOut Whether the request timed out. This is the case when $globalTimeout or $lowSpeedTime has been reached.
     * @property boolean $tooManyRedirects Whether the request exceeded the configured $maxRedirects value.
     * @property string $error The error message of the underlying curl resource.
     * @property integer $errorNumber The error number of the underlying curl resource.
     * @property integer $effectiveUrl The last visited url.
     * @property integer $httpCode The returned http code.
     * @property string $headers The returned http headers.
     * @property string $content The returned content.
     */
    
    protected $handle;
    protected $config;
    protected $result;
    
    public function __construct($handle, CurlConfig $config, $result)
    {
        $this->handle = $handle;
        $this->config = $config;
        $this->result = $result;
    }

    public function getHandle()
    {
        return $this->handle;
    }
    
    public function getConfig()
    {
        return $this->config;
    }
    
    public function getResult()
    {
        return $this->result;
    }
    
    public function getFailed()
    {
        return $this->result === false;
    }

    public function getAborted()
    {
        return $this->getErrorNumber() === CURLE_ABORTED_BY_CALLBACK;
    }
    
    public function getTimedOut()
    {
        return $this->getErrorNumber() === CURLE_OPERATION_TIMEDOUT;
    }
    
    public function getTooManyRedirects()
    {
        return $this->getErrorNumber() === CURLE_TOO_MANY_REDIRECTS;
    }

    public function getError()
    {
        return curl_error($this->handle);
    }
    
    public function getErrorNumber()
    {
        return curl_errno($this->handle);
    }
    
    public function getEffectiveUrl()
    {
        return curl_getinfo($this->handle, CURLINFO_EFFECTIVE_URL);
    }
    
    public function getHttpCode()
    {
        return curl_getinfo($this->handle, CURLINFO_HTTP_CODE);
    }
    
    public function getHeaders()
    {
        return substr($this->result, 0, curl_getinfo($this->handle, CURLINFO_HEADER_SIZE));
    }
    
    public function getContent()
    {
        return (string)substr($this->result, curl_getinfo($this->handle, CURLINFO_HEADER_SIZE));
    }
    
}