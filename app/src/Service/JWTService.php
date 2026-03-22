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

    // On récupère le payload d'un token
    public function getPayload(string $token): array
    {
        // On "casse" le token
        $array = explode('.', $token);

        // On décode le payload
        $payload = json_decode(base64_decode($array[1]), true);

        return $payload;
    }

    // On récupère le header d'un token
    public function getHeader(string $token): array
    {
        // On "casse" le token
        $array = explode('.', $token);

        // On décode le header
        $header = json_decode(base64_decode($array[0]), true);

        return $header;
    }

    // On vérifie si le token est expiré
    public function isExpired(string $token): bool
    {
        $payload = $this->getPayload($token);

        $now = new DateTimeImmutable();
        return $payload['exp'] < $now->getTimestamp();
    }

    // On vérifie la signature du token
    public function check(string $token, string $secret): bool
    {
        // On récupère le header et le payload
        $header = $this->getHeader($token);
        $payload = $this->getPayload($token);

        // On régénère un token avec le header et le payload (la même chose que le token passé en argument) avec la même clé secrète
        $verifToken = $this->generate($header, $payload, $secret, 0);

        return $token === $verifToken;
    }
}
