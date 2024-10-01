<?php
declare(strict_types=1);
namespace ASC\types;

interface ICNGnManager {
    public function getBalance(): string;
    public function getTransactionHistory(): string;
    public function swapBetweenChains(array $data): string;
    public function depositForRedemption(array $data): string;
    public function createVirtualAccount(array $data): string;
    public function whitelistAddress(array $data): string;
}