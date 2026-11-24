<?php
include('valida_sessao.php');

$mensagem = null;
$class    = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome    = trim($_POST['nome'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $assunto = trim($_POST['assunto'] ?? '');
    $texto   = trim($_POST['mensagem'] ?? '');

    if (empty($nome) || empty($email) || empty($assunto) || empty($texto)) {
        $mensagem = "Por favor, preencha todos os campos obrigatórios.";
        $class    = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem = "Informe um endereço de e-mail válido.";
        $class    = "error";
    } else {
        $mensagem = "Mensagem enviada com sucesso! A equipe da CineVerso agradece o seu contato, " . htmlspecialchars($nome) . ".";
        $class    = "success";
        $_POST    = [];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entre em Contato — CineVerso</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 100 100%27><text y=%27.9em%27 font-size=%2790%27>🎬</text></svg>">
    <link rel="stylesheet" href="styles.css?v=11">
</head>

<body>

    <div class="container">
        <div class="topbar">
            <img src="img/logo-cineverso.png" alt="logo-cineverso" class="logo">

            <nav class="nav-links">
                <a href="index.php">Home</a>
                <a href="quemSomos.php">Quem somos</a>
                <a href="listagem_series.php"><strong>Catálogo</strong></a>
                <a href="entreContato.php">Entre em contato</a>
            </nav>

            <a href="logout.php" class="btn-back">🚪 Sair</a>
        </div>
        
        <div class="page-header">
            <h1>Fale Conosco</h1>
            <p>Tem alguma dúvida, sugestão de série ou feedback sobre a plataforma? Envie uma mensagem para nossa equipe.</p>
        </div>

        <?php if (isset($mensagem)): ?>
            <div class="message <?php echo $class; ?>">
                <?php echo $class === 'success' ? '✅' : '⚠️'; ?> <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <div class="form-grid">

                <div class="form-group">
                    <label for="nome">Seu Nome <span style="color:var(--red)">*</span></label>
                    <input type="text" id="nome" name="nome"
                        value="<?php echo htmlspecialchars((string)($_POST['nome'] ?? '')); ?>"
                        required placeholder="Digite seu nome completo">
                </div>

                <div class="form-group">
                    <label for="email">Seu E-mail <span style="color:var(--red)">*</span></label>
                    <input type="text" id="email" name="email"
                        value="<?php echo htmlspecialchars((string)($_POST['email'] ?? '')); ?>"
                        required placeholder="seuemail@exemplo.com">
                </div>

                <div class="form-group full">
                    <label for="assunto">Assunto <span style="color:var(--red)">*</span></label>
                    <select id="assunto" name="assunto" required>
                        <option value="">Selecione o motivo do contato</option>
                        <option value="Sugestão de Série" <?php echo (($_POST['assunto'] ?? '') === 'Sugestão de Série') ? 'selected' : ''; ?>>Sugestão de Série ou Diretor</option>
                        <option value="Dúvida sobre o Sistema" <?php echo (($_POST['assunto'] ?? '') === 'Dúvida sobre o Sistema') ? 'selected' : ''; ?>>Dúvida sobre o Sistema</option>
                        <option value="Reportar Problema" <?php echo (($_POST['assunto'] ?? '') === 'Reportar Problema') ? 'selected' : ''; ?>>Reportar Problema Técnico</option>
                        <option value="Elogios e Feedback" <?php echo (($_POST['assunto'] ?? '') === 'Elogios e Feedback') ? 'selected' : ''; ?>>Elogios e Feedback</option>
                    </select>
                </div>

                <div class="form-group full">
                    <label for="mensagem">Mensagem <span style="color:var(--red)">*</span></label>
                    <textarea id="mensagem" name="mensagem" required
                        placeholder="Escreva sua mensagem detalhada aqui…"><?php
                                                                            echo htmlspecialchars((string)($_POST['mensagem'] ?? ''));
                                                                            ?></textarea>
                </div>

            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">📨 Enviar Mensagem</button>
                <a href="index.php" class="btn-back">✖ Cancelar</a>
            </div>
        </form>

        <div class="filters">
            <h3>📍 Informações de Atendimento</h3>
            <p class="mb-1"><strong>Empresa:</strong> CineVerso Tecnologia e Entretenimento</p>
            <p class="mb-1"><strong>Localização:</strong> Santo André – SP, 2026</p>
            <p class="mb-1"><strong>E-mail de suporte:</strong> contato@cineverso.com.br</p>
            <p><strong>Horário de atendimento:</strong> Segunda a Sexta, das 09h às 18h</p>
        </div>

    </div>
    <?php include('footer.php'); ?>
</body>

</html>