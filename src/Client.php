<?php

namespace Chesterlyd\CfxClient;

class Client
{
    private array $config = [];
    private string $url = '';
    private array $headers = ["Content-type" => 'application/json'];
    private int $expire = 0;
    private string $jwt = '';

    public function __construct($config)
    {
        $this->config = $config;
        $this->url = $this->config['url'] . '/' . $this->config['version'] . '/';
        $this->getJWT();
    }

    public function http($url, $headers = [], $method = '', $body = [], $params = [])
    {
        $client = new \GuzzleHttp\Client(['headers' => $headers]);
        if (strtolower($method) == 'get') {
            $response = $client->request($method, $url, [
                'query' => $params
            ]);
        } else {
            $response = $client->request($method, $url, [
                'form_params' => $body
            ]);
        }

        $body = $response->getBody(); //获取响应体，对象
        return json_decode($body->getContents(), true);
    }

    /**
     * 获取jwt
     * @return void
     */
    private function getJWT(): void
    {
        $url = $this->url . 'login';
        $body = [
            'app_id' => $this->config['app_id'],
            'app_secret' => $this->config['app_secret']
        ];

        $result = $this->http($url, $this->headers, method: 'POST', body: $body);
        $this->expire = $result['expire'];
        $this->jwt = $result['expire'];
        $this->headers['Authorization'] = 'Bearer ' . $result['token'];
    }

    /**
     * 上传文件
     * @return mixed
     */
    public function uploadFile(): mixed
    {
        $url = $this->url . 'files';
        $body = [
            'file' => $_FILES['file']
        ];

        return $this->http($url, ['Content-Type' => 'multipart/form-data', 'Authorization' => "Bearer {$this->jwt}"], method: 'POST', body: $body);
    }

    /**
     * 上传文件到OSS
     * @return mixed
     */
    public function UploadFileToOss(): mixed
    {
        $url = $this->url . 'files/oss';
        $body = [
            'file' => $_FILES['file']
        ];

        return $this->http($url, ['Content-Type' => 'multipart/form-data', 'Authorization' => "Bearer {$this->jwt}"], method: 'POST', body: $body);
    }

    /**
     * 获取文件列表
     * @param int $page 页数
     * @param int $limit 每页数量
     * @return mixed
     */
    public function getFileList(int $page, int $limit = 10): mixed
    {
        $url = $this->url . 'files';
        $headers = $this->headers;
        $params = [
            'page' => $page,
            'limit' => $limit
        ];

        return $this->http($url, $headers, method: 'GET', params: $params);
    }

    // todo
    public function createMetadata($name, $image, $description, $external_link = '', $metadataAttribute = [])
    {
        $url = $this->url . 'metadata';
        $body = [
            "attributes" => $metadataAttribute,
            "description" => $description,
            "image" => $image,
            "name" => $name
        ];

        return $this->http($url, $this->headers, method: 'POST', body: $body);
    }

    // todo
    public function getMetadata()
    {
        
    }

    /**
     * 获取metadata列表
     * @param string $nftAddress 合约地址
     * @param int $page 页数
     * @param int $limit 每页数量
     * @return mixed
     */
    public function getMetadataList(string $nftAddress = '', int $page = 1, int $limit = 10): mixed
    {
        $url = $this->url . 'metadata';
        $params = [
            'nft_address' => $nftAddress,
            'page' => $page,
            'limit' => $limit
        ];

        return $this->http($url, $this->headers, method: 'GET', params: $params);
    }
}