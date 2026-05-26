<?php

namespace App\Services\Asaas;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AsaasClient
{
    public function isConfigured(): bool
    {
        return !empty(config('asaas.api_key'));
    }

    public function baseUrl(): string
    {
        $env = config('asaas.environment', 'sandbox');

        return config("asaas.base_urls.{$env}") ?? config('asaas.base_urls.sandbox');
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function post(string $path, array $payload = [], ?string $apiKey = null): Response
    {
        return $this->request('post', $path, $payload, $apiKey);
    }

    /**
     * @param  array<string, mixed>  $query
     */
    public function get(string $path, array $query = [], ?string $apiKey = null): Response
    {
        return $this->request('get', $path, $query, $apiKey);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function request(string $method, string $path, array $data = [], ?string $apiKey = null): Response
    {
        $key = $apiKey ?? config('asaas.api_key');
        $url = rtrim($this->baseUrl(), '/').'/'.ltrim($path, '/');

        $pending = Http::withHeaders([
            'access_token' => $key,
            'Content-Type' => 'application/json',
            'User-Agent' => 'CotaBrasilis/1.0',
        ])->acceptJson();

        $response = $method === 'get'
            ? $pending->get($url, $data)
            : $pending->post($url, $data);

        if ($response->failed()) {
            Log::warning('Asaas API error', [
                'method' => strtoupper($method),
                'path' => $path,
                'status' => $response->status(),
                'body' => $response->json() ?? $response->body(),
            ]);
        }

        return $response;
    }
}
