<?php
require_once('includes/pdoconnect.php');
require_once('includes/dataholderclasses.php');

updateTaxon($db);

function updateTaxon($db) {
    $update = "UPDATE biocase.abcd_identification
        SET FullScientificNameString=?, TaxonRank=?, AuthorTeam=?, GenusOrMonomial=?,
          FirstEpithet=?, Rank=?, InfraspecificEpithet=?, HybridFlag=?, HybridFlagInsertionPoint=?
        WHERE DeterminationID=?";
    $updIdentificationStmt = $db->prepare($update);
    
    $update = "UPDATE biocase.abcd_unit
        SET DateLastEdited=NOW()
        WHERE CollectionObjectID=?";
    $updUnitStmt = $db->prepare($update);
    
    $select = "SELECT co.CollectionObjectID, d.DeterminationID,
        t.UnitName1, td.Name, t.Author,
        t.UnitName2, t.UnitName3, td.TextBefore,
        t.UnitName4, t.UsfwsCode, t.RankID
        FROM taxon t
        JOIN taxontreedefitem td ON t.TaxonTreeDefitemID=td.TaxonTreeDefItemID
        JOIN determination d ON t.TaxonID=d.TaxonID
        JOIN collectionobject co ON d.CollectionObjectID=co.CollectionObjectID
        JOIN biocase.abcd_identification i ON d.DeterminationID=i.DeterminationID
        WHERE (t.UnitName1!=i.FullScientificNameString OR td.Name!=i.TaxonRank OR t.Author!=i.AuthorTeam
          OR t.UnitName2!=i.GenusOrMonomial OR t.UnitName3!=REPLACE(i.FirstEpithet, '×', '') OR td.TextBefore!=i.Rank
          OR t.UnitName4!=i.InfraspecificEpithet OR (t.UsfwsCode='x' AND i.HybridFlag!='x') OR (t.UsfwsCode!='x' AND i.HybridFlag='x'))";
    $selStmt = $db->prepare($select);
    
    $selStmt->execute();
    $result = $selStmt->fetchAll(5);
    if ($result) {
        echo count($result) . "\r\n";
        foreach ($result as $row) {
            $ident = new TaxonUpdate();
            $ident->DeterminationID = $row->DeterminationID;
            $ident->FullScientificNameString = $row->UnitName1;
            $ident->TaxonRank = strtolower($row->Name);
            $ident->AuthorTeam = $row->Author;
            $ident->GenusOrMonomial = $row->UnitName2;
            $ident->FirstEpithet = $row->UnitName3;
            $ident->Rank = $row->TextBefore;
            $ident->InfraspecificEpithet = $row->UnitName4;
            if ($row->UsfwsCode == 'x') {
                $ident->HybridFlag = 'x';
                if ($row->RankID == 180)
                    $ident->HybridFlagInsertionPoint = 1;
                elseif ($row->RankID == 220)
                    $ident->HybridFlagInsertionPoint = 2;
                elseif ($row->RankID > 220)
                    $ident->HybridFlagInsertionPoint = 3;
            }
            
            $updIdentificationStmt->execute(array_values((array) $ident));
            if ($updIdentificationStmt->errorCode() != '00000')
                print_r($updIdentificationStmt->errorInfo());
            
            $updUnitStmt->execute(array($row->CollectionObjectID));
            if ($updUnitStmt->errorCode() != '00000')
                print_r($updUnitStmt->errorInfo());
            
        }
    }
}

class TaxonUpdate {
    var $FullScientificNameString = NULL;
    var $TaxonRank = NULL;
    var $AuthorTeam = NULL;
    var $GenusOrMonomial = NULL;
    var $FirstEpithet = NULL;
    var $Rank = NULL;
    var $InfraspecificEpithet = NULL;
    var $HybridFlag = NULL;
    var $HybridFlagInsertionPoint = NULL;
    var $DeterminationID = NULL;
}


?>
