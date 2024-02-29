<?php
function segmentarArquivo($xml) {
    $segmentos = array();
    $atualSegmento = null;

    foreach ($xml->trk->trkseg as $trkseg) {
        foreach ($trkseg->trkpt as $trkpt) {
            $time = strtotime((string)$trkpt->time);

            // Condições para separar os segmentos
            if ($atualSegmento === null || ($time - $atualSegmento['inicio']) >= $atualSegmento['duracao']) {
                $nomeSegmento = obterNomeSegmento($time);
                $atualSegmento = array(
                    'nome' => $nomeSegmento,
                    'inicio' => $time,
                    'duracao' => obterDuracaoSegmento($nomeSegmento),
                    'pontos' => array()
                );
                $segmentos[$nomeSegmento][] = $atualSegmento;
            }

            $atualSegmento['pontos'][] = $trkpt;
        }
    }

    return $segmentos;
}

function obterNomeSegmento($time) {
    // Lógica para determinar o nome do segmento com base no tempo
    // Personalize conforme necessário
    if ($time < strtotime('20 minutes')) {
        return 'Aquecimento';
    } elseif ($time < strtotime('26 minutes')) {
        return 'Tiros';
    } elseif ($time < strtotime('31 minutes')) {
        return 'Recuperacao1';
    } elseif ($time < strtotime('36 minutes')) {
        return 'TiroLongo';
    } elseif ($time < strtotime('46 minutes')) {
        return 'RecuperacaoEndurance';
    } elseif ($time < strtotime('66 minutes')) {
        return 'ContraRelogio';
    } elseif ($time < strtotime('81 minutes')) {
        return 'Endurance';
    } elseif ($time < strtotime('91 minutes')) {
        return 'Desaquecimento';
    } else {
        return 'Outro';
    }
}

function obterDuracaoSegmento($nomeSegmento) {
    // Lógica para determinar a duração do segmento com base no nome
    // Personalize conforme necessário
    switch ($nomeSegmento) {
        case 'Aquecimento':
            return 20 * 60; // 20 minutos em segundos
        case 'Tiros':
            return 6 * 60; // 6 minutos em segundos
        case 'Recuperacao1':
        case 'RecuperacaoEndurance':
        case 'Endurance':
        case 'Desaquecimento':
            return 5 * 60; // 5 minutos em segundos
        case 'TiroLongo':
            return 5 * 60; // 5 minutos em segundos
        case 'ContraRelogio':
            return 20 * 60; // 20 minutos em segundos
        default:
            return 0;
    }
}

// Exemplo de uso:
if (isset($_FILES['arquivo_gpx']) && $_FILES['arquivo_gpx']['error'] === UPLOAD_ERR_OK) {
    $xml = simplexml_load_file($_FILES['arquivo_gpx']['tmp_name']);
    $segmentos = segmentarArquivo($xml);

    // Salvar os segmentos em um arquivo JSON
    $jsonFileName = 'segmentos-' . date('d-m-y-H-i-s') . '.json';
    file_put_contents($jsonFileName, json_encode($segmentos));

    // Redirecionar para o processamento
    header("Location: processarArquivo.php?arquivo_json=$jsonFileName");
    exit;
}
?>
