<?php

namespace App\Services;

use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Encryption\Algorithm\ContentEncryption\A128CBCHS256;
use Jose\Component\Encryption\Algorithm\KeyEncryption\Dir;
use Jose\Component\Encryption\Compression\CompressionMethodManager;
use Jose\Component\Encryption\Compression\Deflate;
use Jose\Component\Encryption\JWEBuilder;
use Jose\Component\Core\JWK;
use Jose\Component\Encryption\JWEDecrypter;
use Jose\Component\Encryption\Serializer\CompactSerializer;
use Jose\Component\Encryption\Serializer\JWESerializerManager;


class EncryptionService
{
    private $keySet;
    private $jwk;
    private $keyEncryptionAlgorithmManager;
    private $contentEncryptionAlgorithmManager;
    private $compressionMethodManager;

    public function __construct()
    {
        //get the key
        $this->keySet= $this->getKey();
        // Our key.
        $this->jwk = new JWK([
            'kty' => $this->keySet->get('kty'),
            'kid' => $this->keySet->get('kid'),
            'use' => $this->keySet->get('use'),
            'alg' => $this->keySet->get('alg'),
            'k'   => $this->keySet->get('k'),
        ]);

        // The key encryption algorithm manager with the A256KW algorithm.
        $this->keyEncryptionAlgorithmManager = new AlgorithmManager([
            new Dir(),
        ]);

        // The content encryption algorithm manager with the A256CBC-HS256 algorithm.
        $this->contentEncryptionAlgorithmManager = new AlgorithmManager([
            new A128CBCHS256(),
        ]);

        // The compression method manager with the DEF (Deflate) method.
        $this->compressionMethodManager = new CompressionMethodManager([
            new Deflate(),
        ]);

    }

    public function getEncryptedData(array $payload) : string
    {
        //dd($this->jwk);
//        //get the key
//        $keySet = $this->getKey();
//        // Our key.
//        $jwk = new JWK([
//            'kty' => $keySet->get('kty'),
//            'kid' => $keySet->get('kid'),
//            'use' => $keySet->get('use'),
//            'alg' => $keySet->get('alg'),
//            'k'   => $keySet->get('k'),
//        ]);
        // We instantiate our JWE Builder.
        $jweBuilder = new JWEBuilder($this->keyEncryptionAlgorithmManager, $this->contentEncryptionAlgorithmManager, $this->compressionMethodManager);

        $jwe = $jweBuilder->create()              // We want to create a new JWE
                          ->withPayload(json_encode($payload)) // We set the payload
                          ->withSharedProtectedHeader([
            'alg' => 'dir',        // Key Encryption Algorithm
            'enc' => $this->keySet->get('alg'),          // Content Encryption Algorithm
            'zip' => 'DEF',         // We enable the compression (irrelevant as the payload is small, just for the example).
            'kid' => $this->keySet->get('kid'),
        ])
                          ->addRecipient($this->jwk)    // We add a recipient (a shared key or public key).
                          ->build();              // We build it

        $serializer = new CompactSerializer();         // The serializer
        return $serializer->serialize($jwe, 0); // We serialize the recipient at index 0 (we only have one recipient).
    }

    public function getDecryptedData(string $payload):array
    {
//        //get the key
//        $keySet = $this->getKey();
//        // Our key.
//        $jwk = new JWK([
//            'kty' => $keySet->get('kty'),
//            'kid' => $keySet->get('kid'),
//            'use' => $keySet->get('use'),
//            'alg' => $keySet->get('alg'),
//            'k'   => $keySet->get('k'),
//        ]);
        // We instantiate our JWE Decrypter.
        $jweDecrypter = new JWEDecrypter($this->keyEncryptionAlgorithmManager, $this->contentEncryptionAlgorithmManager, $this->compressionMethodManager);

        // The serializer manager. We only use the JWE Compact Serialization Mode.
        $serializerManager = new JWESerializerManager([
            new CompactSerializer(),
        ]);

        // We try to load the token.
        $jwe = $serializerManager->unserialize($payload);

        // We decrypt the token. This method does NOT check the header.
        $success = $jweDecrypter->decryptUsingKey($jwe, $this->jwk, 0);
        if ($success) {
            return json_decode($jwe->getPayload(), true);
        }else{
            return ['status'=>false,'msg'=>'Decryption Failed'];
        }
    }

    private function getKey()
    {
        //key id
        $keyId = config('encryption.'.env('APP_ENV'));
        if (!$keyId) {
            return collect(['status' => false, 'message' => 'No kid found.']);
        }
        $key = collect(config('keystore.keys'))->first(function ($value) use ($keyId) {
            return $value['kid'] === $keyId;
        });

        if (!$key) {
            return collect(['status' => false, 'message' => 'No key found.']);
        }
        return collect($key);
    }

}
