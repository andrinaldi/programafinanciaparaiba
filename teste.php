<?php
// --- INÍCIO DO CÓDIGO PHP ---

$form_message = ''; // Para exibir mensagens de erro/sucesso no formulário
$numeroTelefone = '+5511959145787'; // Seu número de telefone [cite: 567]

// Função para sanitizar a entrada de dados (prevenir XSS ao exibir dados no HTML)
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data); // Remove barras invertidas desnecessárias
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8'); // Converte caracteres especiais em entidades HTML
    return $data;
}

// Função para validar e converter valores monetários
function validate_currency($amount_str) {
    // Remove "R$", espaços, e separadores de milhar (.), depois substitui vírgula decimal por ponto.
    $amount_str_cleaned = str_replace('R$', '', $amount_str);
    $amount_str_cleaned = preg_replace('/\s+/', '', $amount_str_cleaned); // Remove todos os espaços
    $amount_str_cleaned = str_replace('.', '', $amount_str_cleaned);    // Remove separador de milhar
    $amount_str_cleaned = str_replace(',', '.', $amount_str_cleaned);   // Substitui vírgula decimal por ponto
    
    if ($amount_str_cleaned === '') {
        return null; // Permite campos opcionais vazios como 'entrada'
    }

    if (!is_numeric($amount_str_cleaned) || floatval($amount_str_cleaned) < 0) {
        return false; // Número inválido ou negativo
    }
    return floatval($amount_str_cleaned);
}

// Verifica se o formulário foi submetido via método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitiza os dados recebidos do formulário
    $nome = isset($_POST['nome']) ? sanitize_input($_POST['nome']) : '';
    $modelo = isset($_POST['modelo']) ? sanitize_input($_POST['modelo']) : '';
    $valor_str = isset($_POST['valor']) ? $_POST['valor'] : '';     // Mantém string original para validação
    $entrada_str = isset($_POST['entrada']) ? $_POST['entrada'] : ''; // Mantém string original para validação

    $errors = []; // Array para armazenar mensagens de erro

    // Validações no lado do servidor
    if (empty($nome)) {
        $errors[] = "Nome Completo é obrigatório.";
    }
    if (empty($modelo)) {
        $errors[] = "Modelo do Veículo é obrigatório.";
    }

    $valor_num = validate_currency($valor_str);
    if ($valor_num === false || $valor_num === null) { // valor_num não pode ser null aqui, pois é obrigatório
        $errors[] = "Valor (R$) é obrigatório e deve ser um número válido (Ex: 50.000,00).";
    }
    
    $entrada_num = null;
    if (!empty($entrada_str)) { // Só valida 'entrada' se não estiver vazia
        $entrada_num = validate_currency($entrada_str);
        if ($entrada_num === false) { // Se validate_currency retornou false, o valor é inválido
            $errors[] = "Entrada Desejada (R$) deve ser um número válido (Ex: 10.000,00) se preenchida.";
        }
    }

    // Se não houver erros, processa os dados
    if (empty($errors)) {
        // Constrói a mensagem para o WhatsApp
        $mensagem = "Olá, tenho interesse no seguinte veículo:\n\n"; // [cite: 568]
        $mensagem .= "Nome Completo: " . $nome . "\n";               // [cite: 569]
        $mensagem .= "Modelo do Veículo: " . $modelo . "\n";         // [cite: 569]
        $mensagem .= "Valor: R$" . number_format($valor_num, 2, ',', '.') . "\n"; // [cite: 569]
        
        if ($entrada_num !== null) {
            $mensagem .= "Entrada Desejada: R$" . number_format($entrada_num, 2, ',', '.') . "\n"; // [cite: 570]
        } else {
            $mensagem .= "Entrada Desejada: Não informada\n"; // [cite: 571]
        }

        // Cria o link do WhatsApp
        $whatsapp_link = "https://wa.me/" . $numeroTelefone . "?text=" . urlencode($mensagem); // [cite: 572]

        // === PONTO IMPORTANTE SOBRE SQL INJECTION ===
        // Se você fosse salvar estes dados em um banco de dados SQL,
        // aqui você usaria PDO ou MySQLi com prepared statements. Por exemplo:
        //
        // $dsn = "mysql:host=localhost;dbname=sua_base_de_dados;charset=utf8mb4";
        // $options = [ PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, /* ... outras opções ... */ ];
        // try {
        //     $pdo = new PDO($dsn, "usuario", "senha", $options);
        //     $stmt = $pdo->prepare("INSERT INTO leads (nome, modelo, valor, entrada) VALUES (?, ?, ?, ?)");
        //     $stmt->execute([$nome, $modelo, $valor_num, $entrada_num]);
        //     // $form_message = "Dados salvos com sucesso!"; // Exemplo
        // } catch (PDOException $e) {
        //     // $form_message = "Erro ao salvar dados: " . $e->getMessage(); // Tratar erro apropriadamente
        // }
        // Como não estamos usando banco de dados agora, esta parte é apenas um exemplo ilustrativo.

        // Redireciona para o WhatsApp
        header("Location: " . $whatsapp_link);
        exit; // Garante que o script pare após o redirecionamento

    } else {
        // Se houver erros, monta a mensagem de erro para ser exibida no formulário
        $form_message = "<strong>Por favor, corrija os seguintes erros:</strong><br>" . implode("<br>", $errors);
    }
}
// --- FIM DO CÓDIGO PHP ---
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulário de Interesse em Veículo</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, rgba(240, 100, 0, 0.3), rgba(255, 140, 0, 0.3)), url('testebg.jpg'); /* Gradiente com opacidade e imagem */ /* [cite: 539] */
            background-repeat: no-repeat; /* [cite: 540] */
            background-size: cover; /* [cite: 541] */
            background-position: center; /* [cite: 541] */
            background-blend-mode: overlay; /* [cite: 541] */
            margin: 0; /* [cite: 541] */
            padding: 20px; /* [cite: 541] */
            display: flex; /* [cite: 541] */
            justify-content: center; /* [cite: 541] */
            align-items: center; /* [cite: 541] */
            min-height: 100vh; /* [cite: 541] */
            color: #333; /* [cite: 541] */
            box-sizing: border-box; /* [cite: 542] */
        }
        .container { /* [cite: 543] */
            background-color: rgba(255, 255, 255, 0.9); /* [cite: 543] */
            padding: 30px; /* [cite: 543] */
            border-radius: 10px; /* [cite: 543] */
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1); /* [cite: 543] */
            width: 95%; /* [cite: 543] */
            max-width: 400px; /* [cite: 544] */
        }
        h1 { /* [cite: 545] */
            color: #f06400; /* [cite: 545] */
            text-align: center; /* [cite: 545] */
            margin-bottom: 25px; /* [cite: 545] */
            font-size: 2em; /* [cite: 545] */
        }
        div.form-group { /* Adicionado para espaçamento e estrutura */
            margin-bottom: 15px; /* [cite: 546] */
        }
        label { /* [cite: 547] */
            display: block; /* [cite: 547] */
            font-size: 1.1em; /* [cite: 547] */
            color: #555; /* [cite: 547] */
            margin-bottom: 5px; /* [cite: 547] */
            font-weight: bold; /* [cite: 547] */
        }
        input[type="text"],
        input[type="number"] { /* Embora estejamos usando type="text" para moeda, manteremos a regra CSS geral */ /* [cite: 548] */
            width: 100%; /* Ajustado para ocupar 100% do contêiner do div */
            padding: 12px; /* [cite: 548] */
            border: 1px solid #ddd; /* [cite: 548] */
            border-radius: 5px; /* [cite: 548] */
            font-size: 1em; /* [cite: 549] */
            box-sizing: border-box; /* [cite: 549] */
        }
        input::placeholder { /* [cite: 550] */
            color: #aaa; /* [cite: 550] */
            opacity: 0.8; /* [cite: 550] */
        }
        button { /* [cite: 551] */
            background-color: #f06400; /* [cite: 551] */
            color: white; /* [cite: 551] */
            padding: 15px; /* [cite: 551] */
            border: none; /* [cite: 551] */
            border-radius: 8px; /* [cite: 551] */
            cursor: pointer; /* [cite: 551] */
            font-size: 1.1em; /* [cite: 551] */
            transition: background-color 0.3s ease; /* [cite: 552] */
            width: 100%; /* [cite: 552] */
            box-sizing: border-box; /* [cite: 552] */
        }
        button:hover {
            background-color: #e05a00; /* [cite: 553] */
        }
        .error-message { /* Estilo para a mensagem de erro do PHP */
            color: red;
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid red;
            border-radius: 5px;
            background-color: #ffe0e0;
        }

        /* Media Queries permanecem as mesmas */ /* */
         @media (max-width: 600px) { /* [cite: 554] */
            body { padding: 15px; } /* [cite: 554] */
            .container { padding: 20px; border-radius: 8px; } /* [cite: 555] */
            h1 { font-size: 1.8em; margin-bottom: 20px; } /* [cite: 556] */
            label { font-size: 1em; } /* [cite: 557] */
            input[type="text"], input[type="number"] { font-size: 0.9em; padding: 10px; } /* [cite: 558] */
            button { font-size: 1em; padding: 12px; border-radius: 6px; } /* [cite: 559] */
        }
        @media (max-width: 375px) { /* [cite: 560] */
            h1 { font-size: 1.6em; } /* [cite: 560] */
            label { font-size: 0.9em; } /* [cite: 561] */
            input[type="text"], input[type="number"] { font-size: 0.85em; } /* [cite: 562] */
            button { font-size: 0.9em; } /* [cite: 563] */
        }
    </style>
</head>
<body>
    <div class="container"> <h1>Preencha o Formulário para Mais Informações</h1> <?php if (!empty($form_message)): ?>
            <div class="error-message"><?php echo $form_message; ?></div>
        <?php endif; ?>

        <form id="formInteresse" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"> <div class="form-group">
                <label for="nome">Nome Completo:</label>
                <input type="text" id="nome" name="nome" required value="<?php echo isset($_POST['nome']) && !empty($errors) ? htmlspecialchars($_POST['nome']) : ''; ?>"> </div>
            <div class="form-group">
                <label for="modelo">Modelo do Veículo:</label>
                <input type="text" id="modelo" name="modelo" required value="<?php echo isset($_POST['modelo']) && !empty($errors) ? htmlspecialchars($_POST['modelo']) : ''; ?>"> </div>
            <div class="form-group">
                <label for="valor">Valor (R$):</label>
                <input type="text" id="valor" name="valor" placeholder="Ex: 50.000,00" required value="<?php echo isset($_POST['valor']) && !empty($errors) ? htmlspecialchars($_POST['valor']) : ''; ?>"> </div>
            <div class="form-group">
                <label for="entrada">Entrada Desejada (R$):</label>
                <input type="text" id="entrada" name="entrada" placeholder="Ex: 10.000,00" value="<?php echo isset($_POST['entrada']) && !empty($errors) ? htmlspecialchars($_POST['entrada']) : ''; ?>"> </div>
            <button type="submit">Enviar para WhatsApp</button> </form>
    </div>

    <script>
        // Validação client-side para feedback rápido (UX)
        // A validação principal e de segurança ocorre no servidor (PHP)
        document.getElementById('formInteresse').addEventListener('submit', function(event) {
            const nomeInput = document.getElementById('nome');
            const modeloInput = document.getElementById('modelo');
            const valorInput = document.getElementById('valor');
            // A 'entrada' é opcional, então não precisa de validação client-side tão estrita aqui,
            // mas pode ser adicionada se desejado.

            let valid = true;
            let message = [];

            if (nomeInput.value.trim() === '') {
                message.push('Nome Completo é obrigatório.');
                valid = false;
            }
            if (modeloInput.value.trim() === '') {
                message.push('Modelo do Veículo é obrigatório.');
                valid = false;
            }
            if (valorInput.value.trim() === '') {
                message.push('Valor (R$) é obrigatório.');
                valid = false;
            } else {
                // Validação básica de formato para valor (permite números, ponto, vírgula)
                // Uma regex mais simples para client-side, a validação robusta está no PHP
                const valorRegex = /^[0-9.,\sR$]*$/; 
                if (!valorRegex.test(valorInput.value.trim())) {
                    message.push('Valor (R$) parece ter caracteres inválidos.');
                    valid = false;
                }
            }
            
            // Validação para entrada, se preenchida
            const entradaInput = document.getElementById('entrada');
            if (entradaInput.value.trim() !== '') {
                const entradaRegex = /^[0-9.,\sR$]*$/;
                 if (!entradaRegex.test(entradaInput.value.trim())) {
                    message.push('Entrada Desejada (R$) parece ter caracteres inválidos.');
                    valid = false;
                }
            }

            if (!valid) {
                alert('Por favor, corrija os seguintes problemas:\n- ' + message.join('\n- '));
                event.preventDefault(); // Impede o envio do formulário se houver erro no client-side
            }
            // Se passar na validação client-side, o formulário será enviado ao PHP
        });
    </script>
</body>
</html>