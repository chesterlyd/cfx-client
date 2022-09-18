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
     * @throws \Exception
     */
    private function getJWT(): void
    {
        $url = $this->url . 'login';
        $body = [
            'app_id' => $this->config['app_id'],
            'app_secret' => $this->config['app_secret']
        ];

        $result = $this->http($url, $this->headers, method: 'POST', body: $body);
        if (isset($result['code'])) {
            throw new \Exception(json_encode($result, true));
        }
        $this->expire = $result['expire'];
        $this->jwt = $result['expire'];
        $this->headers['Authorization'] = 'Bearer ' . $result['token'];
    }

    /**
     * 上传文件
     * @return mixed
     * @throws \Exception
     */
    public function uploadFile(): mixed
    {
        $url = $this->url . 'files';
        $body = [
            'file' => $_FILES['file']
        ];

        $result = $this->http($url, ['Content-Type' => 'multipart/form-data', 'Authorization' => "Bearer {$this->jwt}"], method: 'POST', body: $body);
        if (isset($result['code']) && $result['code'] == 40104) {
            $this->getJWT();
            $this->uploadFile();
        }

        return $result;
    }

    /**
     * 上传文件到OSS
     * @return mixed
     * @throws \Exception
     */
    public function UploadFileToOss(): mixed
    {
        $url = $this->url . 'files/oss';
        $body = [
            'file' => $_FILES['file']
        ];

        $result = $this->http($url, ['Content-Type' => 'multipart/form-data', 'Authorization' => "Bearer {$this->jwt}"], method: 'POST', body: $body);
        if (isset($result['code']) && $result['code'] == 40104) {
            $this->getJWT();
            $this->UploadFileToOss();
        }

        return $result;
    }

    /**
     * 获取文件列表
     * @param int $page 页数
     * @param int $limit 每页数量
     * @return mixed
     * @throws \Exception
     */
    public function getFileList(int $page, int $limit = 10): mixed
    {
        $url = $this->url . 'files';
        $headers = $this->headers;
        $params = [
            'page' => $page,
            'limit' => $limit
        ];

        $result = $this->http($url, $headers, method: 'GET', params: $params);
        if (isset($result['code']) && $result['code'] == 40104) {
            $this->getJWT();
            $this->getFileList();
        }

        return $result;
    }

    // todo
    public function createMetadata($name, $image, $description, $external_link = '', $metadataAttribute = [])
    {
        $url = $this->url . 'metadata';
        $body = [
            "attributes" => $metadataAttribute,
            "description" => $description,
            "image" => $image,
            "name" => $name,
            "external_link" => $external_link
        ];

        $result = $this->http($url, $this->headers, method: 'POST', body: $body);
        if (isset($result['code']) && $result['code'] == 40104) {
            $this->getJWT();
            $this->createMetadata($name, $image, $description, $external_link, $metadataAttribute);
        }

        return $result;
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
     * @throws \Exception
     */
    public function getMetadataList(string $nftAddress = '', int $page = 1, int $limit = 10): mixed
    {
        $url = $this->url . 'metadata';
        $params = [
            'nft_address' => $nftAddress,
            'page' => $page,
            'limit' => $limit
        ];

        $result = $this->http($url, $this->headers, method: 'GET', params: $params);
        if (isset($result['code']) && $result['code'] == 40104) {
            $this->getJWT();
            $this->getMetadataList($nftAddress, $page, $limit);
        }

        return $result;
    }

    /**
     * @param string $chain conflux or conflux_test
     * @param string $name 合约名
     * @param string $symbol nft symbol
     * @param string $owner_address 创建者地址
     * @param string $type erc721 erc115
     * @param int $royalties_bps The bps of the royalties
     * @param string $royalties_address The address of the royalties
     * @param bool $tokens_burnable Whether the burning tokens is supported
     * @param bool $tokens_transferable Whether the transferring tokens is supported
     * @param int $transfer_cooldown_time The cooldown time of transfering tokens
     * @param string $base_uri The uri of the NFT
     * @return mixed
     * @throws \Exception
     */
    public function deployContract(string $chain, string $name, string $symbol, string $owner_address, string $type, int $royalties_bps, string $royalties_address, bool $tokens_burnable, bool $tokens_transferable, int $transfer_cooldown_time, string $base_uri = ''): mixed
    {
        $url = $this->url . 'contracts';
        $body = [
            "chain" => $chain,
            "name" => $name,
            "symbol" => $symbol,
            "owner_address" => $owner_address,
            "type" => $type,
            "base_uri" => $base_uri,
            "royalties_bps" => $royalties_bps,
            "royalties_address" => $royalties_address,
            "tokens_burnable" => $tokens_burnable,
            "tokens_transferable" => $tokens_transferable,
            "transfer_cooldown_time" => $transfer_cooldown_time,
        ];

        $result = $this->http($url, $this->headers, 'POST', body: $body);
        if (isset($result['code']) && $result['code'] == 40104) {
            $this->getJWT();
            $this->deployContract($chain, $name, $symbol, $owner_address, $type, $royalties_bps, $royalties_address, $tokens_burnable, $tokens_transferable, $transfer_cooldown_time, $base_uri);
        }

        return $result;
    }

    /**
     * 设置赞助
     * @param string $address 合约地址
     * @return mixed
     * @throws \Exception
     */
    public function setSponsor(string $address): mixed
    {
        $url = $this->url . "contracts/{$address}/sponsor";
        $result = $this->http($url, $this->headers, 'POST');
        if (isset($result['code']) && $result['code'] == 40104) {
            $this->getJWT();
            $this->setSponsor($address);
        }

        return $result;
    }

    /**
     * 获取合约列表
     * @param int $page
     * @param int $limit
     * @return mixed
     * @throws \Exception
     */
    public function getContractList(int $page = 1, int $limit = 10): mixed
    {
        $url = $this->url . "contracts";
        $result = $this->http($url, $this->headers, 'GET');
        if (isset($result['code']) && $result['code'] == 40104) {
            $this->getJWT();
            $this->getContractList($page, $limit);
        }

        return $result;
    }

    /**
     * 获取合约详情
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function getContractDetail($id): mixed
    {
        $url = $this->url . "contracts/detail/{$id}";
        $result = $this->http($url, $this->headers, 'GET');
        if (isset($result['code']) && $result['code'] == 40104) {
            $this->getJWT();
            $this->getContractDetail($id);
        }

        return $result;
    }

    /**
     * 获取合约赞助商
     * @param string $address 合约地址
     * @return mixed
     * @throws \Exception
     */
    public function getContractSponsor(string $address): mixed
    {
        $url = $this->url . "contracts/{$address}/sponsor";
        $result = $this->http($url, $this->headers, 'GET');
        if (isset($result['code']) && $result['code'] == 40104) {
            $this->getJWT();
            $this->getContractSponsor($address);
        }

        return $result;
    }

    /**
     * mint NFT
     * @param string $chain conflux or conflux_test
     * @param string $mint_to_address 接收地址
     * @param string $contract_address 合约地址
     * @param string $contract_type erc721 or erc1155
     * @param string $metadata_uri metadata 链接
     * @param $token_id
     * @return mixed
     * @throws \Exception
     */
    public function mintNft(string $chain, string $mint_to_address, string $contract_address, string $contract_type, string $metadata_uri, $token_id = ''): mixed
    {
        $url = $this->url . "mints";
        $body = [
            "chain" => $chain,
            "token_id" => $token_id,
            "mint_to_address" => $mint_to_address,
            "contract_address" => $contract_address,
            "contract_type" => $contract_type,
            "metadata_uri" => $metadata_uri,
        ];
        $result = $this->http($url, $this->headers, 'POST', body: $body);
        if (isset($result['code']) && $result['code'] == 40104) {
            $this->getJWT();
            $this->mintNft($chain, $mint_to_address, $contract_address, $contract_type, $metadata_uri, $token_id);
        }

        return $result;
    }

    /**
     * 批量铸造NFT
     * @param string $chain conflux or conflux_test
     * @param string $contract_type erc721 or erc1155
     * @param string $contract_address 合约地址
     * @param array $mint_items {
                                    * "mint_to_address": "123",
                                    * "metadata_uri": "123",
                                    * "token_id":"11",   可选
                                    * "amount": 1        可选
                                * },
     * @param string $description 介绍
     * @return mixed
     * @throws \Exception
     */
    public function mintNfts(string $chain, string $contract_type, string $contract_address, array $mint_items, string $description = ''): mixed
    {
        $url = $this->url . "mints/customizable/batch";
        $body = [
            "chain" => $chain,
            "description" => $description,
            "contract_type" => $contract_type,
            "contract_address" => $contract_address,
            "mint_items" => $mint_items,
        ];
        $result = $this->http($url, $this->headers, 'POST', body: $body);
        if (isset($result['code']) && $result['code'] == 40104) {
            $this->getJWT();
            $this->mintNfts($chain, $contract_type, $contract_address, $mint_items, $description);
        }

        return $result;
    }

    /**
     * @param string $name NFT名称
     * @param string $chain conflux or conflux_test
     * @param string $mint_to_address 接收地址
     * @param string $description 介绍
     * @param $file mixed 上传文件
     * @return mixed
     * @throws \Exception
     */
    public function mintNftWithFile(string $name, string $chain, string $mint_to_address, string $description): mixed
    {
        $url = $this->url . "mints/easy/files";
        $body = [
            "name" => $name,
            "chain" => $chain,
            "mint_to_address" => $mint_to_address,
            "description" => $description,
            "file" => $_FILES['file'],
        ];
        $result = $this->http($url, ['Content-Type' => 'multipart/form-data', 'Authorization' => "Bearer {$this->jwt}"], 'POST', body: $body);
        if (isset($result['code']) && $result['code'] == 40104) {
            $this->getJWT();
            $this->mintNftWithFile($name, $chain, $mint_to_address, $description);
        }

        return $result;
    }

    /**
     * @param string $name NFT名称
     * @param string $chain conflux or conflux_test
     * @param string $mint_to_address 接收地址
     * @param string $description 介绍
     * @param string $file_url metadata 文件URL
     * @return mixed
     * @throws \Exception
     */
    public function mintNftWithMetadata(string $name, string $chain, string $mint_to_address, string $description, string $file_url): mixed
    {
        $url = $this->url . "mints/easy/urls";
        $body = [
            "name" => $name,
            "chain" => $chain,
            "mint_to_address" => $mint_to_address,
            "description" => $description,
            "file_url" => $file_url,
        ];
        $result = $this->http($url, $this->headers, 'POST', body: $body);
        if (isset($result['code']) && $result['code'] == 40104) {
            $this->getJWT();
            $this->mintNftWithMetadata($name, $chain, $mint_to_address, $description, $file_url);
        }

        return $result;
    }

    /**
     * 获取NFT列表
     * @param int $page
     * @param int $limit
     * @return mixed
     * @throws \Exception
     */
    public function getNftList(int $page = 1, int $limit = 10): mixed
    {
        $url = $this->url . "mints";
        $params = [
            "page" => $page,
            "limit" => $limit
        ];
        $result = $this->http($url, $this->headers, 'GET', params:$params);
        if (isset($result['code']) && $result['code'] == 40104) {
            $this->getJWT();
            $this->getNftList($page, $limit);
        }

        return $result;
    }

    /**
     * 获取NFT详情
     * @param int $id
     * @return mixed
     * @throws \Exception
     */
    public function getNftDetail(int $id): mixed
    {
        $url = $this->url . "mints/{$id}";
        $result = $this->http($url, $this->headers, 'GET');
        if (isset($result['code']) && $result['code'] == 40104) {
            $this->getJWT();
            $this->getNftDetail($id);
        }

        return $result;
    }

    public function transferNft($chain, $contract_address, $contract_type, $transfer_from_address, $transfer_to_address, $token_id, $amount)
    {
        $url = $this->url . "transfers/customizable";
        $body = [

        ];
        $result = $this->http($url, $this->headers, 'GET');
        if (isset($result['code']) && $result['code'] == 40104) {
            $this->getJWT();
            $this->getNftDetail($id);
        }

        return $result;
    }
}