<?php
function fetchCryptoData(string $selectedCurrency): array
{
    $allowed = ['bitcoin', 'ethereum', 'dogecoin'];
    if (!in_array($selectedCurrency, $allowed, true)) {
        return [
            'success' => false,
            'error' => 'Invalid currency selected.',
        ];
    }

    $url = 'https://api.coingecko.com/api/v3/simple/price?ids=bitcoin,ethereum,dogecoin&vs_currencies=usd&include_24hr_change=true';

    $response = fetchWithFileGetContents($url, true);
    if ($response === false) {
        $response = fetchWithCurl($url, true);
    }
    if ($response === false) {
        $response = fetchWithFileGetContents($url, false);
    }
    if ($response === false) {
        $response = fetchWithCurl($url, false);
    }

    if ($response === false) {
        return [
            'success' => false,
            'error' => 'Error',
        ];
    }

    $decoded = json_decode($response, true);
    if (!is_array($decoded) || !isset($decoded[$selectedCurrency])) {
        return [
            'success' => false,
            'error' => 'Unexpected API response.',
        ];
    }

    return [
        'success' => true,
        'data' => $decoded[$selectedCurrency],
    ];
}

function fetchWithFileGetContents(string $url, bool $verifySsl)
{
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "Accept: application/json\r\nUser-Agent: PHP-Crypto-Tracker/1.0\r\n",
            'timeout' => 10,
        ],
        'ssl' => [
            'verify_peer' => $verifySsl,
            'verify_peer_name' => $verifySsl,
        ],
    ]);

    return @file_get_contents($url, false, $context);
}

function fetchWithCurl(string $url, bool $verifySsl)
{
    if (!function_exists('curl_init')) {
        return false;
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'User-Agent: PHP-Crypto-Tracker/1.0',
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $verifySsl);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $verifySsl ? 2 : 0);

    $result = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($result === false || $httpCode >= 400) {
        return false;
    }

    return $result;
}
