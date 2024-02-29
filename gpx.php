<?php
// Função para calcular a média de um array
function calcularMedia($array) {
    return count($array) > 0 ? array_sum($array) / count($array) : 0;
}

// Função para calcular a máxima de um array
function calcularMaxima($array) {
    return count($array) > 0 ? max($array) : 0;
}

// Carrega o arquivo GPX
$xml = simplexml_load_file("seuarquivo.gpx");

// Inicializa as variáveis para as métricas
$segmentos = array(
    'Aquecimento' => array(),
    'Tiros' => array(),
    'Recuperacao1' => array(),
    'TiroLongo' => array(),
    'Recuperacao2' => array(),
    'ContraRelogio' => array(),
    'Endurance' => array(),
    'Desaquecimento' => array()
);

// Processa os pontos do GPX
foreach ($xml->trk->trkseg->trkpt as $ponto) {
    $timestamp = strtotime((string) $ponto->time);
    $hora = date('H:i:s', $timestamp);
    $velocidade = (float) $ponto->speed; // Se estiver disponível no GPX
    $frequenciaCardiaca = (int) $ponto->extensions->gpxtpx->hr; // Frequência cardíaca
    $cadencia = isset($ponto->extensions->gpxtpx->cad) ? (int) $ponto->extensions->gpxtpx->cad : null; // Cadência
    $potencia = isset($ponto->extensions->gpxtpx->watts) ? (int) $ponto->extensions->gpxtpx->watts : null; // Potência

    // Determina o segmento com base no tempo
    if ($timestamp < strtotime('+20 minutes', strtotime($xml->metadata->time))) {
        $segmento = 'Aquecimento';
    } elseif ($timestamp < strtotime('+26 minutes', strtotime($xml->metadata->time))) {
        $segmento = 'Tiros';
    } elseif ($timestamp < strtotime('+31 minutes', strtotime($xml->metadata->time))) {
        $segmento = 'Recuperacao1';
    } elseif ($timestamp < strtotime('+36 minutes', strtotime($xml->metadata->time))) {
        $segmento = 'TiroLongo';
    } elseif ($timestamp < strtotime('+46 minutes', strtotime($xml->metadata->time))) {
        $segmento = 'Recuperacao2';
    } elseif ($timestamp < strtotime('+66 minutes', strtotime($xml->metadata->time))) {
        $segmento = 'ContraRelogio';
    } elseif ($timestamp < strtotime('+81 minutes', strtotime($xml->metadata->time))) {
        $segmento = 'Endurance';
    } elseif ($timestamp < strtotime('+91 minutes', strtotime($xml->metadata->time))) {
        $segmento = 'Desaquecimento';
    } else {
        continue; // Ignora pontos fora dos segmentos definidos
    }

    // Adiciona as métricas ao segmento
    $segmentos[$segmento]['velocidade'][] = $velocidade;
    $segmentos[$segmento]['frequenciaCardiaca'][] = $frequenciaCardiaca;
    if (!is_null($cadencia)) $segmentos[$segmento]['cadencia'][] = $cadencia;
    if (!is_null($potencia)) $segmentos[$segmento]['potencia'][] = $potencia;
}

// Gera a tabela HTML
echo '<table border="1">';
echo '<tr><th>Segmento</th><th>Velocidade Média</th><th>Velocidade Máxima</th><th>Frequência Cardíaca Média</th><th>Frequência Cardíaca Máxima</th><th>Cadência Média</th><th>Cadência Máxima</th><th>Potência Média</th><th>Potência Máxima</th></tr>';

foreach ($segmentos as $segmento => $metricas) {
    echo '<tr>';
    echo '<td>' . $segmento . '</td>';
    echo '<td>' . number_format(calcularMedia($metricas['velocidade']), 2) . '</td>';
    echo '<td>' . number_format(calcularMaxima($metricas['velocidade']), 2) . '</td>';
    echo '<td>' . number_format(calcularMedia($metricas['frequenciaCardiaca'])) . '</td>';
    echo '<td>' . calcularMaxima($metricas['frequenciaCardiaca']) . '</td>';
    echo '<td>' . (isset($metricas['cadencia']) ? number_format(calcularMedia($metricas['cadencia'])) : '-') . '</td>';
    echo '<td>' . (isset($metricas['cadencia']) ? calcularMaxima($metricas['cadencia']) : '-') . '</td>';
    echo '<td>' . (isset($metricas['potencia']) ? number_format(calcularMedia($metricas['potencia'])) : '-') . '</td>';
    echo '<td>' . (isset($metricas['potencia']) ? calcularMaxima($metricas['potencia']) : '-') . '</td>';
    echo '</tr>';
}

echo '</table>';
?>
