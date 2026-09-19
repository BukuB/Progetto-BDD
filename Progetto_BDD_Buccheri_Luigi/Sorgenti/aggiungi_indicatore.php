<?php
session_start();

$connessione = new mysqli("localhost", "root", "", "progetto"); 

if ($connessione->connect_error) {
    die("Connessione fallita.");
}

if (!isset($_SESSION['cf'])) {
    die("Errore: Codice Fiscale non trovato. Rifai il Login!");
}

$nome = $_POST['nome_indicatore']; 
$tipo = $_POST['tipologia'];
$ril = $_POST['rilevanza'];
$cf_admin = $_SESSION['cf']; 

$immagine = isset($_FILES['immagine_upload']['name']) ? $_FILES['immagine_upload']['name'] : '';

$normativa = isset($_POST['codice_normativa']) ? $_POST['codice_normativa'] : '';
$ambito = isset($_POST['ambito_sociale']) ? $_POST['ambito_sociale'] : '';
$frequenza = isset($_POST['frequenza_rilevazione']) ? $_POST['frequenza_rilevazione'] : '';

$sql = "CALL PopolamentoIndEsg('$nome', '$immagine', '$ril', '$cf_admin', '$tipo', '$normativa', '$ambito', '$frequenza')";

if ($connessione->query($sql)) {
    
    require_once 'logger.php';
    registraLog("Nuovo indicatore aggiunto. Nome Indicatore: " . $nome . " - CF Admin: " . $cf_admin);

    header("Location: gestione_indicatori.html");
    exit(); 
} else {
    echo "<h2>Attenzione, errore durante l'inserimento:</h2>";
    echo "<p>" . $connessione->error . "</p>";
    echo "<a href='gestione_indicatori.html'>Torna all'area di lavoro</a>";
}

$connessione->close();
?>