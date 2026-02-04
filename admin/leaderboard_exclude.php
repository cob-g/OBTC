<?php
require __DIR__ . '/../app/bootstrap.php';
require_role('admin');

// Handle AJAX requests to exclude/include clients from leaderboard
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $clientId = (int) ($_POST['client_id'] ?? 0);
    $action = trim((string) ($_POST['action'] ?? ''));
    
    if ($clientId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid client ID']);
        exit;
    }
    
    if (!in_array($action, ['exclude', 'include'], true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit;
    }
    
    try {
        // Check if column exists
        $hasColumn = db_has_column('clients', 'excluded_from_leaderboard');
        
        if (!$hasColumn) {
            // Add column if it doesn't exist
            db()->exec('ALTER TABLE clients ADD COLUMN excluded_from_leaderboard TINYINT(1) NOT NULL DEFAULT 0');
        }
        
        $excludeValue = $action === 'exclude' ? 1 : 0;
        
        $stmt = db()->prepare('UPDATE clients SET excluded_from_leaderboard = ? WHERE id = ?');
        $stmt->execute([$excludeValue, $clientId]);
        
        echo json_encode([
            'success' => true,
            'message' => $action === 'exclude' 
                ? 'Client excluded from leaderboard successfully' 
                : 'Client included back in leaderboard',
            'excluded' => $excludeValue
        ]);
        
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
    
    exit;
}

// Redirect if accessed directly
header('Location: ' . url('/admin/leaderboard.php'));
exit;
