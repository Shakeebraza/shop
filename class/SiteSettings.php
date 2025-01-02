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
        // s
        $sql = "SELECT base_name FROM credit_cards WHERE base_name != 'NA' AND base_name IS NOT NULL";
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
    

}
?>
