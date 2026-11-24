<?php

include('valida_sessao.php');
include('conexao.php');

// Inicialização das variáveis
$mensagem = null;
$tipo_msg = null;

$where  = "1=1";
$params = [];
$types  = "";

if (!empty($_GET['diretor'])) {
    $where   .= " AND d.id = ?";
    $params[] = intval($_GET['diretor']);
    $types   .= "i";
}

if (!empty($_GET['genero'])) {
    $where   .= " AND s.genero LIKE ?";
    $params[] = "%" . $_GET['genero'] . "%";
    $types   .= "s";
}

if (!empty($_GET['busca'])) {
    $where   .= " AND (s.titulo LIKE ? OR s.plataforma LIKE ? OR d.nome_diretor LIKE ?)";
    $busca    = "%" . $_GET['busca'] . "%";
    $params[] = $busca; // para s.titulo
    $params[] = $busca; // para s.plataforma
    $params[] = $busca; // para d.nome_diretor
    $types   .= "sss";
}

// Carrega os selects de filtro
$diretores = $conn->query("SELECT id, nome_diretor FROM diretores ORDER BY nome_diretor");
$generos   = $conn->query("SELECT DISTINCT genero FROM series WHERE genero IS NOT NULL AND genero != '' ORDER BY genero");

if (isset($_GET['delete_id'])) {
    $id   = intval($_GET['delete_id']);
    $stmt = $conn->prepare("SELECT imagem_capa FROM series WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $arq  = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($arq && !empty($arq['imagem_capa']) && file_exists($arq['imagem_capa'])) {
        unlink($arq['imagem_capa']);
    }

    $stmt = $conn->prepare("DELETE FROM series WHERE id = ?");
    $stmt->bind_param("i", $id);
    $tipo_msg = $stmt->execute() ? "success" : "error";
    $mensagem = $tipo_msg === "success" ? "Série excluída!" : "Erro: " . $stmt->error;
    $stmt->close();
}

$sql = "SELECT s.*, d.nome_diretor AS diretor_nome
        FROM series s
        LEFT JOIN diretores d ON s.id_diretor = d.id
        WHERE $where
        ORDER BY d.nome_diretor, s.titulo";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $series = $stmt->get_result();
} else {
    $series = $conn->query($sql);
}

// Mantém filtros atuais na URL ao excluir, mas remove o delete_id anterior
$filtros_ativos = $_GET;
unset($filtros_ativos['delete_id']);
$query_filtros = !empty($filtros_ativos) ? '&' . http_build_query($filtros_ativos) : '';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Séries — CineVerso</title>
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

    <div class="page-header-bar">
        <h2>📺 Catálogo de Séries</h2>
        <div class="header-actions">
            <a href="cadastro_serie.php" class="btn-primary">➕ Nova Série</a>
        </div>
    </div>

    <!-- -----------------------------------------------
         FILTROS
    ----------------------------------------------- -->
    <div class="filters">
        <form method="get" class="filter-form">
            <div class="filter-group">
                <div class="filter-item">
                    <label for="busca">Busca geral</label>
                    <input type="text" name="busca" id="busca"
                           value="<?php echo htmlspecialchars($_GET['busca'] ?? ''); ?>"
                           placeholder="Título, plataforma ou diretor…">
                </div>
                <div class="filter-item">
                    <label for="diretor">Diretor</label>
                    <select name="diretor" id="diretor">
                        <option value="">Todos os diretores</option>
                        <?php if ($diretores): ?>
                            <?php while ($d = $diretores->fetch_assoc()): ?>
                                <option value="<?php echo $d['id']; ?>"
                                    <?php echo (isset($_GET['diretor']) && $_GET['diretor'] == $d['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($d['nome_diretor']); ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="filter-item">
                    <label for="genero">Gênero</label>
                    <select name="genero" id="genero">
                        <option value="">Todos os gêneros</option>
                        <?php if ($generos): ?>
                            <?php while ($g = $generos->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($g['genero']); ?>"
                                    <?php echo (isset($_GET['genero']) && $_GET['genero'] == $g['genero']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($g['genero']); ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn-primary">🔍 Filtrar</button>
                <?php if (!empty($filtros_ativos)): ?>
                    <a href="listagem_series.php" class="btn-back">✖ Limpar</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Feedback de operações -->
    <?php if (isset($mensagem)): ?>
        <div class="message <?php echo $tipo_msg; ?>">
            <?php echo $tipo_msg === 'success' ? '✅' : '⚠️'; ?> <?php echo $mensagem; ?>
        </div>
    <?php endif; ?>

    <!-- TABELA DE SÉRIES -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th class="col-capa">Capa</th>
                    <th>Título</th>
                    <th>Diretor</th>
                    <th>Gênero</th>
                    <th>Ano</th>
                    <th>Temp. / Ep.</th>
                    <th>Plataforma</th>
                    <th class="col-acoes">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($series && $series->num_rows > 0): ?>
                    <?php while ($row = $series->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?php if (!empty($row['imagem_capa'])): ?>
                                    <img src="<?php echo htmlspecialchars($row['imagem_capa']); ?>"
                                         alt="Capa" class="table-thumbnail" loading="lazy">
                                <?php else: ?>
                                    <div class="no-image">Sem<br>capa</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($row['titulo']); ?></strong>
                                <?php if (!empty($row['sinopse'])): ?>
                                    <small><?php echo htmlspecialchars(mb_strimwidth($row['sinopse'], 0, 80, '…')); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['diretor_nome'] ?? '') ?: '—'; ?></td>
                            <td><?php echo htmlspecialchars($row['genero'] ?? '') ?: '—'; ?></td>
                            <td><?php echo htmlspecialchars($row['ano_lancamento'] ?? '') ?: '—'; ?></td>
                            <td>
                                <?php
                                    $temp = !empty($row['temporadas']) ? $row['temporadas'] . ' temp.' : '';
                                    $ep   = !empty($row['episodios'])  ? $row['episodios'] . ' ep.'   : '';
                                    echo ($temp || $ep) ? trim("$temp $ep") : '—';
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['plataforma'] ?? '') ?: '—'; ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="cadastro_serie.php?edit_id=<?php echo $row['id']; ?>"
                                       class="btn-edit">✏️ Editar</a>
                                    <a href="?delete_id=<?php echo $row['id']; ?><?php echo $query_filtros; ?>"
                                       onclick="return confirm('Excluir <?php echo addslashes($row['titulo']); ?>?')"
                                       class="btn-delete">🗑️</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="empty-cell">
                            <?php echo !empty($filtros_ativos) ? 'Nenhuma série encontrada com esses filtros.' : 'Nenhuma série cadastrada ainda.'; ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>
    <?php include('footer.php'); ?>
</body>
</html>