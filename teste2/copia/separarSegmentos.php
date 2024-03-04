<?php
    include 'calcularDistancia.php';
    // Função para escrever os dados em um arquivo txt
    function writeToFile($filename, $data)
    {
        $file = fopen($filename, 'w');
        fwrite($file, json_encode($data));
        fclose($file);
        echo "Arquivo $filename criado com sucesso.";
    }

    // Verifica se o arquivo GPX foi enviado
    if ($_FILES["arquivo_gpx"]["error"] == UPLOAD_ERR_OK) {
        // Lê o conteúdo do arquivo GPX
        $conteudo_gpx = file_get_contents($_FILES["arquivo_gpx"]["tmp_name"]);

        // Utiliza expressão regular para encontrar os intervalos entre as tags <trkpt>
        preg_match_all('/<trkpt[^>]*>(.*?)<\/trkpt>/s', $conteudo_gpx, $intervalos);

        // Array para armazenar os intervalos de tempo
        $tempos = array();
        // Arrays para armazenar as informações associadas aos tempos
        $local = array();
        $elevacao = array();
        $power = array();
        $temp = array();
        $batimento = array();
        $cadencia = array();

        // Obtém o tempo e outras informações de cada intervalo e armazena nas respectivas arrays
        foreach ($intervalos[0] as $intervalo) {
            // Tempo
            preg_match('/<time>(.*?)<\/time>/', $intervalo, $tempo_match);
            $tempo = isset($tempo_match[1]) ? $tempo_match[1] : '';
            if (!empty($tempo)) {
                $tempos[] = $tempo;
            }

            // Local
            preg_match('/<trkpt[^>]*lat="(.*?)" lon="(.*?)"/', $intervalo, $local_match);
            $latitude = isset($local_match[1]) ? $local_match[1] : '';
            $longitude = isset($local_match[2]) ? $local_match[2] : '';
            if (!empty($latitude) && !empty($longitude)) {
                $local[$tempo] = array('latitude' => $latitude, 'longitude' => $longitude);
            }

            // Elevação
            preg_match('/<ele>(.*?)<\/ele>/', $intervalo, $elevacao_match);
            $elev = isset($elevacao_match[1]) ? $elevacao_match[1] : '1'; // Se a elevação não estiver presente, coloque 1
            if (!empty($elev)) {
                $elevacao[$tempo] = $elev;
            }

            // Power
            preg_match('/<power>(.*?)<\/power>/', $intervalo, $power_match);
            $power_value = isset($power_match[1]) ? $power_match[1] : '1'; // Se o power não estiver presente, coloque 1
            if (!empty($power_value)) {
                $power[$tempo] = $power_value;
            }

            // Temperatura
            preg_match('/<gpxtpx:atemp>(.*?)<\/gpxtpx:atemp>/', $intervalo, $temp_match);
            $temp_value = isset($temp_match[1]) ? $temp_match[1] : '1'; // Se a temperatura não estiver presente, coloque 1
            if (!empty($temp_value)) {
                $temp[$tempo] = $temp_value;
            }

            // Batimento cardíaco
            preg_match('/<gpxtpx:hr>(.*?)<\/gpxtpx:hr>/', $intervalo, $heart_rate_match);
            $heart_rate = isset($heart_rate_match[1]) ? $heart_rate_match[1] : '1'; // Se o batimento cardíaco não estiver presente, coloque 1
            if (!empty($heart_rate)) {
                $batimento[$tempo] = $heart_rate;
            }

            // Cadência
            preg_match('/<gpxtpx:cad>(.*?)<\/gpxtpx:cad>/', $intervalo, $cadence_match);
            $cadence = isset($cadence_match[1]) ? $cadence_match[1] : '1'; // Se a cadência não estiver presente, coloque 1
            if (!empty($cadence)) {
                $cadencia[$tempo] = $cadence;
            }
        }

        // Verifica se existem dados suficientes para os primeiros 20 minutos
        $tempo_limite = strtotime($tempos[0]) + (20 * 60);
        $warmup = array();
        foreach ($tempos as $tempo) {
            if (strtotime($tempo) <= $tempo_limite) {
                $warmup[$tempo] = array(
                    'local' => isset($local[$tempo]) ? $local[$tempo] : array('latitude' => '1', 'longitude' => '1'),
                    'elevacao' => isset($elevacao[$tempo]) ? $elevacao[$tempo] : '1',
                    'power' => isset($power[$tempo]) ? $power[$tempo] : '1',
                    'temp' => isset($temp[$tempo]) ? $temp[$tempo] : '1',
                    'batimento' => isset($batimento[$tempo]) ? $batimento[$tempo] : '1',
                    'cadencia' => isset($cadencia[$tempo]) ? $cadencia[$tempo] : '1',
                );
            } else {
                break;
            }
        }

        // Variável para armazenar o primeiro bloco (6 minutos após o warmup)
        $tempo_init = strtotime($tempos[0]) + (20 * 60);
        $tempo_limite = strtotime($tempos[0]) + (26 * 60);
        $primeiro_bloco = array();
        foreach ($tempos as $tempo) {
            if (strtotime($tempo) >= $tempo_init && strtotime($tempo) < $tempo_limite) {
                $primeiro_bloco[$tempo] = array(
                    'local' => isset($local[$tempo]) ? $local[$tempo] : array('latitude' => '1', 'longitude' => '1'),
                    'elevacao' => isset($elevacao[$tempo]) ? $elevacao[$tempo] : '1',
                    'power' => isset($power[$tempo]) ? $power[$tempo] : '1',
                    'temp' => isset($temp[$tempo]) ? $temp[$tempo] : '1',
                    'batimento' => isset($batimento[$tempo]) ? $batimento[$tempo] : '1',
                    'cadencia' => isset($cadencia[$tempo]) ? $cadencia[$tempo] : '1',
                );
            } elseif (strtotime($tempo) >= $tempo_limite) {
                break;
            }
        }

        // Variável para armazenar o segundo bloco (5 minutos após o primeiro bloco)
        $tempo_init = $tempo_limite;
        $tempo_limite = strtotime($tempos[0]) + (31 * 60);
        $segundo_bloco = array();
        foreach ($tempos as $tempo) {
            if (strtotime($tempo) >= $tempo_init && strtotime($tempo) < $tempo_limite) {
                $segundo_bloco[$tempo] = array(
                    'local' => isset($local[$tempo]) ? $local[$tempo] : array('latitude' => '1', 'longitude' => '1'),
                    'elevacao' => isset($elevacao[$tempo]) ? $elevacao[$tempo] : '1',
                    'power' => isset($power[$tempo]) ? $power[$tempo] : '1',
                    'temp' => isset($temp[$tempo]) ? $temp[$tempo] : '1',
                    'batimento' => isset($batimento[$tempo]) ? $batimento[$tempo] : '1',
                    'cadencia' => isset($cadencia[$tempo]) ? $cadencia[$tempo] : '1',
                );
            } elseif (strtotime($tempo) >= $tempo_limite) {
                break;
            }
        }

        // Variável para armazenar o terceiro bloco (5 minutos após o segundo bloco)
        $tempo_init = $tempo_limite;
        $tempo_limite = strtotime($tempos[0]) + (36 * 60);
        $terceiro_bloco = array();
        foreach ($tempos as $tempo) {
            if (strtotime($tempo) >= $tempo_init && strtotime($tempo) < $tempo_limite) {
                $terceiro_bloco[$tempo] = array(
                    'local' => isset($local[$tempo]) ? $local[$tempo] : array('latitude' => '1', 'longitude' => '1'),
                    'elevacao' => isset($elevacao[$tempo]) ? $elevacao[$tempo] : '1',
                    'power' => isset($power[$tempo]) ? $power[$tempo] : '1',
                    'temp' => isset($temp[$tempo]) ? $temp[$tempo] : '1',
                    'batimento' => isset($batimento[$tempo]) ? $batimento[$tempo] : '1',
                    'cadencia' => isset($cadencia[$tempo]) ? $cadencia[$tempo] : '1',
                );
            } elseif (strtotime($tempo) >= $tempo_limite) {
                break;
            }
        }

        // Variável para armazenar o quarto bloco (10 minutos após o terceiro bloco)
        $tempo_init = $tempo_limite;
        $tempo_limite = strtotime($tempos[0]) + (46 * 60);
        $quarto_bloco = array();
        foreach ($tempos as $tempo) {
            if (strtotime($tempo) >= $tempo_init && strtotime($tempo) < $tempo_limite) {
                $quarto_bloco[$tempo] = array(
                    'local' => isset($local[$tempo]) ? $local[$tempo] : array('latitude' => '1', 'longitude' => '1'),
                    'elevacao' => isset($elevacao[$tempo]) ? $elevacao[$tempo] : '1',
                    'power' => isset($power[$tempo]) ? $power[$tempo] : '1',
                    'temp' => isset($temp[$tempo]) ? $temp[$tempo] : '1',
                    'batimento' => isset($batimento[$tempo]) ? $batimento[$tempo] : '1',
                    'cadencia' => isset($cadencia[$tempo]) ? $cadencia[$tempo] : '1',
                );
            } elseif (strtotime($tempo) >= $tempo_limite) {
                break;
            }
        }

        // Variável para armazenar o bloco principal (20 minutos após o quarto bloco)
        $tempo_init = $tempo_limite;
        $tempo_limite = strtotime($tempos[0]) + (66 * 60);
        $bloco_principal = array();
        foreach ($tempos as $tempo) {
            if (strtotime($tempo) >= $tempo_init && strtotime($tempo) < $tempo_limite) {
                $bloco_principal[$tempo] = array(
                    'local' => isset($local[$tempo]) ? $local[$tempo] : array('latitude' => '1', 'longitude' => '1'),
                    'elevacao' => isset($elevacao[$tempo]) ? $elevacao[$tempo] : '1',
                    'power' => isset($power[$tempo]) ? $power[$tempo] : '1',
                    'temp' => isset($temp[$tempo]) ? $temp[$tempo] : '1',
                    'batimento' => isset($batimento[$tempo]) ? $batimento[$tempo] : '1',
                    'cadencia' => isset($cadencia[$tempo]) ? $cadencia[$tempo] : '1',
                );
            } elseif (strtotime($tempo) >= $tempo_limite) {
                break;
            }
        }

        // Variável para armazenar o último bloco (5 minutos após o bloco principal)
        $tempo_init = $tempo_limite;
        $tempo_limite = strtotime($tempos[0]) + (71 * 60);
        $ultimo_bloco = array();
        foreach ($tempos as $tempo) {
            if (strtotime($tempo) >= $tempo_init && strtotime($tempo) < $tempo_limite) {
                $ultimo_bloco[$tempo] = array(
                    'local' => isset($local[$tempo]) ? $local[$tempo] : array('latitude' => '1', 'longitude' => '1'),
                    'elevacao' => isset($elevacao[$tempo]) ? $elevacao[$tempo] : '1',
                    'power' => isset($power[$tempo]) ? $power[$tempo] : '1',
                    'temp' => isset($temp[$tempo]) ? $temp[$tempo] : '1',
                    'batimento' => isset($batimento[$tempo]) ? $batimento[$tempo] : '1',
                    'cadencia' => isset($cadencia[$tempo]) ? $cadencia[$tempo] : '1',
                );
            } elseif (strtotime($tempo) >= $tempo_limite) {
                break;
            }
        }

        // Variável para armazenar o cooldown (10 minutos após o último bloco)
        $tempo_init = $tempo_limite;
        $tempo_limite = strtotime($tempos[0]) + (81 * 60);
        $cooldown = array();
        foreach ($tempos as $tempo) {
            if (strtotime($tempo) >= $tempo_init && strtotime($tempo) < $tempo_limite) {
                $cooldown[$tempo] = array(
                    'local' => isset($local[$tempo]) ? $local[$tempo] : array('latitude' => '1', 'longitude' => '1'),
                    'elevacao' => isset($elevacao[$tempo]) ? $elevacao[$tempo] : '1',
                    'power' => isset($power[$tempo]) ? $power[$tempo] : '1',
                    'temp' => isset($temp[$tempo]) ? $temp[$tempo] : '1',
                    'batimento' => isset($batimento[$tempo]) ? $batimento[$tempo] : '1',
                    'cadencia' => isset($cadencia[$tempo]) ? $cadencia[$tempo] : '1',
                );
            } elseif (strtotime($tempo) >= $tempo_limite) {
                break;
            }
        }

        // Variável para armazenar o restante
        $tempo_init = $tempo_limite;
        $restante = array();
        foreach ($tempos as $tempo) {
            if (strtotime($tempo) >= $tempo_init) {
                $restante[$tempo] = array(
                    'local' => isset($local[$tempo]) ? $local[$tempo] : array('latitude' => '1', 'longitude' => '1'),
                    'elevacao' => isset($elevacao[$tempo]) ? $elevacao[$tempo] : '1',
                    'power' => isset($power[$tempo]) ? $power[$tempo] : '1',
                    'temp' => isset($temp[$tempo]) ? $temp[$tempo] : '1',
                    'batimento' => isset($batimento[$tempo]) ? $batimento[$tempo] : '1',
                    'cadencia' => isset($cadencia[$tempo]) ? $cadencia[$tempo] : '1',
                );
            }
        }

        // Escreve os dados em arquivos txt
        writeToFile("warmup.txt", $warmup);
        writeToFile("primeiro_bloco.txt", $primeiro_bloco);
        writeToFile("segundo_bloco.txt", $segundo_bloco);
        writeToFile("terceiro_bloco.txt", $terceiro_bloco);
        writeToFile("quarto_bloco.txt", $quarto_bloco);
        writeToFile("bloco_principal.txt", $bloco_principal);
        writeToFile("ultimo_bloco.txt", $ultimo_bloco);
        writeToFile("cooldown.txt", $cooldown);
        writeToFile("restante.txt", $restante);

        foreach ($arquivos_segmentos as $arquivo) {
            $distancias_segmento = calcularDistanciaSegmento($arquivo);
        }

    } else {
        echo "Erro ao enviar arquivo.";
    }

?>
