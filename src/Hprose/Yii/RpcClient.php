<?php

namespace Hprose\Yii;

use Hprose\Http\Client;
use yii\base\Component;
use yii\base\ErrorException;
use yii\base\Event;
use yii\httpclient\Client as HttpClient;
use yii\httpclient\CurlTransport;

/**
 * 支持服务中心的rpc客户端
 * User: 李鹏飞 <523260513@qq.com>
 * Date: 2016/7/14
 * Time: 18:26
 */
class RpcClient extends Component
{
    /**
     * @var array
     */
    public $discoverUrls;

    /**
     * @var array
     */
    private $_config;
    /**
     * @var array
     */
    private $_service;

    const EVENT_AFTER_INIT_CLIENT = 'after_init_client';

    /**
     * 获取服务url配置
     * @param $name
     * @return array
     * @throws ErrorException
     */
    protected function getServiceUrls($name)
    {
        if (!$this->_config) {
            $httpClient = new HttpClient();
            $httpClient->transport = CurlTransport::class;
            $requests = [];
            foreach ($this->discoverUrls as $discoverUrl) {
                $requests[] = $httpClient->get($discoverUrl);
            }
            $responses = $httpClient->batchSend($requests);

            $list = [];
            foreach ($responses as $response) {
                foreach ($response->data as $name => $url) {
                    $list[$name][] = $url;
                }
            }

            $this->_config = $list;
        }

        if (isset($this->_config[$name]) && $this->_config[$name]) {
            return $this->_config[$name];
        } else {
            throw new ErrorException('不存在该服务，请检查服务中心');
        }
    }

    /**
     * 查找服务
     * @param $service
     * @param $async
     * @return \Hprose\Client
     * @throws \Exception
     */
    public function getService($service, $async = true)
    {
        if ($async) {
            $group = 'async';
        } else {
            $group = 'sync';
        }

        if (!isset($this->_service[$service][$group])) {
            $obj = new Client($this->getServiceUrls($service), $async);
            $this->trigger(static::EVENT_AFTER_INIT_CLIENT, new Event([
                'data' => &$obj
            ]));
            $this->_service[$service][$group] = $obj;
        }

        return $this->_service[$service][$group];
    }
}