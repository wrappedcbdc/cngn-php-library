<?php
declare(strict_types=1);
namespace ASC;

use ASC\Types\ICNGnManager;


require __DIR__ . '/../vendor/autoload.php';

class CNGnmanager implements ICNGnManager {
    

    public function __construct(private string $apiKey, private string $privateKey, private string $encryptionKey ){
        
    }

    private function handleApiError($error){
        // handle error
    }

    private function makeCalls($method, $endpoint, $data = null){
        // make calls
        try {
            //code...
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function getBalance(): float{
        // get balance
        throw new \Exception('Not implemented');
    }

    public function getTransactionHistory(){
        throw new \Exception('Not implemented');
    }

    public function swapBetweenChains(){
        throw new \Exception('Not implemented');
    }

    public function depositForRedemption(){
        throw new \Exception('Not implemented');
    }

    public function createVirtualAccount(){
        throw new \Exception('Not implemented');
    }

    public function whitelistAddress(){
        throw new \Exception('Not implemented');
    }



}

