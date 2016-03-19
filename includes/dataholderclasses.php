<?php

class NamedArea {
    var $NamedAreaID;
    var $CollectionObjectID;
    var $AreaClass;
    var $AreaName;
    var $AreaCode;
}

class Collector {
    var $CollectorID;
    var $SpecifyCollectorID;
    var $CollectionObjectID;
    var $PrimaryCollector;
    var $Sequence;
    var $AgentText;
}

class HigherTaxon {
    var $HigherTaxonID;
    var $DeterminationID;
    var $CollectionObjectID;
    var $HigherTaxonName;
    var $HigherTaxonRank;
}

class TaxonHigherTaxon {
    var $HigherTaxonID;
    var $TaxonID;
    var $HigherTaxonName;
    var $HigherTaxonRank;
}

class TaxonName {
    var $TaxonID;
    var $FullScientificNameString;
    var $GenusOrMonomial;
    var $FirstEpithet;
    var $Rank;
    var $InfraspecificEpithet;
    var $AuthorTeam;
    var $HybridFlag;
    var $HybridFlagInsertionPoint;
}

class Identification {
    var $DeterminationID;
    var $CollectionObjectID;
    var $IdentificationID;
    var $PreferredFlag;
    var $IdentificationIsoDateTimeBegin;
    var $IdentifierRole;
    var $IdentifiersText;
    var $IdentificationNotes;
    var $FullScientificNameString;
    var $TaxonRank;
    var $IdentificationQualifier;
    var $IdentificationQualifierInsertionPoint;
    var $NameAddendum;
    var $AuthorTeam;
    var $GenusOrMonomial;
    var $FirstEpithet;
    var $Rank;
    var $InfraspecificEpithet;
    var $HybridFlag;
    var $HybridFlagInsertionPoint;
}

class PreviousUnit {
    var $PreviousUnitID;
    var $CollectionObjectID;
    var $SourceID;
    var $SourceInstitutionID;
    var $UnitID;
}

class Unit {
    var $CollectionObjectID;
    var $MetaDataID;

    // Unit
    var $UnitID;
    var $UnitGuid;
    var $SourceID;
    var $SourceInstitutionID;
    var $KindOfUnit;
    var $RecordBasis;
    var $CollectorsFieldNumber;
    var $DateLastEdited;

    // Gathering
    var $AltitudeIsQuantitative = NULL;
    var $AltitudeLowerValue = NULL;
    var $AltitudeUpperValue = NULL;
    var $AltitudeUnitOfMeasurement = NULL;
    var $DepthIsQuantitative = NULL;
    var $DepthLowerValue = NULL;
    var $DepthUpperValue = NULL;
    var $DepthUnitOfMeasurement = NULL;
    var $BiotopeText = NULL;
    var $AssociatedTaxa = NULL;
    var $CountryName = NULL;
    var $CountryIso3166Code = NULL;
    var $GatheringIsoDateTimeBegin = NULL;
    var $GatheringIsoDateTimeEnd = NULL;
    var $GatheringDateText = NULL;
    var $LocalityText = NULL;
    var $NearNamedPlace = NULL;
    var $NearNamedPlaceRelationTo = NULL;
    var $NearNamedPlaceRelationDerivedFlag = NULL;
    var $GatheringNotes = NULL;
    var $UnitNotes = NULL;
    var $CoordinateMethod = NULL;
    var $GeoreferencedBy = NULL;
    var $GeoreferencedDate = NULL;
    var $GeoreferenceSources = NULL;
    var $GeoreferenceVerificationStatus = NULL;
    var $GeoreferenceRemarks = NULL;
    var $CoordinateErrorDistanceInMeters = NULL;
    var $LatitudeDecimal = NULL;
    var $VerbatimLatitude = NULL;
    var $LongitudeDecimal = NULL;
    var $VerbatimLongitude = NULL;
    var $CoordinatesText = NULL;
    var $SpatialDatum = NULL;
    var $CoordinatePrecision = NULL;
    var $VerbatimCoordinateSystem = NULL;
    var $VerbatimSRS = NULL;

    // Nomenclatural Type Designation
    var $TypeStatus = NULL;
    var $DoubtfulFlag = NULL;
    var $TypifiedName = NULL;
    var $TypeStatusVerifier = NULL;
    var $TypeStatusVerificationDate = NULL;
    var $NomenclaturalTypeDesignationNotes = NULL;
    
    var $AcquiredFrom = NULL;
    
    var $Island = NULL;
    var $IslandGroup = NULL;
    var $WaterBody = NULL;
}

class SiteMeasurementOrFact {
    var $SiteMeasurementOrFactID;
    var $CollectionObjectID;
    var $Parameter;
    var $LowerValue;
    var $UpperValue;
    var $UnitOfMeasurement;
    var $IsQuantitative;
}

class UnitMeasurementOrFact {
    var $UnitMeasurementOrFactID;
    var $CollectionObjectID;
    var $Parameter;
    var $LowerValue;
    var $UpperValue;
    var $UnitOfMeasurement;
    var $IsQuantitative;
}

class Loan {
    var $CollectionObjectID = NULL;
    var $LoanIdentifier = NULL;
    var $LoanDestination = NULL;
    var $LoanForBotanist = NULL;
    var $LoanDate = NULL;
    var $LoanReturnDate = NULL;
    var $DateLastEdited = NULL;
}

class Exchange {
    var $CollectionObjectID = NULL;
    var $DuplicatesDistributedTo = NULL;
    var $DateLastEdited = NULL;
}

class Sequence {
    var $DNASequenceID = NULL;
    var $CollectionObjectID = NULL;
    var $SequencedPart = NULL;
    var $IDInDatabase = NULL;
    var $URI = NULL;
    var $SequencingAgent = NULL;
    var $Length = NULL;
}

?>
