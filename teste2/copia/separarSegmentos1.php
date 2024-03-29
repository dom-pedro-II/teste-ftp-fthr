<?php
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
  
   // Variável para armazenar o último bloco (15 minutos após o principal)
   $tempo_init = $tempo_limite;
   $tempo_limite = strtotime($tempos[0]) + (81 * 60);
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

   // Variável para armazenar o cooldown (10 minutos finais)
   $tempo_init = $tempo_limite;
    $tempo_limite = strtotime($tempos[0]) + (91 * 60);
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

    // Variável para armazenar o restante do tempo após o cooldown
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

   // Função para escrever informações em arquivo
   function escrever_arquivo($nome_arquivo, $dados)
   {
       $arquivo = fopen($nome_arquivo, "w");
       foreach ($dados as $index => $info) {
           fwrite($arquivo, "Tempo[$index]: '$index'\n");
           fwrite($arquivo, "Local[$index]: " . $info['local']['latitude'] . ", " . $info['local']['longitude'] . "\n");
           fwrite($arquivo, "Elevação[$index]: " . $info['elevacao'] . "\n");
           fwrite($arquivo, "Power[$index]: " . $info['power'] . "\n");
           fwrite($arquivo, "Temperatura[$index]: " . $info['temp'] . "\n");
           fwrite($arquivo, "Batimento[$index]: " . $info['batimento'] . "\n");
           fwrite($arquivo, "Cadência[$index]: " . $info['cadencia'] . "\n\n");
       }
       fclose($arquivo);
   }

   // Escreve as informações do warmup no arquivo warmup.txt
   escrever_arquivo("warmup.txt", $warmup);

   // Escreve as informações do primeiro bloco no arquivo 1bloco.txt
   escrever_arquivo("1bloco.txt", $primeiro_bloco);

   // Escreve as informações do segundo bloco no arquivo 2bloco.txt
   escrever_arquivo("2bloco.txt", $segundo_bloco);

   // Escreve as informações do terceiro bloco no arquivo 3bloco.txt
   escrever_arquivo("3bloco.txt", $terceiro_bloco);

   // Escreve as informações do quarto bloco no arquivo 4bloco.txt
   escrever_arquivo("4bloco.txt", $quarto_bloco);

   // Escreve as informações do bloco principal no arquivo bloco_principal.txt
   escrever_arquivo("bloco_principal.txt", $bloco_principal);

   // Escreve as informações do último bloco no arquivo ultimobloco.txt
   escrever_arquivo("ultimobloco.txt", $ultimo_bloco);

   // Escreve as informações do cooldown no arquivo cooldown.txt
   escrever_arquivo("cooldown.txt", $cooldown);

   // Escreve as informações do restante do tempo no arquivo restante.txt
    escrever_arquivo("restante.txt", $restante);


   echo "Informações do warmup salvas em 'warmup.txt'. <a href='warmup.txt'>Baixar warmup</a><br>";
   echo "Informações do primeiro bloco salvas em '1bloco.txt'. <a href='1bloco.txt'>Baixar 1bloco</a><br>";
   echo "Informações do segundo bloco salvas em '2bloco.txt'. <a href='2bloco.txt'>Baixar 2bloco</a><br>";
   echo "Informações do terceiro bloco salvas em '3bloco.txt'. <a href='3bloco.txt'>Baixar 3bloco</a><br>";
   echo "Informações do quarto bloco salvas em '4bloco.txt'. <a href='4bloco.txt'>Baixar 4bloco</a><br>";
   echo "Informações do bloco principal salvas em 'bloco_principal.txt'. <a href='bloco_principal.txt'>Baixar bloco principal</a><br>";
   echo "Informações do último bloco salvas em 'ultimobloco.txt'. <a href='ultimobloco.txt'>Baixar ultimobloco</a><br>";
   echo "Informações do cooldown salvas em 'cooldown.txt'. <a href='cooldown.txt'>Baixar cooldown</a><br>";
   echo "Informações do restante do tempo salvas em 'restante.txt'. <a href='restante.txt'>Baixar restante</a><br>";

} else {
   echo "Erro ao fazer upload do arquivo GPX.";
}
