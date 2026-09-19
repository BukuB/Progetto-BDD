<?php
session_start();

$connessione = new mysqli("localhost", "root", "", "progetto"); 

if ($connessione->connect_error) {
    die("Connessione fallita.");
}

if (!isset($_SESSION['cf'])) {
    die("Errore: Codice Fiscale non trovato. Rifai il Login!");
}

$nome = $_POST['nome_voce']; 
$descrizione = $_POST['descrizione_voce'];
$cf_admin = $_SESSION['cf']; 

$sql = "CALL CreazioneTemplate('$nome', '$descrizione', '$cf_admin')";

if ($connessione->query($sql)) {

    require_once 'logger.php';
    registraLog("Nuova Voce aggiunta al Template. Nome Voce: " . $nome . " - CF Admin: " . $cf_admin);

    header("Location: gestione_template.html");
    exit(); 
} else {
    echo "<h2>Attenzione, errore durante l'inserimento:</h2>";
    echo "<p>" . $connessione->error . "</p>";
    echo "<a href='#'>Torna all'area di lavoro</a>";
}

$connessione->close();
?>