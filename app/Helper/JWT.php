<?php

namespace App\Helper;

use Firebase\JWT\JWT as JwtLib;
use Firebase\JWT\Key;

class JWT
{
    private $_public_key;
    private $_private_key;
    private $_algo;

    public function __construct($config = null)
    {
        if ($config) {
            $this->_public_key = $config['public'] ?? null;
            $this->_private_key = $config['private'] ?? null;
            $this->_algo = $config['algo'] ?? null;
        } else {
            $this->_public_key = config('secret.public');
            $this->_private_key = config('secret.private');
            $this->_algo = config('secret.algo');
        }
    }

    public function encode($payload, $salt = true)
    {
        $result = new Result();
        $key = $this->_private_key;

        if ($key && $this->_algo) {
            try {
                if ($salt) {
                    $payload->salt = bin2hex(random_bytes(32));
                }
                $res = JwtLib::encode($payload, $key, $this->_algo);
                $result->data = $res;
            } catch (\Exception $e) {
                $result->code = 2;
                $result->info = $e->getMessage();
            }
        } else {
            $result->code = 1;
            $result->info = 'service_unavailable';
        }

        return $result;
    }

    public function decode($jwt, $private = false)
    {
        $result = new Result();
        $key = $this->_public_key;

        if ($private) {
            $key = $this->_private_key;
        }

        if ($key && $this->_algo) {
            try {
                $res = JwtLib::decode($jwt, new Key($key, $this->_algo));
                $result->data = $res;
            } catch (\Exception $e) {
                $result->code = 2;
                $result->info = $e->getMessage();
            }
        } else {
            $result->code = 1;
            $result->info = 'service_unavailable';
        }

        return $result;
    }

    public static function initialize($config)
    {
        return new JWT($config);
    }

    public function getKey($payload)
    {
        return $this->encode($payload);
    }

    public function authorize($header)
    {
        $result = new Result();

        if ($header) {
            $bearer = substr(strstr($header, ' '), 1);
            $result = $this->decode($bearer);
            if ($result->code != $result::STATUS_SUCCESS) {
                $result->status = 403;
            }
        } else {
            $result->code = 2;
            $result->info = 'Authorization header is not provided.';
            $result->status = 403;
        }

        return $result;
    }
}
