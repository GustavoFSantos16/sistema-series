-- Criar banco de dados do sistema de séries
CREATE DATABASE sistema_series;

-- Usar o banco de dados
USE sistema_series;

-- Criar tabela de usuários
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL,
    senha VARCHAR(255) NOT NULL
);

-- Criar tabela de diretores
CREATE TABLE diretores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_diretor VARCHAR(100) NOT NULL,
    nacionalidade VARCHAR(50),
    imagem VARCHAR(255)
);

-- Criar tabela de séries
CREATE TABLE series (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_diretor VARCHAR(100) NOT NULL,
    titulo VARCHAR(100) NOT NULL,
    ano_lancamento YEAR,
    temporadas INT,
    episodios INT,
    genero VARCHAR(50),
    sinopse TEXT,
    plataforma VARCHAR(100),
    imagem_capa VARCHAR(255),
    FOREIGN KEY (nome_diretor) REFERENCES diretores(nome_diretor)
);

-- Inserir usuário padrão "admin"
-- Utilize uma senha com hash gerado por password_hash() no PHP.
INSERT INTO usuarios (usuario, senha)
VALUES ('admin', '$2y$12$zw1c2aDgt4HHnAzXc3g3dOV6Mn96Dm2UaSIketIhYe1xmtuaJG6qa');
