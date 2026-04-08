<?php

namespace App\Service;

use DateTimeImmutable;

/**
 * Service de génération et validation de JWT "maison"
 *
 * ⚠️ Attention : implémentation custom, non conforme à 100% aux standards RFC JWT
 * (mais suffisante pour un projet pédagogique ou personnel)
 *
 * Responsabilités :
 * - génération de token JWT
 * - décodage header/payload
 * - validation de signature
 * - vérification d'expiration
 */
class JWTService
{
    /**
     * Génère un token JWT signé
     *
     * @param array  $header   Header JWT (alg, typ, etc.)
     * @param array  $payload  Données du token
     * @param string $secret   Clé secrète (base64 encodée)
     * @param int    $validity Durée de validité en secondes
     */
    public function generate(array $header, array $payload, string $secret, int $validity = 10800): string
    {
        // Ajout automatique des claims standards si expiration activée
        if ($validity > 0) {
            $now = new DateTimeImmutable();
            $exp = $now->getTimestamp() + $validity;

            $payload['iat'] = $now->getTimestamp(); // issued at
            $payload['exp'] = $exp;                 // expiration
        }

        // Encodage base64 du header et payload
        $base64Header = base64_encode(json_encode($header));
        $base64Payload = base64_encode(json_encode($payload));

        // Conversion en base64 URL-safe (format JWT)
        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], $base64Header);
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], $base64Payload);

        // Signature HMAC SHA256 du token
        $secret = base64_decode($secret);
        $signature = hash_hmac('sha256', "$base64Header.$base64Payload", $secret, true);

        $base64Signature = base64_encode($signature);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], $base64Signature);

        // Construction finale du JWT
        return "$base64Header.$base64Payload.$base64Signature";
    }

    /**
     * Vérifie si le format du token est valide (structure JWT)
     */
    public function isValid(string $token): bool
    {
        return preg_match(
            '/^[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+$/',
            $token
        ) === 1;
    }

    /**
     * Décodage sécurisé base64
     * Retourne false si données invalides
     */
    private function safeBase64Decode(string $data): string|false
    {
        $decoded = base64_decode($data, true);

        if ($decoded === false) {
            return false;
        }

        return $decoded;
    }

    /**
     * Récupère le payload du JWT
     *
     * ⚠️ Ne valide pas la signature (voir check())
     */
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

    /**
     * Récupère le header du JWT
     */
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

    /**
     * Vérifie si le token est expiré
     *
     * ⚠️ Dépend du claim "exp"
     */
    public function isExpired(string $token): bool
    {
        $payload = $this->getPayload($token);

        if (!isset($payload['exp'])) {
            return true; // sécurité par défaut : token invalide si pas d'exp
        }

        return $payload['exp'] < (new DateTimeImmutable())->getTimestamp();
    }

    /**
     * Vérifie l'intégrité du token (signature HMAC)
     *
     * ⚠️ Compare la signature reconstruite avec celle du token
     */
    public function check(string $token, string $secret): bool
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return false;
        }

        [$header, $payload, $signature] = $parts;

        // Recréation du token sans expiration pour éviter divergence de temps
        $verifSignature = $this->generate(
            $this->getHeader($token),
            $this->getPayload($token),
            $secret,
            0
        );

        $verifParts = explode('.', $verifSignature);

        // Comparaison sécurisée anti timing attack
        return hash_equals($signature, $verifParts[2] ?? '');
    }
}
