<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processar Arquivo</title>
</head>
<body>
    <?php
    // Verificar se o parâmetro 'arquivo_json' está presente na URL
    if (isset($_GET['arquivo_json'])) {
        $jsonFileName = $_GET['arquivo_json'];

        // Carregar dados do arquivo JSON
        $jsonContent = file_get_contents($jsonFileName);
        $segmentos = json_decode($jsonContent, true);

        // Exibir dados para verificação
        echo '<h2>Dados do Arquivo JSON</h2>';
        echo '<pre>';
        print_r($segmentos);
        echo '</pre>';
    } else {
        echo '<p>Nenhum arquivo JSON fornecido.</p>';
    }
    ?>
</body>
</html>
