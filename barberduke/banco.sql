-- Criação do banco (se quiser rodar isoladamente)
CREATE DATABASE IF NOT EXISTS barbearia CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE barbearia;

-- =====================================================
-- 🧑‍💼 TABELA DE USUÁRIOS (clientes)
-- =====================================================
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cpf VARCHAR(14) UNIQUE NOT NULL,
    telefone VARCHAR(20)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 💈 TABELA DE BARBEIROS
-- =====================================================
CREATE TABLE barbeiros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- ✂️ TABELA DE PROCEDIMENTOS
-- =====================================================
CREATE TABLE procedimentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    duracao INT NOT NULL, -- duração em minutos
    valor DECIMAL(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inserir procedimentos padrão
INSERT INTO procedimentos (nome, duracao, valor) VALUES
('Corte', 30, 35.00),
('Barba', 20, 25.00),
('Corte + Barba', 45, 55.00),
('Sobrancelha', 15, 15.00);

-- =====================================================
-- 🕒 TABELA DE AGENDAMENTOS
-- =====================================================
CREATE TABLE agendamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    barbeiro_id INT NOT NULL,
    data DATE NOT NULL,
    horario TIME NOT NULL,
    status ENUM('ativo', 'confirmado', 'cancelado', 'passado') DEFAULT 'ativo',
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (barbeiro_id) REFERENCES barbeiros(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 🔗 RELAÇÃO ENTRE AGENDAMENTOS E PROCEDIMENTOS
-- =====================================================
CREATE TABLE agendamento_procedimentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    agendamento_id INT NOT NULL,
    procedimento_id INT NOT NULL,
    FOREIGN KEY (agendamento_id) REFERENCES agendamentos(id) ON DELETE CASCADE,
    FOREIGN KEY (procedimento_id) REFERENCES procedimentos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 👨‍🔧 Inserindo barbeiros de exemplo
-- =====================================================
INSERT INTO barbeiros (nome, status) VALUES
('Yuri', 'ativo'),
('Bruno', 'ativo'),
('Rubens', 'ativo');
