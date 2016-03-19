<?php

require_once('includes/pdoconnect.php');
require_once('includes/dataholderclasses.php');
require_once('includes/biocaseunit.php');
require_once('includes/biocaseloan.php');
require_once('includes/biocaseexchange.php');
require_once('includes/biocaseload.php');
require_once('includes/taxon.php');

if (!isset($argv[1])) {
    exit ("Argument missing...\n");
}

if (!isset($argv[2]))
    $argv[2] = FALSE;

if ($argv[2] == 'loans') {
    $update = new UpdateBioCase($db, FALSE);
    $update->updateLoans();
}
elseif ($argv[2] == 'exchange') {
    $update = new UpdateBioCase($db, FALSE);
    $update->updateExchange();
}
else {
    $starttime = getStartTime($argv);
    $update = new UpdateBioCase($db, $starttime);
    if ($argv[1] == 'hourly' || $argv[1] == 'daily' || $argv[2] == 'taxa') $update->updateTaxa();
    $update->updateUnits();
    $update->updateLoans();
    $update->updateExchange();
}

function getStartTime($argv) {
    //date_default_timezone_set('Australia/Melbourne');
    if($argv[1] == 'reindex')
        $starttime = FALSE;
    elseif(preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $argv[1])) $starttime = $argv[1];
    elseif(preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/', $argv[1])) $starttime = $argv[1];
    else {
        $date = new DateTime(date('Y-m-d H:i:s'));
        switch ($argv[1]) {
            case '10min':
                $date->sub(new DateInterval('PT10M'));
                break;
            
            case 'hourly':
                $date->sub(new DateInterval('PT01H'));
                break;

            case 'daily':
                $date->sub(new DateInterval('P01D'));
                break;

            case 'weekly':
                $date->sub(new DateInterval('P07D'));
                break;
            
            case 'monthly':
                $date->sub(new DateInterval('P01M'));
                break;

            default:
                break;
        }
        $starttime = $date->format('Y-m-d H:i:s');
    }
    return $starttime;
}

class UpdateBioCase {
    var $db;
    var $starttime;
    
    public function __construct($db, $starttime) {
        $this->db = $db;
        $this->starttime = $starttime;
    }

    function updateUnits() {
        if ($this->starttime) {
        $select = "SELECT co.CollectionObjectID
            FROM collectionobject co
            JOIN preparation p ON co.CollectionObjectID=p.CollectionObjectID
            WHERE co.CollectionMemberID=4 AND p.PrepTypeID IN (1, 2, 3, 4, 6, 7, 12, 13)
              AND (co.TimestampModified>='$this->starttime')
            GROUP BY co.CollectionObjectID

            UNION

            SELECT co.CollectionObjectID
            FROM taxon t
            JOIN taxontreedefitem td ON t.TaxonTreeDefitemID=td.TaxonTreeDefItemID
            JOIN determination d ON t.TaxonID=d.TaxonID
            JOIN collectionobject co ON d.CollectionObjectID=co.CollectionObjectID
            JOIN preparation p ON co.CollectionObjectID=p.CollectionObjectID
            JOIN biocase.abcd_identification i ON d.DeterminationID=i.DeterminationID
            WHERE co.CollectionMemberID=4 AND p.PrepTypeID IN (1, 2, 3, 4, 6, 7, 12, 13) AND t.TimestampModified>='$this->starttime'
              AND (t.UnitName1!=i.FullScientificNameString OR td.Name!=i.TaxonRank OR t.Author!=i.AuthorTeam
                OR t.UnitName2!=i.GenusOrMonomial OR t.UnitName3!=REPLACE(i.FirstEpithet, '×', '') OR td.TextBefore!=i.Rank
                OR t.UnitName4!=i.InfraspecificEpithet OR (t.UsfwsCode='x' AND i.HybridFlag!='x') OR (t.UsfwsCode!='x' AND i.HybridFlag='x'))
            GROUP BY co.CollectionObjectID

            UNION

            SELECT co.CollectionObjectID
            FROM collectionobject co
            JOIN preparation p ON co.CollectionObjectID=p.CollectionObjectID
            JOIN collectingevent ce ON co.CollectingEventID=ce.CollectingEventID
            JOIN locality l ON ce.LocalityID=l.LocalityID
            WHERE co.CollectionMemberID=4 AND p.PrepTypeID IN (1, 2, 3, 4, 6, 7, 12, 13)
              AND (l.TimestampModified>='$this->starttime')
            GROUP BY co.CollectionObjectID";
        }
        else {
            $select = "SELECT co.CollectionObjectID
                FROM collectionobject co
                JOIN preparation p ON co.CollectionObjectID=p.CollectionObjectID
                WHERE co.CollectionMemberID=4 AND p.PrepTypeID IN (1, 2, 3, 4, 6, 7, 12, 13)
                GROUP BY co.CollectionObjectID";
        }
        
        //echo $select . "\n";

        $stmt = $this->db->prepare($select);
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
            $update = new BioCASeUnit($this->db, $row->CollectionObjectID);
            $load = new BioCASeLoad($this->db, $row->CollectionObjectID);

            $update->updateUnit();
            $load->load('unit', $update->Unit);
            if ($update->Sequences) {
                $load->load('sequence', $update->Sequences);
            }

            $update->updateIdentification();
            $load->load('identification', $update->Identifications);
            $load->load('abcd_highertaxon', $update->HigherTaxa);

            $update->updateCollector();
            $load->load('collector', $update->Collectors);

            $update->updateNamedArea();
            $load->load('namedarea', $update->NamedAreas);

            $update->updatePreviousUnits();
            $load->load('previousunit', $update->PreviousUnits);

            $update->updateUnitMeasurementOrFact();
            $load->load('unitmeasurementorfact', $update->UnitMeasurementOrFacts);
        }
    }

    function updateTaxa() {
        $select = "SELECT TaxonID FROM taxon";
        if ($this->starttime)
            $select .= " WHERE TimestampModified>='$this->starttime'";
        $stmt = $this->db->prepare($select);
        $stmt->execute();
        $n = 0;
        while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
            $taxon = new Taxon($this->db, $row->TaxonID);
            $load = new BioCASeLoad($this->db, $row->TaxonID);

            $taxon->HigherTaxa();

            if ($taxon->HigherTaxa) 
                $load->load('highertaxon', $taxon->HigherTaxa);

            $taxon->TaxonName();
            $load->load('taxonname', $taxon->TaxonName);
        }
    }
    
    function updateTaxonData() {
        $update = "UPDATE biocase.abcd_identification
            SET FullScientificNameString=?, TaxonRank=?, AuthorTeam=?, GenusOrMonomial=?,
              FirstEpithet=?, Rank=?, InfraspecificEpithet=?, HybridFlag=?, HybridFlagInsertionPoint=?
            WHERE DeterminationID=?";
        $updIdentificationStmt = $this->db->prepare($update);

        $update = "UPDATE biocase.abcd_unit
            SET DateLastEdited=NOW()
            WHERE CollectionObjectID=?";
        $updUnitStmt = $this->db->prepare($update);

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
        $selStmt = $this->db->prepare($select);

        $selStmt->execute();
        $result = $selStmt->fetchAll(5);
        if ($result) {
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

    function updateLoans() {
        $select = "SELECT LoanID
            FROM loan";
        if ($this->starttime)
            $select .= " WHERE TimestampModified>='$this->starttime'";
        $query = $this->db->prepare($select);
        $query->execute();
        
        while ($row = $query->fetch(PDO::FETCH_OBJ)) {
            $loan = new BioCASeLoan($this->db, $row->LoanID);
            $loan->Loan();
            $loan->deleteLoanInfo();
            $loan->updateLoanInfo();
        }
    }
    
    function updateExchange() {
        $select = "SELECT GiftID
            FROM gift";
        if ($this->starttime)
            $select .= " WHERE TimestampModified>='$this->starttime'";
        $query = $this->db->prepare($select);
        $query->execute();
        while ($row = $query->fetch(PDO::FETCH_OBJ)) {
            $exchange = new BioCASeExchange($this->db, $row->GiftID);
            $exchange->updateDuplicatesSentTo();
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
