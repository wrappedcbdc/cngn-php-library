<?php
declare(strict_types=1);
namespace WrappedCBDC\types;

use WrappedCBDC\constants\AssetType;

interface ICNGnManager {
    public function getBalance(): string;
    public function getTransactionHistory(int $page, int $limit): string;
    public function withdraw(array $data): string;
    public function getBanks(): string;
    public function redeemAssets(array $data): string;
    public function createVirtualAccount(array $data): string;
    public function updateExternalAccounts(array $data): string;
    public function swapAssets(array $data): string;
};

