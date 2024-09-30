<?php
declare(strict_types=1);
namespace ASC\Types;


interface ICNGnManager {
    public function getBalance(): float;
    public function getTransactionHistory();
    public function swapBetweenChains();
    public function depositForRedemption();
    public function createVirtualAccount();
    public function whitelistAddress();
}