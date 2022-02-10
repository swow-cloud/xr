<?php
/**
 * This file is part of Swow-Cloud/Job
 * @license  https://github.com/serendipity-swow/serendipity-job/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Chevere\Xr;

use CurlHandle;

final class Client
{
    public function __construct(
        private string $host = 'localhost',
        private int $port = 27420,
    ) {
    }

    public function getUrl(string $endpoint): string
    {
        return "http://{$this->host}:{$this->port}/{$endpoint}";
    }

    public function sendMessage(Message $message): void
    {
        try {
            $curlHandle = $this->getCurlHandle('message', $message->toArray());
            curl_exec($curlHandle);
        } finally {
            curl_close($curlHandle);
        }
    }

    private function getCurlHandle(string $endpoint, array $data): CurlHandle
    {
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $this->getUrl($endpoint));
        curl_setopt($curlHandle, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curlHandle, CURLOPT_ENCODING, '');
        curl_setopt($curlHandle, CURLINFO_HEADER_OUT, true);
        curl_setopt($curlHandle, CURLOPT_FAILONERROR, true);
        curl_setopt($curlHandle, CURLOPT_POST, true);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, http_build_query($data));

        return $curlHandle;
    }
}
