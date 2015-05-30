<?php

namespace kidol\curl;

abstract class CurlRequestException extends CurlException
{
    
    /**
     * @var CurlResponse The related response object.
     */
    public $response;
    
    /**
     * @param CurlResponse $response The related response object.
     * @param string $message The error message.
     * @param integer $code The error code of the underlying curl resource.
     */
    public function __construct(CurlResponse $response, $message, $code)
    {
        $this->response = $response;
        parent::__construct($message, $code);
    }
    
}