<?php
// Ambiente: desativa exibição de erros para não vazar HTML/stacktraces em respostas JSON
ini_set('display_errors', '0');
error_reporting(0);

// Early API handler: responde antes de qualquer saída HTML para evitar
// que HTML (ex.: cabeçalho da página) quebre o parse de JSON no cliente.
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// Load configuration (copy and edit `config.php` for production)
$cfgPath = __DIR__ . '/config.php';
if (file_exists($cfgPath)) {
    require_once $cfgPath;
} else {
    // fallback defaults already set above for local dev
    $host = $host ?? 'localhost';
    $user = $user ?? 'root';
    $password = $password ?? '';
    $database = $database ?? 'novageracao_db';
}

// GET: fetch sponsors
if (isset($_GET['action']) && $_GET['action'] === 'get_sponsors') {
    header('Content-Type: application/json; charset=utf-8');
    $sponsors = [];
    $conn = @new mysqli($host, $user, $password, $database);
    if ($conn->connect_errno !== 0) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao conectar no banco: ' . $conn->connect_error]);
        exit;
    }
    $result = $conn->query("SELECT id, nome, email, telefone, empresa, data_inscricao, status FROM patrocinadores_inscritos ORDER BY data_inscricao DESC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $sponsors[] = $row;
        }
        echo json_encode($sponsors);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Query error: ' . $conn->error]);
    }
    $conn->close();
    exit;
}

// POST handlers (newsletter, sponsor_register) - mirror existing logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json; charset=utf-8');
    // minimal safe DB connection
    $conn = @new mysqli($host, $user, $password, $database);
    if ($conn->connect_errno !== 0) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro DB: ' . $conn->connect_error]);
        exit;
    }
    if ($_POST['action'] === 'subscribe_newsletter') {
        $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'E-mail inválido']);
            exit;
        }
        $check = $conn->prepare("SELECT id FROM newsletter WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $res = $check->get_result();
        if ($res && $res->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'E-mail já inscrito']);
            exit;
        }
        $stmt = $conn->prepare("INSERT INTO newsletter (email, ativo) VALUES (?, 1)");
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Inscrito com sucesso']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao inserir: ' . $conn->error]);
        }
        $conn->close();
        exit;
    }
    if ($_POST['action'] === 'sponsor_register') {
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        if ($nome === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
            exit;
        }
        $stmt = $conn->prepare("INSERT INTO patrocinadores_inscritos (nome, email, telefone, empresa, data_inscricao, status) VALUES (?, ?, ?, ?, NOW(), 'pendente')");
        $telefone = $_POST['telefone'] ?? '';
        $empresa = $_POST['empresa'] ?? '';
        $stmt->bind_param('ssss', $nome, $email, $telefone, $empresa);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Inscrição recebida']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao salvar: ' . $conn->error]);
        }
        $conn->close();
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Qlacon Esports - Time Profissional de Brawl Stars</title>
    <meta name="description" content="Time profissional de Brawl Stars - Qlacon Esports. Excelência, inovação e estratégia no topo do competitivo.">
    <meta name="keywords" content="Brawl Stars, esports, time profissional, competitivo, torneios">
    <meta name="author" content="Qlacon Esports">
    <meta property="og:title" content="Qlacon Esports - Time Profissional de Brawl Stars">
    <meta property="og:description" content="Conheça nosso time profissional de Brawl Stars. Excelência, inovação e estratégia.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://qlaconesports.wuaze.com">
    <meta name="theme-color" content="#071029">
    <link rel="canonical" href="https://qlaconesports.wuaze.com">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect fill='%23c9a83c' width='64' height='64'/%3E%3Ctext x='32' y='48' font-size='40' font-weight='bold' fill='%23000' text-anchor='middle'%3EQ%3C/text%3E%3C/svg%3E">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Sora:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <?php
    // ============================================
    // QLACON ESPORTS - Time Profissional Brawl Stars
    // ============================================
    // Arquivo único com HTML + PHP + CSS + JS
    
    header('Content-Type: text/html; charset=utf-8');
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    // carregar config (se existir)
    $cfgPath = __DIR__ . '/config.php';
    if (file_exists($cfgPath)) require_once $cfgPath;
    
    // Admin Configuration
    $admin_password = "0125Qlaconadministracao";
    $admin_logged = false;
    
    // Check if admin is logged in
    if (isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true) {
        $admin_logged = true;
    }
    
    // Handle admin login
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
        $senha = $_POST['admin_password'] ?? '';
        if ($senha === $admin_password) {
            $_SESSION['admin_logged'] = true;
            $admin_logged = true;
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $admin_error = "Senha incorreta!";
        }
    }
    
    // Handle admin logout
    if (isset($_GET['admin_logout'])) {
        unset($_SESSION['admin_logged']);
        session_destroy();
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    
    // API endpoint to get sponsors list
    if (isset($_GET['action']) && $_GET['action'] === 'get_sponsors') {
        header('Content-Type: application/json; charset=utf-8');
        $sponsors = [];
        $error = '';
        
        $conn = new mysqli($host, $user, $password, $database);
        if ($conn->connect_errno !== 0) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao conectar no banco: ' . $conn->connect_error]);
            exit;
        }
        
        $result = $conn->query("SELECT id, nome, email, telefone, empresa, data_inscricao, status FROM patrocinadores_inscritos ORDER BY data_inscricao DESC");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $sponsors[] = $row;
            }
            echo json_encode($sponsors);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Query error: ' . $conn->error]);
        }
        $conn->close();
        exit;
    }
    
    // Handle POST requests EARLY before any HTML output
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        header('Content-Type: application/json; charset=utf-8');
        
        if ($_POST['action'] === 'subscribe_newsletter') {
            $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => 'E-mail inválido']);
                exit;
            }
            
            $conn = @new mysqli($host, $user, $password, $database);
            if ($conn->connect_errno === 0) {
                // Check if email already exists
                $check = $conn->prepare("SELECT id FROM newsletter WHERE email = ?");
                $check->bind_param("s", $email);
                $check->execute();
                
                if ($check->get_result()->num_rows > 0) {
                    echo json_encode(['success' => false, 'message' => 'E-mail já inscrito']);
                } else {
                    // Insert new email
                    $stmt = $conn->prepare("INSERT INTO newsletter (email, ativo) VALUES (?, 1)");
                    $stmt->bind_param("s", $email);
                    
                    if ($stmt->execute()) {
                        echo json_encode(['success' => true, 'message' => 'E-mail adicionado com sucesso']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Erro ao inserir e-mail']);
                    }
                }
                $conn->close();
            } else {
                echo json_encode(['success' => false, 'message' => 'Erro na conexão com o banco']);
            }
            exit;
        }
        
        if ($_POST['action'] === 'sponsor_register') {
            $nome = filter_var($_POST['nome'] ?? '', FILTER_SANITIZE_STRING);
            $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
            $telefone = filter_var($_POST['telefone'] ?? '', FILTER_SANITIZE_STRING);
            $empresa = filter_var($_POST['empresa'] ?? '', FILTER_SANITIZE_STRING);
            
            if (!$nome || !filter_var($email, FILTER_VALIDATE_EMAIL) || !$telefone || !$empresa) {
                echo json_encode(['success' => false, 'message' => 'Preencha todos os campos corretamente']);
                exit;
            }
            
            $conn = @new mysqli($host, $user, $password, $database);
            if ($conn->connect_errno === 0) {
                // Check if email already exists
                $check = $conn->prepare("SELECT id FROM patrocinadores_inscritos WHERE email = ?");
                $check->bind_param("s", $email);
                $check->execute();
                
                if ($check->get_result()->num_rows > 0) {
                    echo json_encode(['success' => false, 'message' => 'E-mail já cadastrado']);
                } else {
                    // Insert sponsor
                    $stmt = $conn->prepare("INSERT INTO patrocinadores_inscritos (nome, email, telefone, empresa, data_inscricao) VALUES (?, ?, ?, ?, NOW())");
                    $stmt->bind_param("ssss", $nome, $email, $telefone, $empresa);
                    
                    if ($stmt->execute()) {
                        echo json_encode(['success' => true, 'message' => 'Inscrição realizada com sucesso! Entraremos em contato em breve.']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar inscrição']);
                    }
                }
                $conn->close();
            } else {
                echo json_encode(['success' => false, 'message' => 'Erro na conexão com o banco']);
            }
            exit;
        }
    }
    
    // If admin panel requested, show it
    if (isset($_GET['admin']) && $_GET['admin'] === '1') {
        if (!$admin_logged) {
            // Show login form
            ?>
            <!DOCTYPE html>
            <html lang="pt-BR">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Painel Admin - Qlacon Esports</title>
                <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
                <style>
                    * { margin: 0; padding: 0; box-sizing: border-box; }
                    body {
                        font-family: 'Inter', sans-serif;
                        background: linear-gradient(135deg, #0f0f0f, #1a1a1a);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        min-height: 100vh;
                        color: #fff;
                    }
                    .login-container {
                        background: #242424;
                        border: 2px solid rgba(212, 175, 55, 0.3);
                        border-radius: 14px;
                        padding: 40px;
                        width: 100%;
                        max-width: 400px;
                        box-shadow: 0 8px 24px rgba(212, 175, 55, 0.2);
                    }
                    .login-container h1 {
                        text-align: center;
                        margin-bottom: 12px;
                        font-size: 1.8rem;
                        background: linear-gradient(90deg, #ffd700, #d4af37);
                        -webkit-background-clip: text;
                        -webkit-text-fill-color: transparent;
                        background-clip: text;
                    }
                    .login-container p {
                        text-align: center;
                        color: #999;
                        margin-bottom: 28px;
                        font-size: 0.9rem;
                    }
                    .login-container form {
                        display: flex;
                        flex-direction: column;
                        gap: 16px;
                    }
                    .login-container input {
                        padding: 12px;
                        background: #1a1a1a;
                        border: 1px solid rgba(212, 175, 55, 0.3);
                        border-radius: 6px;
                        color: #fff;
                        font-size: 0.95rem;
                        font-family: inherit;
                    }
                    .login-container input::placeholder {
                        color: #666;
                    }
                    .login-container input:focus {
                        outline: none;
                        border-color: #ffd700;
                        box-shadow: 0 0 12px rgba(212, 175, 55, 0.3);
                    }
                    .login-container button {
                        padding: 12px;
                        background: linear-gradient(135deg, #ffd700, #d4af37);
                        border: none;
                        border-radius: 6px;
                        color: #000;
                        font-weight: 700;
                        font-size: 0.95rem;
                        cursor: pointer;
                        transition: all 0.3s ease;
                    }
                    .login-container button:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 8px 20px rgba(212, 175, 55, 0.4);
                    }
                    .error {
                        background: rgba(239, 68, 68, 0.1);
                        border: 1px solid #ef4444;
                        color: #fca5a5;
                        padding: 12px;
                        border-radius: 6px;
                        font-size: 0.9rem;
                    }
                </style>
            </head>
            <body>
                <div class="login-container">
                    <h1>🔐 Painel Admin</h1>
                    <p>Qlacon Esports</p>
                    <?php if (isset($admin_error)): ?>
                        <div class="error"><?php echo htmlspecialchars($admin_error); ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <input type="password" name="admin_password" placeholder="Digite a senha de admin" required autofocus>
                        <button type="submit" name="admin_login" value="1">Acessar Painel</button>
                    </form>
                </div>
            </body>
            </html>
            <?php
            exit;
        } else {
            // Show admin panel
            // Fetch sponsors from database
            $sponsors_list = [];
            $conn = @new mysqli($host, $user, $password, $database);
            if ($conn->connect_errno === 0) {
                $result = $conn->query("SELECT id, nome, email, telefone, empresa, data_inscricao, status FROM patrocinadores_inscritos ORDER BY data_inscricao DESC");
                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                        $sponsors_list[] = $row;
                    }
                }
                $conn->close();
            }
            
            // Handle delete sponsor
            if (isset($_POST['delete_sponsor'])) {
                $sponsor_id = (int)$_POST['sponsor_id'];
                $conn = @new mysqli($host, $user, $password, $database);
                if ($conn->connect_errno === 0) {
                    $stmt = $conn->prepare("DELETE FROM patrocinadores_inscritos WHERE id = ?");
                    $stmt->bind_param("i", $sponsor_id);
                    $stmt->execute();
                    $conn->close();
                    header('Location: ' . $_SERVER['PHP_SELF'] . '?admin=1');
                    exit;
                }
            }
            
            // Handle update status
            if (isset($_POST['update_status'])) {
                $sponsor_id = (int)$_POST['sponsor_id'];
                $new_status = $_POST['status'] ?? 'pendente';
                $conn = @new mysqli($host, $user, $password, $database);
                if ($conn->connect_errno === 0) {
                    $stmt = $conn->prepare("UPDATE patrocinadores_inscritos SET status = ? WHERE id = ?");
                    $stmt->bind_param("si", $new_status, $sponsor_id);
                    $stmt->execute();
                    $conn->close();
                    header('Location: ' . $_SERVER['PHP_SELF'] . '?admin=1');
                    exit;
                }
            }
            
            ?>
            <!DOCTYPE html>
            <html lang="pt-BR">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Painel Admin - Qlacon Esports</title>
                <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
                <style>
                    * { margin: 0; padding: 0; box-sizing: border-box; }
                    body {
                        font-family: 'Inter', sans-serif;
                        background: #0f0f0f;
                        color: #fff;
                        padding: 20px;
                    }
                    .admin-container {
                        max-width: 1200px;
                        margin: 0 auto;
                    }
                    .admin-header {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        margin-bottom: 40px;
                        padding-bottom: 20px;
                        border-bottom: 2px solid rgba(212, 175, 55, 0.3);
                    }
                    .admin-header h1 {
                        font-size: 2rem;
                        background: linear-gradient(90deg, #ffd700, #d4af37);
                        -webkit-background-clip: text;
                        -webkit-text-fill-color: transparent;
                        background-clip: text;
                    }
                    .admin-header a {
                        background: #c41e3a;
                        color: #fff;
                        padding: 10px 20px;
                        border-radius: 6px;
                        text-decoration: none;
                        font-weight: 600;
                        transition: all 0.3s ease;
                    }
                    .admin-header a:hover {
                        background: #8b1529;
                    }
                    .sponsors-table {
                        background: #242424;
                        border: 1px solid rgba(212, 175, 55, 0.2);
                        border-radius: 10px;
                        overflow: hidden;
                        box-shadow: 0 8px 24px rgba(212, 175, 55, 0.1);
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                    }
                    th {
                        background: linear-gradient(135deg, rgba(212, 175, 55, 0.1), rgba(255, 215, 0, 0.05));
                        padding: 16px;
                        text-align: left;
                        font-weight: 700;
                        color: #ffd700;
                        border-bottom: 2px solid rgba(212, 175, 55, 0.3);
                    }
                    td {
                        padding: 16px;
                        border-bottom: 1px solid rgba(212, 175, 55, 0.1);
                    }
                    tr:hover {
                        background: rgba(212, 175, 55, 0.05);
                    }
                    .status {
                        display: inline-block;
                        padding: 6px 12px;
                        border-radius: 4px;
                        font-size: 0.85rem;
                        font-weight: 600;
                    }
                    .status.pendente {
                        background: rgba(255, 193, 7, 0.2);
                        color: #ffc107;
                    }
                    .status.aprovado {
                        background: rgba(76, 175, 80, 0.2);
                        color: #4caf50;
                    }
                    .status.rejeitado {
                        background: rgba(244, 67, 54, 0.2);
                        color: #f44336;
                    }
                    .actions {
                        display: flex;
                        gap: 8px;
                    }
                    .btn-small {
                        padding: 6px 12px;
                        border: none;
                        border-radius: 4px;
                        cursor: pointer;
                        font-size: 0.85rem;
                        font-weight: 600;
                        transition: all 0.3s ease;
                    }
                    .btn-approve {
                        background: #4caf50;
                        color: #fff;
                    }
                    .btn-approve:hover {
                        background: #45a049;
                    }
                    .btn-reject {
                        background: #f44336;
                        color: #fff;
                    }
                    .btn-reject:hover {
                        background: #da190b;
                    }
                    .btn-delete {
                        background: #c41e3a;
                        color: #fff;
                    }
                    .btn-delete:hover {
                        background: #8b1529;
                    }
                    .empty-state {
                        text-align: center;
                        padding: 40px;
                        color: #999;
                    }
                    .stats {
                        display: grid;
                        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                        gap: 20px;
                        margin-bottom: 40px;
                    }
                    .stat-card {
                        background: #242424;
                        border: 1px solid rgba(212, 175, 55, 0.2);
                        padding: 20px;
                        border-radius: 10px;
                        text-align: center;
                    }
                    .stat-number {
                        font-size: 2.5rem;
                        font-weight: 700;
                        color: #ffd700;
                        margin-bottom: 8px;
                    }
                    .stat-label {
                        color: #999;
                        font-size: 0.9rem;
                    }
                </style>
            </head>
            <body>
                <div class="admin-container">
                    <div class="admin-header">
                        <h1>🔐 Painel Admin</h1>
                        <a href="<?php echo $_SERVER['PHP_SELF']; ?>?admin_logout=1">Sair</a>
                    </div>
                    
                    <div class="stats">
                        <div class="stat-card">
                            <div class="stat-number"><?php echo count($sponsors_list); ?></div>
                            <div class="stat-label">Total de Inscrições</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo count(array_filter($sponsors_list, fn($s) => $s['status'] === 'pendente')); ?></div>
                            <div class="stat-label">Pendentes</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo count(array_filter($sponsors_list, fn($s) => $s['status'] === 'aprovado')); ?></div>
                            <div class="stat-label">Aprovados</div>
                        </div>
                    </div>
                    
                    <div class="sponsors-table">
                        <?php if (empty($sponsors_list)): ?>
                            <div class="empty-state">
                                <i class="fas fa-inbox" style="font-size: 3rem; color: #666; margin-bottom: 16px; display: block;"></i>
                                <p>Nenhuma inscrição de patrocinador ainda.</p>
                            </div>
                        <?php else: ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>E-mail</th>
                                        <th>Telefone</th>
                                        <th>Empresa</th>
                                        <th>Data</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sponsors_list as $sponsor): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($sponsor['nome']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($sponsor['email']); ?></td>
                                            <td><?php echo htmlspecialchars($sponsor['telefone']); ?></td>
                                            <td><?php echo htmlspecialchars($sponsor['empresa']); ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($sponsor['data_inscricao'])); ?></td>
                                            <td>
                                                <span class="status <?php echo htmlspecialchars($sponsor['status']); ?>">
                                                    <?php 
                                        if ($sponsor['status'] === 'pendente') echo '⏳ Pendente';
                                        elseif ($sponsor['status'] === 'aprovado') echo '✓ Aprovado';
                                        elseif ($sponsor['status'] === 'rejeitado') echo '✗ Rejeitado';
                                    ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="actions">
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="sponsor_id" value="<?php echo $sponsor['id']; ?>">
                                                        <input type="hidden" name="status" value="aprovado">
                                                        <button type="submit" name="update_status" value="1" class="btn-small btn-approve" title="Aprovar">Aprovar</button>
                                                    </form>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="sponsor_id" value="<?php echo $sponsor['id']; ?>">
                                                        <input type="hidden" name="status" value="rejeitado">
                                                        <button type="submit" name="update_status" value="1" class="btn-small btn-reject" title="Rejeitar">Rejeitar</button>
                                                    </form>
                                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja deletar?');">
                                                        <input type="hidden" name="sponsor_id" value="<?php echo $sponsor['id']; ?>">
                                                        <button type="submit" name="delete_sponsor" value="1" class="btn-small btn-delete" title="Deletar">Deletar</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </body>
            </html>
            <?php
            exit;
        }
    }

// Generate a sync token for manual trigger (kept in session)
if (empty($_SESSION['sync_token'])) {
    try {
        $_SESSION['sync_token'] = bin2hex(random_bytes(16));
    } catch (Exception $e) {
        $_SESSION['sync_token'] = substr(md5(uniqid('', true)), 0, 32);
    }
}

// Generate a CSRF token for forms
if (empty($_SESSION['csrf_token'])) {
    try {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    } catch (Exception $e) {
        $_SESSION['csrf_token'] = substr(md5(uniqid('', true)), 0, 32);
    }
}

// Function to fetch player data from Brawlify API
function fetchBrawlifyPlayer($player_id) {
    $api_url = "https://api.brawlify.com/v1/players/" . urlencode($player_id);
    
    try {
        // Try with cURL first (mais confiável)
        if (function_exists('curl_init')) {
            $ch = curl_init($api_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code === 200 && $response) {
                $data = json_decode($response, true);
                if (isset($data['result'])) {
                    $player = $data['result'];
                    return [
                        'trofeus' => $player['trophies'] ?? 0,
                        'brawlers' => count($player['brawlers'] ?? []),
                        'experiencia' => $player['expPoints'] ?? 0,
                        'nivel' => $player['expLevel'] ?? 1,
                        'rank' => $player['rank'] ?? '?'
                    ];
                }
            }
        }
    } catch (Exception $e) {
        // Silently fail and use fallback
    }
    
    return null;
}

// Meta data from Noff.gg (cached)
$meta_data = [
    'last_update' => 'Atualizado há 4 horas',
    'source' => 'Noff.gg',
    's_tier' => ['Shelly', 'Mortis', 'Kit', 'Lily', 'Edgar'],
    'a_tier' => ['Cordelius', 'Surge', 'Byron', 'Leon', 'Stu'],
    'b_tier' => ['Bull', 'Gus', 'Barley', 'Nita', 'Rosa'],
    'c_tier' => ['Jacky', 'Penny', 'Dynamike', 'Tick', 'Emz'],
    'pick_rate_top' => ['Shelly (7.75%)', 'Mortis (4.5%)', 'Edgar (3.38%)', 'Kit (3.28%)', 'Lily (3.09%)'],
    'current_date' => date('d/m/Y H:i')
];

// Initialize data arrays
$players = [];
$videos = [];
$sponsors = [];
$valores = [];
$horarios_treino = [];
$estatisticas = [];
$newsletter_emails = [];
$db_errors = [];
$db_connected = false;

// Database Connection
$conn = @new mysqli($host, $user, $password, $database);
if ($conn->connect_errno === 0) {
    $db_connected = true;
    
    // Fetch Players
    // Checa se a coluna vitorias_3x3 existe para evitar erros em bases antigas
    $has_vitorias_col = false;
    $col_check_sql = "SELECT COUNT(*) as c FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . $conn->real_escape_string($database) . "' AND TABLE_NAME = 'jogadores' AND COLUMN_NAME = 'vitorias_3x3'";
    $col_check = $conn->query($col_check_sql);
    if ($col_check) {
        $tmp = $col_check->fetch_assoc();
        $has_vitorias_col = ((int)$tmp['c'] > 0);
    }

    $selectCols = "id, nome, player_id, funcao, descricao, trofeus, brawlers";
    if ($has_vitorias_col) {
        $selectCols .= ", vitorias_3x3";
    }
    $result = $conn->query("SELECT " . $selectCols . " FROM jogadores WHERE ativo = 1 ORDER BY posicao ASC LIMIT 12");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $db_trofeus = (int)str_replace(['.', ','], '', $row['trofeus'] ?? 0);
            $db_brawlers = (int)$row['brawlers'] ?? 0;
            $row['trofeus'] = $db_trofeus;
            $row['experiencia'] = $db_trofeus;
            $row['brawlers'] = $db_brawlers;
            // Se a coluna não existir, garante fallback 0
            $row['vitorias_3x3'] = isset($row['vitorias_3x3']) ? (int)$row['vitorias_3x3'] : 0;
            // Mantemos 'nivel' como fallback caso alguma parte do site ainda use
            $row['nivel'] = isset($row['nivel']) ? $row['nivel'] : 280;
            $row['from_api'] = false;
            $players[] = $row;
        }
    } else {
        $db_errors[] = "Players query: " . $conn->error;
    }
    
    // Fetch Videos
    $result = $conn->query("SELECT id, titulo, descricao, url_video, canal FROM videos WHERE destaque = 1 ORDER BY data_publicacao DESC LIMIT 4");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $videos[] = $row;
        }
    } else {
        $db_errors[] = "Videos query: " . $conn->error;
    }
    
    // Fetch Sponsors
    $result = $conn->query("SELECT id, nome, logo, website FROM patrocinadores WHERE ativo = 1 ORDER BY posicao ASC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $sponsors[] = $row;
        }
    } else {
        $db_errors[] = "Sponsors query: " . $conn->error;
    }
    
    // Fetch Valores
    $result = $conn->query("SELECT id, titulo, descricao, icone FROM valores WHERE ativo = 1 ORDER BY posicao ASC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $valores[] = $row;
        }
    } else {
        $db_errors[] = "Valores query: " . $conn->error;
    }
    
    // Fetch Horários de Treino
    $result = $conn->query("SELECT id, dia_semana, hora_inicio, hora_fim, atividade, descricao FROM horarios_treino WHERE ativo = 1 ORDER BY FIELD(SUBSTR(dia_semana, 1, 3), 'Seg', 'Sáb', 'Dom')");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $row['hora_inicio'] = substr($row['hora_inicio'], 0, 5);
            $row['hora_fim'] = substr($row['hora_fim'], 0, 5);
            $horarios_treino[] = $row;
        }
    } else {
        $db_errors[] = "Horários query: " . $conn->error;
    }
    
    // Fetch Estatísticas
    $result = $conn->query("SELECT torneios_vencidos, anos_experiencia, total_fas, total_trofeus FROM estatisticas LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $estatisticas = $result->fetch_assoc();
    } else {
        $estatisticas = [
            'torneios_vencidos' => 3,
            'total_trofeus' => 250000,
            'total_fas' => 500,
            'anos_experiencia' => 4
        ];
        $db_errors[] = "Estatísticas não encontradas, usando fallback";
    }
    
    // Fetch Newsletter Emails
    $result = $conn->query("SELECT COUNT(*) as total FROM newsletter WHERE ativo = 1");
    if ($result) {
        $row = $result->fetch_assoc();
        $newsletter_emails['total'] = $row['total'];
    }
    
    $conn->close();
} else {
    $db_errors[] = "Connection failed: " . $conn->connect_error;
}

// Fallback data if DB is empty
if (empty($players)) {
    $players = [
        [
            'nome' => 'NOVA | Pradoz',
            'player_id' => '#289VLRVR9U',
            'funcao' => 'Atirador',
            'trofeus' => 31197,
            'experiencia' => 31197,
            'brawlers' => 76,
            'nivel' => 280,
            'vitorias_3x3' => 1240,
            'descricao' => 'Líder estratégico com mentalidade analítica e visão tática precisa.',
            'foto' => null,
            'from_api' => false
        ],
        [
            'nome' => 'NOVA | LiebeToxic',
            'player_id' => '#2QJPV28RJ',
            'funcao' => 'Agressor',
            'trofeus' => 56114,
            'experiencia' => 56114,
            'brawlers' => 96,
            'nivel' => 330,
            'vitorias_3x3' => 3102,
            'descricao' => 'Força impulsionadora do time desde 2021. Especialista em criar oportunidades.',
            'foto' => null,
            'from_api' => false
        ],
        [
            'nome' => 'NOVA | AjaxBr',
            'player_id' => '#L8JYV8Y2J',
            'funcao' => 'Suporte',
            'trofeus' => 39150,
            'experiencia' => 39150,
            'brawlers' => 89,
            'nivel' => 290,
            'vitorias_3x3' => 1985,
            'descricao' => 'Cérebro tático do time com visão estratégica avançada. Veterano desde 2021.',
            'foto' => null,
            'from_api' => false
        ]
    ];
}

if (empty($videos)) {
    $videos = [
        [
            'titulo' => 'Em Breve - Canal Oficial',
            'descricao' => 'Acompanhe nossas análises, gameplays e momentos importantes',
            'url_video' => '#',
            'canal' => 'QLACON ESPORTS'
        ],
        [
            'titulo' => 'Em Breve - Treinos e Estratégias',
            'descricao' => 'Dicas e estratégias do time profissional',
            'url_video' => '#',
            'canal' => 'QLACON ESPORTS'
        ],
        [
            'titulo' => 'Em Breve - Torneios e Competições',
            'descricao' => 'Cobertura completa de nossos torneios',
            'url_video' => '#',
            'canal' => 'QLACON ESPORTS'
        ],
        [
            'titulo' => 'Em Breve - Brawler Reviews',
            'descricao' => 'Análise completa de cada Brawler meta',
            'url_video' => '#',
            'canal' => 'QLACON ESPORTS'
        ]
    ];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Qlacon Esports - Time Profissional de Brawl Stars</title>
    <meta name="description" content="Time profissional de Brawl Stars - Qlacon Esports. Excelência, inovação e estratégia no topo do competitivo.">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Sora:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* ========== DESIGN TOKENS ========== */
        :root {
            /* Palette: corporate dark with subtle gold accents */
            --color-accent: #c9a83c; /* muted gold */
            --color-accent-dark: #a8842f;
            --color-brand-dark: #071129; /* deep navy */
            --color-surface: #0f1724; /* card surface */
            --color-border: rgba(255,255,255,0.04);

            --color-bg: #071029; /* main background */
            --color-bg-secondary: #0b1220;
            --color-bg-tertiary: var(--color-surface);

            --color-text-primary: #e6eef8;
            --color-text-secondary: #c7d7ee;
            --color-text-muted: #8b99ab;

            --radius-sm: 6px;
            --radius-md: 10px;
            --radius-lg: 12px;

            --shadow-sm: 0 6px 18px rgba(2,6,23,0.5);
            --shadow-md: 0 10px 30px rgba(2,6,23,0.6);
            --shadow-lg: 0 18px 60px rgba(2,6,23,0.7);

            --transition: all 0.28s cubic-bezier(0.22, 0.9, 0.28, 1);

            /* Hero specific tokens (overridden in light-mode) */
            --hero-gradient: linear-gradient(180deg, rgba(7,16,41,0.9) 0%, rgba(11,18,36,0.9) 100%);
            --hero-highlight: rgba(201,168,60,0.06);
        }

        /* ========== LIGHT MODE OVERRIDES ========== */
        body.light-mode {
            --color-bg: #ffffff;
            --color-bg-secondary: #f6f6f6;
            --color-bg-tertiary: #ffffff;
            --color-text-primary: #0b0b0b;
            --color-text-secondary: #2b2b2b;
            --color-text-muted: #6b6b6b;
            --color-primary: #b8860b;
            --color-primary-dark: #99720a;
            --color-accent: #ffd700;
            --color-accent-dark: #ffec70;
            --color-secondary: #c41e3a;
            /* Hero overrides for light mode */
            --hero-gradient: linear-gradient(180deg, rgba(255,255,255,0.95) 0%, rgba(246,246,246,0.95) 100%);
            --hero-highlight: rgba(0,0,0,0.04);
        }

        /* Evita que imagens sejam invertidas se houver filtros globais */
        body.light-mode img,
        body img {
            filter: none !important;
        }

        /* ========== RESET & GLOBALS ========== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
            font-size: 16px;
        }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--color-bg);
            color: var(--color-text-primary);
            line-height: 1.6;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* ========== TYPOGRAPHY ========== */
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Sora', sans-serif;
            font-weight: 700;
            line-height: 1.2;
            letter-spacing: -0.5px;
        }

        h1 { font-size: clamp(2.4rem, 8vw, 4rem); }
        h2 { font-size: clamp(1.8rem, 6vw, 3rem); }
        h3 { font-size: clamp(1.4rem, 4vw, 2.2rem); }
        h4 { font-size: clamp(1.1rem, 3vw, 1.6rem); }
        h5 { font-size: 1.1rem; }

        p {
            color: var(--color-text-secondary);
            margin-bottom: 1rem;
        }

        a {
            color: var(--color-primary);
            text-decoration: none;
            transition: var(--transition);
        }

        a:hover {
            color: var(--color-accent);
        }

        img {
            max-width: 100%;
            height: auto;
            display: block;
        }

        /* ========== BUTTONS ========== */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 28px;
            border-radius: var(--radius-md);
            font-weight: 600;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
            cursor: pointer;
            transition: var(--transition);
            white-space: nowrap;
        }

        .btn-primary {
            background: var(--color-surface);
            color: var(--color-accent);
            box-shadow: var(--shadow-sm);
            font-weight: 700;
            border: 1px solid var(--color-border);
            position: relative;
            overflow: hidden;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s ease;
        }

        .btn-primary:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
            background: linear-gradient(90deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-secondary {
            background: transparent;
            color: var(--color-text-secondary);
            border: 1px solid var(--color-border);
            font-weight: 700;
        }

        .btn-secondary:hover {
            border-color: rgba(255,255,255,0.08);
            background: rgba(255,255,255,0.02);
            color: var(--color-text-primary);
            box-shadow: var(--shadow-sm);
        }

        /* ========== HEADER ========== */
        header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(6,10,20,0.65);
            backdrop-filter: blur(6px);
            border-bottom: 1px solid var(--color-border);
            z-index: 1000;
            transition: var(--transition);
            box-shadow: 0 6px 18px rgba(2,6,23,0.6);
        }

        header.scrolled {
            box-shadow: 0 10px 30px rgba(2,6,23,0.7);
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }

        .header-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 0;
            max-width: 1200px;
            margin: 0 auto;
            padding-left: 20px;
            padding-right: 20px;
            position: relative;
            flex-wrap: nowrap; /* evita quebra inesperada em telas pequenas */
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
            font-size: 1.05rem;
            text-transform: none;
            letter-spacing: 0.6px;
            color: var(--color-accent);
            min-width: 0; /* permite encolher sem forçar overflow */
        }

        /* Garantir que o nav ocupe espaço flexível e não force overflow */
        .header-inner > nav {
            flex: 1 1 auto;
            min-width: 0;
        }

        .header-inner > nav ul {
            display: flex;
            gap: 18px;
            align-items: center;
            justify-content: center;
            padding: 0;
            margin: 0;
            list-style: none;
        }

        .logo img {
            height: 44px;
            width: auto;
            max-width: 100%;
            filter: none;
            transition: transform 0.28s ease, box-shadow 0.28s ease;
            display: block;
        }

        .logo img:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(6,10,20,0.55);
        }

        .logo img {
            height: 40px;
            width: auto;
            object-fit: contain;
            filter: drop-shadow(0 0 8px rgba(212, 175, 55, 0.5));
        }

        nav ul {
            display: flex;
            list-style: none;
            gap: 28px;
        }

        nav a {
            font-weight: 700;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--color-accent-light);
            position: relative;
            transition: color 0.3s ease;
        }

        nav a::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--color-accent-dark), var(--color-secondary));
            transition: width 0.3s ease;
            box-shadow: 0 0 8px rgba(255, 215, 0, 0.6);
        }

        nav a:hover, nav a.active {
            color: var(--color-accent-dark);
        }

        nav a:hover::after, nav a.active::after {
            width: 100%;
            box-shadow: 0 0 12px rgba(255, 215, 0, 0.8);
        }

        /* ========== HERO SECTION ========== */
        .hero {
            margin-top: 70px;
            padding: 100px 0 60px;
            background: var(--hero-gradient);
            border-bottom: 1px solid var(--color-border);
            min-height: 70vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 10%;
            right: 5%;
            width: 420px;
            height: 420px;
            background: radial-gradient(circle at 20% 20%, var(--hero-highlight), transparent 50%);
            border-radius: 50%;
            pointer-events: none;
        }

        .hero .container {
            text-align: center;
            position: relative;
            z-index: 2;
            max-width: 900px;
            margin: 0 auto;
        }

        .hero h1 {
            color: var(--color-text-primary);
            margin-bottom: 6px;
            font-size: clamp(2.4rem, 6vw, 3.6rem);
            letter-spacing: -1px;
        }

        .hero .subtitle {
            font-size: 1.05rem;
            color: var(--color-text-secondary);
            margin-bottom: 18px;
            font-weight: 600;
        }

        .hero p {
            max-width: 720px;
            margin-left: auto;
            margin-right: auto;
            margin-bottom: 40px;
            font-size: 1.1rem;
            color: var(--color-text-secondary);
        }

        .hero .actions {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
        }

        /* ========== SECTION LAYOUT ========== */
        section {
            padding: 100px 0;
            position: relative;
        }

        section:nth-child(odd) {
            background: linear-gradient(180deg, rgba(212, 175, 55, 0.03) 0%, transparent 100%);
        }

        .section-title {
            text-align: center;
            margin-bottom: 64px;
        }

        .section-title h2 {
            margin-bottom: 12px;
            background: linear-gradient(90deg, var(--color-accent), var(--color-primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .section-title::after {
            content: '';
            display: block;
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, var(--color-primary), var(--color-accent));
            margin: 16px auto 0;
            border-radius: 2px;
        }

        .section-subtitle {
            font-size: 1.05rem;
            color: var(--color-text-secondary);
            max-width: 600px;
            margin: 0 auto;
        }

        /* ========== VALUES SECTION ========== */
        .values {
            background: var(--color-bg-secondary);
            border-top: 1px solid rgba(255, 215, 0, 0.1);
        }

        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 32px;
        }

        .value-card {
            background: var(--color-bg-tertiary);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-lg);
            padding: 36px 28px;
            text-align: center;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .value-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, rgba(201,168,60,0.12), rgba(201,168,60,0.06));
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.45s ease;
        }

        .value-card:hover {
            border-color: rgba(255,255,255,0.08);
            background: rgba(201,168,60,0.03);
            transform: translateY(-6px);
        }

        .value-card:hover::before {
            transform: scaleX(1);
        }

        .value-icon {
            font-size: 2.8rem;
            color: var(--color-accent);
            margin-bottom: 16px;
            display: block;
        }

        .value-card h4 {
            margin-bottom: 12px;
            color: var(--color-text-primary);
        }

        .value-card p {
            margin-bottom: 0;
            font-size: 0.95rem;
        }

        /* ========== TEAM SECTION ========== */
        .team {
            background: linear-gradient(135deg, rgba(226, 27, 50, 0.05), rgba(255, 215, 0, 0.02));
            border-top: 1px solid rgba(255, 215, 0, 0.1);
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 32px;
        }

        .player-card {
            background: var(--color-bg-tertiary);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            position: relative;
        }

        .player-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, rgba(201,168,60,0.12), rgba(201,168,60,0.06));
            opacity: 0;
            transition: opacity 0.28s ease;
        }

        .player-card:hover {
            border-color: rgba(255,255,255,0.08);
            transform: translateY(-6px);
            box-shadow: var(--shadow-md);
        }

        .player-card:hover::after {
            opacity: 1;
        }

        .player-avatar {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, rgba(212, 175, 55, 0.15), rgba(196, 30, 58, 0.15));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            color: var(--color-accent-dark);
            border-bottom: 2px solid rgba(212, 175, 55, 0.15);
            position: relative;
            overflow: hidden;
        }

        .player-avatar::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(212, 175, 55, 0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .player-info {
            padding: 28px;
        }

        .player-name {
            font-size: 1.2rem;
            font-weight: 800;
            margin-bottom: 4px;
            background: linear-gradient(90deg, var(--color-accent), var(--color-primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .player-id {
            font-size: 0.85rem;
            color: var(--color-accent-dark);
            margin-bottom: 8px;
            font-family: 'Courier New', monospace;
            font-weight: 700;
        }

        .player-role {
            font-size: 0.95rem;
            color: var(--color-secondary);
            font-weight: 700;
            margin-bottom: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .player-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 16px;
            padding: 16px 0;
            border-top: 2px solid rgba(212, 175, 55, 0.15);
            border-bottom: 2px solid rgba(212, 175, 55, 0.15);
        }

        .stat {
            text-align: center;
        }

        .stat-value {
            display: block;
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--color-accent);
        }

        .stat-label {
            font-size: 0.8rem;
            color: var(--color-text-muted);
            text-transform: uppercase;
            margin-top: 4px;
        }

        .player-desc {
            font-size: 0.95rem;
            color: var(--color-text-secondary);
            margin-bottom: 0;
        }

        /* ========== META SECTION ========== */
        .meta {
            background: var(--color-bg-secondary);
            border-top: 1px solid rgba(255, 215, 0, 0.1);
        }

        .meta-card {
            background: var(--color-bg-tertiary);
            border: 1px solid rgba(255, 215, 0, 0.1);
            border-radius: var(--radius-lg);
            padding: 32px;
            margin-bottom: 32px;
        }

        .meta-tabs {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .meta-tab {
            padding: 8px 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: transparent;
            border-radius: var(--radius-md);
            color: var(--color-text-secondary);
            cursor: pointer;
            transition: var(--transition);
            font-weight: 600;
            font-size: 0.9rem;
        }

        .meta-tab.active, .meta-tab:hover {
            background: linear-gradient(135deg, var(--color-primary), var(--color-accent));
            color: var(--color-bg);
            border-color: var(--color-accent);
        }

        /* ========== VIDEOS SECTION ========== */
        .videos {
            background: linear-gradient(135deg, rgba(226, 27, 50, 0.05), rgba(255, 215, 0, 0.02));
            border-top: 1px solid rgba(255, 215, 0, 0.1);
        }

        .videos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 28px;
        }

        .video-card {
            background: linear-gradient(135deg, var(--color-bg-tertiary) 0%, rgba(196, 30, 58, 0.03) 100%);
            border: 2px solid rgba(212, 175, 55, 0.2);
            border-radius: var(--radius-lg);
            overflow: hidden;
            transition: var(--transition);
            position: relative;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.1);
        }

        .video-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--color-accent), var(--color-secondary));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .video-card:hover {
            border-color: var(--color-accent);
            transform: translateY(-10px);
            box-shadow: 0 12px 35px rgba(212, 175, 55, 0.25);
            background: linear-gradient(135deg, rgba(26, 26, 26, 0.8) 0%, rgba(196, 30, 58, 0.08) 100%);
        }

        .video-card:hover::before {
            opacity: 1;
        }

        .video-thumb {
            width: 100%;
            height: 180px;
            background: linear-gradient(135deg, rgba(212, 175, 55, 0.18), rgba(196, 30, 58, 0.12));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: var(--color-accent-dark);
            position: relative;
            overflow: hidden;
            border-bottom: 2px solid rgba(212, 175, 55, 0.15);
        }

        .video-thumb::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 150px;
            height: 150px;
            background: radial-gradient(circle, rgba(212, 175, 55, 0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .video-content {
            padding: 20px;
        }

        .video-title {
            font-weight: 700;
            margin-bottom: 8px;
            font-size: 1.05rem;
            background: linear-gradient(90deg, var(--color-accent), var(--color-primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .video-channel {
            font-size: 0.85rem;
            color: var(--color-secondary);
            margin-bottom: 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .video-desc {
            font-size: 0.9rem;
            color: var(--color-text-secondary);
            margin-bottom: 12px;
        }
        
        /* ========== ADMIN MODAL ========== */
        .admin-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }
        
        .admin-modal.active {
            display: flex;
        }
        
        .admin-modal-content {
            background: var(--color-bg-tertiary);
            border: 2px solid rgba(212, 175, 55, 0.3);
            border-radius: var(--radius-lg);
            padding: 40px;
            width: 90%;
            max-width: 500px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 12px 40px rgba(212, 175, 55, 0.3);
            animation: slideDown 0.3s ease;
        }
        
        .admin-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 2px solid rgba(212, 175, 55, 0.2);
        }
        
        .admin-modal-header h2 {
            color: var(--color-accent);
            font-size: 1.5rem;
        }
        
        .admin-close-btn {
            background: none;
            border: none;
            color: var(--color-accent);
            font-size: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .admin-close-btn:hover {
            color: var(--color-accent-dark);
            transform: scale(1.2);
        }
        
        .admin-form-group {
            margin-bottom: 16px;
        }
        
        .admin-form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--color-text-primary);
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .admin-form-group input {
            width: 100%;
            padding: 12px;
            background: var(--color-bg);
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: 6px;
            color: var(--color-text-primary);
            font-family: inherit;
            font-size: 0.95rem;
            box-sizing: border-box;
        }
        
        .admin-form-group input:focus {
            outline: none;
            border-color: var(--color-accent);
            box-shadow: 0 0 12px rgba(212, 175, 55, 0.3);
        }
        
        .admin-submit-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, var(--color-accent-dark), var(--color-primary));
            border: none;
            border-radius: 6px;
            color: #000;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 8px;
        }
        
        .admin-submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(212, 175, 55, 0.4);
        }
        
        .admin-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid #ef4444;
            color: #fca5a5;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 16px;
            font-size: 0.9rem;
        }
        
        .logo {
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .logo:hover img {
            filter: drop-shadow(0 0 20px rgba(255, 215, 0, 0.8));
            transform: scale(1.05);
        }
        
        /* ========== INTERACTIVE ELEMENTS ========== */
        /* Theme Toggle (header) */
        .theme-toggle {
            background: transparent;
            border: none;
            cursor: pointer;
            color: var(--color-accent);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            padding: 4px;
            transition: transform 0.22s ease, color 0.22s ease;
        }

        header .theme-toggle {
            margin-left: 18px;
        }

        .theme-toggle:hover {
            transform: translateY(-2px);
            color: var(--color-accent);
        }

        /* Smooth theme transition for major elements */
        body, header, .hero, .team, .meta, .stats, footer, .container {
            transition: background-color 0.32s ease, color 0.32s ease, border-color 0.32s ease;
        }

        /* Style Guide Modal */
        .style-guide-modal {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1200;
        }

        .style-guide-modal.active { display: flex; }

        .style-guide-overlay {
            position: absolute; inset: 0; background: rgba(0,0,0,0.6);
        }

        .style-guide-content {
            position: relative;
            background: var(--color-bg-tertiary);
            border: 1px solid var(--color-border);
            padding: 22px;
            border-radius: 12px;
            width: min(920px, 96%);
            max-height: 86vh;
            overflow: auto;
            box-shadow: var(--shadow-lg);
        }

        .style-guide-close {
            position: absolute; top: 12px; right: 12px; background: transparent; border: none; color: var(--color-text-secondary); font-size: 1.1rem; cursor: pointer;
        }

        /* Icon animation states */
        .theme-toggle svg { transition: transform 0.35s ease, color 0.35s ease; }
        .theme-toggle[data-theme="day"] svg { transform: rotate(0deg) scale(1); }
        .theme-toggle[data-theme="night"] svg { transform: rotate(180deg) scale(0.95); }
        
        /* Back to Top Button */
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 999;
            background: linear-gradient(135deg, var(--color-accent-dark), var(--color-primary));
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            cursor: pointer;
            display: none;
            align-items: center;
            justify-content: center;
            color: #000;
            font-size: 1.3rem;
            font-weight: 700;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(212, 175, 55, 0.3);
        }
        
        .back-to-top.show {
            display: flex;
        }
        
        .back-to-top:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.5);
        }
        
        /* Search Bar */
        .search-bar {
            max-width: 400px;
            margin: 20px auto;
            display: flex;
            gap: 8px;
        }
        
        .search-bar input {
            flex: 1;
            padding: 12px 16px;
            background: var(--color-bg-secondary);
            border: 2px solid rgba(212, 175, 55, 0.3);
            border-radius: 6px;
            color: var(--color-text-primary);
            font-size: 0.95rem;
        }
        
        .search-bar input:focus {
            outline: none;
            border-color: var(--color-accent);
            box-shadow: 0 0 12px rgba(212, 175, 55, 0.3);
        }
        
        .search-bar button {
            padding: 12px 20px;
            background: linear-gradient(135deg, var(--color-accent-dark), var(--color-primary));
            border: none;
            border-radius: 6px;
            color: #000;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .search-bar button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(212, 175, 55, 0.4);
        }
        
        /* Share Buttons */
        .share-buttons {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .share-btn {
            padding: 8px 16px;
            border: 2px solid rgba(212, 175, 55, 0.3);
            background: transparent;
            color: var(--color-accent);
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .share-btn:hover {
            background: rgba(212, 175, 55, 0.1);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(212, 175, 55, 0.2);
        }
        
        /* Mobile Menu */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: var(--color-accent);
            font-size: 1.5rem;
            cursor: pointer;
            /* agora exibido inline no header, sem posição absoluta por padrão */
            position: relative;
            right: auto;
            top: auto;
            z-index: 1200;
            padding: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .mobile-menu {
            position: fixed;
            top: 0;
            right: -320px;
            width: min(92vw, 320px);
            height: 100vh;
            background: var(--color-bg-secondary);
            border-left: 1px solid var(--color-border);
            padding: 84px 18px 20px; /* garante espaço abaixo do header fixo */
            transition: right 0.28s ease;
            z-index: 1199;
            overflow-y: auto;
        }
        
        .mobile-menu.active {
            right: 0;
        }
        
        .mobile-menu a {
            display: block;
            padding: 12px 0;
            color: var(--color-accent);
            text-decoration: none;
            border-bottom: 1px solid rgba(212, 175, 55, 0.1);
            transition: all 0.3s ease;
        }
        
        .mobile-menu a:hover {
            color: var(--color-accent-dark);
            padding-left: 8px;
        }
        
        /* Tooltip */
        .tooltip {
            position: relative;
            display: inline-block;
        }
        
        .tooltip .tooltiptext {
            visibility: hidden;
            background-color: var(--color-primary);
            color: #000;
            text-align: center;
            border-radius: 6px;
            padding: 8px 12px;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            margin-left: -50px;
            opacity: 0;
            transition: opacity 0.3s;
            white-space: nowrap;
            font-size: 0.85rem;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(212, 175, 55, 0.3);
        }
        
        .tooltip:hover .tooltiptext {
            visibility: visible;
            opacity: 1;
        }
        
        /* Animations */
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .animate-on-scroll {
            opacity: 0;
            animation: slideInUp 0.6s ease forwards;
        }
        
        /* Stat Counter */
        .stat-counter {
            font-size: 2.5rem;
            font-weight: 700;
        }

        /* ========== DEBUG ========== */
        .debug-panel {
            background: var(--color-bg-tertiary);
            border: 1px solid var(--color-primary);
            border-radius: var(--radius-lg);
            padding: 20px;
            margin-bottom: 20px;
            font-size: 0.85rem;
        }

        .debug-panel pre {
            background: rgba(0, 0, 0, 0.5);
            padding: 12px;
            border-radius: var(--radius-sm);
            overflow-x: auto;
            max-height: 300px;
            overflow-y: auto;
        }

        /* ========== SPONSORS SECTION ========== */
        .sponsors {
            background: var(--color-bg-secondary);
            border-top: 1px solid rgba(212, 175, 55, 0.15);
        }

        .sponsors-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 24px;
            align-items: center;
        }

        .sponsor-card {
            background: var(--color-bg-tertiary);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-lg);
            padding: 20px;
            text-align: center;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 120px;
        }

        .sponsor-card:hover {
            border-color: rgba(255,255,255,0.08);
            transform: translateY(-6px);
            box-shadow: var(--shadow-md);
        }

        .sponsor-logo {
            max-width: 120px;
            max-height: 90px;
            object-fit: contain;
            filter: none;
            transition: transform 0.25s ease, opacity 0.25s ease;
        }

        .sponsor-card:hover .sponsor-logo {
            filter: grayscale(0%);
        }

        .sponsor-name {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--color-text-secondary);
            margin-top: 12px;
        }

        /* ========== STATS SECTION ========== */
        .stats {
            background: var(--color-bg-secondary);
            border-top: 1px solid var(--color-border);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 28px;
        }

        .stat-card {
            background: var(--color-bg-tertiary);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-lg);
            padding: 28px 20px;
            text-align: center;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, rgba(201,168,60,0.12), rgba(201,168,60,0.06));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-6px);
            border-color: rgba(255,255,255,0.08);
            box-shadow: var(--shadow-md);
        }

        .stat-card:hover::before {
            opacity: 1;
        }

        .stat-card-number {
            font-size: 2.4rem;
            font-weight: 800;
            color: var(--color-accent);
            margin-bottom: 8px;
        }

        .stat-card-label {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--color-text-secondary);
            margin-bottom: 4px;
        }

        .stat-card-desc {
            font-size: 0.85rem;
            color: var(--color-text-muted);
        }

        /* ========== SCHEDULE SECTION ========== */
        .schedule {
            background: var(--color-bg-secondary);
            border-top: 1px solid rgba(212, 175, 55, 0.15);
        }

        .schedule-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
        }

        .schedule-card {
            background: linear-gradient(135deg, var(--color-bg-tertiary) 0%, rgba(196, 30, 58, 0.03) 100%);
            border: 2px solid rgba(212, 175, 55, 0.15);
            border-radius: var(--radius-lg);
            padding: 24px;
            text-align: center;
            transition: var(--transition);
        }

        .schedule-card:hover {
            border-color: var(--color-accent);
            background: linear-gradient(135deg, rgba(26, 26, 26, 0.9) 0%, rgba(196, 30, 58, 0.08) 100%);
            transform: translateY(-6px);
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.2);
        }

        .schedule-day {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--color-accent);
            margin-bottom: 8px;
        }

        .schedule-time {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--color-secondary);
            margin-bottom: 8px;
        }

        .schedule-activity {
            font-size: 0.9rem;
            color: var(--color-text-secondary);
        }

        /* ========== NEWSLETTER SECTION ========== */
        .newsletter {
            background: linear-gradient(135deg, rgba(212, 175, 55, 0.12), rgba(196, 30, 58, 0.08));
            border-top: 1px solid rgba(212, 175, 55, 0.15);
            border-bottom: 1px solid rgba(212, 175, 55, 0.15);
        }

        .newsletter-content {
            max-width: 600px;
            margin: 0 auto;
            text-align: center;
        }

        .newsletter-form {
            display: flex;
            gap: 12px;
            margin-top: 24px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .newsletter-form input {
            flex: 1;
            min-width: 200px;
            padding: 14px 18px;
            background: var(--color-bg-tertiary);
            border: 2px solid rgba(212, 175, 55, 0.2);
            border-radius: var(--radius-md);
            color: var(--color-text-primary);
            font-size: 0.95rem;
            transition: var(--transition);
        }

        .newsletter-form input::placeholder {
            color: var(--color-text-muted);
        }

        .newsletter-form input:focus {
            outline: none;
            border-color: var(--color-accent);
            background: var(--color-bg-secondary);
            box-shadow: 0 0 12px rgba(212, 175, 55, 0.3);
        }

        .newsletter-form button {
            padding: 14px 28px;
            background: linear-gradient(135deg, var(--color-accent-dark), var(--color-primary));
            color: #000;
            border: none;
            border-radius: var(--radius-md);
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            transition: var(--transition);
            text-transform: uppercase;
            letter-spacing: 1px;
            white-space: nowrap;
        }

        .newsletter-form button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(212, 175, 55, 0.4);
            background: linear-gradient(135deg, var(--color-accent), var(--color-primary-dark));
        }

        /* ========== FOOTER ========== */
        footer {
            background: var(--color-bg-secondary);
            border-top: 2px solid var(--color-accent);
            padding: 32px 0;
            margin-top: 60px;
        }

        .footer-bottom {
            text-align: center;
            color: var(--color-text-muted);
            font-size: 0.95rem;
        }

        footer a {
            color: var(--color-accent);
        }

        footer a:hover {
            color: var(--color-accent-dark);
            text-decoration: underline;
        }

        /* ========== FOOTER ========== */
        footer {
            background: #000;
            border-top: 1px solid rgba(255, 215, 0, 0.1);
            padding: 60px 0 20px;
            margin-top: 40px;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            color: var(--color-text-muted);
            font-size: 0.9rem;
        }

        /* ========== RESPONSIVE - MOBILE FIRST ========== */
        
        /* MOBILE (até 768px) */
        @media (max-width: 768px) {
            /* Remove header nav, show mobile menu */
            nav ul {
                display: none;
            }
            
            .mobile-menu-btn {
                display: block;
            }
            
            header .theme-toggle { 
                display: none; 
            }
            
            .mobile-menu .mobile-theme-row { 
                display: flex; 
            }
            
            /* Hero */
            .hero {
                margin-top: 70px;
                padding: 60px 16px 40px !important;
                min-height: 50vh;
            }
            
            .hero h1 {
                font-size: clamp(1.8rem, 5vw, 2.4rem);
            }
            
            .hero p {
                font-size: 0.95rem;
            }
            
            .hero .actions {
                flex-direction: column;
            }
            
            .hero .actions a {
                width: 100%;
            }
            
            .hero .actions > div {
                width: 100%;
            }
            
            /* Sections */
            section {
                padding: 50px 16px !important;
            }
            
            .section-title h2 {
                font-size: clamp(1.4rem, 5vw, 1.8rem);
            }
            
            /* Grids - all single column */
            .values-grid,
            .team-grid,
            .videos-grid,
            .sponsors-grid,
            .stat-cards,
            .schedule-grid {
                grid-template-columns: 1fr !important;
                gap: 12px !important;
            }
            
            .player-stats {
                grid-template-columns: 2fr 1fr 1fr !important;
                gap: 8px !important;
            }
            
            .meta-tabs {
                flex-direction: column;
                gap: 8px;
            }
            
            /* Buttons & Forms */
            .btn {
                padding: 10px 18px;
                font-size: 0.9rem;
            }
            
            /* Melhorias mobile: alvos de toque maiores e botões full-width
               exceto controles pequenos (ex: .btn-small usados no admin) */
            .btn:not(.btn-small) {
                width: 100%;
                box-sizing: border-box;
                padding: 14px 18px;
                font-size: 1rem;
                min-height: 48px; /* recomendado para acessibilidade/touch */
                border-radius: calc(var(--radius-md) + 2px);
            }

            /* Ajustes para botões pequenos e ícones (share, busca, menu) */
            .btn-small {
                padding: 8px 10px;
                font-size: 0.85rem;
            }

            .share-btn,
            .search-bar button,
            .mobile-menu-btn {
                min-width: 44px;
                min-height: 44px;
                padding: 10px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }
            .newsletter-form {
                flex-direction: column;
            }
            
            .newsletter-form input,
            .newsletter-form button {
                width: 100%;
                min-width: unset;
            }
            
            /* Back to top */
            .back-to-top {
                bottom: 20px;
                right: 20px;
                width: 45px;
                height: 45px;
                font-size: 1.2rem;
            }
            
            /* Header */
            .header-inner {
                padding-left: 12px;
                padding-right: 12px;
            }
            
            .logo {
                font-size: 0.9rem;
            }
            
            .logo img {
                height: 36px;
            }
            
            /* Mobile menu */
            body.menu-open {
                overflow: hidden;
            }
        }
        
        /* TABLET (769px a 1023px) */
        @media (min-width: 769px) and (max-width: 1023px) {
            .values-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .team-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .videos-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .stat-cards {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .sponsors-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        /* DESKTOP (1024px+) */
        @media (min-width: 1024px) {
            .values-grid {
                grid-template-columns: repeat(3, 1fr);
            }
            
            .team-grid {
                grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            }
            
            .videos-grid {
                grid-template-columns: repeat(4, 1fr);
            }
            
            .sponsors-grid {
                grid-template-columns: repeat(4, 1fr);
            }
            
            .stat-cards {
                grid-template-columns: repeat(4, 1fr);
            }
        }
        
        /* Animations */
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideUp {
            from {
                opacity: 1;
                transform: translateY(0);
            }
            to {
                opacity: 0;
                transform: translateY(-20px);
            }
        }
    </style>
    <?php
    // PHP encerrado aqui - agora vem o HTML puro
    ?>
</head>
<body>
    <!-- BACK TO TOP BUTTON -->
    <button class="back-to-top" onclick="scrollToTop()" title="Voltar ao topo">↑</button>
    
    <!-- MOBILE MENU -->
    <nav class="mobile-menu" id="mobileMenu">
        <a href="#home" onclick="closeMobileMenu()">HOME</a>
        <a href="#valores" onclick="closeMobileMenu()">VALORES</a>
        <a href="#meta" onclick="closeMobileMenu()">META</a>
        <a href="#time" onclick="closeMobileMenu()">TIME</a>
        <a href="#stats" onclick="closeMobileMenu()">STATS</a>
        <a href="#schedule" onclick="closeMobileMenu()">TREINOS</a>
        <a href="#sponsors" onclick="closeMobileMenu()">PATROCINADORES</a>
        <div class="mobile-theme-row" style="padding:16px 0;border-top:1px solid var(--color-border);margin-top:12px;">
            <button id="mobileThemeToggle" class="btn-secondary" onclick="cycleThemeMode(); closeMobileMenu();">Alternar Tema (Auto/Dia/Noite)</button>
        </div>
    </nav>

    <!-- HEADER -->
    <header id="mainHeader">
        <div class="header-inner">
            <div class="logo" onclick="openAdminModal()" title="Clique para acessar o painel admin">
                <img src="Qlaconlogo.jpg" alt="Logo Qlacon Esports" style="height: 50px; width: auto; margin: 0; transition: all 0.3s ease;" onerror="this.style.display='none'">
                <span style="color: var(--color-accent); font-weight: 800; letter-spacing: 2px;">Qlacon Esports</span>
            </div>
            <nav>
                <ul>
                    <li><a href="#home" class="nav-link active">HOME</a></li>
                    <li><a href="#valores" class="nav-link">VALORES</a></li>
                    <li><a href="#meta" class="nav-link">META</a></li>
                    <li><a href="#time" class="nav-link">TIME</a></li>
                    <li><a href="#stats" class="nav-link">STATS</a></li>
                    <li><a href="#schedule" class="nav-link">TREINOS</a></li>
                    <li><a href="#sponsors" class="nav-link">PATROCINADORES</a></li>
                </ul>
            </nav>
            <!-- right-side controls -->
            <div style="display:flex;align-items:center;gap:12px;">
                        <!-- Theme toggle in header (custom SVG switch) -->
                        <button class="theme-toggle" id="headerThemeToggle" aria-label="Alternar Tema" title="Alternar tema">
                                <!-- Sun/Moon combined SVG - we'll toggle class on the button -->
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12" cy="12" r="5" fill="currentColor" />
                                    <g class="sun-rays" stroke="currentColor" stroke-width="1.2" stroke-linecap="round">
                                        <line x1="12" y1="1" x2="12" y2="3" />
                                        <line x1="12" y1="21" x2="12" y2="23" />
                                        <line x1="1" y1="12" x2="3" y2="12" />
                                        <line x1="21" y1="12" x2="23" y2="12" />
                                        <line x1="4.2" y1="4.2" x2="5.8" y2="5.8" />
                                        <line x1="18.2" y1="18.2" x2="19.8" y2="19.8" />
                                        <line x1="4.2" y1="19.8" x2="5.8" y2="18.2" />
                                        <line x1="18.2" y1="5.8" x2="19.8" y2="4.2" />
                                    </g>
                                </svg>
                        </button>
                        <!-- Mobile menu button (moved into header for consistent alignment) -->
                        <button class="mobile-menu-btn" onclick="toggleMobileMenu()" title="Menu" aria-label="Abrir menu móvel"><i class="fas fa-bars"></i></button>
            </div>
        </div>
    </header>

    <!-- HERO -->
    <section id="home" class="hero">
        <div class="container">
            <div style="display: flex; justify-content: center; align-items: center; margin-bottom: 30px;">
                <img src="Qlaconlogo.jpg" alt="Logo Qlacon Esports" style="height: 150px; width: auto; max-width: 100%; filter: drop-shadow(0 0 20px rgba(255, 215, 0, 0.6));">
            </div>
            <h1><span style="background: linear-gradient(90deg, var(--color-accent), #ffed4e); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">QLACON</span> <span style="color: var(--color-text-primary);">ESPORTS</span></h1>
            <div class="subtitle">Time Profissional de Brawl Stars</div>
            <p>Excelência, estratégia e inovação. Somos a força em ascensão no cenário competitivo de Brawl Stars, preparados para dominar torneios e estabelecer novos padrões de performance.</p>
            <div class="actions">
                <a href="#time" class="btn btn-primary">Conheça o Time</a>
                <a href="https://www.youtube.com/@QlaconEsports" target="_blank" class="btn btn-secondary" style="display:inline-flex;align-items:center;gap:8px;"><i class="fab fa-youtube"></i> Canal YouTube</a>
            </div>
            <div style="margin-top: 40px; padding: 20px; background: linear-gradient(135deg, rgba(255, 215, 0, 0.1), rgba(196, 30, 58, 0.08)); border: 2px solid rgba(255, 215, 0, 0.2); border-radius: var(--radius-lg); text-align: center; animation: slideInUp 0.6s ease 0.3s forwards; opacity: 0;">
                <p style="color: var(--color-text-secondary); margin-bottom: 8px; font-size: 0.95rem;">
                    <i class="fas fa-video" style="color: var(--color-accent); margin-right: 8px;"></i>
                    <strong style="color: var(--color-accent);">Acompanhe nosso canal no YouTube</strong>
                </p>
                <p style="color: var(--color-text-muted); margin: 0; font-size: 0.9rem;">
                    Análises, gameplays, treinos e momentos exclusivos de nosso time profissional. Em breve, muito conteúdo sendo preparado! 🚀
                </p>
            </div>
        </div>
    </section>

    <!-- VALORES -->
    <section id="valores" class="values">
        <div class="container">
            <div class="section-title">
                <h2>Nossos Valores</h2>
                <p class="section-subtitle">Princípios que guiam nossa trajetória rumo à excelência</p>
            </div>
            <div class="values-grid">
                <?php if (!empty($valores)): ?>
                    <?php foreach ($valores as $valor): ?>
                    <div class="value-card">
                        <i class="fas <?php echo htmlspecialchars($valor['icone']); ?> value-icon"></i>
                        <h4><?php echo htmlspecialchars($valor['titulo']); ?></h4>
                        <p><?php echo htmlspecialchars($valor['descricao']); ?></p>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="value-card">
                        <i class="fas fa-star value-icon"></i>
                        <h4>Excelência</h4>
                        <p>Buscamos constantemente a perfeição em cada partida, treino e estratégia.</p>
                    </div>
                    <div class="value-card">
                        <i class="fas fa-users value-icon"></i>
                        <h4>Unidade</h4>
                        <p>Trabalho em equipe, comunicação clara e confiança mútua.</p>
                    </div>
                    <div class="value-card">
                        <i class="fas fa-lightbulb value-icon"></i>
                        <h4>Inovação</h4>
                        <p>Estratégias criativas e análise profunda do meta competitivo.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- META -->
    <section id="meta" class="meta">
        <div class="container">
            <div class="section-title">
                <h2>Meta Atual - Brawl Stars</h2>
                <p class="section-subtitle">Dados atualizados em tempo real do Noff.gg • <?php echo htmlspecialchars($meta_data['current_date']); ?></p>
            </div>
            <div class="meta-card">
                <div style="text-align: center; margin-bottom: 24px;">
                    <p style="color: var(--color-text-muted); font-size: 0.9rem;"><strong>Fonte:</strong> <?php echo htmlspecialchars($meta_data['source']); ?> • <?php echo htmlspecialchars($meta_data['last_update']); ?></p>
                </div>

                <!-- Tier Rankings -->
                <div style="margin-bottom: 32px;">
                    <h4 style="color: var(--color-accent); margin-bottom: 20px; text-align: center;">RANKING POR TIER</h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                        <!-- S-Tier -->
                        <div style="background: linear-gradient(135deg, rgba(255, 215, 0, 0.15), rgba(255, 215, 0, 0.05)); border: 2px solid var(--color-accent); padding: 20px; border-radius: var(--radius-md);">
                            <h5 style="color: var(--color-accent); margin-bottom: 12px; font-size: 1.2rem; text-align: center;">🔥 S-TIER</h5>
                            <ul style="list-style: none; color: var(--color-text-secondary);">
                                <?php foreach($meta_data['s_tier'] as $brawler): ?>
                                    <li style="padding: 6px 0; border-bottom: 1px solid rgba(255, 215, 0, 0.1);">
                                        <i class="fas fa-star" style="color: var(--color-accent); margin-right: 8px;"></i><?php echo htmlspecialchars($brawler); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <!-- A-Tier -->
                        <div style="background: linear-gradient(135deg, rgba(226, 27, 50, 0.15), rgba(226, 27, 50, 0.05)); border: 2px solid var(--color-primary); padding: 20px; border-radius: var(--radius-md);">
                            <h5 style="color: var(--color-primary); margin-bottom: 12px; font-size: 1.2rem; text-align: center;">⭐ A-TIER</h5>
                            <ul style="list-style: none; color: var(--color-text-secondary);">
                                <?php foreach($meta_data['a_tier'] as $brawler): ?>
                                    <li style="padding: 6px 0; border-bottom: 1px solid rgba(226, 27, 50, 0.1);">
                                        <i class="fas fa-check-circle" style="color: var(--color-primary); margin-right: 8px;"></i><?php echo htmlspecialchars($brawler); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <!-- B-Tier -->
                        <div style="background: linear-gradient(135deg, rgba(200, 200, 200, 0.15), rgba(200, 200, 200, 0.05)); border: 2px solid rgba(200, 200, 200, 0.5); padding: 20px; border-radius: var(--radius-md);">
                            <h5 style="color: rgba(200, 200, 200, 0.9); margin-bottom: 12px; font-size: 1.2rem; text-align: center;">✓ B-TIER</h5>
                            <ul style="list-style: none; color: var(--color-text-secondary);">
                                <?php foreach($meta_data['b_tier'] as $brawler): ?>
                                    <li style="padding: 6px 0; border-bottom: 1px solid rgba(200, 200, 200, 0.1);">
                                        <i class="fas fa-circle-check" style="color: rgba(200, 200, 200, 0.7); margin-right: 8px;"></i><?php echo htmlspecialchars($brawler); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Popular Brawlers -->
                <div style="background: var(--color-bg-secondary); padding: 24px; border-radius: var(--radius-lg); border: 1px solid rgba(255, 215, 0, 0.1);">
                    <h4 style="color: var(--color-text-primary); margin-bottom: 16px;">📊 TOP 5 MAIS POPULARES (Pick Rate 24h)</h4>
                    <div style="display: grid; gap: 10px;">
                        <?php foreach($meta_data['pick_rate_top'] as $idx => $brawler): ?>
                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px; background: rgba(255, 215, 0, 0.05); border-radius: var(--radius-sm); border-left: 3px solid var(--color-accent);">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <span style="font-weight: 700; color: var(--color-accent); font-size: 1.1rem;">#<?php echo $idx + 1; ?></span>
                                    <span style="color: var(--color-text-primary);"><?php echo htmlspecialchars(explode(' ', $brawler)[0]); ?></span>
                                </div>
                                <span style="color: var(--color-accent); font-weight: 700;"><?php echo htmlspecialchars(strstr($brawler, '(')); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Update Info -->
                <div style="margin-top: 20px; padding: 12px; background: rgba(0, 100, 200, 0.1); border-radius: var(--radius-sm); border-left: 3px solid #0064c8; text-align: center;">
                    <p style="margin: 0; color: var(--color-text-muted); font-size: 0.85rem;">
                        <i class="fas fa-info-circle"></i> Dados obtidos do <a href="https://www.noff.gg/brawl-stars/tier-list" target="_blank" style="color: #0064c8;">Noff.gg</a> • Atualiza a cada 4 horas
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- TIME -->
    <section id="time" class="team">
        <div class="container">
            <div class="section-title">
                <h2>Nosso Time</h2>
                <p class="section-subtitle">Conheça os talentos que compõem a <span style="color: var(--color-accent);">Qlacon Esports</span></p>
                        <!-- SEARCH BAR -->
                        <div class="search-bar">
                            <input type="text" id="playerSearch" placeholder="Buscar por nome ou cargo..." onkeyup="searchPlayers()">
                            <button onclick="searchPlayers()"><i class="fas fa-search"></i></button>
                        </div>
            
                        <!-- SHARE BUTTONS -->
                        <div class="share-buttons">
                            <button class="share-btn" onclick="shareOn('whatsapp')" title="Compartilhar no WhatsApp">
                                <i class="fab fa-whatsapp"></i> WhatsApp
                            </button>
                            <button class="share-btn" onclick="shareOn('twitter')" title="Compartilhar no Twitter">
                                <i class="fab fa-twitter"></i> Twitter
                            </button>
                            <button class="share-btn" onclick="shareOn('facebook')" title="Compartilhar no Facebook">
                                <i class="fab fa-facebook"></i> Facebook
                            </button>
                            <button class="share-btn" onclick="shareOn('linkedin')" title="Compartilhar no LinkedIn">
                                <i class="fab fa-linkedin"></i> LinkedIn
                            </button>
                            <button class="share-btn" onclick="shareOn('youtube')" title="Visitar canal no YouTube">
                                <i class="fab fa-youtube"></i> YouTube
                            </button>
                            <button class="share-btn" onclick="shareOn('instagram')" title="Visitar Instagram">
                                <i class="fab fa-instagram"></i> Instagram
                            </button>
                        </div>
            </div>

            <?php if (isset($_GET['debug']) && $_GET['debug'] === '1'): ?>
            <div class="debug-panel">
                <h4 style="color: var(--color-accent); margin-bottom: 12px;">🔍 DEBUG INFO</h4>
                <p><strong>DB Connected:</strong> <?php echo $db_connected ? '✓ SIM' : '✗ NÃO'; ?></p>
                <?php if (!empty($db_errors)): ?>
                    <p style="color: #ff6b6b; margin-top: 8px;"><strong>Errors:</strong></p>
                    <pre><?php echo htmlspecialchars(implode("\n", $db_errors)); ?></pre>
                <?php endif; ?>
                <p style="margin-top: 8px;"><strong>Players Carregados:</strong> <?php echo count($players); ?></p>
                <pre><?php print_r(array_slice($players, 0, 2)); ?></pre>
                <div style="margin-top:12px;">
                    <button id="btnSyncNow" class="btn" style="background:var(--color-accent); color:var(--color-bg);">Sincronizar agora</button>
                    <span id="syncResult" style="margin-left:12px;color:var(--color-accent);"></span>
                </div>
            </div>
            <?php endif; ?>

            <div class="team-grid">
                <?php foreach ($players as $player): ?>
                <div class="player-card">
                    <div class="player-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="player-info">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <img src="emoji_champie_brazil.png" alt="Brawl Stars" style="height: 24px; width: auto;">
                                <div class="player-name"><?php echo htmlspecialchars($player['nome']); ?></div>
                            </div>
                            <?php if ($player['from_api']): ?>
                                <span style="font-size: 0.7rem; background: var(--color-accent); color: var(--color-bg); padding: 3px 8px; border-radius: 4px; font-weight: 700;">LIVE</span>
                            <?php endif; ?>
                        </div>
                        <div class="player-id"><?php echo htmlspecialchars($player['player_id']); ?></div>
                        <div class="player-role"><?php echo htmlspecialchars($player['funcao']); ?></div>
                        <p class="player-desc"><?php echo htmlspecialchars($player['descricao']); ?></p>
                        <?php
                            $profileLink = $player['profile_url'] ?? ('https://brawlify.com/br/stats/profile/' . ltrim($player['player_id'], '#'));
                        ?>
                        <a class="btn" href="<?php echo htmlspecialchars($profileLink); ?>" target="_blank" rel="noopener noreferrer" title="Abrir perfil no Brawlify" aria-label="Abrir perfil no Brawlify" style="background: linear-gradient(135deg,var(--color-accent),var(--color-primary)); color: var(--color-bg); padding:6px 10px; border-radius:6px; font-weight:700; display:inline-flex; align-items:center; gap:8px; text-decoration:none;">
                            <i class="fas fa-trophy" style="font-size:0.9rem; color: #ffd700;"></i>
                            <span>Ver Perfil</span>
                            <i class="fas fa-external-link-alt" style="font-size:0.8rem; opacity:0.85;"></i>
                        </a>

                        <?php if ($player['from_api']): ?>
                            <div style="margin-top: 8px; padding: 8px; background: rgba(255, 215, 0, 0.1); border-radius: 4px; border-left: 2px solid var(--color-accent);">
                                <p style="font-size: 0.75rem; color: var(--color-text-muted); margin: 0;">
                                    <i class="fas fa-sync-alt"></i> Dados atualizados via Brawlify API
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- VÍDEOS -->
    <section id="videos" class="videos">
        <div class="container">
            <div class="section-title">
                <h2>Conteúdo em Vídeo</h2>
                <p class="section-subtitle">Acompanhe nosso canal oficial no YouTube para análises, treinos e highlights</p>
            </div>

            <div style="display:flex;align-items:center;justify-content:center;gap:20px;flex-direction:column;padding:30px 0;">
                <div style="max-width:720px;text-align:center;background:var(--color-bg-tertiary);padding:24px;border-radius:12px;border:1px solid rgba(255,215,0,0.06);">
                    <h3 style="margin-bottom:8px;color:var(--color-text-primary);">Canal Oficial no YouTube</h3>
                    <p style="color:var(--color-text-muted);margin-bottom:12px;">Já temos canal oficial! Clique no botão abaixo para nos seguir e conferir os vídeos assim que forem publicados.</p>
                    <a class="btn btn-primary" href="https://www.youtube.com/@QlaconEsports" target="_blank" style="display:inline-flex;align-items:center;gap:10px;"><i class="fab fa-youtube"></i> Visitar Canal</a>
                </div>

                <div style="display:flex;gap:12px;flex-wrap:wrap;justify-content:center;margin-top:12px;">
                    <a class="btn btn-secondary" href="mailto:contato.QlaconEsports@outlook.com.br">Enviar E-mail</a>
                    <a class="btn btn-secondary" href="#sponsors">Patrocine-nos</a>
                </div>
            </div>
        </div>
    </section>

    <!-- ESTATÍSTICAS -->
    <section id="stats" class="stats">
        <div class="container">
            <div class="section-title">
                <h2>Estatísticas & Achievements</h2>
                <p class="section-subtitle">Conquistas e marcos da equipe</p>
            </div>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-number"><?php echo $estatisticas['torneios_vencidos'] ?? '0'; ?>+</div>
                    <div class="stat-card-label">Torneios Vencidos</div>
                    <div class="stat-card-desc">Histórico de vitórias</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-number"><?php echo number_format($estatisticas['total_trofeus'] ?? 250000); ?></div>
                    <div class="stat-card-label">Troféus Totais</div>
                    <div class="stat-card-desc">Experiência de elite</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-number"><?php echo $estatisticas['total_fas'] ?? 500; ?></div>
                    <div class="stat-card-label">Brawlers Desbloqueados</div>
                    <div class="stat-card-desc">Domínio completo do jogo</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-number"><?php echo (date('Y') - ($estatisticas['anos_experiencia'] ?? 4)); ?></div>
                    <div class="stat-card-label">Anos Desde 2021</div>
                    <div class="stat-card-desc">Trajetória profissional</div>
                </div>
            </div>
        </div>
    </section>

    <!-- HORÁRIO DE TREINOS -->
    <section id="schedule" class="schedule">
        <div class="container">
            <div class="section-title">
                <h2>Horário de Treinos</h2>
                <p class="section-subtitle">Quando nos encontramos para praticar e competir</p>
            </div>
            <div class="schedule-grid">
                <?php if (!empty($horarios_treino)): ?>
                    <?php foreach ($horarios_treino as $horario): ?>
                    <div class="schedule-card">
                        <div class="schedule-day"><?php echo htmlspecialchars($horario['dia_semana']); ?></div>
                        <div class="schedule-time">
                            <?php
                                $inicio = $horario['hora_inicio'];
                                $fim = $horario['hora_fim'];
                                if ($inicio === '00:00:00' || $inicio === NULL || empty($inicio)) {
                                    echo 'Horário Flexível';
                                } else {
                                    echo htmlspecialchars(substr($inicio, 0, 5)) . ' - ' . htmlspecialchars(substr($fim, 0, 5));
                                }
                            ?>
                        </div>
                        <div class="schedule-activity"><?php echo htmlspecialchars($horario['atividade']); ?></div>
                        <?php if ($horario['descricao']): ?>
                        <div style="margin-top: 8px; font-size: 0.85rem; color: var(--color-text-muted);"><?php echo htmlspecialchars($horario['descricao']); ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="schedule-card">
                        <div class="schedule-day">Seg-Sex</div>
                        <div class="schedule-time">20:00 - 23:00</div>
                        <div class="schedule-activity">Treino Competitivo</div>
                    </div>
                    <div class="schedule-card">
                        <div class="schedule-day">Sábado</div>
                        <div class="schedule-time">19:00 - 22:00</div>
                        <div class="schedule-activity">Treino de Estratégia</div>
                    </div>
                    <div class="schedule-card">
                        <div class="schedule-day">Domingo</div>
                        <div class="schedule-time">18:00 - 21:00</div>
                        <div class="schedule-activity">Análise & Preparação</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- NEWSLETTER -->
    <section id="newsletter" class="newsletter">
        <div class="container">
            <div class="newsletter-content">
                <h2 style="margin-bottom: 12px;">Fique por Dentro</h2>
                <p style="color: var(--color-text-secondary); margin-bottom: 0;">Inscreva-se em nossa newsletter para receber novidades sobre torneios, análises e conteúdo exclusivo</p>
                <form class="newsletter-form" onsubmit="handleNewsletterSubmit(event)">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                    <input type="email" placeholder="seu@email.com" required>
                    <button type="submit">Inscrever</button>
                </form>
            </div>
        </div>
    </section>

    <!-- PATROCINADORES -->
    <section id="sponsors" class="sponsors">
        <div class="container">
            <div class="section-title">
                <h2>Nossos Patrocinadores</h2>
                <p class="section-subtitle">Parceiros que apoiam a excelência da Qlacon Esports</p>
            </div>
            <?php if (!empty($sponsors)): ?>
            <div class="sponsors-grid">
                <?php foreach ($sponsors as $sponsor): ?>
                <div class="sponsor-card">
                    <?php if ($sponsor['logo']): ?>
                        <img src="<?php echo htmlspecialchars($sponsor['logo']); ?>" alt="<?php echo htmlspecialchars($sponsor['nome']); ?>" class="sponsor-logo">
                    <?php else: ?>
                        <div style="text-align: center;">
                            <i class="fas fa-award" style="font-size: 3rem; color: var(--color-accent); margin-bottom: 8px;"></i>
                            <div class="sponsor-name"><?php echo htmlspecialchars($sponsor['nome']); ?></div>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div style="max-width: 720px; margin: 0 auto; text-align: center;">
                <div style="background: var(--color-bg-tertiary); border: 2px solid rgba(212, 175, 55, 0.15); border-radius: var(--radius-lg); padding: 40px;">
                    <i class="fas fa-handshake" style="font-size: 3rem; color: var(--color-accent); margin-bottom: 16px; display: block;"></i>
                    <h3 style="margin-bottom: 12px; color: var(--color-text-primary);">Procuramos Patrocinadores</h3>
                    <p style="color: var(--color-text-secondary); margin-bottom: 16px;">Estamos em busca de parceiros que acreditam na excelência competitiva. Suas marcas estarão destacadas junto a um time profissional de elite.</p>
                    <p style="color: var(--color-text-muted); margin-bottom: 20px; font-size: 0.95rem;">Entre em contato para conhecer oportunidades de patrocínio exclusivas.</p>
                    <button class="btn btn-primary" id="btnBeSponsor" style="cursor: pointer;">Seja um Patrocinador</button>
                </div>

                <!-- Sponsor Registration Form (Hidden by default) -->
                <div id="sponsorFormContainer" style="display: none; margin-top: 30px; background: linear-gradient(135deg, rgba(212, 175, 55, 0.1), rgba(255, 215, 0, 0.05)); border: 2px solid rgba(212, 175, 55, 0.2); border-radius: var(--radius-lg); padding: 40px; animation: slideDown 0.3s ease;">
                    <h4 style="text-align: center; color: var(--color-accent); margin-bottom: 24px; font-size: 1.2rem;">Inscreva-se como Patrocinador</h4>
                    <form id="sponsorForm" style="display: flex; flex-direction: column; gap: 16px;">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                        <div>
                            <label style="display: block; margin-bottom: 8px; color: var(--color-text-primary); font-weight: 600;">Nome Completo</label>
                            <input type="text" name="nome" placeholder="Seu nome" required style="width: 100%; padding: 12px; border: 1px solid rgba(212, 175, 55, 0.3); border-radius: 6px; background: var(--color-bg); color: var(--color-text-primary); font-family: inherit; box-sizing: border-box;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 8px; color: var(--color-text-primary); font-weight: 600;">E-mail</label>
                            <input type="email" name="email" placeholder="seu@email.com" required style="width: 100%; padding: 12px; border: 1px solid rgba(212, 175, 55, 0.3); border-radius: 6px; background: var(--color-bg); color: var(--color-text-primary); font-family: inherit; box-sizing: border-box;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 8px; color: var(--color-text-primary); font-weight: 600;">Telefone</label>
                            <input type="tel" name="telefone" placeholder="(11) 99999-9999" required style="width: 100%; padding: 12px; border: 1px solid rgba(212, 175, 55, 0.3); border-radius: 6px; background: var(--color-bg); color: var(--color-text-primary); font-family: inherit; box-sizing: border-box;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 8px; color: var(--color-text-primary); font-weight: 600;">Empresa</label>
                            <input type="text" name="empresa" placeholder="Nome da sua empresa" required style="width: 100%; padding: 12px; border: 1px solid rgba(212, 175, 55, 0.3); border-radius: 6px; background: var(--color-bg); color: var(--color-text-primary); font-family: inherit; box-sizing: border-box;">
                        </div>
                        <div style="display: flex; gap: 12px;">
                            <button type="submit" class="btn btn-primary" style="flex: 1;">Enviar Inscrição</button>
                            <button type="button" id="btnCancelSponsor" class="btn" style="flex: 1; background: rgba(212, 175, 55, 0.2); color: var(--color-accent);">Cancelar</button>
                        </div>
                        <span id="sponsorResult" style="text-align: center; margin-top: 12px; font-size: 0.9rem; color: var(--color-accent);"></span>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- FOOTER -->
    <footer>
        <div class="container">
            <div style="display:flex;gap:24px;align-items:center;justify-content:space-between;padding:28px 0;border-bottom:1px solid var(--color-border);margin-bottom:18px;">
                <div style="display:flex;align-items:center;gap:16px;">
                    <img src="Qlaconlogo.jpg" alt="Logo Qlacon Esports" style="height:64px;width:auto;max-width:100%;">
                    <div>
                        <strong style="color:var(--color-accent);">Qlacon Esports</strong>
                        <div style="color:var(--color-text-muted);font-size:0.9rem;">Competitivo • Parcerias • Treinos</div>
                    </div>
                </div>
                <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
                    <a href="mailto:contato.QlaconEsports@outlook.com.br" class="btn-secondary">contato.QlaconEsports@outlook.com.br</a>
                    <a href="https://www.youtube.com/@QlaconEsports" target="_blank" class="btn-secondary" style="display:inline-flex;align-items:center;gap:8px;"><i class="fab fa-youtube"></i> Canal YouTube</a>
                    <a href="https://www.instagram.com/qlaconesports/" target="_blank" class="btn-secondary" style="display:inline-flex;align-items:center;gap:8px;"><i class="fab fa-instagram"></i> Instagram</a>
                    <button class="btn-secondary" onclick="toggleStyleGuide()">Ver Style Guide</button>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; 2025 <span style="color: var(--color-accent); font-weight: 700;">QLACON ESPORTS</span>. Todos os direitos reservados. • <a href="#" style="color:var(--color-text-secondary);text-decoration:none;">Política de Privacidade</a></p>
            </div>
        </div>
    </footer>

    <!-- STYLE GUIDE (modal) -->
    <div class="style-guide-modal" id="styleGuideModal" role="dialog" aria-hidden="true">
        <div class="style-guide-overlay" onclick="closeStyleGuideModal()"></div>
        <div class="style-guide-content" role="document">
            <button class="style-guide-close" onclick="closeStyleGuideModal()">✕</button>
            <h3 style="margin-bottom:12px;color:var(--color-text-primary);">Style Guide Rápido</h3>
            <div style="display:flex;gap:24px;flex-wrap:wrap;align-items:flex-start;">
                <div style="min-width:240px;">
                    <h4 style="margin-bottom:8px;color:var(--color-text-secondary);">Cores</h4>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <div style="width:64px;height:64px;border-radius:8px;background:var(--color-bg);border:1px solid var(--color-border);"></div>
                        <div style="width:64px;height:64px;border-radius:8px;background:var(--color-bg-secondary);border:1px solid var(--color-border);"></div>
                        <div style="width:64px;height:64px;border-radius:8px;background:var(--color-accent);border:1px solid rgba(0,0,0,0.05);"></div>
                    </div>
                </div>
                <div style="min-width:240px;">
                    <h4 style="margin-bottom:8px;color:var(--color-text-secondary);">Tipografia</h4>
                    <div style="font-family:'Sora',sans-serif;font-weight:700;font-size:1.2rem;color:var(--color-text-primary);">H1 — Sora Bold</div>
                    <div style="font-family:'Inter',sans-serif;color:var(--color-text-secondary);">Body — Inter Regular</div>
                </div>
                <div style="flex:1;min-width:240px;">
                    <h4 style="margin-bottom:8px;color:var(--color-text-secondary);">Componentes</h4>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <button class="btn-primary">Primário</button>
                        <button class="btn-secondary">Secundário</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Handle Newsletter Submission
        function handleNewsletterSubmit(event) {
            event.preventDefault();
            const email = event.target.querySelector('input[type="email"]').value;
            const button = event.target.querySelector('button');
            const originalText = button.textContent;
            
            // Send to server
            fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=subscribe_newsletter&email=' + encodeURIComponent(email)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    button.textContent = '✓ E-mail adicionado!';
                    button.style.background = 'linear-gradient(135deg, #22c55e, #16a34a)';
                    event.target.reset();
                } else {
                    button.textContent = '✗ ' + (data.message || 'Erro ao inscrever');
                    button.style.background = 'linear-gradient(135deg, #ef4444, #dc2626)';
                }
                
                setTimeout(() => {
                    button.textContent = originalText;
                    button.style.background = '';
                }, 3000);
            })
            .catch(error => {
                console.error('Error:', error);
                button.textContent = '✗ Erro na conexão';
                setTimeout(() => {
                    button.textContent = originalText;
                    button.style.background = '';
                }, 3000);
            });
        }

        // Header scroll effect
        const header = document.getElementById('mainHeader');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const target = document.querySelector(link.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        // Active nav link
        window.addEventListener('scroll', () => {
            const sections = document.querySelectorAll('section');
            const navLinks = document.querySelectorAll('.nav-link');
            
            let currentSection = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                if (pageYOffset >= sectionTop - 200) {
                    currentSection = section.getAttribute('id');
                }
            });

            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === `#${currentSection}`) {
                    link.classList.add('active');
                }
            });
        });
    </script>
    <script>
        // Toggle Sponsor Form
        document.addEventListener('DOMContentLoaded', () => {
            const btnBeSponsor = document.getElementById('btnBeSponsor');
            const sponsorFormContainer = document.getElementById('sponsorFormContainer');
            const btnCancelSponsor = document.getElementById('btnCancelSponsor');
            
            if (!btnBeSponsor || !sponsorFormContainer) return;
            
            btnBeSponsor.addEventListener('click', () => {
                sponsorFormContainer.style.display = 'block';
                btnBeSponsor.style.display = 'none';
                sponsorFormContainer.scrollIntoView({ behavior: 'smooth' });
            });
            
            btnCancelSponsor.addEventListener('click', () => {
                sponsorFormContainer.style.display = 'none';
                btnBeSponsor.style.display = 'block';
                document.getElementById('sponsorResult').textContent = '';
                document.getElementById('sponsorForm').reset();
            });
        });
    </script>
    <script>
        // Sponsor Form Handler
        document.addEventListener('DOMContentLoaded', () => {
            const sponsorForm = document.getElementById('sponsorForm');
            if (!sponsorForm) return;
            
            sponsorForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(sponsorForm);
                const button = sponsorForm.querySelector('button');
                const resultEl = document.getElementById('sponsorResult');
                const originalText = button.textContent;
                
                button.disabled = true;
                button.textContent = 'Enviando...';
                resultEl.textContent = '';
                
                try {
                    const response = await fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams(formData) + '&action=sponsor_register'
                    });
                    const data = await response.json();
                    
                    if (data.success) {
                        resultEl.textContent = '✓ ' + data.message;
                        resultEl.style.color = '#22c55e';
                        sponsorForm.reset();
                        setTimeout(() => {
                            resultEl.textContent = '';
                        }, 5000);
                    } else {
                        resultEl.textContent = '✗ ' + data.message;
                        resultEl.style.color = '#ef4444';
                    }
                } catch (error) {
                    resultEl.textContent = '✗ Erro na requisição';
                    resultEl.style.color = '#ef4444';
                }
                
                button.disabled = false;
                button.textContent = originalText;
            });
        });
    </script>
    <script>
        // Sync button handler (visible only on debug mode)
        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.getElementById('btnSyncNow');
            if (!btn) return;
            btn.addEventListener('click', async () => {
                btn.disabled = true;
                const original = btn.textContent;
                btn.textContent = 'Sincronizando...';
                const token = '<?php echo $_SESSION['sync_token']; ?>';
                try {
                    const res = await fetch('sync_trigger.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'token=' + encodeURIComponent(token)
                    });
                    const data = await res.json();
                    const el = document.getElementById('syncResult');
                    if (data.success) {
                        el.textContent = '✓ Sincronização concluída';
                    } else {
                        el.textContent = '✗ Erro: ' + (data.message || JSON.stringify(data));
                    }
                } catch (e) {
                    document.getElementById('syncResult').textContent = '✗ Falha na requisição';
                }
                btn.textContent = original;
                btn.disabled = false;
            });
        });
    </script>
    
    <!-- ADMIN MODAL -->
    <div id="adminModal" class="admin-modal">
        <div class="admin-modal-content">
            <div class="admin-modal-header">
                <h2>🔐 Painel Administrativo</h2>
                <button class="admin-close-btn" onclick="closeAdminModal()">&times;</button>
            </div>
            
            <div id="adminLoginForm">
                <div class="admin-form-group">
                    <label>Senha de Admin</label>
                    <div style="position: relative;">
                        <input type="password" id="adminPassword" placeholder="Digite a senha" onkeypress="if(event.key==='Enter') loginAdmin()" style="padding-right: 40px;">
                        <button type="button" onclick="togglePasswordVisibility()" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--color-accent); font-size: 1.2rem; padding: 0;">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>
                <button class="admin-submit-btn" onclick="loginAdmin()">Acessar</button>
                <div id="adminError" class="admin-error" style="display: none;"></div>
            </div>
            
            <div id="adminPanel" style="display: none;">
                <div style="margin-bottom: 24px;">
                    <button class="admin-submit-btn" onclick="logoutAdmin()" style="background: #c41e3a;">Sair</button>
                </div>
                
                <h3 style="color: var(--color-accent); margin-bottom: 16px; font-size: 1.2rem;">📋 Inscrições de Patrocinadores</h3>
                <div id="sponsorsContainer" style="max-height: 400px; overflow-y: auto;"></div>
            </div>
        </div>
    </div>
    
    <script>
        const ADMIN_PASSWORD = '0125Qlaconadministracao';
        let adminLoggedIn = false;
        
        function togglePasswordVisibility() {
            const input = document.getElementById('adminPassword');
            const icon = document.getElementById('eyeIcon');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        function openAdminModal() {
            document.getElementById('adminModal').classList.add('active');
            if (adminLoggedIn) {
                loadSponsorsList();
            }
        }
        
        function closeAdminModal() {
            document.getElementById('adminModal').classList.remove('active');
        }
        
        function loginAdmin() {
            const password = document.getElementById('adminPassword').value;
            const errorDiv = document.getElementById('adminError');
            
            if (password === ADMIN_PASSWORD) {
                adminLoggedIn = true;
                document.getElementById('adminLoginForm').style.display = 'none';
                document.getElementById('adminPanel').style.display = 'block';
                loadSponsorsList();
            } else {
                errorDiv.textContent = '❌ Senha incorreta!';
                errorDiv.style.display = 'block';
                document.getElementById('adminPassword').value = '';
            }
        }
        
        function logoutAdmin() {
            adminLoggedIn = false;
            document.getElementById('adminLoginForm').style.display = 'block';
            document.getElementById('adminPanel').style.display = 'none';
            document.getElementById('adminPassword').value = '';
            document.getElementById('adminError').style.display = 'none';
        }
        
        function loadSponsorsList() {
            fetch('<?php echo $_SERVER['PHP_SELF']; ?>?action=get_sponsors')
                .then(response => {
                    if (!response.ok) throw new Error('HTTP ' + response.status);
                    const ct = response.headers.get('content-type') || '';
                    if (!ct.includes('application/json')) {
                        return response.text().then(t => { throw new Error('Resposta não é JSON: ' + (t || '').slice(0, 300)); });
                    }
                    return response.json();
                })
                .then(data => {
                    const container = document.getElementById('sponsorsContainer');
                    
                    // Check for error response
                    if (data.error) {
                        container.innerHTML = '<p style="color: #f44336;">❌ ' + data.error + '</p>';
                        return;
                    }
                    
                    if (!Array.isArray(data) || data.length === 0) {
                        container.innerHTML = '<p style="color: #999; text-align: center;">✓ Nenhuma inscrição ainda.</p>';
                        return;
                    }
                    
                    let html = '<table style="width: 100%; border-collapse: collapse;">';
                    html += '<tr style="background: rgba(212, 175, 55, 0.1); border-bottom: 2px solid rgba(212, 175, 55, 0.3);">';
                    html += '<th style="padding: 10px; text-align: left; color: #ffd700;">Nome</th>';
                    html += '<th style="padding: 10px; text-align: left; color: #ffd700;">E-mail</th>';
                    html += '<th style="padding: 10px; text-align: left; color: #ffd700;">Status</th>';
                    html += '<th style="padding: 10px; text-align: left; color: #ffd700;">Data</th>';
                    html += '</tr>';
                    
                    data.forEach(sponsor => {
                        const statusColor = sponsor.status === 'pendente' ? '#ffc107' : (sponsor.status === 'aprovado' ? '#4caf50' : '#f44336');
                        const statusText = sponsor.status === 'pendente' ? '⏳' : (sponsor.status === 'aprovado' ? '✓' : '✗');
                        const data_inscricao = new Date(sponsor.data_inscricao);
                        const dataFormatada = data_inscricao.toLocaleDateString('pt-BR') + ' ' + data_inscricao.toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'});
                        
                        html += '<tr style="border-bottom: 1px solid rgba(212, 175, 55, 0.1);">';
                        html += '<td style="padding: 10px;"><strong>' + sponsor.nome + '</strong></td>';
                        html += '<td style="padding: 10px; font-size: 0.85rem;">' + sponsor.email + '</td>';
                        html += '<td style="padding: 10px;"><span style="color: ' + statusColor + '; font-size: 0.85rem;">' + statusText + ' ' + sponsor.status + '</span></td>';
                        html += '<td style="padding: 10px; font-size: 0.8rem; color: #999;">' + dataFormatada + '</td>';
                        html += '</tr>';
                    });
                    
                    html += '</table>';
                    container.innerHTML = html;
                })
                .catch(error => {
                    console.error('Erro:', error);
                    document.getElementById('sponsorsContainer').innerHTML = '<p style="color: #f44336;">❌ Erro ao carregar dados: ' + error.message + '</p>';
                });
        }
        
        // Fechar modal ao clicar fora
        document.getElementById('adminModal').addEventListener('click', (e) => {
            if (e.target === document.getElementById('adminModal')) {
                closeAdminModal();
            }
        });

        // ========== THEME MODE: auto / day / night ==========
        // Valores em localStorage: 'themeMode' = 'auto'|'day'|'night'
        function getStoredThemeMode() {
            return localStorage.getItem('themeMode') || 'auto';
        }

        function isDayTimeNow() {
            const h = new Date().getHours();
            return h >= 7 && h < 19; // 07:00-18:59 considered day
        }

        function prefersSystemDark() {
            try {
                return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            } catch (e) {
                return false;
            }
        }

        function applyThemeFromMode(mode) {
            // Determine effective theme: if 'auto' prefer system setting, fallback to time of day
            let effective;
            if (mode === 'auto') {
                if (typeof window !== 'undefined' && window.matchMedia) {
                    effective = prefersSystemDark() ? 'night' : 'day';
                } else {
                    effective = isDayTimeNow() ? 'day' : 'night';
                }
            } else {
                effective = mode;
            }
            if (effective === 'day') {
                document.body.classList.add('light-mode');
            } else {
                document.body.classList.remove('light-mode');
            }
            // mark body with data-theme for easier CSS selectors
            document.body.setAttribute('data-theme', effective);
        }

        function setThemeMode(mode) {
            localStorage.setItem('themeMode', mode);
            applyThemeFromMode(mode);
            updateThemeButton();
        }

        function cycleThemeMode() {
            const order = ['auto', 'day', 'night'];
            const current = getStoredThemeMode();
            const next = order[(order.indexOf(current) + 1) % order.length];
            setThemeMode(next);
        }

        function updateThemeButton() {
            const btn = document.getElementById('headerThemeToggle');
            if (!btn) return;
            const mode = getStoredThemeMode();
            let effective;
            if (mode === 'auto') {
                if (typeof window !== 'undefined' && window.matchMedia) {
                    effective = prefersSystemDark() ? 'night' : 'day';
                } else {
                    effective = isDayTimeNow() ? 'day' : 'night';
                }
            } else {
                effective = mode;
            }
            btn.title = 'Tema: ' + (mode === 'auto' ? 'Auto (' + effective + ')' : (mode === 'day' ? 'Diurno' : 'Noturno'));
            // change color and data attribute based on effective mode (used for icon animation)
            btn.setAttribute('data-theme', effective);
            if (effective === 'day') {
                btn.style.color = 'var(--color-accent)';
            } else {
                btn.style.color = 'var(--color-text-secondary)';
            }
            // also update mobile toggle text if exists
            const mobileBtn = document.getElementById('mobileThemeToggle');
            if (mobileBtn) {
                const label = mode === 'auto' ? `Auto (${effective})` : (mode === 'day' ? 'Diurno' : 'Noturno');
                mobileBtn.textContent = 'Alternar Tema (' + label + ')';
            }
        }

        // Attach click handler (cycle auto -> day -> night)
        const headerToggleBtn = document.getElementById('headerThemeToggle');
        if (headerToggleBtn) headerToggleBtn.addEventListener('click', cycleThemeMode);

        // sync across tabs/windows
        window.addEventListener('storage', (e) => {
            if (e.key === 'themeMode') {
                applyThemeFromMode(getStoredThemeMode());
                updateThemeButton();
            }
        });

        // Initialize theme at load
        applyThemeFromMode(getStoredThemeMode());
        updateThemeButton();

        // ========== BACK TO TOP BUTTON ==========
        window.addEventListener('scroll', () => {
            const backToTop = document.querySelector('.back-to-top');
            if (!backToTop) return;
            if (window.pageYOffset > 300) {
                backToTop.classList.add('show');
            } else {
                backToTop.classList.remove('show');
            }
        });

        function scrollToTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // ========== MOBILE MENU ==========
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            if (!menu) return;
            const willOpen = !menu.classList.contains('active');
            menu.classList.toggle('active');
            // Toggle body lock to prevent background scroll when mobile menu open
            document.body.classList.toggle('menu-open', willOpen);
        }

        function closeMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            if (menu) menu.classList.remove('active');
            document.body.classList.remove('menu-open');
        }

        document.querySelectorAll('.mobile-menu a').forEach(link => {
            link.addEventListener('click', closeMobileMenu);
        });

        // ========== PLAYER SEARCH ==========
        function searchPlayers() {
            const searchTerm = document.getElementById('playerSearch') ? document.getElementById('playerSearch').value.toLowerCase() : '';
            const playerCards = document.querySelectorAll('.player-card');

            playerCards.forEach(card => {
                const nameEl = card.querySelector('h3');
                const roleEl = card.querySelector('.player-role');
                const playerName = nameEl ? nameEl.textContent.toLowerCase() : '';
                const playerRole = roleEl ? roleEl.textContent.toLowerCase() : '';

                if (playerName.includes(searchTerm) || playerRole.includes(searchTerm)) {
                    card.style.display = '';
                    card.style.animation = 'slideInUp 0.4s ease';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // ========== SOCIAL SHARE ==========
        function shareOn(platform) {
            const url = window.location.href;
            const text = 'Confira o nosso time profissional de Brawl Stars!';
            let shareUrl = '';

            switch(platform) {
                case 'whatsapp':
                    shareUrl = `https://wa.me/?text=${encodeURIComponent(text + ' ' + url)}`;
                    break;
                case 'twitter':
                    shareUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(url)}`;
                    break;
                case 'facebook':
                    shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
                    break;
                case 'linkedin':
                    shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}`;
                    break;
                case 'youtube':
                    // Abrir canal do YouTube em nova aba
                    shareUrl = 'https://www.youtube.com/@QlaconEsports';
                    break;
                case 'instagram':
                    // Instagram não possui endpoint de share direto para páginas; abrir perfil
                    shareUrl = 'https://www.instagram.com/qlaconesports/';
                    break;
            }

            if (shareUrl) window.open(shareUrl, '_blank', 'width=600,height=400');
        }

        // ========== SCROLL ANIMATIONS ==========
        const observerOptions = { threshold: 0.1, rootMargin: '0px 0px -50px 0px' };
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-on-scroll');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        document.querySelectorAll('section, .value-card, .player-card, .stat-card, .schedule-card, .sponsor-card').forEach(el => {
            observer.observe(el);
        });

        // ========== ANIMATED STAT COUNTERS ==========
        function animateCounter(element, target, duration = 2000) {
            const increment = target / (duration / 16);
            let current = 0;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    element.textContent = target.toLocaleString('pt-BR');
                    clearInterval(timer);
                } else {
                    element.textContent = Math.floor(current).toLocaleString('pt-BR');
                }
            }, 16);
        }

        const counterObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !entry.target.dataset.animated) {
                    entry.target.dataset.animated = 'true';
                    const target = parseInt(entry.target.textContent.replace(/\D/g, '')) || 0;
                    if (target > 0) animateCounter(entry.target, target);
                    counterObserver.unobserve(entry.target);
                }
            });
        }, observerOptions);

        document.querySelectorAll('.stat-counter').forEach(el => counterObserver.observe(el));

        // ========== GALLERY & TOOLTIP INIT ==========
        function initTooltips() {
            document.querySelectorAll('[title]').forEach(el => {
                if (el.classList.contains('tooltip')) return;
                el.classList.add('tooltip');
                const title = el.getAttribute('title');
                const tooltip = document.createElement('span');
                tooltip.className = 'tooltiptext';
                tooltip.textContent = title;
                el.appendChild(tooltip);
                el.removeAttribute('title');
            });
        }

        function initGallery() {
            document.querySelectorAll('img[data-gallery]').forEach(img => {
                img.style.cursor = 'pointer';
                img.addEventListener('click', (e) => {
                    showGalleryModal(e.target.src);
                });
            });
        }

        function showGalleryModal(imgSrc) {
            const modal = document.createElement('div');
            modal.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.8);display:flex;align-items:center;justify-content:center;z-index:1000;cursor:pointer;';
            const img = document.createElement('img');
            img.src = imgSrc;
            img.style.cssText = 'max-width:90%;max-height:90%;border-radius:8px;box-shadow:0 0 40px rgba(212,175,55,0.5);';
            modal.appendChild(img);
            document.body.appendChild(modal);
            modal.addEventListener('click', () => modal.remove());
            img.addEventListener('click', (e) => e.stopPropagation());
        }

        initTooltips();
        initGallery();
        
        // ========== STYLE GUIDE MODAL HANDLERS ==========
        function openStyleGuideModal() {
            const modal = document.getElementById('styleGuideModal');
            if (!modal) return;
            modal.classList.add('active');
            modal.setAttribute('aria-hidden', 'false');
        }

        function closeStyleGuideModal() {
            const modal = document.getElementById('styleGuideModal');
            if (!modal) return;
            modal.classList.remove('active');
            modal.setAttribute('aria-hidden', 'true');
        }

        // previous toggle used by footer button, now opens modal
        function toggleStyleGuide() {
            const modal = document.getElementById('styleGuideModal');
            if (!modal) return;
            if (modal.classList.contains('active')) closeStyleGuideModal(); else openStyleGuideModal();
        }
    </script>
</body>
</html>
