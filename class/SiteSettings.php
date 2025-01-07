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
}
?>
