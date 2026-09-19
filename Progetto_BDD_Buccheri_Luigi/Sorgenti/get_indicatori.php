<?php
header('Content-Type: application/json');
$connessione = new mysqli("localhost", "root", "", "progetto");

$sql = "SELECT i.nome,  i.immagine,  i.rilevanza, a.codice_normativa, s.ambito_sociale, s.frequenza_rilevazione,
            CASE 
                WHEN a.nome_indicatore IS NOT NULL THEN 'ambientali'
                WHEN s.nome_indicatore IS NOT NULL THEN 'sociali'
                ELSE 'altro'
            END AS tipo
        FROM INDICATORE_ESG i
        LEFT JOIN AMBIENTALI a ON i.nome = a.nome_indicatore
        LEFT JOIN SOCIALI s ON i.nome = s.nome_indicatore";
$risultato = $connessione->query($sql);

$voci = [];
while($riga = $risultato->fetch_assoc()) {
    $voci[] = $riga;
}

echo json_encode($voci); 
$connessione->close();
?>