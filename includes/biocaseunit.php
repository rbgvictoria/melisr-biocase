<?php
class BioCASeUnit {
    private $db;
    private $collectionobjectid;

    private $Init;

    var $Unit;
    var $Collectors;
    var $HigherTaxa;
    var $Identifications;
    var $NamedAreas;
    var $PreviousUnits;
    var $UnitMeasurementOrFacts;
    var $Sequences;
    
    public function __construct($db, $collectionobjectid) {
        $this->db = $db;
        $this->collectionobjectid = $collectionobjectid;
        $this->init();
    }
    
    private function init() {
        $select = "SELECT co.CollectionObjectID, co.Guid, co.CatalogNumber, co.TimestampModified, 
              co.Remarks AS MiscellaneousNotes, co.Text1 AS DescriptiveNotes,
              coa.Remarks AS EthnobotanyInfo, coa.Text3 AS ToxicityInfo,
              coa.Number1 AS Flowers, coa.Number2 AS Fruit, coa.Number3 AS 'Buds', 
              coa.Number4 AS Leafless, coa.Number5 AS Fertile, coa.Number6 AS Sterile,
              ce.CollectingEventID, ce.StationFieldNumber, ce.Remarks AS Habitat, 
              ce.StartDate, ce.StartDatePrecision, ce.EndDate, ce.EndDatePrecision, ce.VerbatimLocality AS CollectingNotes,
              cea.Text5 AS Host, cea.Text4 AS Substrate, cea.Text3 AS Provenance, cea.Text6 AS VerbatimCollectingDate,
              cea.Text11 AS NaturalOccurrence, cea.Text13 AS CultivatedOccurrence, cea.Text2 AS AssociatedTaxa,
              l.LocalityName, l.MinElevation, l.MaxElevation, l.Text1 AS ElevationUnit, l.GeographyID, 
              l.LatLongMethod, l.Text2 AS LatLongSource, l.OriginalElevationUnit AS CoordinateError, l.Lat1Text, l.Latitude1, l.Long1Text, 
              l.Longitude1, l.Datum, l.OriginalLatLongUnit, l.Datum,
              ld.StartDepth, ld.EndDepth, ld.Township AS NearNamedPlace, ld.TownshipDirection AS NearNamedPlaceRelationTo, 
              ld.Text1 AS IBRARegion, NationalParkName AS IBRASubregion, ld.Text3 AS MapReference,
              ld.Island, ld.IslandGroup, ld.WaterBody,
              gc.GeoRefVerificationStatus, gc.GeoRefRemarks, gc.GeoRefDetDate, oi.Identifier AS AcquiredFrom, gc.Text1 AS GeoreferenceSources,
              count(dna.DNASequenceID) AS DNASequences
            FROM collectionobject co
            JOIN collectingevent ce ON co.CollectingEventID=ce.CollectingEventID
            LEFT JOIN locality l ON ce.LocalityID=l.LocalityID
            LEFT JOIN collectionobjectattribute coa ON co.CollectionObjectAttributeID=coa.CollectionObjectAttributeID
            LEFT JOIN localitydetail ld ON l.LocalityID=ld.LocalityID
            LEFT JOIN geocoorddetail gc ON l.LocalityID=gc.LocalityID
            LEFT JOIN collectingeventattribute cea ON ce.CollectingEventAttributeID=cea.CollectingEventAttributeID
            LEFT JOIN otheridentifier oi ON co.CollectionObjectID=oi.CollectionObjectID
            LEFT JOIN dnasequence dna ON co.CollectionObjectID=dna.CollectionObjectID
            WHERE co.CollectionObjectID=$this->collectionobjectid
            GROUP BY co.CollectionObjectID";
        
        $stmt = $this->db->prepare($select);
        $stmt->execute();
        if (!$this->Init = $stmt->fetch(PDO::FETCH_OBJ))
            echo "Can't initialise for collection object with ID $this->collectionobjectid\n";
    }
    
    public function updateUnit() {
        $this->Unit = new Unit();
        
        // Unit
        $this->Unit->CollectionObjectID = $this->Init->CollectionObjectID;
        $this->Unit->MetaDataID = 1;
        $this->Unit->SourceID = 'MELISR';
        $this->Unit->SourceInstitutionID = 'MEL';
        $this->Unit->UnitID = $this->Init->CatalogNumber;
        $this->Unit->UnitGuid = $this->Init->Guid;
        $this->Unit->KindOfUnit = $this->getKindOfUnit();
        $this->Unit->RecordBasis = 'PreservedSpecimen';
        $this->Unit->CollectorsFieldNumber = $this->Init->StationFieldNumber;
        $this->Unit->DateLastEdited = $this->DateLastEdited();
        
        // Gathering
        $this->Unit->AltitudeLowerValue = $this->Init->MinElevation;
        $this->Unit->AltitudeUpperValue = $this->Init->MaxElevation;
        $this->Unit->AltitudeUnitOfMeasurement = $this->Init->ElevationUnit;
        $this->Unit->DepthLowerValue = $this->Init->StartDepth;
        $this->Unit->DepthUpperValue = $this->Init->EndDepth;
        $this->Unit->DepthUnitOfMeasurement = ($this->Init->StartDepth) ? 'm' : NULL;
        $this->Unit->BiotopeText = $this->getBiotopeText();
        $this->Unit->AssociatedTaxa = $this->getAssociatedTaxa();
        $country = $this->getCountryName();
        $this->Unit->CountryName = ($country) ? $country['CountryName'] : NULL;
        $this->Unit->CountryIso3166Code = ($country) ? $country['CountryCode'] : NULL;
        $this->Unit->GatheringIsoDateTimeBegin = $this->getIsoDate($this->Init->StartDate, $this->Init->StartDatePrecision);
        $this->Unit->GatheringIsoDateTimeEnd = $this->getIsoDate($this->Init->EndDate, $this->Init->EndDatePrecision);
        $this->Unit->GatheringDateText = $this->Init->VerbatimCollectingDate;
        $this->Unit->LocalityText = $this->Init->LocalityName;
        $this->Unit->NearNamedPlace = $this->Init->NearNamedPlace;
        $this->Unit->NearNamedPlaceRelationTo = $this->Init->NearNamedPlaceRelationTo;
        $this->Unit->NearNamedPlaceRelationDerivedFlag = ($this->Init->NearNamedPlace) ? 1 : NULL;
        $this->Unit->GatheringNotes = $this->Init->CollectingNotes;
        $this->Unit->UnitNotes = $this->getUnitNotes();
        $this->Unit->CoordinateMethod = $this->getCoordinateMethod();
        $this->Unit->GeoreferencedBy = $this->getGeoreferencedBy();
        $this->Unit->GeoreferencedDate = $this->Init->GeoRefDetDate;
        $this->Unit->GeoreferenceSources = ($this->Init->GeoreferenceSources) ? $this->Init->GeoreferenceSources : $this->Init->MapReference;
        
        $this->Unit->Island = $this->Init->Island;
        $this->Unit->IslandGroup = $this->Init->IslandGroup;
        $this->Unit->WaterBody = $this->Init->WaterBody;
        
        if ($this->Init->GeoRefVerificationStatus == 2)
            $this->Unit->GeoreferenceVerificationStatus = 'Verified by collector';
        elseif ($this->Init->GeoRefVerificationStatus == 3)
            $this->Unit->GeoreferenceVerificationStatus = 'Verified by curator';
        
        
        $this->Unit->GeoreferenceRemarks = $this->Init->GeoRefRemarks;
        $this->Unit->CoordinateErrorDistanceInMeters = $this->getCoordinateErrorDistanceInMeters();
        if ($this->Init->Latitude1 && $this->Init->Longitude1) {
            $this->Unit->LatitudeDecimal = $this->Init->Latitude1;
            $this->Unit->LongitudeDecimal = $this->Init->Longitude1;
            $this->Unit->SpatialDatum = $this->Init->Datum;
            $this->Unit->VerbatimLatitude = $this->Init->Lat1Text;
            $this->Unit->VerbatimLongitude = $this->Init->Long1Text;
            $this->Unit->CoordinatesText = $this->Init->Lat1Text . ' ' . $this->Init->Long1Text;
            $this->updateSiteMeasurementOrFact();
        }
        else {
            $this->Unit->LatitudeDecimal = NULL;
            $this->Unit->LongitudeDecimal = NULL;
            $this->Unit->CoordinatesText = NULL;
        }
        
        // Herbarium unit
        /* This will be done when Loans or Exchange are updated*/
        
        // Nomenclatural Type Designation
        $typification = $this->getNomenclaturalTypeDesignation();
        list($this->Unit->TypeStatus, $this->Unit->DoubtfulFlag, $this->Unit->TypifiedName, $this->Unit->TypeStatusVerifier,
                $this->Unit->TypeStatusVerificationDate, $this->Unit->NomenclaturalTypeDesignationNotes) = array_values($typification);
        
        // DNA Sequences
        
    }
    
    private function DateLastEdited() {
        $select = "SELECT co.TimestampModified AS co_modified, l.TimestampModified AS loc_modified,
                t.TimestampModified AS taxon_modified, lo.TimestampModified AS loan_modified,
                g.TimestampModified AS gift_modified
            FROM collectionobject co
            JOIN collectingevent ce ON co.CollectingEventID=ce.CollectingEventID
            JOIN locality l ON ce.LocalityID=l.LocalityID
            JOIN determination d ON co.CollectionObjectID=d.CollectionObjectID AND d.IsCurrent=1
            JOIN taxon t ON d.TaxonID=t.TaxonID
            JOIN preparation p ON co.CollectionObjectID=p.CollectionObjectID
            LEFT JOIN loanpreparation lp ON p.PreparationID=lp.PreparationID
            LEFT JOIN loan lo ON lp.LoanID=lo.LoanID
            LEFT JOIN giftpreparation gp ON p.PreparationID=gp.PreparationID
            LEFT JOIN gift g ON gp.GiftID=g.GiftID
            WHERE co.CollectionObjectID=$this->collectionobjectid";
        $query = $this->db->query($select);
        $result = $query->fetchAll(5);
        if ($result) {
            $row = $result[0];
            $time = $row->co_modified;
            if ($row->loc_modified && $row->loc_modified > $time) $time = $row->loc_modified;
            if ($row->taxon_modified && $row->taxon_modified > $time) $time = $row->taxon_modified;
            if ($row->loan_modified && $row->loan_modified > $time) $time = $row->loan_modified;
            if ($row->gift_modified && $row->gift_modified > $time) $time = $row->gift_modified;
            return $time;
        }
        else {
            return FALSE;
        }
    }
    
    public function updateDNASequence() {
        $this->Sequences = array();
        $select = "SELECT d.DNASequenceID, d.TargetMarker, d.GenBankAccessionNumber, CONCAT_WS(', ', a.LastName, a.FirstName) AS SequencingAgent,
                d.TotalResidues
            FROM dnasequence d
            JOIN agent a ON d.AgentID=a.AgentID
            WHERE co.CollectionObjectID=$this->collectionobjectid";
        $query = $this->db->query($select);
        $result = $query->fetchAll(5);
        if ($result) {
            foreach ($result as $row) {
                $seq = new Sequence();
                $seq->CollectionObjectID = $this->collectionobjectid;
                $seq->DNASequenceID = $row->DNASequenceID;
                $seq->SequencedPart = $row->TargetMarker;
                $seq->IDInDatabase = $row->GenBankAccessionNumber;
                $seq->SequencingAgent = $row->SequencingAgent;
                $seq->Length = $row->TotalResidues;
                $seq->URI = 'http://www.ncbi.nlm.nih.gov/nuccore/' . $row->GenBankAccessionNumber;
                $this->Sequences[] = $seq;
            }
        }
    }
    
    public function updateCollector() {
        $this->Collectors = array();
        $select = "SELECT CollectorID, IsPrimary=1 AS IsPrimary, OrderNumber, AgentID
            FROM collector
            WHERE CollectingEventID={$this->Init->CollectingEventID}";
        $stmt = $this->db->prepare($select);
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
            $collector = new Collector();
            $collector->SpecifyCollectorID = $row->CollectorID;
            $collector->CollectionObjectID = $this->Init->CollectionObjectID;
            $collector->PrimaryCollector = $row->IsPrimary;
            $collector->Sequence = $row->OrderNumber;
            $collector->AgentText = $this->getAgentName($row->AgentID);
            $this->Collectors[] = $collector;
        }
    }
    
    public function updateIdentification() {
        $this->Identifications = array();
        $this->HigherTaxa = array();
        $select = "SELECT DeterminationID, IsCurrent=1 AS IsCurrent, DeterminedDate, DeterminedDatePrecision,
              FeatureOrBasis, DeterminerID, NameUsage, Remarks, TaxonID, Qualifier, 
              VarQualifier AS QualifierRank, Addendum, GUID
            FROM determination
            WHERE CollectionObjectID={$this->Init->CollectionObjectID}
                AND (FeatureOrBasis != 'Type status' OR isnull(FeatureOrBasis)) AND TaxonID IS NOT NULL";
        $stmt = $this->db->prepare($select);
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
            $identification = new Identification();
            $identification->DeterminationID = $row->DeterminationID;
            $identification->CollectionObjectID = $this->Init->CollectionObjectID;
            $identification->IdentificationID = $row->GUID;
            $identification->IdentificationNotes = $row->Remarks;
            $identification->PreferredFlag = $row->IsCurrent;
            $identification->IdentificationIsoDateTimeBegin = 
                $this->getIsoDate($row->DeterminedDate, $row->DeterminedDatePrecision);
            $identification->IdentifiersText = $this->getAgentName($row->DeterminerID);
            if ($row->DeterminerID) {
                switch ($row->FeatureOrBasis) {
                    case 'Det.':
                    case 'Annot.':
                    case 'AVH annot.':
                    case 'Acc. name change':
                        $identification->IdentifierRole = 'det.';
                        break;
                    
                    case 'Conf.':
                        $identification->IdentifierRole = 'conf.';
                        break;
                }
            }
            $name = $this->getScientificName($row->TaxonID);
            if ($name)
                list($identification->FullScientificNameString,
                    $identification->GenusOrMonomial,
                    $identification->FirstEpithet,
                    $identification->Rank,
                    $identification->InfraspecificEpithet,
                    $identification->AuthorTeam,
                    $identification->HybridFlag,
                    $identification->HybridFlagInsertionPoint,
                    $identification->TaxonRank) = array_values ($name);
            
            if ($row->Qualifier) {
                $identification->IdentificationQualifier = $row->Qualifier;
                if ($row->QualifierRank) 
                    $qualifierrank = $row->QualifierRank;
                else {
                    $select = "SELECT td.Name AS Rank
                        FROM taxon t
                        JOIN taxontreedefitem td
                        ON t.TaxonTreeDefItemID=td.TaxonTreeDefItemID
                        WHERE t.TaxonID=$row->TaxonID";
                    $stmt2 = $this->db->prepare($select);
                    $stmt2->execute();
                    if ($row2 = $stmt2->fetch(PDO::FETCH_OBJ)) {
                        $qualifierrank = ucfirst($row2->Rank);
                    }
                }
                switch ($row->QualifierRank) {
                    case 'subspecies':
                    case 'variety':
                    case 'subvariety':
                    case 'forma':
                    case 'subforma':
                        $identification->IdentificationQualifierInsertionPoint = 3;
                        break;

                    case 'species':
                        $identification->IdentificationQualifierInsertionPoint = 2;
                        break;

                    default:
                        $identification->IdentificationQualifierInsertionPoint = 1;
                        break;
                }
            }
            $identification->NameAddendum = $this->getNameAddendum($row->Addendum);
            
            $this->Identifications[] = $identification;
            
            $highertaxa = $this->updateHigherTaxon($row->DeterminationID, $row->TaxonID);
            $this->HigherTaxa = array_merge($this->HigherTaxa, $highertaxa);
        }
    }
    
    public function getScientificName($taxonid) {
        $select = "SELECT t.UnitName1, t.UnitName2, t.UnitName3, t.UnitName4, t.Author, t.UsfwsCode,
              LOWER(td.Name) AS TaxonRank, t.RankID
            FROM taxon t
            JOIN taxontreedefitem td ON t.TaxonTreeDefItemID=td.TaxonTreeDefItemID
            WHERE t.TaxonID=$taxonid";
        $stmt = $this->db->prepare($select);
        $stmt->execute();
        if ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
            $ret = array(
                'FullScientificNameString' => $row->UnitName1,
                'GenusOrMonomial' => $row->UnitName2,
                'FirstEpithet' => $row->UnitName3,
                'Rank' => NULL,
                'InfraspecificEpithet' => $row->UnitName4,
                'AuthorTeam' => $row->Author,
                'HybridFlag' => NULL,
                'HybridFlagInsertionPoint' => NULL,
                'TaxonRank' => $row->TaxonRank,
            );

            switch ($row->TaxonRank) {
                case 'subspecies':
                    $ret['Rank'] = 'subsp.';
                    break;
                case 'variety':
                    $ret['Rank'] = 'var.';
                    break;
                case 'subvariety':
                    $ret['Rank'] = 'subvar.';
                    break;
                case 'forma':
                    $ret['Rank'] = 'f.';
                    break;
                case 'subforma':
                    $ret['Rank'] = 'subf.';
                    break;
                default:
                    $ret['Rank'] = NULL;
                    break;
            }
            
            if ($row->UsfwsCode == 'x') {
                $ret['HybridFlag'] = 'x';
                switch ($row->RankID) {
                    case 180:
                        $ret['HybridFlagInsertionPoint'] = 1;
                        break;
                    case 220:
                        $ret['HybridFlagInsertionPoint'] = 2;
                        break;
                    case 230:
                    case 240:
                    case 250:
                    case 260:
                    case 270:
                        $ret['HybridFlagInsertionPoint'] = 1;
                        break;
                }
            }
            return $ret;
        } 
        else {
            return FALSE;
        }
    
    }
    
    public function updateHigherTaxon($determinationid, $taxonid) {
        $highertaxa = array();
        $select = "SELECT HigherTaxonName, HigherTaxonRank
            FROM biocase.aux_highertaxon
            WHERE TaxonID=$taxonid
            AND HigherTaxonRank IN ('Kingdom', 'Subkingdom', 'Division', 'Subdivision', 'Class', 
                'Subclass', 'Superorder', 'Order', 'Suborder', 'Family', 'Subfamily', 'Tribe')";
        $stmt = $this->db->prepare($select);
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
            $highertaxon = new HigherTaxon();
            $highertaxon->DeterminationID = $determinationid;
            $highertaxon->CollectionObjectID = $this->Init->CollectionObjectID;
            $highertaxon->HigherTaxonID = $taxonid;
            $highertaxon->HigherTaxonName = $row->HigherTaxonName;
            $highertaxon->HigherTaxonRank = $row->HigherTaxonRank;
            $highertaxa[] = $highertaxon;
        }
        return $highertaxa;
    }
    
    public function updateNamedArea() {
        $this->NamedAreas = array();
        
        // State
        $select = "SELECT NodeNumber
            FROM geography
            WHERE GeographyID={$this->Init->GeographyID}";
        $stmt = $this->db->prepare($select);
        $stmt->execute();
        if ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
            $nodenumber = $row->NodeNumber;
            
            // state
            $select = "SELECT Name FROM geography
                WHERE NodeNumber<=$nodenumber AND HighestChildNodeNumber>=$nodenumber
                  AND GeographyTreeDefItemID=4";
            $stmt = $this->db->prepare($select);
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
                $namedarea = new NamedArea();
                $namedarea->CollectionObjectID = $this->Init->CollectionObjectID;
                $namedarea->AreaClass = 'State';
                switch ($row->Name) {
                    case 'Macquarie Island':
                        $namedarea->AreaName = 'Tasmania';
                        break;

                    case 'Lord Howe Island':
                        $namedarea->AreaName = 'New South Wales';
                        break;

                    default:
                        $namedarea->AreaName = $row->Name;
                        break;
                }
                
                $this->NamedAreas[] = $namedarea;
            }
            
            // continent
            $namedarea = new NamedArea();
            $namedarea->CollectionObjectID = $this->Init->CollectionObjectID;
            $namedarea->AreaClass = 'Continent';
            
            $select = "SELECT Name, Text2 AS Continent FROM geography
                WHERE NodeNumber<=$nodenumber AND HighestChildNodeNumber>=$nodenumber
                  AND GeographyTreeDefItemID=3"; // country
            $stmt = $this->db->prepare($select);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_OBJ);
            if ($result) {
                $row = $result[0];
                if ($row->Continent) {
                    $namedarea->AreaName = $row->Continent;
                }
            }
            
            if (!$namedarea->AreaName && ($this->Init->Island || $this->Init->IslandGroup)) {
                $islandGroup = $this->Init->IslandGroup;
                $island = $this->Init->Island;
                $select = "SELECT Continent 
                    FROM aux_island ";
                if ($islandGroup && $island) {
                    $select .= "WHERE IslandGroup='$islandGroup' AND Island='$island'";
                }
                elseif ($islandGroup) {
                    $select .= "WHERE IslandGroup='$islandGroup' AND Island IS NULL";
                }
                elseif ($island) {
                    $select .= "WHERE IslandGroup IS NULL AND Island='$island'";
                }
                $query = $this->db->query($select);
                $result = $query->fetchAll(5);
                if ($result) {
                    $row = $result[0];
                    $namedarea->AreaName = $row->Continent;
                }
            }
            
            if ($namedarea->AreaName) {
                $this->NamedAreas[] = $namedarea;
            }
        }
        
        // IBRA region
        if ($this->Init->IBRARegion) {
            $namedarea = new NamedArea();
            $namedarea->CollectionObjectID = $this->Init->CollectionObjectID;
            $namedarea->AreaClass = 'IBRA region';
            $namedarea->AreaName = $this->Init->IBRARegion;
            $this->NamedAreas[] = $namedarea;
        }
        
        // IBRA region
        if ($this->Init->IBRASubregion) {
            $namedarea = new NamedArea();
            $namedarea->CollectionObjectID = $this->Init->CollectionObjectID;
            $namedarea->AreaClass = 'IBRA subregion';
            $namedarea->AreaName = $this->Init->IBRASubregion;
            $this->NamedAreas[] = $namedarea;
        }
    }
    
    public function updatePreviousUnits() {
        $this->PreviousUnits = array();
        $select = "SELECT Identifier, Institution
            FROM otheridentifier
            WHERE CollectionObjectID={$this->Init->CollectionObjectID}
              AND Remarks='Ex herbarium' AND !isnull(Institution) AND Institution!=''";
        $stmt = $this->db->prepare($select);
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
            $oi = new PreviousUnit();
            $oi->CollectionObjectID = $this->Init->CollectionObjectID;
            $oi->SourceID = $row->Identifier;
            $oi->SourceInstitutionID = $row->Identifier;
            $oi->UnitID = $row->Institution;
            
            $this->PreviousUnits[] = $oi;
        }
    }
    
    public function updateSiteMeasurementOrFact() {
        // verbatimCoordinateSystem
        $vcs = new SiteMeasurementOrFact();
        $vcs->CollectionObjectID = $this->Init->CollectionObjectID;
        $vcs->Parameter = 'verbatimCoordinateSystem';
        $vcs->IsQuantitative = 0;

        // coordinatePrecision
        $cp = new SiteMeasurementOrFact();
        $cp->CollectionObjectID = $this->Init->CollectionObjectID;
        $cp->Parameter = 'coordinatePrecision';
        $cp->IsQuantitative = 1;
        $cp->UnitOfMeasurement = 'degrees';

        // verbatimSRS
        $vsrs = new SiteMeasurementOrFact();
        $vsrs->CollectionObjectID = $this->Init->CollectionObjectID;
        $vsrs->Parameter = 'verbatimSRS';
        $vsrs->IsQuantitative = 0;

        switch ($this->Init->OriginalLatLongUnit) {
            case 0:
                $vcs->LowerValue = 'decimal degrees';
                break;

            case 1:
                $vcs->LowerValue = 'degrees minutes seconds';
                break;

            case 2:
                $vcs->LowerValue = 'degrees decimal minutes';
                break;

            default:
                break;
        }

        $latbits = explode(' ', substr($this->Init->Lat1Text, 0, strlen($this->Init->Lat1Text)-2));
        $longbits = explode(' ', substr($this->Init->Long1Text, 0, strlen($this->Init->Long1Text)-2));

        $clat = count($latbits);
        $clong = count($longbits);

        switch ($clat) {
            case 1: // degrees
                $vcs->LowerValue = ($clat >= $clong) ? 'decimal degrees' : NULL;
                $latitudePrecision = $this->decimalPrecision(substr($latbits[0], 0, strlen($latbits[0])-1));
                break;

            case 2: // minutes
                $vcs->LowerValue = ($clat >= $clong) ? 'degrees decimal minutes' : NULL;
                $latitudePrecision = 0.01667 * $this->decimalPrecision(substr($latbits[1], 0, strlen($latbits[1])-1));
                break;

            case 3: // seconds
                $vcs->LowerValue = ($clat >= $clong) ? 'degrees minutes seconds' : NULL;
                $latitudePrecision = 0.000278 * $this->decimalPrecision(substr($latbits[2], 0, strlen($latbits[2])-1));
                break;
            
            default :
                $vcs->LowerValue = NULL;
                $latitudePrecision = NULL;
        }

        switch ($clong) {
            case 1: // degrees
                if ($clong > $clat) $vcs->LowerValue =  'decimal degrees';
                $longitudePrecision = $this->decimalPrecision(substr($longbits[0], 0, strlen($longbits[0])-1));
                break;

            case 2: // minutes
                if ($clong > $clat) $vcs->LowerValue =  'degrees decimal minutes';
                $longitudePrecision = 0.01667 * $this->decimalPrecision(substr($longbits[1], 0, strlen($longbits[1])-1));
                break;

            case 3: // seconds
                if ($clong > $clat) $vcs->LowerValue =  'degrees minutes seconds';
                $longitudePrecision = 0.000278 * $this->decimalPrecision(substr($longbits[2], 0, strlen($longbits[2])-1));
                break;
            
            default :
                $longitudePrecision = NULL;
        }

        $cp->LowerValue = ($latitudePrecision <= $longitudePrecision) ? $latitudePrecision : $longitudePrecision;

        $datums = array(
            'GDA94' => 'EPSG:4283',
            'WGS84' => 'EPSG:4326',
            'AGD66' => 'EPSG:4202',
            'AGD84' => 'EPSG:4203',
            'NAD84' => 'EPSG:????',
            'NAD83' => 'EPSG:4269',
            'NAD27' => 'EPSG:4267'
        );

        if ($this->Init->Datum)
            $vsrs->LowerValue = $datums[$this->Init->Datum];
        
        $this->Unit->CoordinatePrecision = $cp->LowerValue;
        $this->Unit->VerbatimCoordinateSystem = $vcs->LowerValue;
        $this->Unit->VerbatimSRS = $vsrs->LowerValue;
    }
    
    private function decimalPrecision($number) {
        if (strpbrk($number, '.')) {
            $numdec = strlen(substr(strrchr($number, '.'), 1));
            $prec = '0.';
            while ($numdec > 1) {
                $prec .= '0';
                $numdec--;
            }
            $prec .= '1';
        }
        else 
            $prec = 1;
        return $prec;
    }

    
    public function updateUnitMeasurementOrFact() {
        $this->UnitMeasurementOrFacts = array();
        if ($this->Init->NaturalOccurrence) {
            $mof = new UnitMeasurementOrFact();
            $mof->CollectionObjectID = $this->Init->CollectionObjectID;
            $mof->Parameter = 'NaturalOccurrence';
            $mof->LowerValue = $this->Init->NaturalOccurrence;
            $mof->IsQuantitative = 0;
            $this->UnitMeasurementOrFacts[] = $mof;
        }
        
        if ($this->Init->CultivatedOccurrence) {
            $mof = new UnitMeasurementOrFact();
            $mof->CollectionObjectID = $this->Init->CollectionObjectID;
            $mof->Parameter = 'CultivatedOccurrence';
            $mof->LowerValue = $this->Init->CultivatedOccurrence;
            $mof->IsQuantitative = 0;
            $this->UnitMeasurementOrFacts[] = $mof;
        }
        
        $phenology = array();
        if ($this->Init->Flowers) $phenology[] = 'flowers';
        if ($this->Init->Fruit) $phenology[] = 'fruit'; 
        if ($this->Init->Buds) $phenology[] = 'buds'; 
        if ($this->Init->Leafless) $phenology[] = 'leafless'; 
        if ($this->Init->Fertile) $phenology[] = 'fertile'; 
        if ($this->Init->Sterile) $phenology[] = 'sterile';
        
        if ($phenology) {
            $mof = new UnitMeasurementOrFact();
            $mof->CollectionObjectID = $this->Init->CollectionObjectID;
            $mof->Parameter = 'Phenology';
            $mof->LowerValue = implode('; ', $phenology);
            $mof->IsQuantitative = 0;
            $this->UnitMeasurementOrFacts[] = $mof;
        }
    }
    
    private function getKindOfUnit() {
        $select = "SELECT p.PrepTypeID
            FROM preparation p
            WHERE p.CollectionObjectID={$this->Init->CollectionObjectID}
                AND p.PrepTypeID IN (1, 2, 3, 4, 8, 10, 13)";
        $stmt = $this->db->prepare($select);
        $stmt->execute();
        
        $kou = NULL;
        if ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
            switch ($row->PrepTypeID) {
                case 1: // Sheet
                case 13: // Display Set
                    $kou = 'sheet';
                    break;
                case 2: // Spirit
                    $kou = 'alcohol';
                    break;
                case 3: // Carpological
                    $kou = 'fruit';
                    break;
                case 4: // Packet
                    $kou = 'packet';
                    break;
                case 8: // Cibachrome
                case 10: // Photograph of specimen
                    $kou = 'image';
                    break;
            }
            
        }
        else {
            $select = "SELECT p.PrepTypeID
                FROM preparation p
                WHERE p.CollectionObjectID=$this->collectionobjectid";
            $stmt = $this->db->prepare($select);
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
                switch ($row->PrepTypeID) {
                    case 5: // Photographic slide
                        $kou = 'transparency';
                        break;
                    case 6: // MicroscopeSlide
                        $kou = 'slide';
                        break;
                    case 7: // Silicagel sample
                        $kou = 'other';
                        break;
                    case 12: // Fungal culture
                        $kou = 'packet';
                        break;
                    case 14: // Seed collection
                        $kou = 'seed';
                        break;
                    default:
                        $kou = 'other';
                        break;
                }
            }
        }
        return $kou;
    }
    
    
    /**
     * getBiotopeText function
     * 
     * Concatenates contents of Habitat, Associated Taxa, Substrate and Host fields into 
     * single Habitat field.
     */
    private function getBiotopeText() {
        $habitat = array();
        if ($this->Init->Habitat)
            $habitat[] = trim($this->Init->Habitat);
        if ($this->Init->Substrate)
            $habitat[] = trim($this->Init->Substrate);
        
        if ($habitat) {
            foreach ($habitat as $key => $value) {
                if (substr($value, strlen($value)-1) != '.')
                    $habitat[$key] = $value . '.';
            }
            return implode(' ', $habitat);
        }
        else
            return NULL;
    }
    
    private function getAssociatedTaxa() {
        $associated = array();
        if ($this->Init->Host)
            $associated[] = 'host: ' . trim($this->Init->Host);
        if ($this->Init->AssociatedTaxa)
            $associated[] = 'cohabitatants: ' . trim($this->Init->AssociatedTaxa);
        
        if ($associated) {
            foreach ($associated as $key => $value) {
                if (substr($value, strlen($value)-1) != '.')
                    $associated[$key] = $value . '.';
            }
            return implode('; ', $associated);
        }
        else
            return NULL;
    }
    
    private function getCountryName() {
        $country = FALSE;
        $stmt = $this->db->prepare("SELECT NodeNumber FROM geography 
            WHERE GeographyID={$this->Init->GeographyID}");
        $stmt->execute();
        $result = $stmt->fetchAll(5);
        if ($result) {
            $row = $result[0];
            $selStmt = $this->db->prepare("SELECT Name, GeographyCode FROM geography
                WHERE NodeNumber<=$row->NodeNumber AND HighestChildNodeNumber>=$row->NodeNumber
                    AND GeographyTreeDefItemID=3");
            $selStmt->execute();
            $res = $selStmt->fetchAll(5);
            if ($res) {
                $r = $res[0];
                $country = array(
                    'CountryName' => $r->Name,
                    'CountryCode' => $r->GeographyCode
                );
            }
        }
        return $country;
    }
    
    private function getIsoDate($date, $precision=FALSE) {
        switch ($precision) {
            case 2:
                $date = substr($date, 0, 7);
                break;
            case 3:
                $date = substr($date, 0, 4);
                break;
            default:
                $date = substr($date, 0, 10);
                break;
        }
        return $date;
    }
    
    private function getCoordinateMethod() {
        $method = NULL;
        switch ($this->Init->LatLongMethod) {
            case 'GEOLocate':
                $method = 'GEOLocate';
                break;
            
            case 3: // AMG conversion
            case 'AMG conversion':
            case 7: // Map or atlas
            case 'Map or atlas':
                $method = 'Map';
                break;

            case 4:
            case 'GPS':
                $method = 'GPS';
                break;
            
            case 8:
            case 'GeoNames':
                $method = 'GeoNames';
                break;
            
            case 9:
            case 'GA gazetteer':
                $method = 'GA gazetteer';
                break;
            
            case 'Google Earth':
                $method = 'Google Earth';
                break;
            
            default:
                $method = NULL;
                break;
        }
        
        if ($this->Init->Latitude1 && $this->Init->Longitude1 && !$this->Init->LatLongMethod) {
            $method = 'Unknown';
        }
        
        return $method;
    }
    
    private function getGeoreferencedBy() {
        $source = NULL;
        switch ($this->Init->LatLongSource) {
            case '1': // Collector
                $select = "SELECT GROUP_CONCAT(CONCAT_WS(', ', a.LastName, a.FirstName) ORDER BY c.OrderNumber SEPARATOR '|')
                    FROM collectionobject co
                    JOIN collectingevent ce ON co.CollectingEventID=ce.CollectingEventID
                    JOIN collector c ON ce.CollectingEventID=c.CollectingEventID AND c.IsPrimary=1
                    JOIN agent a ON c.AgentID=a.AgentID
                    WHERE co.CollectionObjectID=$this->collectionobjectid";
                $query = $this->db->query($select);
                $result = $query->fetchAll(3);
                if ($result) {
                    $row = $result[0];
                    $source = $row[0];
                }
                break;
            
            case '2': // Curator
                $source = 'National Herbarium of Victoria (MEL)';
                break;
            
            case '3': // Exchange label
                $select = "SELECT CONCAT(SUBSTRING(a.LastName, LOCATE('--', a.LastName)+3), ' (', a.Abbreviation, ')')
                    FROM collectionobject co
                    JOIN collectingevent ce ON co.CollectingEventID=ce.CollectingEventID
                    JOIN locality l ON ce.LocalityID=l.LocalityID
                    LEFT JOIN otheridentifier oi ON co.CollectionObjectID=oi.CollectionObjectID AND oi.Remarks='Ex herbarium'
                    LEFT JOIN agent a ON oi.Identifier=a.Abbreviation
                    WHERE l.Text2=3 AND co.CollectionObjectID=$this->collectionobjectid";
                $query = $this->db->query($select);
                $result = $query->fetchAll(3);
                if ($result) {
                    $row = $result[0];
                    $source = $row[0];
                }
                break;
            
            default:
                break;
        }
        return $source;
    }
    
    private function getCoordinateErrorDistanceInMeters() {
        switch ($this->Init->CoordinateError) {
            case 1:
                $error = 50;
                break;

            case 2:
                $error = 1000;
                break;

            case 3:
                $error = 10000;
                break;

            case 4:
                $error = 25000;
                break;

            default:
                $error = NULL;
                break;
        }
        return $error;
    }
    
    private function getNomenclaturalTypeDesignation() {
        $typification = array(
            'TypeStatus' => NULL,
            'DoubtfulFlag' => NULL,
            'TypifiedName' => NULL,
            'TypeStatusVerifier' => NULL,
            'TypeStatusVerificationDate' => NULL,
            'NomenclaturalTypeDesignationNotes' => NULL,
        );
        $select = "SELECT TypeStatusName, SubSpQualifier, TaxonID, DeterminerID, 
              DeterminedDate, DeterminedDatePrecision, Remarks
            FROM determination
            WHERE CollectionObjectID={$this->Init->CollectionObjectID}
              AND YesNo1=1 AND TypeStatusName!='Authentic specimen'";
        $stmt = $this->db->prepare($select);
        $stmt->execute();
        if ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
            $typification['TypeStatus'] = ($row->TypeStatusName == 'Paraneotype') 
                ? 'syntype' : strtolower($row->TypeStatusName);
            $typification['DoubtfulFlag'] = str_replace('le', 'ly', $row->SubSpQualifier);
            $typifiedname = $this->getScientificName($row->TaxonID);
            $typification['TypifiedName'] = $typifiedname['FullScientificNameString'];
            $typification['TypeStatusVerifier'] = $this->getAgentName($row->DeterminerID);
            $typification['TypeStatusVerificationDate'] = $this->getIsoDate($row->DeterminedDate, $row->DeterminedDatePrecision);
            $typification['NomenclaturalTypeDesignationNotes'] = $row->Remarks;
        }
        return $typification;
    }
    
    private function getAgentName($agentid) {
        $agentname = NULL;
        $select = "SELECT LastName, FirstName
            FROM agent
            WHERE AgentID=$agentid";
        $stmt = $this->db->prepare($select);
        $stmt->execute();
        if ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
            $agentname = $row->LastName;
            $agentname .= ($row->FirstName) ? ', ' . $row->FirstName : '';
            if (substr($agentname, 0, 6) == 'MEL --')
                $agentname = 'National Herbarium of Victoria (MEL)';
        }
        return $agentname;
    }
    
    
    private function getUnitNotes() {
        $notes = array();
        if($this->Init->DescriptiveNotes) $notes[] = trim($this->Init->DescriptiveNotes);
        if($this->Init->EthnobotanyInfo) $notes[] = trim($this->Init->EthnobotanyInfo);
        if($this->Init->ToxicityInfo) $notes[] = trim($this->Init->ToxicityInfo);
        if($this->Init->MiscellaneousNotes) $notes[] = trim($this->Init->MiscellaneousNotes);
        
        if ($notes) {
            foreach ($notes as $key => $value) {
                if (substr($value, strlen($value)-1) != '.') $notes[$key] .= '.';
            }
        }
        return implode(' ', $notes);
    }
    
    private function getNameAddendum($addendum) {
        switch ($addendum) {
            case 's.l.':
                $addendum = 's. lat.';
                break;

            case 's. str.':
                $addendum = 's. str.';
                break;

            case 'complex':
            case 'group':
                $addendum = 'agg.';
                break;

            default:
                $addendum = NULL;
                break;
        };
        return $addendum;
    }
}


?>
