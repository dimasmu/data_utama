<?php
$jwt_secret_key = env('JWT_SECRET');
return [
    'jwt_secret_key' => $jwt_secret_key,
    'jwt_expired_time' => 3600, // in second
];
