<?php
include('valida_sessao.php');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quem Somos — CineVerso</title>
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
        <h1>Quem somos? - CineVerso</h1>
        <p>Apresentação da Empresa e do Projeto · Santo André – 2026</p>
    </div>

    <div class="filters">
        <h3>1. Quem somos</h3>
        <p class="mb-2">
            A <strong>CineVerso</strong> é uma empresa fictícia de tecnologia e entretenimento criada em 2026 por cinco amigos que identificaram uma dificuldade comum entre os fãs de séries: encontrar informações organizadas sobre títulos, gêneros, temporadas e diretores em um único lugar.
        </p>
        <p>
            Inspirados por grandes plataformas de entretenimento, como a Netflix, os fundadores decidiram criar uma solução própria. A proposta da CineVerso é reunir informações sobre produções audiovisuais em um catálogo digital simples, prático e fácil de utilizar.
        </p>

        <h3>2. Nossa história</h3>
        <p class="mb-2">
            A ideia da CineVerso surgiu quando seus fundadores perceberam que, com a grande quantidade de séries disponíveis, muitas pessoas acabam procurando informações em vários sites diferentes. Além disso, nem sempre é fácil descobrir quem dirigiu uma produção ou consultar seus principais dados.
        </p>
        <p>
            Diante dessa necessidade, o grupo decidiu transformar a ideia em um projeto: desenvolver um sistema que permita cadastrar, consultar, editar e excluir informações sobre séries e seus respectivos diretores. Assim nasceu a CineVerso, com o propósito de facilitar o acesso e a organização dessas informações.
        </p>

        <h3>3. Nosso produto</h3>
        <p class="mb-2">
            O principal produto da CineVerso é um sistema de cadastro e consulta de séries e diretores. Ele funciona como um catálogo digital, reunindo em um só ambiente informações importantes de cada produção:
        </p>
        <ul style="margin-left: 1.5rem; margin-bottom: 1rem;">
            <li><strong>Cadastro de séries:</strong> registrar título, gênero, ano de lançamento, quantidade de temporadas e sinopse.</li>
            <li><strong>Cadastro de diretores:</strong> armazenar os dados dos profissionais relacionados às produções.</li>
            <li><strong>Relacionamento:</strong> associar cada série ao seu respectivo diretor cadastrado.</li>
            <li><strong>Pesquisa:</strong> localizar séries pelo título e consultar os registros disponíveis.</li>
            <li><strong>Gerenciamento:</strong> editar e excluir informações quando necessário.</li>
        </ul>

        <h3>4. Missão, visão e valores</h3>
        <ul style="margin-left: 1.5rem; margin-bottom: 1rem;">
            <li><strong>Missão:</strong> facilitar a organização e a consulta de informações sobre séries e diretores.</li>
            <li><strong>Visão:</strong> tornar-se uma referência em catálogo digital de informações sobre produções audiovisuais.</li>
            <li><strong>Valores:</strong> inovação, organização, praticidade, acessibilidade e compromisso com a experiência do usuário.</li>
        </ul>

        <h3>5. Como a CineVerso pretende crescer</h3>
        <p class="mb-2">
            Em versões futuras, a CineVerso poderá ampliar seu catálogo com recursos como imagens e pôsteres, avaliações dos usuários, filtros por gênero e ano, informações sobre elenco e recomendações de séries. Essas melhorias poderão tornar a plataforma ainda mais completa.
        </p>

        <div class="login-hint mt-3">
            <strong>CINEVERSO — O universo das séries em um só lugar.</strong>
        </div>
    </div>

</div>
    <?php include('footer.php'); ?>
</body>
</html>
