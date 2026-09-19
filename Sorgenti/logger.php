<?php
date_default_timezone_set('Europe/Rome');

function registraLog($messaggio) {
    try {
        $manager = new MongoDB\Driver\Manager("mongodb://localhost:27017");
        
        $documento = [
            'timestamp' => date('Y-m-d H:i:s'),
            'evento' => $messaggio
        ];
        
        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->insert($documento);
        
        $manager->executeBulkWrite('piattaforma_esg.log_eventi', $bulk);
        
    } catch (Exception $e) {
        error_log("Errore MongoDB: " . $e->getMessage());
    }
}
?>