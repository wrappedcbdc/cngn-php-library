<?php

use ASC\CNGnManager;

require __DIR__ ."/../vendor/autoload.php";

$manager = new CNGnManager("cngn_live_5Kf30QtGX7EvpCAC3SZpiYHRT1XuAqaLv2Dq6JSsp65kpeDo9iD", "-----BEGIN OPENSSH PRIVATE KEY-----
b3BlbnNzaC1rZXktdjEAAAAABG5vbmUAAAAEbm9uZQAAAAAAAAABAAAAMwAAAAtzc2gtZW
QyNTUxOQAAACDWLpnnTWPHNnQEPsrkSqMncsNz8rS/RJuPnl+nWx5WUgAAAJg9WOTlPVjk
5QAAAAtzc2gtZWQyNTUxOQAAACDWLpnnTWPHNnQEPsrkSqMncsNz8rS/RJuPnl+nWx5WUg
AAAEDxz5HK5ah+9MUrwW2OxF7LRqoKG2gNEcHt5Tc1pRPR5NYumedNY8c2dAQ+yuRKoydy
w3PytL9Em4+eX6dbHlZSAAAADnRlc3RAZ21haWwuY29tAQIDBAUGBw==
-----END OPENSSH PRIVATE KEY-----", "71X8SjHQodEayz5dMYvWpgHuhPGGubPAAcMnL1J2nIwk9CrBSRYCrlaRvSJKMP");

print_r($manager->getBalance());
print_r($manager->getTransactionHistory());