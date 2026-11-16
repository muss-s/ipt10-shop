<?php

namespace Core;

/**
 * Small cURL wrapper used to call external JSON APIs.
 */
class HttpClient
{
    public function __construct(private int $timeout = 5)
    {
    }

    /**
     * GET a URL and decode its JSON body.
     * Throws HttpClientException on network errors, non-2xx status, or bad JSON.
     */
    public function getJson(string $url, array $query = [], array $headers = []): array
    {
        if ($query) {
            $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($query);
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => array_merge(['Accept: application/json'], $headers),
            CURLOPT_USERAGENT => 'IPT10-Shop/1.0',
        ]);

        $body = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);

        if ($body === false) {
            throw new HttpClientException("Request failed: $error", 0);
        }
        if ($status === 429) {
            throw new HttpClientException('Rate limit reached', 429);
        }
        if ($status < 200 || $status >= 300) {
            throw new HttpClientException("API returned HTTP $status", $status);
        }

        $data = json_decode($body, true);
        if (!is_array($data)) {
            throw new HttpClientException('API returned invalid JSON', $status);
        }
        return $data;
    }
}

class HttpClientException extends \RuntimeException
{
}
