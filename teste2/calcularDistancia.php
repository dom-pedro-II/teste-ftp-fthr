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

    // Abre o arquivo para escrita (para registrar as distâncias)
    $arquivo_registro = fopen($arquivo_segmento, 'a');

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

        // Registra a distância no arquivo entre os pontos
        fwrite($arquivo_registro, "Distância entre último ponto e $tempo_atual: $distancia_ponto_atual (Distância acumulada: $distancia_total)\n");

        // Atualiza a última posição para o próximo loop
        $last_position = $segmento[$tempo_atual]['local'];
    }

    // Fecha o arquivo de registro
    fclose($arquivo_registro);

    // Retorna a distância total do segmento
    return $distancia_total;
}



// Função para somar todas as distâncias de um segmento
function somarDistancias($distancias)
{
    return array_sum($distancias);
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

/*Calcular e registrar a distância total
$distancia_total = 0;
foreach ($arquivos_segmentos as $arquivo) {
    try {
        $distancias_segmento = calcularDistanciaSegmento($arquivo);
        $distancia_total += somarDistancias($distancias_segmento);
    } catch (Exception $e) {
        echo 'Erro: ' . $e->getMessage();
        // Se houver um erro ao calcular a distância do segmento, continuaremos com o próximo segmento
        continue;
    }
}

// Registrar a distância total em um arquivo
$distancia_total_arquivo = "distancia_total.txt";
file_put_contents($distancia_total_arquivo, $distancia_total);

echo "Distância total calculada e registrada com sucesso: $distancia_total";*/

?>
