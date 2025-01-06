<?php

namespace Capon\Investments\Logic;

class MetricCalculator
{
    public static function determinarRecomendacion($data)
    {
        // Factores relevantes
        $peRatio = $data['overview']['PERatio'] ?? null;
        $priceToBook = $data['overview']['PriceToBookRatio'] ?? null;
        $currentPrice = $data['currentPrice'] ?? null;
        $targetPrice = $data['overview']['AnalystTargetPrice'] ?? null;

        // Criterio para recomendación
        $criterio = "No se cumple ningún criterio específico.";
        $decision = "Mantener";

        if (is_numeric($currentPrice) && is_numeric($targetPrice) && $currentPrice < $targetPrice * 0.9) {
            $criterio = "El precio actual está significativamente por debajo del precio objetivo.";
            $decision = "Comprar";
        } elseif (is_numeric($peRatio) && $peRatio > 35) {
            $criterio = "El PERatio es muy alto, indicando una posible sobrevaloración.";
            $decision = "Vender";
        } elseif (is_numeric($priceToBook) && $priceToBook > 3) {
            $criterio = "El Price-to-Book Ratio es demasiado alto, indicando sobrevaloración.";
            $decision = "Vender";
        }

        return [
            'decision' => $decision,
            'criterio' => $criterio,
        ];
    }
}
