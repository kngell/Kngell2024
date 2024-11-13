<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class StatusJsScanner
{
    private const string URL = 'https://status.js.org/codes.json';

    /**
     * @return StatusCode[]
     * @throws GuzzleException
     */
    public function scan() : array
    {
        $httpClient = new Client();
        $statusCodeData = json_decode($httpClient->get(self::URL)->getBody()->getContents(), true);
        $codes = [];
        foreach ($statusCodeData as $key => $value) {
            if (str_contains(strval($key), 'xx')) {
                continue;
            }
            $codes[] = new StatusCode($value['code'], $value['message'], $value['description']);
        }
        return $codes;
    }
}
