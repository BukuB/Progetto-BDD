<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['cf'])) {
    die(json_encode(["error" => "Non loggato"]));
}

$connessione = new mysqli("localhost", "root", "", "progetto");
$cf_revisore = $_SESSION['cf'];

$sql = "SELECT id_bilancio FROM GIUDIZIO WHERE cf = '$cf_revisore'";
$risultato = $connessione->query($sql);

$bilanci = [];
while($riga = $risultato->fetch_assoc()) {
    $bilanci[] = $riga['id_bilancio'];
}

echo json_encode($bilanci);
$connessione->close();
?>