<?php

namespace App\Helpers;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateInterval;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Hmac\Sha256 as HMAC;
use Lcobucci\JWT\Encoding\CannotDecodeContent;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token\InvalidTokenStructure;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Token\UnsupportedHeaderFound;
use Lcobucci\JWT\UnencryptedToken;
// use Lcobucci\JWT\Signer\Rsa\Sha256 as RSA;

class JWT
{
    public static function isSubjectValid($subject)
    {
        return preg_match('/^\/([^\/]+\/)*([^\/]+|\*)$/', $subject);
    }

    public static function generateToken($permissions, $ttl, $key)
    {
        $config = Configuration::forSymmetricSigner(
            new HMAC(),
            // new RSA(),
            InMemory::plainText(base64_decode($key)),  /* HMAC */
            // InMemory::file($key),                   /* RSA */
        );

        $now   = CarbonImmutable::now();
        $token = $config->builder()
            // // Configures the issuer (iss claim)
            ->issuedBy("http://taxbacks.com")
            // Configures the id (jti claim)
            ->identifiedBy(uniqid('', TRUE))
            // Configures the time that the token was issue (iat claim)
            ->issuedAt($now)
            // Configures the expiration time of the token (exp claim)
            ->expiresAt($now->add(new DateInterval('PT' . $ttl . 'S')))
            // Configures a new claim, called "uid"
            ->withClaim('data', $permissions)
            // Builds a new token
            ->getToken($config->signer(), $config->signingKey());

        return $token->toString();
    }

    public static function parsingToken($jwt_token)
    {
        $parser = new Parser(new JoseEncoder());

        try {
            $token = $parser->parse($jwt_token);
        } catch (CannotDecodeContent | InvalidTokenStructure | UnsupportedHeaderFound $e) {
            echo 'Oh no, an error: ' . $e->getMessage();
        }
        assert($token instanceof UnencryptedToken);
        return $token->claims();
    }

    public static function convertToIndonesianTime($datetime)
    {
        // Create a Carbon instance from the given UTC datetime
        $utcCarbon = Carbon::parse($datetime);

        // Set the timezone to Indonesian timezone (Asia/Jakarta)
        $indonesianTime = $utcCarbon->setTimezone('Asia/Jakarta');

        // Format the datetime to Indonesian format (YYYY-MM-DD HH:mm:ss)
        $formattedIndonesianTime = $indonesianTime->format('Y-m-d H:i:s');

        return $formattedIndonesianTime;
    }
}
