<?php
session_start();

if (!isset($_SESSION['cf'])) {
    die("Errore: Utente non loggato o sessione scaduta.");
}

$connessione = new mysqli("localhost", "root", "", "progetto");
if ($connessione->connect_error) {
    die("Connessione fallita: " . $connessione->connect_error);
}

$cf_admin = $_SESSION['cf'];
$associazioni = $_POST['esg']; 

$successo = true;
$errori = "";

require_once 'logger.php'; 

foreach ($associazioni as $id_bilancio => $dati) {
    
    if (!empty($dati['revisore'])) {

        $cf_revisore = $connessione->real_escape_string($dati['revisore']);
        $id = intval($id_bilancio);

        $sql = "CALL AssociaRevisore('$cf_admin', '$cf_revisore', $id)";
        $risultato = $connessione->query($sql);
        
        if ($risultato) {
            $riga_esito = $risultato->fetch_assoc();

            if (strpos($riga_esito['Esito'], 'Autorizzato') === false) {
                $successo = false;
                $errori .= "- Bilancio ID $id: " . $riga_esito['Esito'] . "<br>";
            } else {
                registraLog("Nuova Associazione Creata. Codice Fiscale del Revisore: " . $cf_revisore . " - Id Bilancio: " . $id . " - CF Admin: " . $cf_admin);
            }

            while($connessione->next_result()) $connessione->store_result();
        } else {
            $successo = false;
            $errori .= "- Errore SQL sul Bilancio ID $id: " . $connessione->error . "<br>";
        }
    }
}

if ($successo && empty($errori)) {
    header("Location: success_associazione_revisore.html");
    exit(); 
} else {
    echo "<h3>Si sono verificati dei problemi:</h3>";
    echo $errori;
    echo "<br><a href='javascript:history.back()'>Torna al modulo e correggi</a>";
}

$connessione->close();
?>