<?php
declare(strict_types=1);
namespace WrappedCBDC\types;

use WrappedCBDC\constants\AssetType;
use WrappedCBDC\constants\Network;

interface IWalletManager{
    public function generateWallet(Network $network);
}