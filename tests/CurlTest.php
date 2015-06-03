<?php

namespace kidol\curl\tests;

use kidol\curl\CurlConfig;
use kidol\curl\CurlRequest;
use kidol\curl\TooManyRedirectsException;

class CurlTest extends \PHPUnit_Framework_TestCase
{

    private $pid;
    private $baseUrl = 'http://127.0.0.1:8000';
    
    public function setUp()
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            $descriptorspec = [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
            ];

            $proc = proc_open("start /b php -S 127.0.0.1:8000 -t " . __DIR__ . ' > NUL 2>&1', $descriptorspec, $pipes);
            
            $parentPid = proc_get_status($proc)['pid'];

            $output = array_filter(explode(' ', shell_exec("wmic process get parentprocessid,processid | \"C:/windows/system32/find\" \"{$parentPid}\"")));

            array_pop($output);
            
            $this->pid = end($output);
        }
        
        register_shutdown_function([$this, 'tearDown']);
    }
    
    public function tearDown()
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            shell_exec("taskkill /F /pid {$this->pid} > NUL 2>&1");
        } else {
            shell_exec("kill {$this->pid}");
        }
    }

    public function testMethod()
    {
        $response = (new CurlRequest([
            'url' => "{$this->baseUrl}/?test=server-var&var=REQUEST_METHOD",
            'method' => CurlRequest::METHOD_GET,
        ]))->send();
            
        $this->assertTrue($response->getContent() === CurlRequest::METHOD_GET);
        
        $response = (new CurlRequest([
            'url' => "{$this->baseUrl}/?test=server-var&var=REQUEST_METHOD",
            'method' => CurlRequest::METHOD_POST,
        ]))->send();

        $this->assertTrue($response->getContent() === CurlRequest::METHOD_POST);
    }
    
    public function testCookies()
    {
        $response = (new CurlRequest([
            'url' => "{$this->baseUrl}/?test=cookies",
            'cookies' => [],
        ]))->send();
            
        $this->assertTrue($response->getContent() === '');
        
        $response = (new CurlRequest([
            'url' => "{$this->baseUrl}/?test=cookies",
            'cookies' => ['test1' => 1, 'test2' => '2', 'test2' => '=', 'test3' => '&', 'test 4' => '; '],
        ]))->send();

        $this->assertTrue($response->getContent() === 'test1=1; test2=%3D; test3=%26; test_4=%3B%20');
    }
    
    public function testHeaders()
    {
        $response = (new CurlRequest([
            'url' => "{$this->baseUrl}/?test=headers",
            'headers' => ['test1' => 'test-header1', 'TEST2' => 'test-header2'],
        ]))->send();
            
        $this->assertTrue($response->getContent() === 'test-header1 test-header2');
    }
    
    public function testUserAgent()
    {
        $response = (new CurlRequest([
            'url' => "{$this->baseUrl}/?test=server-var&var=HTTP_USER_AGENT",
            'userAgent' => null,
        ]))->send();
            
        $this->assertTrue($response->getContent() === '');
        
        $response = (new CurlRequest([
            'url' => "{$this->baseUrl}/?test=server-var&var=HTTP_USER_AGENT",
            'userAgent' => 'test-user-agent',
        ]))->send();
            
        $this->assertTrue($response->getContent() === 'test-user-agent');
    }
    
    public function testFollowRedirects()
    {
        $response = (new CurlRequest([
            'url' => "{$this->baseUrl}/?test=followredirects",
            'followRedirects' => true,
        ]))->send();

        $this->assertTrue(substr($response->getEffectiveUrl(), -5) === 'end=1');

        $response = (new CurlRequest([
            'url' => "{$this->baseUrl}/?test=followredirects",
            'followRedirects' => false,
        ]))->send();

        $this->assertTrue(substr($response->getEffectiveUrl(), -5) !== 'end=1');
    }
    
    public function testMaxRedirects()
    {
        // Test infinite redirects
        
        $response = (new CurlRequest([
            'url' => "{$this->baseUrl}/?test=maxRedirects",
            'maxRedirects' => -1,
        ]))->send();
            
        $this->assertFalse($response->getTooManyRedirects());

        // Test 10 redirects
        
        $response = (new CurlRequest([
            'url' => "{$this->baseUrl}/?test=maxRedirects",
            'maxRedirects' => 10,
        ]))->send();
            
        $this->assertFalse($response->getTooManyRedirects());
        
        // Test failing 0 redirects
        
        try {
            $response = (new CurlRequest([
                'url' => "{$this->baseUrl}/?test=maxRedirects",
                'maxRedirects' => 0,
            ]))->send();
                
            $this->assertTrue(false);
        }
        catch (TooManyRedirectsException $e) {}
        
        // Test failing 0 redirects (without exception)

        $response = (new CurlRequest([
            'url' => "{$this->baseUrl}/?test=maxRedirects",
            'maxRedirects' => 0,
            'throwException' => false,
        ]))->send();
            
        $this->assertTrue($response->getTooManyRedirects());
        
        // Test failing 5 redirects
        
        try {
            $response = (new CurlRequest([
                'url' => "{$this->baseUrl}/?test=maxRedirects",
                'maxRedirects' => 5,
            ]))->send();
                
            $this->assertTrue(false);
        }
        catch (TooManyRedirectsException $e) {}
        
        // Test failing 5 redirects (without exception)
        
        $response = (new CurlRequest([
            'url' => "{$this->baseUrl}/?test=maxRedirects",
            'maxRedirects' => 5,
            'throwException' => false,
        ]))->send();

        $this->assertTrue($response->getTooManyRedirects());
    }
    
    public function testAutoReferer()
    {
        $response = (new CurlRequest([
            'url' => "{$this->baseUrl}/?test=autoReferer",
            'autoReferer' => false,
        ]))->send();
            
        $this->assertTrue($response->getContent() === '');
        
        $response = (new CurlRequest([
            'url' => "{$this->baseUrl}/?test=autoReferer",
            'autoReferer' => true,
        ]))->send();

        $this->assertTrue($response->getContent() !== '');
    }
    
    public function testReferer()
    {
        $response = (new CurlRequest([
            'url' => "{$this->baseUrl}/?test=server-var&var=HTTP_REFERER",
            'referer' => null,
        ]))->send();
            
        $this->assertTrue($response->getContent() === '');
        
        $response = (new CurlRequest([
            'url' => "{$this->baseUrl}/?test=server-var&var=HTTP_REFERER",
            'referer' => 'test-referer',
        ]))->send();

        $this->assertTrue($response->getContent() === 'test-referer');
    }
    
    public function testConfig()
    {
        $config = new CurlConfig;
        
        $config->url = $config->url;
        $this->assertTrue($config->isOptionSet('url'));
        
        $config->method = $config->method;
        $this->assertTrue($config->isOptionSet('method'));
        
        $config->cookies = $config->cookies;
        $this->assertTrue($config->isOptionSet('cookies'));
        
        $config->headers = $config->headers;
        $this->assertTrue($config->isOptionSet('headers'));
        
        $config->userAgent = $config->userAgent;
        $this->assertTrue($config->isOptionSet('userAgent'));
        
        $config->autoReferer = $config->autoReferer;
        $this->assertTrue($config->isOptionSet('autoReferer'));
        
        $config->referer = $config->referer;
        $this->assertTrue($config->isOptionSet('referer'));
        
        $config->connectTimeout = $config->connectTimeout;
        $this->assertTrue($config->isOptionSet('connectTimeout'));
        
        $config->globalTimeout = $config->globalTimeout;
        $this->assertTrue($config->isOptionSet('globalTimeout'));
        
        $config->followRedirects = $config->followRedirects;
        $this->assertTrue($config->isOptionSet('followRedirects'));
        
        $config->maxRedirects = $config->maxRedirects;
        $this->assertTrue($config->isOptionSet('maxRedirects'));
        
        $config->headersOnly = $config->headersOnly;
        $this->assertTrue($config->isOptionSet('headersOnly'));
        
        $config->reuseConnection = $config->reuseConnection;
        $this->assertTrue($config->isOptionSet('reuseConnection'));
        
        $config->expectedHttpCodes = $config->expectedHttpCodes;
        $this->assertTrue($config->isOptionSet('expectedHttpCodes'));
        
        $config->interface = $config->interface;
        $this->assertTrue($config->isOptionSet('interface'));
        
        $config->proxy = $config->proxy;
        $this->assertTrue($config->isOptionSet('proxy'));
        
        $config->sslVerifyHost = $config->sslVerifyHost;
        $this->assertTrue($config->isOptionSet('sslVerifyHost'));
        
        $config->sslVerifyPeer = $config->sslVerifyPeer;
        $this->assertTrue($config->isOptionSet('sslVerifyPeer'));
        
        $config->maxReceiveSpeed = $config->maxReceiveSpeed;
        $this->assertTrue($config->isOptionSet('maxReceiveSpeed'));
        
        $config->maxSendSpeed = $config->maxSendSpeed;
        $this->assertTrue($config->isOptionSet('maxSendSpeed'));
        
        $config->lowSpeedLimit = $config->lowSpeedLimit;
        $this->assertTrue($config->isOptionSet('lowSpeedLimit'));
        
        $config->lowSpeedTime = $config->lowSpeedTime;
        $this->assertTrue($config->isOptionSet('lowSpeedTime'));
        
        $config->throwException = $config->throwException;
        $this->assertTrue($config->isOptionSet('throwException'));
        
        $config->progressHandler = $config->progressHandler;
        $this->assertTrue($config->isOptionSet('progressHandler'));
        
        $config->retryHandler = $config->retryHandler;
        $this->assertTrue($config->isOptionSet('retryHandler'));
        
        // Merge
        $current = new CurlConfig();
        $override = new CurlConfig(['connectTimeout' => 999]);
        $current->merge($override);
        $this->assertTrue($current->connectTimeout === 999);
        
        // No merge
        $current = new CurlConfig(['connectTimeout' => 123]);
        $override = new CurlConfig();
        $current->merge($override);
        $this->assertTrue($current->connectTimeout === 123);
        
        // No merge
        $current = new CurlConfig(['connectTimeout' => 123]);
        $override = new CurlConfig(['connectTimeout' => 999]);
        $current->merge($override);
        $this->assertTrue($current->connectTimeout === 123);
        
        // No merge
        $default = new CurlConfig();
        $current = new CurlConfig(['connectTimeout' => $default->connectTimeout]);
        $override = new CurlConfig(['connectTimeout' => 999]);
        $current->merge($override);
        $this->assertTrue($current->connectTimeout === $default->connectTimeout);
    }

}