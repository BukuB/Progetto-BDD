<?php
header('Content-Type: application/json');
$connessione = new mysqli("localhost", "root", "", "progetto");

$sql = "SELECT b.id, b.data_creazione, b.stato, b.partita_iva
        FROM BILANCIO_DI_ESERCIZIO b";
$risultato = $connessione->query($sql);

$bilanci = [];
while($riga = $risultato->fetch_assoc()) {
    $bilanci[] = $riga;
}

echo json_encode($bilanci); 
$connessione->close();
?>