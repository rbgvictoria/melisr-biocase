<?php

require_once('pdoconnect.php');
require_once('dataholderclasses.php');

class BioCASeLoan {
    private $db;
    private $loanid;
    private $Loan;
    var $Units;
    
    public function __construct($db, $loanid) {
        $this->db = $db;
        $this->loanid = $loanid;
        $this->init();
    }
    
    private function init() {
        $select = "SELECT LoanNumber, IsClosed, s.ShipmentDate, l.TimestampModified
            FROM loan l
            LEFT JOIN shipment s ON l.LoanID=s.LoanID
            WHERE l.LoanID=$this->loanid";
        $query = $this->db->prepare($select);
        $query->execute();
        if ($row = $query->fetch(PDO::FETCH_OBJ)) {
            $loannumber = substr($row->LoanNumber, 0, 9);
            $loandestination = trim(substr($row->LoanNumber, 10));
            $this->Loan = (object) array(
                'LoanNumber' => substr($row->LoanNumber, 0, 9),
                'LoanDestination' => trim(substr($row->LoanNumber, 10)),
                'LoanForBotanist' => $this->getLoanForBotanist(),
                'LoanDate' => $row->ShipmentDate,
                'IsClosed' => $row->IsClosed,
                'DateLastEdited' => $row->TimestampModified
            );
        }
    }
    
    public function deleteLoanInfo() {
        $update = "UPDATE biocase.abcd_unit
            SET LoanIdentifier=NULL, LoanDestination=NULL, LoanForBotanist=NULL,
                LoanReturnDate=NULL, DateLastEdited=IF('{$this->Loan->DateLastEdited}'>DateLastEdited,
                    '{$this->Loan->DateLastEdited}', DateLastEdited)
            WHERE LoanIdentifier='{$this->Loan->LoanNumber}'";
        $query = $this->db->prepare($update);
        $query->execute();
    }
    
    public function Loan() {
        $this->Units = array();
        if ($this->Loan->IsClosed != 1) {
            $select = "SELECT co.CollectionObjectID
                FROM loanpreparation lp
                JOIN preparation p ON lp.PreparationID=p.PreparationID
                JOIN collectionobject co ON p.CollectionObjectID=co.CollectionObjectID
                WHERE lp.LoanID=$this->loanid
                AND lp.IsResolved=0";
            $query = $this->db->prepare($select);
            $query->execute();
            while ($row = $query->fetch(PDO::FETCH_OBJ)) {
                $unit = new Loan();
                $unit->CollectionObjectID = $row->CollectionObjectID;
                $unit->LoanIdentifier = $this->Loan->LoanNumber;
                $unit->LoanDestination = $this->Loan->LoanDestination;
                $unit->LoanForBotanist = $this->Loan->LoanForBotanist;
                $unit->LoanDate = $this->Loan->LoanDate;
                $unit->DateLastEdited = $this->Loan->DateLastEdited;
                $this->Units[] = $unit;
            }
        }
    }
    
    public function updateLoanInfo() {
        if ($this->Units) {
            $update = "UPDATE biocase.abcd_unit
                SET LoanIdentifier=?,
                    LoanDestination=?,
                    LoanForBotanist=?,
                    LoanDate=?,
                    DateLastEdited=IF(DateLastEdited<?, DateLastEdited, ?)
                WHERE CollectionObjectID=?";
            $query = $this->db->prepare($update);
            foreach ($this->Units as $unit) {
                $query->execute(array(
                    $unit->LoanIdentifier,
                    $unit->LoanDestination,
                    $unit->LoanForBotanist,
                    $unit->LoanDate,
                    $unit->DateLastEdited,
                    $unit->DateLastEdited,
                    $unit->CollectionObjectID
                ));
            }
        }
    }
    
    private function getLoanForBotanist() {
        $botanist = array();
        $select = "SELECT a.LastName, a.FirstName
            FROM loanagent la
            JOIN agent a ON la.AgentID=a.AgentID
            WHERE la.LoanID=$this->loanid AND la.Role='Botanist'";
        $query = $this->db->prepare($select);
        $query->execute();
        while ($row = $query->fetch(PDO::FETCH_OBJ))
            $botanist[] = ($row->FirstName) ? ($row->LastName) . ', ' . $row->FirstName : $row->LastName;
        return implode('; ', $botanist);
    }
    
}
?>
