<?php
session_start(); 

error_reporting(E_ALL);
ini_set('display_errors', 1);

$connessione = new mysqli("localhost", "root", "", "progetto"); 

if ($connessione->connect_error) {
    die("Connessione fallita: " . $connessione->connect_error);
}

$cf_responsabile = $_SESSION['cf'];
$nome = $_POST['nome-azienda']; 
$ragione_sociale = $_POST['ragione-sociale'];
$piva = $_POST['partita-iva'];
$settore = $_POST['settore'];
$num_dip = $_POST['numero-dipendenti'];

$logo = $_FILES['logo']['name']; 

$num_bil = 0; 

if(isset($_SESSION['cf'])) {
    $cf_responsabile = $_SESSION['cf']; 
} else {
    die("Errore: Non sei loggato o la sessione è scaduta.");
}

$sql = "CALL RegistrazioneAzienda('$nome', '$ragione_sociale', '$piva', '$settore', '$num_dip', '$logo', '$num_bil', '$cf_responsabile')";

$risultato = $connessione->query($sql);

if ($risultato) {
    $riga = $risultato->fetch_assoc();
    $esito = $riga['Esito']; 

    if (str_starts_with($esito, 'Autorizzato')) {
        
        require_once 'logger.php';
        registraLog("Nuova azienda registrata. Nome Azienda: " . $nome . " - Partita IVA: " . $piva . " - CF Responsabile: " . $cf_responsabile);

        $connessione->close();

        header("Location: register_succes_azienda.html");
        exit();
    } else {
        echo "Attenzione: " . $esito;
    }
} else {
    echo "Errore tecnico del database: " . $connessione->error;
}
?>