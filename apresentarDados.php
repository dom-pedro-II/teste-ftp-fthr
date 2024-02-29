<?php
// Carregue os dados do arquivo JSON gerado pelo segundo módulo
$jsonFileName = 'segmentos-' . date('d-m-y-H-i-s') . '.json';

if (file_exists($jsonFileName)) {
    $dados = json_decode(file_get_contents($jsonFileName), true);

    echo "<h1>Dados dos Segmentos</h1>";

    foreach ($dados as $nomeSegmento => $resultados) {
        echo "<h2>$nomeSegmento</h2>";
        echo "<table border='1'>";
        echo "<tr><th>Métrica</th><th>Média</th><th>Máxima</th><th>Mínima</th></tr>";

        foreach ($resultados['media'] as $metrica => $media) {
            $maxima = $resultados['maxima'][$metrica];
            $minima = $resultados['minima'][$metrica];

            echo "<tr>";
            echo "<td>$metrica</td>";
            echo "<td>$media</td>";
            echo "<td>$maxima</td>";
            echo "<td>$minima</td>";
            echo "</tr>";
        }

        echo "</table>";
    }
} else {
    echo "Arquivo JSON não encontrado.";
}
?>
