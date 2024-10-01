<?php
namespace ASC\utils;


class AESCrypto
{
    private const ALGORITHM = 'aes-256-cbc';
    private const IV_LENGTH = 16;
    private const KEY_LENGTH = 32; // 256 bits

    private static function prepareKey(string $key): string
    {
        // Hash the key to ensure it's always the correct length
        return hash('sha256', $key, true); // returns raw binary output
    }

    public static function encrypt(string $data, string $key): array
    {
        // Generate a random Initialization Vector (IV)
        $iv = openssl_random_pseudo_bytes(self::IV_LENGTH);
        $keyBuffer = self::prepareKey($key);

        // Encrypt the data using AES-256-CBC
        $encrypted = openssl_encrypt($data, self::ALGORITHM, $keyBuffer, OPENSSL_RAW_DATA, $iv);

        // Return the encrypted content and the IV (both base64 encoded)
        return [
            'content' => base64_encode($encrypted),
            'iv' => base64_encode($iv)
        ];
    }

    public static function decrypt(array $encryptedData, string $key): string
    {
        // Decode the base64 encoded IV and content
        $iv = base64_decode($encryptedData['iv']);
        $encryptedContent = base64_decode($encryptedData['content']);
        $keyBuffer = self::prepareKey($key);

        // Decrypt the data
        $decrypted = openssl_decrypt($encryptedContent, self::ALGORITHM, $keyBuffer, OPENSSL_RAW_DATA, $iv);

        return $decrypted;
    }
}


