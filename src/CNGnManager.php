<?php
declare(strict_types=1);
namespace ASC;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . "/utils/AESCrypto.php";
require __DIR__ . "/utils/Ed25519Crypto.php";

use GuzzleHttp\Psr7;
use GuzzleHttp\Client;
use ASC\utils\AESCrypto;
use GuzzleHttp\Exception\{
    ClientException,
    RequestException
};
use ASC\types\ICNGnManager;
use ASC\utils\Ed25519Crypto;


class CNGnManager implements ICNGnManager {
    
    protected $client;
    protected $API_CURRENT_VERSION = 'v1';

    public function __construct(private string $apiKey, private string $privateKey, private string $encryptionKey ){

       $this->client = new Client([
            'base_uri' => "https://staging.api.wrapcbdc.com",
            'headers' => [
                'Authorization' => "Bearer $this->apiKey",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]
       ]);


    }

    private function handleApiError($error){
        // handle error
    }

    private function __makeCalls(string $method, string $endpoint, array $data=[]): string{
        // make calls
        $AESCrypto = new AESCrypto();
        $Ed25519Crypto = new Ed25519Crypto();

        try {

            if(($data)){
                $newdata = json_encode($data);
                $encrypt = $AESCrypto->encrypt($newdata, $this->encryptionKey);
                $data = $encrypt;
            }

            $request = $this->client->request($method, $endpoint, [
                'json' => $data
            ]);

            $response = json_decode($request->getBody()->getContents(), true);
            $decryptedResponse = $Ed25519Crypto->decryptWithPrivateKey($this->privateKey, $response["data"]);

            $response["data"] = json_decode($decryptedResponse, true);
            return json_encode($response);
        }catch (ClientException $e) {
            $error =  Psr7\Message::toString($e->getResponse());
            throw $e;
        }
    }

    public function getBalance(): string{
        return $this->__makeCalls("GET", "/$this->API_CURRENT_VERSION/api/balance");
    }

    public function getTransactionHistory(): string{
        return $this->__makeCalls("GET", "/$this->API_CURRENT_VERSION/api/transactions");
    }

    public function swapBetweenChains(array $data): string{
        return $this->__makeCalls("POST", "/$this->API_CURRENT_VERSION/api/swap");
    }

    public function depositForRedemption(array $data): string {
        return $this->__makeCalls("POST", "/$this->API_CURRENT_VERSION/api/deposit");
    }

    public function createVirtualAccount(array $data): string{
        return $this->__makeCalls("POST", "/$this->API_CURRENT_VERSION/api/createVirtualAccount");
    }

    public function whitelistAddress(array $data): string{
        return $this->__makeCalls("POST", "/$this->API_CURRENT_VERSION/api/whiteListAddress");
    }



}

