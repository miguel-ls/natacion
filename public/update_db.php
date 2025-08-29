<?php
require_once 'config/database.php';
require_once 'src/core/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    $sql = file_get_contents('db/sp_get_matriculas_by_alumn_id.sql');
    $db->exec($sql);
    echo "Stored procedure created successfully.";
} catch (PDOException $e) {
    echo "Error creating stored procedure: " . $e->getMessage();
}
?>
