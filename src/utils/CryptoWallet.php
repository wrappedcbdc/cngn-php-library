<?php

namespace WrappedCBDC\utils;

use Exception;
use kornrunner\Keccak;
use BitWasp\Bitcoin\Mnemonic\Bip39\Bip39SeedGenerator;
use BitWasp\Bitcoin\Mnemonic\MnemonicFactory;
use BitWasp\Bitcoin\Key\Factory\HierarchicalKeyFactory;
use BitWasp\Bitcoin\Network\NetworkFactory;
use BitWasp\Buffertools\Buffer;
use kornrunner\Secp256k1;
use ParagonIE\Sodium\Core\Ed25519;

class CryptoWallet
{
    private const DERIVATION_PATHS = [
        'ETH' => "m/44'/60'/0'/0/0",
        'BSC' => "m/44'/60'/0'/0/0",
        'ATC' => "m/44'/60'/0'/0/0",
        'MATIC' => "m/44'/60'/0'/0/0",
        'TRX' => "m/44'/195'/0'/0/0",
        'XBN' => "m/44'/703'/0'/0"
    ];

    // TRON address constants
    private const TRON_ADDRESS_PREFIX = '41';
    private const TRON_ADDRESS_SIZE = 21;

    public static function generateWalletWithMnemonicDetails(string $network): array
    {
        $bip39 = MnemonicFactory::bip39();
        $mnemonic = $bip39->create(128);
        return self::generateWalletFromMnemonic($mnemonic->getWords(), $network);
    }

    public static function generateWalletFromMnemonic(string $mnemonic, string $network): array
    {
        if ($network === 'XBN') {
            return self::generateXbnWallet($mnemonic);
        } elseif ($network === 'TRX') {
            return self::generateTrxWallet($mnemonic);
        }

        $privateKey = self::getPrivateKeyFromMnemonic($mnemonic, $network);
        $publicKey = self::getPublicKey($privateKey, $network);
        $address = self::getAddressFromPublicKey($publicKey, $network);

        return [
            'mnemonic' => $mnemonic,
            'privateKey' => $privateKey,
            'address' => $address,
            'network' => $network
        ];
    }

    public static function getPublicKey(string $privateKey, string $network): string
    {
        if ($network === 'XBN') {
            $secretKey = hex2bin($privateKey);
            $keypair = sodium_crypto_sign_seed_keypair($secretKey);
            return bin2hex(sodium_crypto_sign_publickey($keypair));
        }

        $secp256k1 = new Secp256k1();
        $publicKey = $secp256k1->publicKey($privateKey, true);
        return $publicKey;
    }

    public static function getPrivateKeyFromMnemonic(string $mnemonic, string $network): string
    {
        $bip39 = MnemonicFactory::bip39();
        $seedGenerator = new Bip39SeedGenerator();
        $seed = $seedGenerator->getSeed($mnemonic);

        $factory = new HierarchicalKeyFactory();
        $master = $factory->fromEntropy($seed);

        $path = self::DERIVATION_PATHS[$network];
        $key = $master;

        foreach (explode('/', substr($path, 1)) as $segment) {
            if (substr($segment, -1) === "'") {
                $hardened = true;
                $segment = substr($segment, 0, -1);
            } else {
                $hardened = false;
            }
            
            $index = (int)$segment;
            if ($hardened) {
                $index += 0x80000000;
            }
            
            $key = $key->deriveChild($index);
        }

        return $key->getPrivateKey()->getHex();
    }

    public static function getAddressFromPublicKey(string $publicKey, string $network): string
    {
        if (in_array($network, ['ETH', 'BSC', 'MATIC', 'ATC'])) {
            return self::getEthereumStyleAddress($publicKey);
        } elseif ($network === 'TRX') {
            return self::getTronAddressFromPublicKey($publicKey);
        }

        throw new Exception("Unsupported network: {$network}");
    }

    public static function getEthereumStyleAddress(string $publicKey): string
    {
        $cleanPublicKey = substr($publicKey, 0, 2) === '04' ? substr($publicKey, 2) : $publicKey;
        $hash = Keccak::hash(hex2bin($cleanPublicKey), 256);
        return '0x' . substr($hash, -40);
    }

    public static function generateTrxWallet(string $mnemonic): array
    {
        $privateKey = self::getPrivateKeyFromMnemonic($mnemonic, 'TRX');
        $publicKey = self::getPublicKey($privateKey, 'TRX');
        $address = self::getTronAddressFromPublicKey($publicKey);

        return [
            'mnemonic' => $mnemonic,
            'privateKey' => $privateKey,
            'address' => $address,
            'network' => 'TRX'
        ];
    }

    public static function getTronAddressFromPublicKey(string $publicKey): string
    {
        // Remove '04' prefix if present
        $pubKeyBinary = hex2bin(substr($publicKey, 0, 2) === '04' ? substr($publicKey, 2) : $publicKey);
        
        // Get Keccak-256 hash
        $hash = Keccak::hash($pubKeyBinary, 256);
        
        // Take last 20 bytes
        $hash = substr($hash, -40);
        
        // Add TRON prefix
        $addressHex = self::TRON_ADDRESS_PREFIX . $hash;
        
        // Calculate checksum (double SHA256)
        $addressBin = hex2bin($addressHex);
        $hash1 = hash('sha256', $addressBin, true);
        $hash2 = hash('sha256', $hash1, true);
        $checksum = substr($hash2, 0, 4);
        
        // Combine address and checksum
        $binaryAddress = $addressBin . $checksum;
        
        // Convert to Base58
        return self::base58Encode($binaryAddress);
    }

    public static function generateXbnWallet(string $mnemonic): array
    {
        $seed = (new Bip39SeedGenerator())->getSeed($mnemonic);
        $factory = new HierarchicalKeyFactory();
        $master = $factory->fromEntropy($seed);

        $path = self::DERIVATION_PATHS['XBN'];
        $key = $master;

        foreach (explode('/', substr($path, 1)) as $segment) {
            if (substr($segment, -1) === "'") {
                $index = (int)substr($segment, 0, -1) + 0x80000000;
            } else {
                $index = (int)$segment;
            }
            $key = $key->deriveChild($index);
        }

        $privateKey = $key->getPrivateKey()->getHex();
        
        // Generate Ed25519 keypair
        $secretKey = hex2bin($privateKey);
        $keypair = sodium_crypto_sign_seed_keypair($secretKey);
        $publicKey = sodium_crypto_sign_publickey($keypair);
        
        // Generate XBN address (using stellar-style encoding)
        $address = self::encodeXbnAddress($publicKey);

        return [
            'mnemonic' => $mnemonic,
            'privateKey' => $privateKey,
            'address' => $address,
            'network' => 'XBN'
        ];
    }

    private static function encodeXbnAddress(string $publicKey): string
    {
        // XBN uses a similar format to Stellar
        $version = chr(48); // 'G' version byte
        $payload = $version . $publicKey;
        
        // Calculate checksum (CRC16-XModem)
        $crc = self::calculateCrc16Xmodem($payload);
        $binary = $payload . pack('n', $crc);
        
        // Base32 encode the result
        return self::base32Encode($binary);
    }

    private static function calculateCrc16Xmodem(string $buffer): int
    {
        $crc = 0x0000;
        $polynomial = 0x1021;

        foreach (str_split($buffer) as $byte) {
            $crc ^= (ord($byte) << 8);
            for ($i = 0; $i < 8; $i++) {
                if ($crc & 0x8000) {
                    $crc = (($crc << 1) & 0xFFFF) ^ $polynomial;
                } else {
                    $crc = ($crc << 1) & 0xFFFF;
                }
            }
        }

        return $crc;
    }

    private static function base32Encode(string $data): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $binary = '';
        $binaryLength = 0;
        $result = '';

        foreach (str_split($data) as $char) {
            $binary .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
            $binaryLength += 8;

            while ($binaryLength >= 5) {
                $chunk = substr($binary, 0, 5);
                $binary = substr($binary, 5);
                $binaryLength -= 5;
                $result .= $alphabet[bindec($chunk)];
            }
        }

        if ($binaryLength > 0) {
            $chunk = str_pad($binary, 5, '0', STR_PAD_RIGHT);
            $result .= $alphabet[bindec($chunk)];
        }

        return $result;
    }

    private static function base58Encode(string $data): string
    {
        $alphabet = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
        $base = strlen($alphabet);

        // Convert binary data to decimal
        $decimal = 0;
        $length = strlen($data);
        for ($i = 0; $i < $length; $i++) {
            $decimal = $decimal * 256 + ord($data[$i]);
        }

        // Convert decimal to base58
        $result = '';
        while ($decimal >= $base) {
            $div = intdiv($decimal, $base);
            $mod = $decimal % $base;
            $result = $alphabet[$mod] . $result;
            $decimal = $div;
        }
        $result = $alphabet[$decimal] . $result;

        // Add leading zeros
        for ($i = 0; $i < $length && $data[$i] === "\x00"; $i++) {
            $result = $alphabet[0] . $result;
        }

        return $result;
    }

    public static function validateAddress(string $address, string $network): bool
    {
        switch ($network) {
            case 'ETH':
            case 'BSC':
            case 'MATIC':
            case 'ATC':
                return preg_match('/^0x[a-fA-F0-9]{40}$/', $address) === 1;

            case 'TRX':
                if (strlen($address) !== 34) return false;
                try {
                    $decoded = self::base58Decode($address);
                    if (strlen($decoded) !== 25) return false;
                    
                    // Check prefix
                    if (substr($decoded, 0, 1) !== hex2bin(self::TRON_ADDRESS_PREFIX)) return false;
                    
                    // Verify checksum
                    $body = substr($decoded, 0, -4);
                    $checksum = substr($decoded, -4);
                    $hash1 = hash('sha256', $body, true);
                    $hash2 = hash('sha256', $hash1, true);
                    return substr($hash2, 0, 4) === $checksum;
                } catch (Exception $e) {
                    return false;
                }

            case 'XBN':
                if (strlen($address) !== 56) return false;
                try {
                    $binary = self::base32Decode($address);
                    if (strlen($binary) !== 37) return false;
                    
                    $payload = substr($binary, 0, -2);
                    $checksum = unpack('n', substr($binary, -2))[1];
                    
                    return self::calculateCrc16Xmodem($payload) === $checksum;
                } catch (Exception $e) {
                    return false;
                }

            default:
                throw new Exception("Unsupported network: {$network}");
        }
    }

    private static function base58Decode(string $data): string
    {
        $alphabet = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
        $base = strlen($alphabet);

        // Convert base58 to decimal
        $decimal = 0;
        $length = strlen($data);
        for ($i = 0; $i < $length; $i++) {
            $pos = strpos($alphabet, $data[$i]);
            if ($pos === false) {
                throw new Exception('Invalid character found');
            }
            $decimal = $decimal * $base + $pos;
        }

        // Convert decimal to binary
        $result = '';
        while ($decimal > 0) {
            $div = intdiv($decimal, 256);
            $mod = $decimal % 256;
            $result = chr($mod) . $result;
            $decimal = $div;
        }

        // Add leading zeros
        for ($i = 0; $i < $length && $data[$i] === $alphabet[0]; $i++) {
            $result = "\x00" . $result;
        }

        return $result;
    }

    private static function base32Decode(string $data): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $binary = '';
        
        foreach (str_split($data) as $char) {
            $pos = strpos($alphabet, $char);
            if ($pos === false) {
                throw new Exception('Invalid character found');
            }
            $binary .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }

        $result = '';
        $chunks = str_split($binary, 8);
        foreach ($chunks as $chunk) {
            if (strlen($chunk) < 8) {
                break;
            }
            $result .= chr(bindec($chunk));
        }

        return $result;
    }
}
?>