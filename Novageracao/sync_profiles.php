<?php
// Sync Profiles Script
// Usage (CLI): C:\xampp\php\php.exe sync_profiles.php your_secret_key
// Usage (HTTP): http://localhost/Novageracao/sync_profiles.php?key=your_secret_key

require_once __DIR__ . '/config_sync.php';

$key = null;
if (php_sapi_name() === 'cli') {
    $key = $argv[1] ?? null;
} else {
    $key = $_GET['key'] ?? null;
}

if ($key !== ($SYNC_SECRET ?? null)) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$host = 'localhost';
$user = 'root';
$password = '';
$database = 'novageracao_db';

function fetchBrawlifyPlayerAPI($player_id) {
    $api_url = 'https://api.brawlify.com/v1/players/' . urlencode($player_id);
    if (!function_exists('curl_init')) return null;
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36');
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code === 200 && $resp) {
        $data = json_decode($resp, true);
        if (isset($data['result'])) {
            $p = $data['result'];
            return [
                'trofeus' => $p['trophies'] ?? null,
                'brawlers' => isset($p['brawlers']) ? count($p['brawlers']) : null,
                'experiencia' => $p['expPoints'] ?? null,
                'nivel' => $p['expLevel'] ?? null,
            ];
        }
    }
    return null;
}

function fetchBrawlifyWinsFromPage($player_id) {
    // Remove possible # from player tag
    $pid = ltrim($player_id, '#');
    $url = 'https://brawlify.com/br/stats/profile/' . urlencode($pid);
    if (!function_exists('curl_init')) return null;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 12);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36');
    $html = curl_exec($ch);
    curl_close($ch);
    if (!$html) return null;

    // Try several regex patterns to be resilient to variations
    $patterns = [
        '/([\d\.\,]+)\s*(?:<[^>]+>\s*)?(?:3v3|3x3)\b/i',
        '/3v3[^>]{0,40}>([\d\.\,]+)/i',
        '/Vitórias\s*3v3[^0-9]{0,40}([\d\.\,]+)/i',
        '/3v3\s+Wins[^0-9]{0,40}([\d\.\,]+)/i',
    ];

    foreach ($patterns as $pat) {
        if (preg_match($pat, $html, $m)) {
            $num = preg_replace('/[^0-9]/', '', $m[1]);
            if ($num !== '') return (int)$num;
        }
    }

    // As fallback, try to find a small block where a label contains "3v3" and a nearby number
    if (preg_match_all('/([\s\S]{0,120}?)(3v3|3x3|Vitórias 3v3|3v3 Wins)([\s\S]{0,120}?)/i', $html, $blocks)) {
        foreach ($blocks[0] as $block) {
            if (preg_match('/([\d\.\,]{1,12})/', $block, $m2)) {
                $num = preg_replace('/[^0-9]/', '', $m2[1]);
                if ($num !== '') return (int)$num;
            }
        }
    }

    return null;
}

function fetchStatsFromPage($player_id) {
    $pid = ltrim($player_id, '#');
    $url = 'https://brawlify.com/br/stats/profile/' . urlencode($pid);
    if (!function_exists('curl_init')) return null;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 12);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36');
    $html = curl_exec($ch);
    curl_close($ch);
    if (!$html) return null;

    $result = ['trofeus' => null, 'brawlers' => null];

    // Troféus (Trophies / Troféus)
    if (preg_match('/(Trophies|Trof[eé]us)[^0-9]{0,40}([\d\.\,]+)/i', $html, $m)) {
        $n = preg_replace('/[^0-9]/', '', $m[2]);
        if ($n !== '') $result['trofeus'] = (int)$n;
    } elseif (preg_match('/([\d\.\,]+)\s*(?:<[^>]+>\s*)?(?:trophies|trof[eé]us)\b/i', $html, $m2)) {
        $n = preg_replace('/[^0-9]/', '', $m2[1]);
        if ($n !== '') $result['trofeus'] = (int)$n;
    }

    // Brawlers count (try to find "Brawlers" label or similar)
    if (preg_match('/Brawlers[^0-9]{0,40}([\d\.\,]+)/i', $html, $mb)) {
        $nb = preg_replace('/[^0-9]/', '', $mb[1]);
        if ($nb !== '') $result['brawlers'] = (int)$nb;
    } elseif (preg_match_all('/class="brawler"|brawler-card/i', $html, $dummy)) {
        // fallback: count occurrences of brawler elements
        if (preg_match_all('/brawler-card|class="brawler"/i', $html, $matches)) {
            $result['brawlers'] = count($matches[0]);
        }
    }

    return $result;
}

 $conn = @new mysqli($host, $user, $password, $database);
if ($conn->connect_errno) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed', 'error' => $conn->connect_error]);
    exit;
}

$summary = ['updated' => [], 'skipped' => [], 'errors' => []];

$res = $conn->query("SELECT id, nome, player_id, profile_url, trofeus, brawlers, vitorias_3x3 FROM jogadores WHERE ativo = 1");
if ($res) {
    $stmt = $conn->prepare("UPDATE jogadores SET trofeus = ?, brawlers = ?, vitorias_3x3 = ?, ultima_atualizacao = NOW() WHERE id = ?");
    while ($row = $res->fetch_assoc()) {
        $id = $row['id'];
        $pid = $row['player_id'];
        $pid_clean = ltrim($pid, '#');
        $profile_url = isset($row['profile_url']) && $row['profile_url'] ? $row['profile_url'] : null;
        $api = fetchBrawlifyPlayerAPI($pid_clean);
        $trofeus = $api['trofeus'] ?? null;
        $brawlers = $api['brawlers'] ?? null;

        // Try scraping wins from page (prioritize page scraping for 3x3 wins)
        if ($profile_url) {
            $wins = fetchBrawlifyWinsFromPage($profile_url);
            $pageStats = fetchStatsFromPage($profile_url);
        } else {
            $wins = fetchBrawlifyWinsFromPage($pid_clean);
            $pageStats = fetchStatsFromPage($pid_clean);
        }

        // If API failed to return trofeus/brawlers, use values found on page
        if ($trofeus === null && !empty($pageStats['trofeus'])) {
            $trofeus = $pageStats['trofeus'];
        }
        if ($brawlers === null && !empty($pageStats['brawlers'])) {
            $brawlers = $pageStats['brawlers'];
        }

        // Final fallbacks
        $trofeus_val = is_numeric($trofeus) ? (int)$trofeus : null;
        $brawlers_val = is_numeric($brawlers) ? (int)$brawlers : null;
        $wins_val = is_numeric($wins) ? (int)$wins : null;

        // Existing DB values (to avoid overwriting with zeros when remote fails)
        $existingTro = (int)($row['trofeus'] ?? 0);
        $existingBraw = (int)($row['brawlers'] ?? 0);
        $existingWins = (int)($row['vitorias_3x3'] ?? 0);

        // If we have trofeus but no wins, estimate wins as floor(trofeus/20)
        if ($wins_val === null && $trofeus_val !== null) {
            $wins_val = (int)floor($trofeus_val / 20);
        }

        // Use existing DB values when remote result is null
        $curTro = ($trofeus_val !== null) ? $trofeus_val : $existingTro;
        $curBraw = ($brawlers_val !== null) ? $brawlers_val : $existingBraw;
        $curWins = ($wins_val !== null) ? $wins_val : $existingWins;

        if ($stmt) {
            $stmt->bind_param('iiii', $curTro, $curBraw, $curWins, $id);
            if ($stmt->execute()) {
                $summary['updated'][] = ['id' => $id, 'nome' => $row['nome'], 'trofeus' => $curTro, 'brawlers' => $curBraw, 'vitorias_3x3' => $curWins];
            } else {
                $summary['errors'][] = ['id' => $id, 'error' => $stmt->error];
            }
        } else {
            $summary['errors'][] = ['message' => 'Prepare failed: ' . $conn->error];
            break;
        }
        // small delay to be polite
        usleep(200000);
    }
    if ($stmt) $stmt->close();
} else {
    $summary['errors'][] = ['message' => 'No players result: ' . $conn->error];
}

$conn->close();

// Try to insert a sync log (best-effort)
try {
    $conn2 = @new mysqli($host, $user, $password, $database);
    if (!$conn2->connect_errno) {
        $updatedCount = count($summary['updated']);
        $errorsCount = count($summary['errors']);
        $updatedJson = $conn2->real_escape_string(json_encode($summary['updated'], JSON_UNESCAPED_UNICODE));
        $conn2->query("INSERT INTO sync_logs (executed_at, updated_count, error_count, details) VALUES (NOW(), $updatedCount, $errorsCount, '$updatedJson')");
        $conn2->close();
    }
} catch (Exception $e) {
    // ignore logging errors
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['success' => true, 'summary' => $summary], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

?>
