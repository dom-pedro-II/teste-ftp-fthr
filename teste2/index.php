<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload de Arquivo GPX</title>
</head>
<body>
    <h2>Upload de Arquivo GPX</h2>
    <form action="separarSegmentos.php" method="post" enctype="multipart/form-data">
        <label for="arquivo_gpx">Selecione um arquivo GPX:</label>
        <input type="file" name="arquivo_gpx" accept=".gpx" required>
        <br>
        <button type="submit">Enviar</button>
    </form>
</body>
</html>
