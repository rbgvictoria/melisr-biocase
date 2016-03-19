<?php

class BioCASeExchange {
    private $db;
    private $giftid;
    
    var $Gift;
    
    public function __construct($db, $giftid) {
        $this->db = $db;
        $this->giftid = $giftid;
        $this->init();
    }
    
    private function init() {
        $select = "SELECT g.TimestampModified
            FROM gift g WHERE g.GiftID=$this->giftid";
        $query = $this->db->prepare($select);
        $query->execute();
        if ($row = $query->fetch(PDO::FETCH_OBJ))
            $this->Gift = (object) array('DateLastEdited' => $row->TimestampModified);
    }
    
    public function updateDuplicatesSentTo() {
        $update = "UPDATE biocase.abcd_unit
            SET DuplicatesDistributedTo=?, DateLastEdited=IF(DateLastEdited>=?, DateLastEdited, ?)
            WHERE CollectionObjectID=?";
        $update = $this->db->prepare($update);

        $select = "SELECT p.CollectionObjectID
            FROM giftpreparation gp
            JOIN preparation p ON gp.PreparationID=p.PreparationID
            WHERE gp.GiftID=$this->giftid
            AND p.PrepTypeID=15";
        $query = $this->db->prepare($select);
        $query->execute();
        while ($row = $query->fetch(PDO::FETCH_OBJ)) {
            $exchange = new Exchange();
            $exchange->CollectionObjectID = $row->CollectionObjectID;
            $exchange->DateLastEdited = $this->Gift->DateLastEdited;
            $exchange->DuplicatesDistributedTo = $this->getDuplicatesString($row->CollectionObjectID);
            $update->execute(array(
                $exchange->DuplicatesDistributedTo,
                $exchange->DateLastEdited,
                $exchange->DateLastEdited,
                $exchange->CollectionObjectID
            ));
        }
    }
    
    private function getDuplicatesString($collectionobjectid) {
        $select = "SELECT group_concat(a.Abbreviation ORDER BY a.Abbreviation SEPARATOR ', ') AS DuplicatesSentTo
            FROM gift g
            JOIN shipment s ON g.GiftID=s.GiftID
            JOIN agent a ON s.ShippedToID=a.AgentID
            JOIN giftpreparation gp ON g.GiftID=gp.GiftID
            JOIN preparation p ON gp.PreparationID=p.PreparationID
            WHERE p.CollectionObjectID=$collectionobjectid
              AND p.PrepTypeID=15
            GROUP BY p.CollectionObjectID";
        $query = $this->db->prepare($select);
        $query->execute();
        if ($row = $query->fetch(PDO::FETCH_OBJ))
            return $row->DuplicatesSentTo;
        else
            return FALSE;
    }
}
?>
