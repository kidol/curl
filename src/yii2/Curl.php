<?php

namespace kidol\curl\yii2;

use kidol\curl\CurlConfig;
use kidol\curl\CurlRequest;

use yii\base\InvalidConfigException;

class Curl extends \yii\base\Component
{
    
    public $configs = ['default' => []];
    
    public function init()
    {
        parent::init();
        
        if (!empty($this->configs)) {
            if (!is_array($this->configs)) {
                throw new InvalidConfigException("Curl::\$configs must be an array.");
            }
            try {
                foreach ($this->configs as $name => $config) {
                    $this->configs[$name] = new CurlConfig($config);
                }
            } catch (\Exception $e) {
                throw new InvalidConfigException("Config \"{$name}\" is invalid. {$e->getMessage()}", 0, $e);
            }
        }
    }
    
    /**
     * @return CurlRequest
     */
    public function getRequest($config = 'default')
    {
        if (!isset($this->configs[$config])) {
            throw new InvalidConfigException("Config \"{$config}\" is not defined.");
        } elseif ($config !== 'default' && isset($this->configs['default'])) {
            $config = clone $this->configs[$config];
            return new CurlRequest($config->merge($this->configs['default']));
        } else {
            $config = clone $this->configs[$config];
            return new CurlRequest($config);
        }
    }
    
}