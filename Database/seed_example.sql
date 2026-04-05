-- Dados de exemplo fictícios para Cadastro System
-- Todos os dados são totalmente fictícios e usados apenas para demonstração.
-- A senha de ambos os usuários é: Demo@123

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- Senha: Demo@123 (hash gerado com PASSWORD_DEFAULT = bcrypt)
INSERT INTO `users` (`id`, `username`, `password`, `is_admin`, `nome`, `sobrenome`) VALUES
(1, 'admin_demo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'Administrador', 'Demo'),
(2, 'user_demo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0, 'Usuário', 'Teste');

INSERT INTO `forms` (`id`, `nome`, `telefone`, `celular`, `email`, `profissao`, `numero_registro`, `conselho`, `evento`, `cidade`, `estado`, `data_hora`, `representante`, `created_by_user_id`) VALUES
(1, 'João da Silva', '(11) 3456-7890', '(11) 98765-4321', 'joao.silva@exemplo.com', 'Médico', 'CRM-12345', 'CRM', 'Congresso de Saúde 2024', 'São Paulo', 'SP', '2024-01-15 10:00:00', 'Administrador Demo', 1),
(2, 'Maria Oliveira', '(21) 2345-6789', '(21) 97654-3210', 'maria.oliveira@exemplo.com', 'Dentista', 'CRO-67890', 'CRO', 'Encontro Odontológico RJ', 'Rio de Janeiro', 'RJ', '2024-02-20 14:00:00', 'Usuário Teste', 2),
(3, 'Carlos Souza', '(31) 3210-9876', '(31) 96543-2109', 'carlos.souza@exemplo.com', 'Farmacêutico', 'CRF-11111', 'CRF', 'Simpósio Farmacêutico MG', 'Belo Horizonte', 'MG', '2024-03-10 09:00:00', 'Administrador Demo', 1);
