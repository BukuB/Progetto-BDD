<?php
session_start();

header('Content-Type: application/json');

if (isset($_SESSION['utente_loggato']) && $_SESSION['utente_loggato'] === true) {
    $risposta = [
        "loggato" => true,
        "username" => $_SESSION['username'],
        "ruolo_interno" => $_SESSION['ruolo_interno'] 
        
    ];
} else {
    $risposta = [
        "loggato" => false
    ];
}

echo json_encode($risposta);
?>