<?php

use WrappedCBDC\types\IWalletManager;
use WrappedCBDC\utils\CryptoWallet;

class WalletManager implements IWalletManager{


    public function generateWallet($network){
        $wallet = new CryptoWallet;
        
        return $wallet->generateWalletWithMnemonicDetails($network);
    }
}