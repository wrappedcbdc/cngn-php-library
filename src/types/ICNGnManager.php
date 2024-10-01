<?php
declare(strict_types=1);
namespace ASC\types;

interface ICNGnManager {
    public function getBalance();
    public function getTransactionHistory();
    public function swapBetweenChains();
    public function depositForRedemption();
    public function createVirtualAccount();
    public function whitelistAddress();
}