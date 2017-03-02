<?php

class Taxon {
    private $db;
    private $taxonid;
    var $CurrentTaxon;
    var $Taxon;
    var $HigherTaxa;
    var $DwcTaxon;
    var $TaxonName;
    
    public function __construct($db, $taxonid) {
        $this->db = $db;
        $this->taxonid = $taxonid;
        $this->init();
    }
    
    private function init() {
        $select = "SELECT t.TaxonID, td.Name AS Rank, t.RankID, t.NodeNumber, t.HighestChildNodeNumber, t.Name, t.FullName, t.Author,
            UnitName3, UnitName4,
            t.UsfwsCode AS HybridType, IF(t.Name LIKE '% %' AND (isnull(UsfwsCode) OR UsfwsCode=''), 1, NULL) AS JunkName
            FROM taxon t
            JOIN taxontreedefitem td ON t.TaxonTreeDefItemID=td.TaxonTreeDefItemID
            WHERE TaxonID=$this->taxonid";
        
        $stmt = $this->db->prepare($select);
        $stmt->execute();
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->CurrentTaxon = $row;
        
            $select = "SELECT t.TaxonID, td.Name AS Rank, t.RankID, t.NodeNumber, t.HighestChildNodeNumber, t.Name, t.FullName, t.Author,
                t.UsfwsCode AS HybridType, IF(t.Name LIKE '% %' AND (isnull(UsfwsCode) OR UsfwsCode=''), 1, NULL) AS JunkName
                FROM taxon t
                JOIN taxontreedefitem td ON t.TaxonTreeDefItemID=td.TaxonTreeDefItemID
                WHERE NodeNumber<=$row[NodeNumber] AND HighestChildNodeNumber>=$row[NodeNumber] AND t.RankID>0";
            $stmt = $this->db->prepare($select);
            $stmt->execute();
            
            $this->Taxon = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
                $this->Taxon[$row['Rank']] = $row;
        }
    }
    
    public function HigherTaxa() {
        $this->HigherTaxa = array();
        foreach ($this->Taxon as $key=>$taxon) {
            $highertaxon = new TaxonHigherTaxon();
            $highertaxon->TaxonID = $this->CurrentTaxon['TaxonID'];
            $highertaxon->HigherTaxonName = str_replace(array('[', ']'), '', $taxon['Name']);
            $highertaxon->HigherTaxonRank = $key;
            $this->HigherTaxa[] = $highertaxon;
        }
    }
    
    public function dwcTaxon() {
        $taxon = new DwcTaxon();
        $taxon->taxonID = $this->taxonid;
        $taxon->taxonRank = $this->CurrentTaxon['Rank'];
        $taxon->scientificName = $this->CurrentTaxon['FullName'];
        $taxon->scientificNameAuthorship = $this->CurrentTaxon['Author'];
        $taxon->kingdom = isset($this->Taxon['kingdom']) ? $this->Taxon['kingdom']['Name'] : NULL;
        $taxon->phylum = isset($this->Taxon['division']) ? $this->Taxon['division']['Name'] : NULL;
        $taxon->class = isset($this->Taxon['class']) ? $this->Taxon['class']['Name'] : NULL;
        $taxon->order = isset($this->Taxon['order']) ? $this->Taxon['order']['Name'] : NULL;
        $taxon->family = isset($this->Taxon['family']) ? $this->Taxon['family']['Name'] : NULL;
        $taxon->genus = isset($this->Taxon['genus']) ? $this->Taxon['genus']['Name'] : NULL;
        $taxon->specificEpithet = isset($this->Taxon['species']) ? $this->Taxon['species']['Name'] : NULL;
        if (isset($this->taxon['subspecies'])) {
            $taxon->infraSpecificEpithet = $this->Taxon['subspecies']['Name'];
        }
        if (isset($this->taxon['variety'])) {
            $taxon->infraSpecificEpithet = $this->Taxon['variety']['Name'];
        }
        if (isset($this->taxon['subvariety'])) {
            $taxon->infraSpecificEpithet = $this->Taxon['subvariety']['Name'];
        }
        if (isset($this->taxon['forma'])) {
            $taxon->infraSpecificEpithet = $this->Taxon['forma']['Name'];
        }
        if (isset($this->taxon['subforma'])) {
            $taxon->infraSpecificEpithet = $this->Taxon['subforma']['Name'];
        }
        $taxon->nomenclaturalCode = 'ICBN';
        return $taxon;
    }
    
    private function FullScientificNameString() {
        switch ($this->CurrentTaxon['RankID']) {
            case 110: // suborder
                $this->TaxonName->FullScientificNameString = $this->Taxon['order']['Name'] . ' subord. ' . $this->CurrentTaxon['Name'];
                break;
            
            case 150: // subfamily
                $this->TaxonName->FullScientificNameString = $this->Taxon['family']['Name'] . ' subfam. ' . $this->CurrentTaxon['Name'];
                $this->TaxonName->FullScientificNameString .= ($this->CurrentTaxon['Author']) ? ' ' . $this->CurrentTaxon['Author'] : '';
                break;
            
            case 160: // tribe
                $this->TaxonName->FullScientificNameString = $this->Taxon['family']['Name'] . ' trib. ' . $this->CurrentTaxon['Name'];
                $this->TaxonName->FullScientificNameString .= ($this->CurrentTaxon['Author']) ? ' ' . $this->CurrentTaxon['Author'] : '';
                break;
            
            case 190: // subgenus
                $this->TaxonName->FullScientificNameString = $this->Taxon['genus']['Name'] . ' subgen. ' . $this->CurrentTaxon['Name'];
                $this->TaxonName->FullScientificNameString .= ($this->CurrentTaxon['Author']) ? ' ' . $this->CurrentTaxon['Author'] : '';
                break;
            
            case 200: // section    
                $this->TaxonName->FullScientificNameString = $this->Taxon['genus']['Name'] . ' sect. ' . $this->CurrentTaxon['Name'];
                $this->TaxonName->FullScientificNameString .= ($this->CurrentTaxon['Author']) ? ' ' . $this->CurrentTaxon['Author'] : '';
                break;
            
            case 220: // species
                $this->TaxonName->FullScientificNameString = $this->Taxon['genus']['Name'] . ' ' . $this->CurrentTaxon['Name'];
                $this->TaxonName->FullScientificNameString .= ($this->CurrentTaxon['Author']) ? ' ' . $this->CurrentTaxon['Author'] : '';
                break;
            
            case 230:
            case 240:
            case 250:
            case 260:
            case 270:
                
                switch ($this->CurrentTaxon['RankID']) {
                    case 230: // subspecies
                        $prefix = 'subsp.';
                        break;
                    case 240: // variety
                        $prefix = 'var.';
                        break;
                    case 250: // subvariety
                        $prefix = 'subvar.';
                        break;
                    case 260: // forma
                        $prefix = 'f.';
                        break;
                    case 270: // subforma
                        $prefix = 'subf.';
                        break;
                    default:
                        break;
                }

                if ($this->CurrentTaxon['HybridType'] == 'x') $prefix = 'notho' . $prefix;
                $this->TaxonName->FullScientificNameString = $this->Taxon['genus']['Name'] . ' ' . $this->Taxon['species']['Name'];
                $this->TaxonName->FullScientificNameString .= ($this->Taxon['species']['Author'] && $this->CurrentTaxon['Name'] == $this->Taxon['species']['Name']) 
                        ? ' ' . $this->Taxon['species']['Author'] : '';
                $this->TaxonName->FullScientificNameString .= ' ' . $prefix . ' ' . $this->CurrentTaxon['Name'];
                $this->TaxonName->FullScientificNameString .= ($this->CurrentTaxon['Author'] && $this->CurrentTaxon['Name'] != $this->Taxon['species']['Name']) 
                        ? ' ' . $this->CurrentTaxon['Author'] : '';
                break;

            default:
                $this->TaxonName->FullScientificNameString = $this->CurrentTaxon['Name'];
                $this->TaxonName->FullScientificNameString .= ($this->CurrentTaxon['Author'] && $this->CurrentTaxon['RankID'] >= 140) 
                        ? ' ' . $this->CurrentTaxon['Author'] : '';
                break;
            
        };
    }
    
    private function AtomisedName() {
        switch ($this->CurrentTaxon['RankID']) {
            case 180:
                $this->TaxonName->GenusOrMonomial = $this->CurrentTaxon['Name'];
                $this->TaxonName->AuthorTeam = $this->CurrentTaxon['Author'];
                break;
            
            case 190:
            case 200:
                $this->TaxonName->GenusOrMonomial = $this->Taxon['genus']['Name'];
                $this->TaxonName->AuthorTeam = $this->CurrentTaxon['Author'];
                break;
            
            case 220:
                $this->TaxonName->GenusOrMonomial = $this->Taxon['genus']['Name'];
                $this->TaxonName->FirstEpithet = $this->CurrentTaxon['Name'];
                $this->TaxonName->AuthorTeam = $this->CurrentTaxon['Author'];
                break;
            
            case 230:
            case 240:
            case 250:
            case 260:
            case 270:
                switch ($this->CurrentTaxon['RankID']) {
                    case 230: // subspecies
                        $this->TaxonName->Rank = 'subsp.';
                        break;
                    case 240: // variety
                        $this->TaxonName->Rank = 'var.';
                        break;
                    case 250: // subvariety
                        $this->TaxonName->Rank = 'subvar.';
                        break;
                    case 260: // forma
                        $this->TaxonName->Rank = 'f.';
                        break;
                    case 270: // subforma
                        $this->TaxonName->Rank = 'subf.';
                        break;
                    default:
                        break;
                }
                $this->TaxonName->GenusOrMonomial = $this->Taxon['genus']['Name'];
                $this->TaxonName->FirstEpithet = $this->Taxon['species']['Name'];
                $this->TaxonName->InfraspecificEpithet = $this->CurrentTaxon['Name'];
                $this->TaxonName->AuthorTeam = ($this->CurrentTaxon['Name'] == $this->Taxon['species']['Name']) 
                        ? $this->Taxon['species']['Author'] : $this->CurrentTaxon['Author'];
                break;
        };
    }
    
    private function HybridName() {
        switch ($this->CurrentTaxon['RankID']) {
            case 180:
                if ($this->CurrentTaxon['HybridType'] == 'x') {
                    $this->TaxonName->HybridFlag = '×';
                    $this->TaxonName->HybridFlagInsertionPoint = 1;
                    $this->CurrentTaxon['Name'] = '×' . $this->CurrentTaxon['Name'];
                }
                break;
                
            case 190:
            case 200:
                if ($this->Taxon['genus']['HybridType'] == 'x') {
                    $this->TaxonName->HybridFlag = '×';
                    $this->TaxonName->HybridFlagInsertionPoint = 1;
                    $this->Taxon['genus']['Name'] = '×' . $this->Taxon['genus']['Name'];
                }
                break;
                
            case 220:
                if ($this->Taxon['genus']['HybridType'] == 'x') {
                    $this->TaxonName->HybridFlag = '×';
                    $this->TaxonName->HybridFlagInsertionPoint = 1;
                    $this->Taxon['genus']['Name'] = '×' . $this->Taxon['genus']['Name'];
                }
                elseif ($this->CurrentTaxon['HybridType'] == 'x') {
                    $this->TaxonName->HybridFlag = '×';
                    $this->TaxonName->HybridFlagInsertionPoint = 2;
                    $this->CurrentTaxon['Name'] = '×' . $this->CurrentTaxon['Name'];
                }
                break;
                
            case 230:
            case 240:
            case 250:
            case 260:
            case 270:
                if ($this->Taxon['genus']['HybridType'] == 'x') {
                    $this->TaxonName->HybridFlag = '×';
                    $this->TaxonName->HybridFlagInsertionPoint = 1;
                    $this->Taxon['genus']['Name'] = '×' . $this->Taxon['genus']['Name'];
                }
                elseif ($this->Taxon['species']['HybridType'] == 'x') {
                    $this->TaxonName->HybridFlag = '×';
                    $this->TaxonName->HybridFlagInsertionPoint = 2;
                    $this->Taxon['species']['Name'] = '×' . $this->Taxon['species']['Name'];
                }
                elseif ($this->CurrentTaxon['HybridType'] == 'x') {
                    $this->TaxonName->HybridFlag = '×';
                    $this->TaxonName->HybridFlagInsertionPoint = 3;
                    $this->CurrentTaxon['Rank'] = 'notho' . $this->CurrentTaxon['Rank'];
                }
                break;

            default:
                break;
        }
    }
    
    public function TaxonName() {
        $this->TaxonName = new TaxonName();
        $this->TaxonName->TaxonID = $this->CurrentTaxon['TaxonID'];
        $this->HybridName();
        $this->FullScientificNameString();
        $this->AtomisedName();
    }
}


?>
