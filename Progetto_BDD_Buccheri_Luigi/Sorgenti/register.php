<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$connessione = new mysqli("localhost", "root", "", "progetto"); 

if ($connessione->connect_error) {
    die("Connessione fallita: " . $connessione->connect_error);
}

$email = $_POST['indirizzo_email']; 
$pass = $_POST['password'];
$user = $_POST['username'];
$cf = $_POST['codice_fiscale'];
$data = $_POST['data_nascita'];
$luogo = $_POST['luogo_nascita'];
$prof = $_POST['professione'];

$ruolo_interno = 'utente_base';
$cv_vuoto = 'nessun_cv.pdf'; 

$password_hash = password_hash($pass, PASSWORD_DEFAULT);

$sql = "CALL RegistrazioneUtente('$email', '$user', '$password_hash', '$cf', '$data', '$luogo', '$ruolo_interno', '$cv_vuoto', '$prof')";

$risultato = $connessione->query($sql);

if ($risultato) { 

    require_once 'logger.php';
    registraLog("Nuovo utente registrato. Nome Utente: " . $user);
    
    $connessione->close();

    header("Location: register_succes.html");
    exit();
    
} else {
    echo "Errore SQL: " . $connessione->error;
    $connessione->close();
}