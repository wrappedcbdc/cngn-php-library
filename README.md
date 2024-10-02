# CNGnManager

CNGnManager is a PHP library for interacting with a CNGN API. It provides a simple interface for various operations such as checking balance, swapping between chains, depositing for redemption, creating virtual accounts, and more.

## Table of Contents

- [Installation](#installation)
- [Usage](#usage)
- [Available Methods](#available-methods)
- [Testing](#testing)
- [Error Handling](#error-handling)
- [Types](#types)
- [Security](#security)
- [Contributing](#contributing)
- [Support](#support)
- [License](#license)

## Installation

To install CNGnManager and its dependencies, run:

```bash
composer require wrappedcbdc/cngn-php-library
```

## Usage

First, import the `CNGnManager` class using it namespace ASC\CNGNManager: and all necessary constants.

```php
<?php declare(strict_types=1);
    require __DIR__ ."/vendor/autoload.php";
    use ASC\CNGnManager;
    use ASC\constants\{Network, ProviderType};
```

Then, create an instance of `CNGnManager` with your secrets:

```php
$apiKey = "cngn_live_sk**********";
$encryptionKey = "yourencryptionkey";
$sshPrivateKey = "-----BEGIN OPENSSH PRIVATE KEY-----
your ssh key
-----END OPENSSH PRIVATE KEY-----";

#NOTE: You can as well get your private key from a file using
$sshPrivateKey = file_get_contents("/path/to/sshkey.key");

$manager = new CNGnManager($apiKey, $sshPrivateKey, $encryptionKey);

// Example: Get balance
$balance = $manager->getBalance();
echo $balance;
```

## Available Methods

### Get Balance

```php
$balance = $manager->getBalance();
echo $balance;
```

### Get Transaction History

```php
$transactions = $manager->getTransactionHistory();
echo $transaction
```

### Swap Between Chains

```php
$swapParams = [
    "amount"=> 100,
    "address" => '0x1234...',
    "network" => Network::BSC
];

$swapResult =  $manager->swapBetweenChains($swapParams);
echo $swapResult;
```

### Deposit for Redemption

```php
$depositParams = [
    "amount"=> 1000,
    "bank"=> 'Example Bank',
    "accountNumber"=> '1234567890'
];

$depositResult = $manager->depositForRedemption($depositParams);
echo $depositResult;
```

### Create Virtual Account

```php
$mintParams = [
    "provider"=> ProviderType::KORAPAY
];

$virtualAccount = $manager->createVirtualAccount($mintParams);
echo $virtualAccount;
```

### Whitelist Address

```php
$whitelistParams: [
    "bscAddress" => '0x1234...',
    "bankName" => 'Example Bank',
    "bankAccountNumber" => '1234567890'
];

$whitelistResult = $manager->whitelistAddress(whitelistParams);
echo $whitelistResult;
```

## Testing

This project uses Jest for testing. To run the tests, follow these steps:

1. Run the test command:

   ```bash
   composer run test
   ```

   This will run all tests in the `__tests__` directory.

### Test Structure

The tests are located in the `__tests__` directory. They cover various aspects of the CNGnManager class, including:

- API calls for different endpoints (GET and POST requests)
- Encryption and decryption of data
- Error handling for various scenarios

## Return Values

All responses are returned as a Json string you have to decode it to an object with; `$data = json_decode($response)` or to an array with; `$data = json_decode($response, true)` .

## Error Handling

The library uses a custom error handling mechanism. All API errors are caught and thrown as `Error` objects with descriptive messages.

## Types

The library includes php definitions for all parameters and return types. Please refer to the type definitions in the source code for more details.

## Security

This library uses AES encryption for request payloads and Ed25519 decryption for response data. Ensure that your `encryptionKey` and `privateKey` are kept secure.

## Contributing

Contributions, issues, and feature requests are welcome. Feel free to check [issues page](https://github.com/wrappedcbdc/cngn-php-library/issues) if you want to contribute.

## Support

If you have any questions or need help using the library, please open an issue in the GitHub repository.

## License

[MIT](https://choosealicense.com/licenses/mit/)