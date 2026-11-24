<?php

include('valida_sessao.php');
include('conexao.php');

$mensagem = null;
$class    = null;

// -----------------------------------------------
// FUNÇÃO: Redimensionar e salvar imagem
// -----------------------------------------------
function redimensionarESalvarImagem(array $arquivo, $largura = 300, $altura = 300)
{
    $dir          = "img/";
    $nome         = uniqid() . '_' . basename($arquivo["name"]);
    $caminho      = $dir . $nome;
    $tipo         = strtolower(pathinfo($caminho, PATHINFO_EXTENSION));

    // Validações
    if (!getimagesize($arquivo["tmp_name"]))         return "Arquivo não é uma imagem válida.";
    if ($arquivo["size"] > 5000000)                  return "Imagem muito grande. Máximo: 5 MB.";
    if (!in_array($tipo, ["jpg", "jpeg", "png"]))    return "Apenas JPG e PNG são aceitos.";

    // Carrega, redimensiona e salva
    $original      = imagecreatefromstring(file_get_contents($arquivo["tmp_name"]));
    $w0 = imagesx($original);
    $h0 = imagesy($original);
    $ratio         = min($largura / $w0, $altura / $h0);
    $nw            = (int)($w0 * $ratio);
    $nh            = (int)($h0 * $ratio);
    $nova          = imagecreatetruecolor($nw, $nh);
    imagecopyresampled($nova, $original, 0, 0, 0, 0, $nw, $nh, $w0, $h0);

    ($tipo === "png") ? imagepng($nova, $caminho) : imagejpeg($nova, $caminho, 90);

    imagedestroy($original);
    imagedestroy($nova);
    return $caminho;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_diretor    = $_POST['id_diretor']    ?? '';
    $nome_diretor  = $_POST['nome_diretor']  ?? '';
    $nacionalidade = $_POST['nacionalidade'] ?? '';

    $imagem = "";
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
        $upload = redimensionarESalvarImagem($_FILES['imagem']);
        if (str_starts_with($upload, 'img/')) {
            $imagem = $upload;
        } else {
            $mensagem = $upload;
            $class    = "error";
        }
    }

    if (!isset($class)) {
        if ($id_diretor) {
            // UPDATE — só troca a imagem se uma nova foi enviada
            $sql    = "UPDATE diretores SET nome_diretor=?, nacionalidade=?";
            $params = [$nome_diretor, $nacionalidade];
            if ($imagem) {
                $sql .= ", imagem=?";
                $params[] = $imagem;
            }
            $sql .= " WHERE id_diretor=?";
            $params[] = intval($id_diretor);
            $stmt  = $conn->prepare($sql);
            $tipos = str_repeat('s', count($params) - 1) . 'i';
            $stmt->bind_param($tipos, ...$params);
            $mensagem = "Diretor atualizado com sucesso!";
        } else {
            // INSERT
            $stmt = $conn->prepare("INSERT INTO diretores (nome_diretor, nacionalidade, imagem) VALUES (?,?,?)");
            $stmt->bind_param("sss", $nome_diretor, $nacionalidade, $imagem);
            $mensagem = "Diretor cadastrado com sucesso!";
        }
        $class = $stmt->execute() ? "success" : "error";
        if ($class === "error") $mensagem = "Erro: " . $stmt->error;
        $stmt->close();
    }
}

// -----------------------------------------------
// DELETE
// -----------------------------------------------
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);

    $chk = $conn->prepare("SELECT COUNT(*) AS total FROM series WHERE id_diretor = ?");
    $chk->bind_param("i", $id);
    $chk->execute();
    $total = $chk->get_result()->fetch_assoc()['total'];
    $chk->close();

    if ($total > 0) {
        $mensagem = "Não é possível excluir: Diretor possui séries cadastradas.";
        $class    = "error";
    } else {
        $stmtImg = $conn->prepare("SELECT imagem FROM diretores WHERE id_diretor = ?");
        $stmtImg->bind_param("i", $id);
        $stmtImg->execute();
        $arq = $stmtImg->get_result()->fetch_assoc();
        $stmtImg->close();

        if ($arq && !empty($arq['imagem']) && file_exists($arq['imagem'])) {
            unlink($arq['imagem']);
        }

        $stmt = $conn->prepare("DELETE FROM diretores WHERE id_diretor = ?");
        $stmt->bind_param("i", $id);
        $class    = $stmt->execute() ? "success" : "error";
        $mensagem = $class === "success" ? "Diretor excluído!" : "Erro: " . $stmt->error;
        $stmt->close();
    }
}

$diretor = null;
if (isset($_GET['edit_id'])) {
    $eid  = intval($_GET['edit_id']);
    $stmt = $conn->prepare("SELECT * FROM diretores WHERE id_diretor = ?");
    $stmt->bind_param("i", $eid);
    $stmt->execute();
    $diretor = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$diretores = $conn->query("SELECT * FROM diretores ORDER BY nome_diretor");
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $diretor ? 'Editar Diretor' : 'Novo Diretor'; ?> — CineVerso</title>
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

        <div class="page-header-bar center">
            <h2><?php echo $diretor ? '✏️ Editar Diretor' : '🎥 Novo Diretor'; ?></h2>
        </div>

        <?php if (isset($mensagem)): ?>
            <div class="message <?php echo $class; ?>">
                <?php echo $class === 'success' ? '✅' : '⚠️'; ?> <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="" enctype="multipart/form-data">

            <input type="hidden" name="id_diretor" value="<?php echo $diretor['id_diretor'] ?? ''; ?>">

            <div class="form-grid">

                <div class="form-group">
                    <label for="nome_diretor">Nome do Diretor <span style="color:var(--red)">*</span></label>
                    <input type="text" id="nome_diretor" name="nome_diretor"
                        value="<?php echo htmlspecialchars($diretor['nome_diretor'] ?? ''); ?>"
                        required placeholder="Ex: Guillermo del Toro, Vince Gilligan…">
                </div>

                <div class="form-group">
                    <label for="nacionalidade">Nacionalidade</label>
                    <input type="text" id="nacionalidade" name="nacionalidade"
                        value="<?php echo htmlspecialchars($diretor['nacionalidade'] ?? ''); ?>"
                        placeholder="Ex: Brasileira, Norte-americana…">
                </div>

                <div class="form-group full">
                    <label for="imagem">Foto do Diretor</label>
                    <input type="file" id="imagem" name="imagem" accept="image/*">
                    <small>JPG ou PNG · Máximo 5 MB<?php echo $diretor ? ' · Deixe vazio para manter a foto atual' : ''; ?></small>

                    <?php if (!empty($diretor['imagem'])): ?>
                        <div class="current-image">
                            <img src="<?php echo htmlspecialchars($diretor['imagem']); ?>" alt="Foto atual">
                            <small>Foto atual</small>
                        </div>
                    <?php endif; ?>
                </div>

            </div><!-- fim .form-grid -->

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <?php echo $diretor ? '💾 Atualizar' : '➕ Cadastrar'; ?>
                </button>
                <?php if ($diretor): ?>
                    <a href="cadastro_diretor.php" class="btn-back">✖ Cancelar</a>
                <?php endif; ?>
            </div>
        </form>

        <!-- -----------------------------------------------
         TABELA DE DIRETORES CADASTRADOS
    ----------------------------------------------- -->
        <h3>📋 Diretores Cadastrados</h3>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th class="col-foto">Foto</th>
                        <th>Nome</th>
                        <th>Nacionalidade</th>
                        <th class="col-acoes">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($diretores && $diretores->num_rows > 0): ?>
                        <?php while ($row = $diretores->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($row['imagem'])): ?>
                                        <img src="<?php echo htmlspecialchars($row['imagem']); ?>"
                                            alt="<?php echo htmlspecialchars($row['nome_diretor']); ?>"
                                            class="table-thumbnail">
                                    <?php else: ?>
                                        <div class="no-image">Sem<br>foto</div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['nome_diretor']); ?></td>
                                <td><?php echo htmlspecialchars($row['nacionalidade']) ?: '—'; ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="?edit_id=<?php echo $row['id_diretor']; ?>" class="btn-edit">✏️ Editar</a>
                                        <a href="?delete_id=<?php echo $row['id_diretor']; ?>"
                                            onclick="return confirm('Excluir <?php echo addslashes($row['nome_diretor']); ?>?')"
                                            class="btn-delete">🗑️</a>
                                        <a href="cadastro_serie.php?id_diretor=<?php echo $row['id_diretor']; ?>"
                                            class="btn-music">🎞️ Séries</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="empty-cell">Nenhum diretor cadastrado ainda.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
    <?php include('footer.php'); ?>
</body>

</html>