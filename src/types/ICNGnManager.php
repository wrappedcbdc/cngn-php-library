<?php
declare(strict_types=1);
namespace WrappedCBDC\types;

use WrappedCBDC\constants\AssetType;

interface ICNGnManager {
    public function getBalance(): string;
    public function getTransactionHistory(): string;
    public function withdraw(array $data): string;
    public function getBanks(): string;
    public function redeenAssets(array $data): string;
    public function createVirtualAccount(array $data): string;
    public function whitelistAddress(array $data): string;
};

