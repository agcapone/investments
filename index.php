<?php

require 'vendor/autoload.php';

use Capon\Investments\API\AlphaVantage;
use Capon\Investments\Logic\MetricCalculator;

// Configuración de la API Key
$apiKey = "SBQOMF6RKFLVTVQ8"; // Cambia esta clave si es necesario
$alphaVantage = new AlphaVantage($apiKey);

$symbol = $_GET['symbol'] ?? null;
$data = [];
$overview = [];
$globalQuote = [];
$recomendacion = [];
$error = null;

if ($symbol) {
    $symbol = strtoupper(trim($symbol));
    $data = $alphaVantage->obtenerDatos($symbol);

    if (isset($data['error'])) {
        $error = $data['error'];
    } else {
        $overview = $data['overview'] ?? [];
        $globalQuote = $data['globalQuote'] ?? [];
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
            display: inline-block;
            padding: 10px 15px;
            text-align: center;
            background-color: #44445e;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .button:hover {
            background-color: #555570;
        }
        .hidden {
            display: none;
        }
        .columns {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .column {
            flex: 1 1 calc(20% - 10px);
            background-color: #2c2c3a;
            padding: 10px;
            border-radius: 5px;
        }
        .column-title {
            font-weight: bold;
            margin-bottom: 5px;
            color: #00ff7f;
        }
    </style>
    <script>
        function toggleMore() {
            const moreSection = document.getElementById('more-data');
            const button = document.getElementById('more-button');
            if (moreSection.classList.contains('hidden')) {
                moreSection.classList.remove('hidden');
                button.textContent = 'Less';
            } else {
                moreSection.classList.add('hidden');
                button.textContent = 'More';
            }
        }
    </script>
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
        <?php elseif ($overview): ?>
            <div class="table-container">
                <h2>Principales Indicadores</h2>
                <table>
                    <tr>
                        <th>Indicador</th>
                        <th>Valor</th>
                    </tr>
                    <tr>
                        <td>P/E Ratio</td>
                        <td class="positive"><?= htmlspecialchars($overview['PERatio'] ?? 'No disponible') ?></td>
                    </tr>
                    <tr>
                        <td>Price to Book Ratio</td>
                        <td><?= htmlspecialchars($overview['PriceToBookRatio'] ?? 'No disponible') ?></td>
                    </tr>
                    <tr>
                        <td>Beta</td>
                        <td><?= htmlspecialchars($overview['Beta'] ?? 'No disponible') ?></td>
                    </tr>
                    <tr>
                        <td>Target Price</td>
                        <td><?= htmlspecialchars($overview['AnalystTargetPrice'] ?? 'No disponible') ?></td>
                    </tr>
                    <tr>
                        <td>52 Week High</td>
                        <td><?= htmlspecialchars($overview['52WeekHigh'] ?? 'No disponible') ?></td>
                    </tr>
                    <tr>
                        <td>52 Week Low</td>
                        <td><?= htmlspecialchars($overview['52WeekLow'] ?? 'No disponible') ?></td>
                    </tr>
                    <tr>
                        <td>Quarterly Revenue Growth (YoY)</td>
                        <td><?= htmlspecialchars($overview['QuarterlyRevenueGrowthYOY'] ?? 'No disponible') ?></td>
                    </tr>
                    <tr>
                        <td>Profit Margin</td>
                        <td><?= htmlspecialchars($overview['ProfitMargin'] ?? 'No disponible') ?></td>
                    </tr>
                    <tr>
                        <td>Current Price</td>
                        <td class="positive"><?= htmlspecialchars($globalQuote['05. price'] ?? 'No disponible') ?></td>
                    </tr>
                </table>
            </div>

            <h2>Recomendación</h2>
            <p>Decisión: <span class="<?= $recomendacion['decision'] === 'Comprar' ? 'positive' : ($recomendacion['decision'] === 'Vender' ? 'negative' : 'neutral') ?>">
                <?= htmlspecialchars($recomendacion['decision'] ?? 'No disponible') ?>
            </span></p>
            <p>Criterio: <?= htmlspecialchars($recomendacion['criterio'] ?? 'No disponible') ?></p>

            <button id="more-button" class="button" onclick="toggleMore()">More</button>

            <div id="more-data" class="hidden">
                <h2>Todos los Datos</h2>
                <div class="columns">
                    <?php foreach ($data as $section => $values): ?>
                        <?php if (is_array($values)): ?>
                            <?php foreach ($values as $key => $value): ?>
                                <div class="column">
                                    <div class="column-title"><?= htmlspecialchars($key) ?></div>
                                    <div><?= htmlspecialchars(is_array($value) ? json_encode($value) : $value) ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="column">
                                <div class="column-title"><?= htmlspecialchars($section) ?></div>
                                <div><?= htmlspecialchars($values) ?></div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
