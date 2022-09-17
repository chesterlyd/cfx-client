<?php
namespace Chesterlyd\CfxClient;

class Client
{
    private $config = [];
    private $jwt = '';
    private $url = '';
    public function __construct($config)
    {
        $this->config = $config;
        $this->url = $this->config['url'] . '/' . $this->config['version'] . '/';
    }

    private function getJWT()
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->post($this->url . 'login', [
            'json' => ['app_id' => $this->config['app_id'], 'app_secret' => $this->config['app_secret']]
        ]);

    }
}