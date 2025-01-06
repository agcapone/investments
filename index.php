<?php

require 'vendor/autoload.php';

use Capon\Investments\API\AlphaVantage;
use Capon\Investments\Logic\MetricCalculator;

// Configuración de la API Key
$apiKey = "SBQOMF6RKFLVTVQ8"; // Cambia esta clave si es necesario
$alphaVantage = new AlphaVantage($apiKey);

$symbol = $_GET['symbol'] ?? null;
$data = [];
$recomendacion = [];
$error = null;

if ($symbol) {
    $symbol = strtoupper(trim($symbol));
    $data = $alphaVantage->obtenerDatos($symbol);

    if (isset($data['error'])) {
        $error = $data['error'];
    } else {
        $recomendacion = MetricCalculator::determinarRecomendacion($data);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recomendador de Acciones</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #1e1e2f;
            color: #ffffff;
            margin: 0;
            padding: 0;
        }
        h1, h2 {
            text-align: center;
        }
        .container {
            width: 80%;
            margin: 20px auto;
        }
        .table-container {
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            padding: 10px;
            text-align: left;
        }
        table th {
            background-color: #33334d;
        }
        table tr:nth-child(even) {
            background-color: #2c2c3a;
        }
        table tr:hover {
            background-color: #44445e;
        }
        .positive {
            color: #00ff7f;
        }
        .negative {
            color: #ff4d4d;
        }
        .neutral {
            color: #ffffff;
        }
        .button {
            display: block;
            width: 100px;
            margin: 0 auto;
            padding: 10px;
            text-align: center;
            background-color: #44445e;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .button:hover {
            background-color: #555570;
        }
    </style>
</head>
<body>
    <h1>Recomendador de Acciones</h1>
    <div class="container">
        <form method="GET">
            <label for="symbol">Símbolo de la Acción:</label>
            <input type="text" id="symbol" name="symbol" value="<?= htmlspecialchars($symbol ?? '') ?>" required>
            <button type="submit">Consultar</button>
        </form>

        <?php if ($error): ?>
            <p class="negative">Error: <?= htmlspecialchars($error) ?></p>
        <?php elseif ($data): ?>
            <div class="table-container">
                <table>
                    <tr>
                        <th>Indicador</th>
                        <th>Valor</th>
                    </tr>
                    <?php foreach ($data['overview'] as $key => $value): ?>
                        <tr>
                            <td><?= htmlspecialchars($key) ?></td>
                            <td class="<?= is_numeric($value) && $value > 0 ? 'positive' : (is_numeric($value) && $value < 0 ? 'negative' : 'neutral') ?>">
                                <?= htmlspecialchars($value) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td>Current Price</td>
                        <td class="positive"><?= htmlspecialchars($data['currentPrice'] ?? 'No disponible') ?></td>
                    </tr>
                </table>
            </div>

            <h2>Recomendación</h2>
            <p>Decisión: <span class="<?= $recomendacion['decision'] === 'Comprar' ? 'positive' : ($recomendacion['decision'] === 'Vender' ? 'negative' : 'neutral') ?>">
                <?= htmlspecialchars($recomendacion['decision'] ?? 'No disponible') ?>
            </span></p>
            <p>Criterio: <?= htmlspecialchars($recomendacion['criterio'] ?? 'No disponible') ?></p>
        <?php endif; ?>
    </div>
</body>
</html>
