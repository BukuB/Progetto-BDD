<?php
session_start(); 

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['cf'])) {
    die("Errore: Sessione mancante. Effettua il login.");
}

$connessione = new mysqli("localhost", "root", "", "progetto"); 
if ($connessione->connect_error) {
    die("Connessione fallita: " . $connessione->connect_error);
}

$nome = $connessione->real_escape_string($_POST['competenza']); 
$cf_revisore = $_SESSION['cf'];

$livello = isset($_POST['livello']) ? intval($_POST['livello']) : 0;

$sql = "CALL CreaCompetenza('$nome', '$cf_revisore', $livello)";

$risultato = $connessione->query($sql);

if ($risultato) {
    $riga = $risultato->fetch_assoc();
    if (strpos($riga['Esito'], 'Autorizzato') !== false) {
        header("Location: inserisci_competenza_livello.html");
        exit();
    } else {
        echo "Esito procedura: " . $riga['Esito'];
    }
} else {
    echo "Errore SQL: " . $connessione->error;
}

$connessione->close();
?>