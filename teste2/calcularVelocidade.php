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

// Função para calcular a velocidade em cada ponto do segmento e registrar no arquivo
function calcularVelocidadeSegmento($arquivo_segmento)
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

    // Abre o arquivo para escrita (para registrar as velocidades)
    $arquivo_registro = fopen($arquivo_segmento, 'a');

    // Inicializa variáveis para calcular a velocidade média
    $total_velocidade = 0;
    $pontos_count = count($segmento);

    // Inicializa a variável para armazenar a última posição e tempo
    $last_position = null;
    $last_tempo = null;

    // Itera sobre os tempos no segmento para calcular a velocidade em cada ponto
    foreach ($segmento as $tempo_atual => $info_ponto) {
        // Verifica se é o primeiro ponto do segmento
        if ($last_position === null) {
            $last_position = $info_ponto['local'];
            $last_tempo = strtotime($tempo_atual);
            continue; // Pula para o próximo loop
        }

        // Calcula a distância entre o ponto atual e o último ponto
        $distancia_ponto_atual = calcularDistancia($last_position['latitude'], $last_position['longitude'], $info_ponto['local']['latitude'], $info_ponto['local']['longitude']);

        // Calcula o tempo decorrido desde o último ponto
        $tempo_atual = strtotime($tempo_atual);
        $tempo_decorrido = $tempo_atual - $last_tempo;

        // Calcula a velocidade em metros por segundo (m/s)
        $velocidade = $distancia_ponto_atual / $tempo_decorrido;

        // Registra a velocidade no arquivo
        fwrite($arquivo_registro, "Velocidade em $tempo_atual: $velocidade m/s\n");

        // Atualiza a última posição e tempo para o próximo loop
        $last_position = $info_ponto['local'];
        $last_tempo = $tempo_atual;

        // Adiciona a velocidade atual à velocidade total
        $total_velocidade += $velocidade;
    }

    // Calcula a velocidade média
    $velocidade_media = $total_velocidade / $pontos_count;

    // Registra a velocidade média no arquivo
    fwrite($arquivo_registro, "Velocidade Média: $velocidade_media m/s\n");

    // Fecha o arquivo de registro
    fclose($arquivo_registro);

    echo "Velocidades calculadas e registradas com sucesso.";
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

// Calcular e registrar a velocidade em cada segmento
foreach ($arquivos_segmentos as $arquivo) {
    calcularVelocidadeSegmento($arquivo);
}

?>
