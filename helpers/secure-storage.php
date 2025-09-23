<?php
if (!defined('ABSPATH')) exit;

class Secure_Storage {

    private $key;

    public function __construct() {
        if (!defined('SECURE_STORAGE_KEY')) {
            throw new Exception("La constante SECURE_STORAGE_KEY n'est pas définie !");
        }
        $this->key = SECURE_STORAGE_KEY;
    }

    // Chiffrement
    public function encrypt($data) {
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = sodium_crypto_secretbox($data, $nonce, $this->key);
        return base64_encode($nonce . $ciphertext);
    }

    // Déchiffrement
    public function decrypt($encrypted) {
        if (empty($encrypted)) {
            return '';
        }
        $decoded = base64_decode($encrypted);
        $nonce = substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $plain = sodium_crypto_secretbox_open($ciphertext, $nonce, $this->key);
        if ($plain === false) {
            throw new Exception("Données corrompues ou clé invalide !");
        }
        return $plain;
    }
}