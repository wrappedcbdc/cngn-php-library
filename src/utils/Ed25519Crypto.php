<?php
namespace ASC\utils;

use Exception;


/**
 * Class Ed25519Crypto
 * 
 * This class provides functionality for decrypting data using Ed25519 private keys.
 * It utilizes the Sodium extension for cryptographic operations.
 */

class Ed25519Crypto
{
    private static $isInitialized = false;

    private static function initialize()
    {
        if (!self::$isInitialized) {
            if (!extension_loaded('sodium')) {
                throw new Exception("The sodium extension is not loaded");
            }
            self::$isInitialized = true;
        }
    }

    private static function parseOpenSSHPrivateKey(string $privateKey): string
    {
        $lines = explode("\n", $privateKey);
        $base64PrivateKey = implode('', array_slice($lines, 1, -1));
        $privateKeyBuffer = base64_decode($base64PrivateKey);

        // Look for the Ed25519 key data
        $keyDataStart = strpos($privateKeyBuffer, pack('C*', 0x00, 0x00, 0x00, 0x40));
        if ($keyDataStart === false) {
            throw new Exception('Unable to find Ed25519 key data');
        }

        // Extract the key data
        return substr($privateKeyBuffer, $keyDataStart + 4, 64);
    }

    public static function decryptWithPrivateKey(string $ed25519PrivateKey, string $encryptedData): string
    {
        self::initialize();

        try {
            $fullPrivateKey = self::parseOpenSSHPrivateKey($ed25519PrivateKey);
            // Convert Ed25519 private key to Curve25519 private key
            $curve25519PrivateKey = sodium_crypto_sign_ed25519_sk_to_curve25519($fullPrivateKey);
            $encryptedBuffer = base64_decode($encryptedData);

            // Extract nonce, ephemeral public key, and ciphertext
            $nonce = substr($encryptedBuffer, 0, SODIUM_CRYPTO_BOX_NONCEBYTES);
            $ephemeralPublicKey = substr($encryptedBuffer, -SODIUM_CRYPTO_BOX_PUBLICKEYBYTES);
            $ciphertext = substr($encryptedBuffer, SODIUM_CRYPTO_BOX_NONCEBYTES, -SODIUM_CRYPTO_BOX_PUBLICKEYBYTES);

            $keyPair = sodium_crypto_box_keypair_from_secretkey_and_publickey($curve25519PrivateKey, $ephemeralPublicKey);

            // Decrypt the ciphertext
            $decrypted = sodium_crypto_box_open($ciphertext, $nonce, $keyPair);

            if ($decrypted === false) {
                throw new Exception('Decryption failed');
            }

            return $decrypted;
        } catch (Exception $e) {
            throw new Exception("Failed to decrypt with the provided Ed25519 private key: " . $e->getMessage());
        }
    }
}
