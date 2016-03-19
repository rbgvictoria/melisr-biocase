<?php

require_once ('includes/pdoconnect.php');
require_once ('includes/dataholderclasses.php');
require_once ('includes/taxon.php');
require_once ('includes/biocaseload.php');

$stmt = $db->prepare("SELECT TaxonID FROM taxon");
$stmt->execute();
while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
    $taxon = new Taxon($db, $row->TaxonID);
    $load = new BioCASeLoad($db, $row->TaxonID);
    
    $taxon->TaxonName();
    $load->load('taxonname', $taxon->TaxonName);
    
    $taxon->HigherTaxa();
    $load->load('highertaxon', $taxon->HigherTaxa);
};

?>
