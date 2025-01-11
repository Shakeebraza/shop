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
        $sql = "SELECT base_name FROM dumps WHERE base_name != 'NA' AND base_name IS NOT NULL";
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
    
    

    
    
    
}
?>
