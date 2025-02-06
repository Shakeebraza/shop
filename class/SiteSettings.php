<?php
class SiteSettings {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getValueByKey($key) {

        $stmt = $this->pdo->prepare("SELECT value FROM site_settings WHERE `key` = :key LIMIT 1");
        $stmt->bindParam(':key', $key, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            return $result['value'];
        } else {
            return null; 
        }
    } 
    function getCreditCardBaseNames() {
    
        $sql = "SELECT DISTINCT base_name FROM credit_cards WHERE base_name != 'NA' AND base_name IS NOT NULL";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $baseNames = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $baseNames;
    }
    
    function getDumpBaseNames() {
        $sql = "SELECT DISTINCT 
                TRIM(REPLACE(REPLACE(LOWER(base_name), '\n', ''), '\r', '')) AS base_name 
                FROM dumps 
                WHERE base_name != 'NA' 
                AND status != 'sold' 
                AND base_name IS NOT NULL";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute();
                $baseNames = $stmt->fetchAll(PDO::FETCH_ASSOC);
                return $baseNames;
    }
    

        
    function getDumpCode() {
        $sql = "SELECT DISTINCT 
                       TRIM(REPLACE(REPLACE(LOWER(code), '\n', ''), '\r', '')) AS code 
                FROM dumps 
                WHERE code != 'NA' 
                  AND status != 'sold' 
                  AND code IS NOT NULL";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $baseNames = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $baseNames;
    }
    
    
    
    
    
    public function generateCsrfToken() {
        if (!isset($_SESSION)) {
            session_start();
        }
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }


    public function verifyCsrfToken($token) {
        if (!isset($_SESSION)) {
            session_start();
        }
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    public function getFilesBySection($section, $limit, $page) {
     
        $limit = (int)$limit;
        
      
        $offset = ($page - 1) * $limit;
    
      
        $sql = "SELECT * FROM uploads WHERE section = :section LIMIT :limit OFFSET :offset";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);  
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);  
        $stmt->execute();
    

        $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
    

        $countSql = "SELECT COUNT(*) FROM uploads WHERE section = :section";
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->bindParam(':section', $section, PDO::PARAM_STR);
        $countStmt->execute();
        $totalFiles = $countStmt->fetchColumn();
    

        $totalPages = ceil($totalFiles / $limit);
  
        return [
            'files' => $files,
            'currentPage' => $page,
            'totalPages' => $totalPages
        ];
    }
    public function getFilesBySection2($section, $limit, $page, $search = '') {
        $limit = (int)$limit;
        $offset = ($page - 1) * $limit;
    
        // Modify SQL query to filter based on search and order by descending
        $sql = "SELECT * FROM uploads 
                WHERE section = :section AND name LIKE :search 
                ORDER BY id DESC 
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->pdo->prepare($sql);
        $searchTerm = '%' . $search . '%'; // Use LIKE for search
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->bindParam(':search', $searchTerm, PDO::PARAM_STR);  
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);  
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);  
        $stmt->execute();
    
        $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        $countSql = "SELECT COUNT(*) FROM uploads WHERE section = :section AND name LIKE :search";
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->bindParam(':section', $section, PDO::PARAM_STR);
        $countStmt->bindParam(':search', $searchTerm, PDO::PARAM_STR);
        $countStmt->execute();
        $totalFiles = $countStmt->fetchColumn();
    
        $totalPages = ceil($totalFiles / $limit);
    
        return [
            'files' => $files,
            'currentPage' => $page,
            'totalPages' => $totalPages
        ];
    }
    
    
    
    public function fetchOrders($userId, $page = 1, $perPage = 6)
    {
        if (!$userId) {
            return [
                'error' => 'User not authenticated',
                'code' => 401
            ];
        }
    
        $offset = ($page - 1) * $perPage;
    
        // Fetch paginated orders
        $stmt = $this->pdo->prepare("
            SELECT uploads.id AS tool_id, uploads.name, uploads.description, uploads.price, uploads.file_path, orders.created_at 
            FROM orders 
            JOIN uploads ON orders.tool_id = uploads.id 
            WHERE orders.user_id = ? 
            ORDER BY orders.created_at DESC
            LIMIT $perPage OFFSET $offset
        ");
        $stmt->execute([$userId]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        // Fetch total order count for pagination
        $countStmt = $this->pdo->prepare("
            SELECT COUNT(*) 
            FROM orders 
            JOIN uploads ON orders.tool_id = uploads.id 
            WHERE orders.user_id = ?
        ");
        $countStmt->execute([$userId]);
        $totalOrders = $countStmt->fetchColumn();
        $totalPages = ceil($totalOrders / $perPage);
    
        return [
            'orders' => $orders,
            'totalOrders' => $totalOrders,
            'totalPages' => $totalPages,
            'currentPage' => $page
        ];
    }

    
    function getCreditCardData($start = 0, $length = 10, $filters = []) {
        global $pdo;
    
        $ccBin = isset($filters['cc_bin']) ? trim($filters['cc_bin']) : '';
        $ccCountry = isset($filters['cc_country']) ? trim($filters['cc_country']) : '';
        $ccState = isset($filters['cc_state']) ? trim($filters['cc_state']) : '';
        $ccCity = isset($filters['cc_city']) ? trim($filters['cc_city']) : '';
        $ccZip = isset($filters['cc_zip']) ? trim($filters['cc_zip']) : '';
        $ccType = isset($filters['cc_type']) ? trim($filters['cc_type']) : 'all';
        $basename = isset($filters['basename']) ? trim($filters['basename']) : 'all';
    
        $sql = "SELECT * FROM credit_cards WHERE buyer_id IS NULL AND status = 'unsold'";
        $params = [];
    
        if (!empty($ccBin)) {
            $bins = array_map('trim', explode(',', $ccBin));
            $sql .= " AND (" . implode(" OR ", array_fill(0, count($bins), "card_number LIKE ?")) . ")";
            foreach ($bins as $bin) {
                $params[] = $bin . '%';
            }
        }
        if (!empty($ccCountry)) {
            $sql .= " AND UPPER(TRIM(country)) = ?";
            $params[] = strtoupper(trim($ccCountry));
        }
        if (!empty($ccState)) {
            $sql .= " AND state LIKE ?";
            $params[] = "%$ccState%";
        }
        if (!empty($ccCity)) {
            $sql .= " AND city LIKE ?";
            $params[] = "%$ccCity%";
        }
        if (!empty($ccZip)) {
            $sql .= " AND zip LIKE ?";
            $params[] = "%$ccZip%";
        }
        if ($ccType !== 'all') {
            $sql .= " AND card_type = ?";
            $params[] = $ccType;
        }
        if ($basename !== 'all') {
            $sql .= " AND base_name = ?";
            $params[] = $basename;
        }
    
      
        $totalSql = str_replace("SELECT *", "SELECT COUNT(*)", $sql);
        $totalStmt = $pdo->prepare($totalSql);
        $totalStmt->execute($params);
        $totalRecords = $totalStmt->fetchColumn();
    
    
        $sql .= " ORDER BY id DESC LIMIT " . intval($start) . ", " . intval($length);
    
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $creditCards = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        return [
            'totalRecords' => $totalRecords,
            'data' => $creditCards
        ];
    }
    
    
    public function insertActivityLog($data) {
        try {
        
            $sql = "INSERT INTO activity_log (user_id, user_name, item_id,buy_itm, item_price, item_type) 
                    VALUES (:user_id, :user_name, :item_id,:buy_itm, :item_price, :item_type)";

            $stmt = $this->pdo->prepare($sql);
            
            
            if (isset($data[0]) && is_array($data[0])) {
                $dataArray = $data;
            } else {
                $dataArray = [$data]; 
            }

            foreach ($dataArray as $row) {
                $stmt->bindParam(':user_id', $row['user_id'], PDO::PARAM_INT);
                $stmt->bindParam(':user_name', $row['user_name'], PDO::PARAM_STR);
                $stmt->bindParam(':item_id', $row['item_id'], PDO::PARAM_STR);
                $stmt->bindParam(':buy_itm', $row['buy_itm'], PDO::PARAM_STR);
                $stmt->bindParam(':item_price', $row['item_price'], PDO::PARAM_STR);
                $stmt->bindParam(':item_type', $row['item_type'], PDO::PARAM_STR);
                $stmt->execute();
            }

            return count($dataArray) . " record(s) inserted successfully!";
        } catch (PDOException $e) {
            return "Error: " . $e->getMessage();
        }
    }
    
    
    
}
?>