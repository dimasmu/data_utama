<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class Helpers
{
    public static function formatJson($dataParam, $responseParam, $messageParam, $statusParam, $payload = null, $route = null)
    {
        $data = array(
            "response" => false,
            "message" => null,
            "data" => []
        );
        $status = $responseParam;
        $data['data'] = $dataParam;
        $data['message'] = $messageParam;
        $data['response'] = $statusParam;

        if ($responseParam === 500) {
            Log::error([
                "message" => $messageParam,
                "payload" => $payload,
                "route" => $route
            ]);
        }

        return [
            "data" => $data,
            "status_response" => $status
        ];
    }
}
