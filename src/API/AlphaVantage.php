<?php

namespace Capon\Investments\API;

use GuzzleHttp\Client;

class AlphaVantage
{
    private $apiKey;
    private $baseUrl;

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
        $this->baseUrl = "https://www.alphavantage.co/query";
    }

    public function obtenerDatos($symbol)
    {
        $client = new Client();

        try {
            // Obtener datos de la empresa (Overview)
            $overviewResponse = $client->get($this->baseUrl, [
                'query' => [
                    'function' => 'OVERVIEW',
                    'symbol' => $symbol,
                    'apikey' => $this->apiKey,
                ],
            ]);

            $overviewData = json_decode($overviewResponse->getBody(), true);

            // Validar respuesta del overview
            if (empty($overviewData)) {
                throw new \Exception("Error al obtener los datos de Overview de la API.");
            }

            // Obtener precio actual de la acción (Global Quote)
            $quoteResponse = $client->get($this->baseUrl, [
                'query' => [
                    'function' => 'GLOBAL_QUOTE',
                    'symbol' => $symbol,
                    'apikey' => $this->apiKey,
                ],
            ]);

            $quoteData = json_decode($quoteResponse->getBody(), true);
            $currentPrice = $quoteData['Global Quote']['05. price'] ?? null;

            // Devolver todos los datos combinados
            return [
                'overview' => $overviewData,
                'globalQuote' => $quoteData['Global Quote'] ?? [],
                'currentPrice' => $currentPrice,
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }
}

