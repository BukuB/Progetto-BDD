<?php
header('Content-Type: application/json');
$connessione = new mysqli("localhost", "root", "", "progetto");

$sql = "SELECT * FROM VOCE_CONTABILE";
$risultato = $connessione->query($sql);

$voci = [];
while($riga = $risultato->fetch_assoc()) {
    $voci[] = $riga;
}

echo json_encode($voci); 
$connessione->close();
?>