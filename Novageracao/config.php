<?php
// Qlacon Esports - Configurações do site
// Copie este arquivo para 'config.php' em produção e atualize os valores.

// Valores para produção (InfinityFree) - atualizados com as credenciais fornecidas
$host = getenv('DB_HOST') ?: 'sql104.infinityfree.com';
$user = getenv('DB_USER') ?: 'if0_40421209';
$password = getenv('DB_PASSWORD') ?: 'Qlcon0125';
$database = getenv('DB_NAME') ?: 'if0_40421209_XXX';

// Senha admin (mude em produção)
$admin_password = getenv('ADMIN_PASSWORD') ?: '0125Qlaconadministracao';

// Opções SMTP (opcional) - preencha se for usar envio por SMTP/API
$smtp = [
    'host' => getenv('SMTP_HOST') ?: '',
    'port' => getenv('SMTP_PORT') ?: 587,
    'user' => getenv('SMTP_USER') ?: '',
    'pass' => getenv('SMTP_PASS') ?: '',
    'from' => getenv('SMTP_FROM') ?: 'contato.QlaconEsports@outlook.com.br',
];

// Nota para InfinityFree:
// - Na hospedagem InfinityFree o host de banco NÃO é 'localhost'. Você deve
//   preencher os dados fornecidos no painel (MySQL Databases -> detalhes).
// - Não faça commit deste arquivo com credenciais reais em repositórios públicos.
?>