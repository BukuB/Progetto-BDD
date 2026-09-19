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

$cf_revisore = $_SESSION['cf'];
$livelli_inseriti = $_POST['livelli']; 

$successo = true;
$errori = "";

if (isset($livelli_inseriti) && is_array($livelli_inseriti)) {
    foreach ($livelli_inseriti as $nome_competenza => $livello) {
        
        if (trim($livello) !== '') {
            
            $nome_pulito = $connessione->real_escape_string($nome_competenza);
            $livello_int = intval($livello);
            
            $sql = "CALL CreaCompetenza('$nome_pulito', '$cf_revisore', $livello_int)";
            $risultato = $connessione->query($sql);
            
            if ($risultato) {
                while($connessione->next_result()) $connessione->store_result();
            } else {
                $successo = false;
                $errori .= "Errore SQL su '$nome_pulito': " . $connessione->error . "<br>";
            }
        }
    }
}

if ($successo && empty($errori)) {

    require_once 'logger.php';
    registraLog("Aggiunto Livello Competenza. CF Revisore: " . $cf_revisore);

    header("Location: success_inserimento_competenza_livello.html");
    exit();
} else {
    echo "<h3>Si sono verificati dei problemi:</h3>";
    echo $errori;
    echo "<br><a href='javascript:history.back()'>Torna indietro e correggi</a>";
}

$connessione->close();
?>