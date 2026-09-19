<?php
session_start(); // Fondamentale per sapere chi è loggato!
header('Content-Type: application/json');

if (!isset($_SESSION['cf'])) {
    die(json_encode(["error" => "Utente non loggato"]));
}

$connessione = new mysqli("localhost", "root", "", "progetto");
if ($connessione->connect_error) {
    die(json_encode(["error" => "Connessione fallita"]));
}

$cf_revisore = $_SESSION['cf'];

$sql = "SELECT c.nome, lc.livello 
        FROM COMPETENZA c 
        LEFT JOIN LIVELLO_COMPETENZA lc 
        ON c.nome = lc.nome_competenza AND lc.cf = '$cf_revisore'"; 

$risultato = $connessione->query($sql);

$competenze = [];
if ($risultato) {
    while($riga = $risultato->fetch_assoc()) {
        $competenze[] = $riga;
    }
}

echo json_encode($competenze);
$connessione->close();
?>