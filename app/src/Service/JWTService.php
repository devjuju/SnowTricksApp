<?php

namespace App\Service;

use DateTimeImmutable;

class JWTService
{
    public function generate(array $header, array $payload, string $secret, int $validity = 10800): string
    {
        if ($validity > 0) {
            $now = new DateTimeImmutable();
            $exp = $now->getTimestamp() + $validity;
            $payload['iat'] = $now->getTimestamp();
            $payload['exp'] = $exp;
        }

        // On encode en base64 header et payload
        $base64Header = base64_encode(json_encode($header));
        $base64Payload = base64_encode(json_encode($payload));

        // On "nettoie" les données encodées en base64 (remplace +, / et = par des caractères valides dans une URL)
        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], $base64Header);
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], $base64Payload);

        // On génère la signature
        $secret = base64_decode($secret);
        $signature = hash_hmac('sha256', "$base64Header.$base64Payload", $secret, true);
        $base64Signature = base64_encode($signature);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], $base64Signature);

        // On crée le token
        return "$base64Header.$base64Payload.$base64Signature";
    }

    // On vérifie qu'un token est valide
    public function isValid(string $token): bool
    {
        return preg_match(
            '/^[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+$/',
            $token
        ) === 1;
    }

    private function safeBase64Decode(string $data): string|false
    {
        $decoded = base64_decode($data, true);

        if ($decoded === false) {
            return false;
        }

        return $decoded;
    }

    // On récupère le payload d'un token
    public function getPayload(string $token): array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return [];
        }

        $payload = $this->safeBase64Decode($parts[1]);

        if ($payload === false) {
            return [];
        }

        return json_decode($payload, true) ?? [];
    }

    // On récupère le header d'un token
    public function getHeader(string $token): array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return [];
        }

        $header = $this->safeBase64Decode($parts[0]);

        if ($header === false) {
            return [];
        }

        return json_decode($header, true) ?? [];
    }

    // On vérifie si le token est expiré
    public function isExpired(string $token): bool
    {
        $payload = $this->getPayload($token);

        if (!isset($payload['exp'])) {
            return true;
        }

        return $payload['exp'] < (new DateTimeImmutable())->getTimestamp();
    }

    // On vérifie la signature du token
    public function check(string $token, string $secret): bool
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return false;
        }

        [$header, $payload, $signature] = $parts;

        $verifSignature = $this->generate(
            $this->getHeader($token),
            $this->getPayload($token),
            $secret,
            0
        );

        $verifParts = explode('.', $verifSignature);

        return hash_equals($signature, $verifParts[2] ?? '');
    }
}
