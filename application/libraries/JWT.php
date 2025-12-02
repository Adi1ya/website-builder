<?php
require 'vendor/autoload.php';

use Firebase\JWT\JWT as FirebaseJWT;
use Firebase\JWT\Key;

class JWT {

    private $key = JWT_SECRET_KEY; // Make sure this is secure and stored properly

    public function encode($data) {
        $issuedAt = time();
        $expirationTime = $issuedAt + 60; // valid for 1 hour
        $payload = [
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'data' => $data
        ];
        return FirebaseJWT::encode($payload, $this->key, 'HS256');
    }

    public function decode($jwt) {
        try {
            return FirebaseJWT::decode($jwt, new Key($this->key, 'HS256'));
        } catch (Exception $e) {
            return null;
        }
    }


    public function verifyToken() {
        $headers = apache_request_headers();
        $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

        if (strpos($authHeader, 'Bearer ') === 0) {
            $jwt = trim(str_replace('Bearer', '', $authHeader));
            $decoded = $this->decode($jwt);

            if (!$decoded) {
                $this->unauthorized();
            } else {
                return $decoded;
            }
        } else {
            $this->unauthorized();
        }
    }

    private function unauthorized() {
        http_response_code(401);
        echo json_encode(['message' => 'Access denied']);
        exit;
    }
}

