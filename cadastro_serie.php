<?php
/*
 * =============================================
 * ARQUIVO: cadastro_serie.php
 * =============================================
 *
 * Cada série pertence a um diretor (id_diretor é
 * chave estrangeira que referencia diretores.id).
 */
include('valida_sessao.php');
include('conexao.php');

// Mesma função de redimensionamento já usada em cadastro_diretor.php
function redimensionarESalvarImagem(array $arquivo, $largura = 300, $altura = 300)
{
    if (!is_dir('img')) {
        mkdir('img', 0755, true);
    }

    $caminho = "img/" . uniqid() . '_' . basename($arquivo["name"]);
    $tipo    = strtolower(pathinfo($caminho, PATHINFO_EXTENSION));

    if (!getimagesize($arquivo["tmp_name"]))      return "Arquivo não é uma imagem válida.";
    if ($arquivo["size"] > 5000000)               return "Imagem muito grande. Máximo: 5 MB.";
    if (!in_array($tipo, ["jpg", "jpeg", "png"])) return "Apenas JPG e PNG são aceitos.";

    $orig = imagecreatefromstring(file_get_contents($arquivo["tmp_name"]));
    $w0   = imagesx($orig);
    $h0   = imagesy($orig);
    $r    = min($largura / $w0, $altura / $h0);
    $nw   = (int)($w0 * $r);
    $nh   = (int)($h0 * $r);
    $nova = imagecreatetruecolor($nw, $nh);
    imagecopyresampled($nova, $orig, 0, 0, 0, 0, $nw, $nh, $w0, $h0);
    ($tipo === "png") ? imagepng($nova, $caminho) : imagejpeg($nova, $caminho, 90);
    imagedestroy($orig);
    imagedestroy($nova);
    return $caminho;
}

// -----------------------------------------------
// CREATE / UPDATE
// -----------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id             = $_POST['id']             ?? '';
    $id_diretor     = !empty($_POST['id_diretor']) ? intval($_POST['id_diretor']) : null;
    $titulo         = $_POST['titulo']         ?? '';
    $ano_lancamento = !empty($_POST['ano_lancamento']) ? intval($_POST['ano_lancamento']) : null;
    $temporadas     = ($_POST['temporadas'] !== '' && isset($_POST['temporadas'])) ? intval($_POST['temporadas']) : null;
    $episodios      = ($_POST['episodios'] !== '' && isset($_POST['episodios'])) ? intval($_POST['episodios']) : null;
    $genero         = $_POST['genero']         ?? '';
    $plataforma     = $_POST['plataforma']     ?? '';
    $sinopse        = $_POST['sinopse']        ?? '';

    $imagem_capa = "";
    if (isset($_FILES['imagem_capa']) && $_FILES['imagem_capa']['error'] == 0) {
        $upload = redimensionarESalvarImagem($_FILES['imagem_capa']);
        if (strpos($upload, 'img/') === 0) {
            $imagem_capa = $upload;
        } else {
            $mensagem = $upload;
            $class    = "error";
        }
    }

    if (!isset($class)) {
        if ($id) {
            // UPDATE — só troca a capa se uma nova foi enviada
            $sql    = "UPDATE series SET id_diretor=?, titulo=?, ano_lancamento=?, temporadas=?, episodios=?, genero=?, sinopse=?, plataforma=?";
            $params = [$id_diretor, $titulo, $ano_lancamento, $temporadas, $episodios, $genero, $sinopse, $plataforma];
            $tipos  = "isiiisss";
            if ($imagem_capa) {
                $sql      .= ", imagem_capa=?";
                $params[]  = $imagem_capa;
                $tipos    .= "s";
            }
            $sql      .= " WHERE id=?";
            $params[]  = intval($id);
            $tipos    .= "i";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param($tipos, ...$params);
            $mensagem = "Série atualizada com sucesso!";
        } else {
            // INSERT
            $stmt = $conn->prepare("INSERT INTO series (id_diretor, titulo, ano_lancamento, temporadas, episodios, genero, sinopse, plataforma, imagem_capa) VALUES (?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("isiiissss", $id_diretor, $titulo, $ano_lancamento, $temporadas, $episodios, $genero, $sinopse, $plataforma, $imagem_capa);
            $mensagem = "Série cadastrada com sucesso!";
        }
        $class = $stmt->execute() ? "success" : "error";
        if ($class === "error") $mensagem = "Erro: " . $stmt->error;
        $stmt->close();
    }
}

// -----------------------------------------------
// DELETE (também remove o arquivo da capa)
// -----------------------------------------------
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
    $class    = $stmt->execute() ? "success" : "error";
    $mensagem = $class === "success" ? "Série excluída!" : "Erro: " . $stmt->error;
    $stmt->close();
}

// -----------------------------------------------
// READ (edição e listagem)
// -----------------------------------------------
$serie = null;
if (isset($_GET['edit_id'])) {
    $eid  = intval($_GET['edit_id']);
    $stmt = $conn->prepare("SELECT * FROM series WHERE id = ?");
    $stmt->bind_param("i", $eid);
    $stmt->execute();
    $serie = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$diretores         = $conn->query("SELECT id, nome_diretor FROM diretores ORDER BY nome_diretor");
$diretor_id_filtro = isset($_GET['id_diretor']) ? intval($_GET['id_diretor'])
    : ($serie['id_diretor'] ?? null);

$series = null;
if ($diretor_id_filtro) {
    $stmt = $conn->prepare("
        SELECT s.*, d.nome_diretor AS diretor_nome
        FROM series s
        JOIN diretores d ON s.id_diretor = d.id
        WHERE s.id_diretor = ?
        ORDER BY s.titulo
    ");
    $stmt->bind_param("i", $diretor_id_filtro);
    $stmt->execute();
    $series = $stmt->get_result();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $serie ? 'Editar Série' : 'Nova Série'; ?> — CineVerso</title>
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
            <h2><?php echo $serie ? '✏️ Editar Série' : '🎞️ Nova Série'; ?></h2>
        </div>

        <?php if (isset($mensagem)): ?>
            <div class="message <?php echo $class; ?>">
                <?php echo $class === 'success' ? '✅' : '⚠️'; ?> <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <!-- FORMULÁRIO -->
        <form method="post" action="" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $serie['id'] ?? ''; ?>">

            <div class="form-grid">

                <!-- Linha 1: Diretor + Título -->
                <div class="form-group">
                    <label for="id_diretor">Diretor <span style="color:var(--red)">*</span></label>
                    <select name="id_diretor" id="id_diretor" required
                        <?php echo isset($_GET['id_diretor']) ? 'disabled' : ''; ?>>
                        <option value="">Selecione um diretor</option>
                        <?php while ($row = $diretores->fetch_assoc()): ?>
                            <?php $sel = $serie['id_diretor'] ?? ($_GET['id_diretor'] ?? ''); ?>
                            <option value="<?php echo $row['id']; ?>"
                                <?php echo ($sel == $row['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['nome_diretor']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <?php if (isset($_GET['id_diretor'])): ?>
                        <input type="hidden" name="id_diretor" value="<?php echo intval($_GET['id_diretor']); ?>">
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="titulo">Título da Série <span style="color:var(--red)">*</span></label>
                    <input type="text" id="titulo" name="titulo"
                        value="<?php echo htmlspecialchars($serie['titulo'] ?? ''); ?>"
                        required placeholder="Ex: Breaking Bad, Stranger Things…">
                </div>

                <!-- Linha 2: Gênero + Ano de Lançamento -->
                <div class="form-group">
                    <label for="genero">Gênero</label>
                    <input type="text" id="genero" name="genero"
                        value="<?php echo htmlspecialchars($serie['genero'] ?? ''); ?>"
                        placeholder="Ex: Drama, Ficção Científica, Suspense…">
                </div>

                <div class="form-group">
                    <label for="ano_lancamento">Ano de Lançamento</label>
                    <input type="number" id="ano_lancamento" name="ano_lancamento"
                        min="1950" max="2100"
                        value="<?php echo htmlspecialchars($serie['ano_lancamento'] ?? ''); ?>"
                        placeholder="Ex: 2024">
                </div>

                <!-- Linha 3: Temporadas + Episódios -->
                <div class="form-group">
                    <label for="temporadas">Temporadas</label>
                    <input type="number" id="temporadas" name="temporadas"
                        min="1"
                        value="<?php echo htmlspecialchars($serie['temporadas'] ?? ''); ?>"
                        placeholder="Ex: 5">
                </div>

                <div class="form-group">
                    <label for="episodios">Episódios</label>
                    <input type="number" id="episodios" name="episodios"
                        min="1"
                        value="<?php echo htmlspecialchars($serie['episodios'] ?? ''); ?>"
                        placeholder="Ex: 62">
                </div>

                <!-- Linha 4: Plataforma + Capa -->
                <div class="form-group">
                    <label for="plataforma">Plataforma / Streaming</label>
                    <input type="text" id="plataforma" name="plataforma"
                        value="<?php echo htmlspecialchars($serie['plataforma'] ?? ''); ?>"
                        placeholder="Ex: Netflix, Max, Prime Video…">
                </div>

                <div class="form-group">
                    <label for="imagem_capa">Capa / Pôster da Série</label>
                    <input type="file" id="imagem_capa" name="imagem_capa" accept="image/*">
                    <small>JPG ou PNG · Máximo 5 MB<?php echo $serie ? ' · Deixe vazio para manter a capa atual' : ''; ?></small>
                    <?php if (!empty($serie['imagem_capa'])): ?>
                        <div class="current-image">
                            <img src="<?php echo htmlspecialchars($serie['imagem_capa']); ?>" alt="Capa atual">
                            <small>Capa atual</small>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Linha 5: Sinopse (ocupa as 2 colunas) -->
                <div class="form-group full">
                    <label for="sinopse">Sinopse da Série</label>
                    <textarea id="sinopse" name="sinopse"
                        placeholder="Escreva a sinopse da série aqui (opcional)…"><?php
                                                                                    echo htmlspecialchars($serie['sinopse'] ?? '');
                                                                                    ?></textarea>
                </div>

            </div><!-- fim .form-grid -->

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <?php echo $serie ? '💾 Atualizar' : '➕ Cadastrar'; ?>
                </button>
                <?php if ($serie): ?>
                    <a href="cadastro_serie.php<?php echo $diretor_id_filtro ? '?id_diretor=' . $diretor_id_filtro : ''; ?>"
                        class="btn-back">✖ Cancelar</a>
                <?php endif; ?>
            </div>
        </form>

        <!-- -----------------------------------------------
         TABELA DE SÉRIES DO DIRETOR SELECIONADO
    ----------------------------------------------- -->
        <?php if ($series && $series->num_rows > 0): ?>
            <h3>🎞️ Séries Cadastradas deste Diretor</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th class="col-capa">Capa</th>
                            <th>Título</th>
                            <th>Gênero</th>
                            <th>Ano</th>
                            <th>Temporadas</th>
                            <th>Plataforma</th>
                            <th class="col-acoes">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
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
                                <td><?php echo htmlspecialchars($row['titulo']); ?></td>
                                <td><?php echo htmlspecialchars($row['genero']) ?: '—'; ?></td>
                                <td><?php echo htmlspecialchars($row['ano_lancamento']) ?: '—'; ?></td>
                                <td><?php echo htmlspecialchars($row['temporadas']) ?: '—'; ?></td>
                                <td><?php echo htmlspecialchars($row['plataforma']) ?: '—'; ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="?id_diretor=<?php echo $diretor_id_filtro; ?>&edit_id=<?php echo $row['id']; ?>"
                                            class="btn-edit">✏️ Editar</a>
                                        <a href="?id_diretor=<?php echo $diretor_id_filtro; ?>&delete_id=<?php echo $row['id']; ?>"
                                            onclick="return confirm('Excluir <?php echo addslashes($row['titulo']); ?>?')"
                                            class="btn-delete">🗑️</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </div>
    <?php include('footer.php'); ?>
</body>

</html>