<?php

namespace Api\Helper;

use Api\Exception\BusinessException;

class CorsHelper
{

    public static function getHeaders(): array
    {

        $allowedOrigin = $_ENV['ALLOWED_ORIGIN'] ?? '';
        if (strlen($allowedOrigin) === 0)
            throw new BusinessException(400, 'Missing ALLOWED_ORIGIN param');

        return [
            'Access-Control-Allow-Origin' => $allowedOrigin,
            'Access-Control-Request-Method' => 'GET, POST, PATCH, DELETE',
            'Access-Control-Allow-Headers' => 'x-user-lang-tag'
        ];
    }
}
