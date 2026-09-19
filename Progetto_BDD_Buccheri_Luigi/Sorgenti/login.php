<?php
session_start();
// Diciamo al browser che stiamo per inviare un segnale JSON, non una pagina web
header('Content-Type: application/json');

$connessione = new mysqli("localhost", "root", "", "progetto");

if ($connessione->connect_error) {
    echo json_encode(["successo" => false, "tipo_errore" => "database"]);
    exit();
}

$user_inserito = $_POST['username']; 
$password_inserita = $_POST['password'];

// 1. CORREZIONE: Ho aggiunto "cf" alla lista della SELECT!
$sql = "SELECT username, password, ruolo_interno, cf FROM UTENTE WHERE username = '$user_inserito'";
$risultato = $connessione->query($sql);

if ($risultato && $risultato->num_rows > 0) {
   
    $riga_utente = $risultato->fetch_assoc();
    
    if (password_verify($password_inserita, $riga_utente['password'])) {
        
        $_SESSION['utente_loggato'] = true;
        $_SESSION['username'] = $riga_utente['username'];
        $_SESSION['ruolo_interno'] = $riga_utente['ruolo_interno']; 
        $_SESSION['cf'] = $riga_utente['cf'];
        
        echo json_encode(["successo" => true]);
        exit();
        
    } else {
        echo json_encode(["successo" => false, "tipo_errore" => "password"]);
        exit();
    }
} else {
    echo json_encode(["successo" => false, "tipo_errore" => "username"]);
    exit();
}

$connessione->close();
?>