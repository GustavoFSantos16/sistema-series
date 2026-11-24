<?php
include('valida_sessao.php');
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Principal — CineVerso</title>
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

        <div class="logo-marca">
            <h1>🍿 CineVerso</h1>
            <small>O universo das séries em um só lugar.</small>
            <p class="welcome-text">Olá, <strong><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong>! O que deseja fazer?</p>
        </div>

        <div class="menu-grid">
            <div class="menu-card">
                <a href="cadastro_diretor.php">
                    <div class="menu-card-icon">🎥</div>
                    <div class="menu-card-title">Gerenciar Diretores</div>
                    <div class="menu-card-desc">Cadastre e edite diretores</div>
                </a>
            </div>

            <div class="menu-card">
                <a href="cadastro_serie.php">
                    <div class="menu-card-icon">🎞️</div>
                    <div class="menu-card-title">Cadastrar Série</div>
                    <div class="menu-card-desc">Adicione séries ao sistema</div>
                </a>
            </div>
        </div>

    </div>
    <?php include('footer.php'); ?>
</body>

</html>