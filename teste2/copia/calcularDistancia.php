<?php

    // Função para calcular a distância entre dois pontos geográficos (em coordenadas de latitude e longitude)
    function calcularDistancia($lat1, $lon1, $lat2, $lon2)
    {
        $earth_radius = 6371; // Raio da Terra em quilômetros
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earth_radius * $c;
        return $distance; // Distância em quilômetros
    }

    // Função para ler os dados de um arquivo JSON e calcular a distância entre os pontos
    function calcularDistanciaSegmento($arquivo_segmento)
    {
        // Verifica se o arquivo do segmento existe
        if (!file_exists($arquivo_segmento)) {
            throw new Exception("Arquivo $arquivo_segmento não encontrado.");
        }

        // Abre o arquivo do segmento em modo de leitura
        $segmento = json_decode(file_get_contents($arquivo_segmento), true);

        // Verifica se o JSON foi decodificado corretamente
        if ($segmento === null) {
            throw new Exception("Falha ao decodificar o JSON do arquivo $arquivo_segmento.");
        }

        // Inicializa a variável para armazenar a distância total do segmento
        $distancia_total = 0;

        // Inicializa a variável para armazenar a última posição
        $last_position = null;

        // Itera sobre os tempos no segmento para calcular a distância entre pontos consecutivos
        $tempos = array_keys($segmento);
        foreach ($tempos as $tempo_atual) {
            // Verifica se é o primeiro ponto do segmento
            if ($last_position === null) {
                $last_position = $segmento[$tempo_atual]['local'];
                continue; // Pula para o próximo loop
            }

            // Calcula a distância entre o ponto atual e o último ponto
            $distancia_ponto_atual = calcularDistancia($last_position['latitude'], $last_position['longitude'], $segmento[$tempo_atual]['local']['latitude'], $segmento[$tempo_atual]['local']['longitude']);

            // Soma a distância ao total do segmento
            $distancia_total += $distancia_ponto_atual;

            // Adiciona a distância ao ponto atual
            $segmento[$tempo_atual]['distancia'] = $distancia_ponto_atual;
            // Adiciona a distância acumulada até o ponto atual
            $segmento[$tempo_atual]['distancia_acumulada'] = $distancia_total;

            // Atualiza a última posição para o próximo loop
            $last_position = $segmento[$tempo_atual]['local'];
        }

        // Reescreve o arquivo JSON com as informações atualizadas
        file_put_contents($arquivo_segmento, json_encode($segmento, JSON_PRETTY_PRINT));

        // Retorna a distância total do segmento
        return;
    }

    // Arquivos de cada segmento
    $arquivos_segmentos = [
        "warmup.txt",
        "primeiro_bloco.txt",
        "segundo_bloco.txt",
        "terceiro_bloco.txt",
        "quarto_bloco.txt",
        "bloco_principal.txt",
        "ultimo_bloco.txt",
        "cooldown.txt",
        "restante.txt"
    ];

?>
