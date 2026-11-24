<?php

$servername = "localhost";      
$username   = "root";           
$password   = "Senai@118";     
$dbname     = "sistema_series"; 

// --- Criando a conexão ---
// mysqli = MySQL Improved: forma moderna de conectar PHP ao MySQL
$conn = new mysqli($servername, $username, $password, $dbname);

// --- Verificando se a conexão funcionou ---
// connect_error retorna null se tudo ok, ou a mensagem de erro
if ($conn->connect_error) {
    // die() para tudo e mostra a mensagem. Útil durante o desenvolvimento.
    // Em produção, nunca exiba detalhes técnicos para o usuário!
    die("Falha na conexão: " . $conn->connect_error);
}

// --- Definindo o charset (conjunto de caracteres) ---
// utf8mb4 suporta acentos, emojis e caracteres especiais
$conn->set_charset("utf8mb4");

// --- Criando o diretório de imagens se não existir ---
// Isso garante que o upload de fotos sempre vai funcionar
if (!file_exists("img")) {
    mkdir("img", 0777, true); // 0777 = permissão total, true = cria subpastas
}
