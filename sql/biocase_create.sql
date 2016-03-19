-- MySQL dump 10.13  Distrib 5.6.17, for Win32 (x86)
--
-- Host: 203.55.15.78    Database: biocase
-- ------------------------------------------------------
-- Server version	5.1.73-0ubuntu0.10.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `abcd_collector`
--

DROP TABLE IF EXISTS `abcd_collector`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `abcd_collector` (
  `CollectorID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `SpecifyCollectorID` int(10) unsigned DEFAULT NULL,
  `CollectionObjectID` int(11) unsigned NOT NULL,
  `PrimaryCollector` tinyint(1) NOT NULL,
  `Sequence` int(2) NOT NULL,
  `AgentText` varchar(255) NOT NULL,
  PRIMARY KEY (`CollectorID`),
  UNIQUE KEY `CollectionObjectID` (`CollectionObjectID`,`Sequence`),
  KEY `SpecifyCollectorID` (`SpecifyCollectorID`)
) ENGINE=MyISAM AUTO_INCREMENT=1084189 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `abcd_highertaxon`
--

DROP TABLE IF EXISTS `abcd_highertaxon`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `abcd_highertaxon` (
  `HigherTaxonID` int(11) NOT NULL AUTO_INCREMENT,
  `DeterminationID` int(11) NOT NULL,
  `CollectionObjectID` int(11) NOT NULL,
  `HigherTaxonName` varchar(80) NOT NULL,
  `HigherTaxonRank` varchar(20) NOT NULL,
  PRIMARY KEY (`HigherTaxonID`),
  UNIQUE KEY `DeterminationID` (`DeterminationID`,`HigherTaxonRank`),
  KEY `CollectionObjectID` (`CollectionObjectID`)
) ENGINE=MyISAM AUTO_INCREMENT=15942408 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `abcd_identification`
--

DROP TABLE IF EXISTS `abcd_identification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `abcd_identification` (
  `DeterminationID` int(11) unsigned NOT NULL,
  `CollectionObjectID` int(11) unsigned NOT NULL,
  `IdentificationID` varchar(128) DEFAULT NULL,
  `PreferredFlag` tinyint(1) unsigned NOT NULL,
  `IdentificationISODateTimeBegin` varchar(10) DEFAULT NULL,
  `IdentifierRole` varchar(30) DEFAULT NULL,
  `IdentifiersText` varchar(40) DEFAULT NULL,
  `IdentificationNotes` text,
  `FullScientificNameString` varchar(256) NOT NULL,
  `TaxonRank` varchar(16) DEFAULT NULL,
  `IdentificationQualifier` varchar(20) DEFAULT NULL,
  `IdentificationQualifierInsertionPoint` tinyint(1) DEFAULT NULL,
  `NameAddendum` varchar(50) DEFAULT NULL,
  `AuthorTeam` varchar(128) DEFAULT NULL,
  `GenusOrMonomial` varchar(64) DEFAULT NULL,
  `FirstEpithet` varchar(128) DEFAULT NULL,
  `Rank` varchar(12) DEFAULT NULL,
  `InfraspecificEpithet` varchar(128) DEFAULT NULL,
  `HybridFlag` varchar(1) DEFAULT NULL,
  `HybridFlagInsertionPoint` tinyint(1) unsigned DEFAULT NULL,
  PRIMARY KEY (`DeterminationID`),
  KEY `CollectionObjectID` (`CollectionObjectID`),
  KEY `PreferredFlag` (`PreferredFlag`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `abcd_metadata`
--

DROP TABLE IF EXISTS `abcd_metadata`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `abcd_metadata` (
  `MetaDataID` int(11) unsigned NOT NULL DEFAULT '0',
  `DatasetTitle` varchar(100) DEFAULT NULL,
  `TechnicalContactName` varchar(100) DEFAULT NULL,
  `DateModified` date DEFAULT NULL,
  `Owner` varchar(100) DEFAULT NULL,
  `SourceID` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`MetaDataID`),
  KEY `SourceID` (`SourceID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `abcd_namedarea`
--

DROP TABLE IF EXISTS `abcd_namedarea`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `abcd_namedarea` (
  `NamedAreaID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `CollectionObjectID` int(11) unsigned NOT NULL,
  `AreaClass` varchar(30) NOT NULL,
  `AreaName` varchar(150) NOT NULL,
  `AreaCode` varchar(4) DEFAULT NULL,
  `AreaCodeStandard` varchar(16) DEFAULT NULL,
  PRIMARY KEY (`NamedAreaID`),
  UNIQUE KEY `CollectionObjectID` (`CollectionObjectID`,`AreaClass`)
) ENGINE=MyISAM AUTO_INCREMENT=5704549 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `abcd_previousunit`
--

DROP TABLE IF EXISTS `abcd_previousunit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `abcd_previousunit` (
  `PreviousUnitID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `CollectionObjectID` int(11) unsigned NOT NULL,
  `SourceID` varchar(50) NOT NULL,
  `SourceInstitutionID` varchar(150) NOT NULL,
  `UnitID` varchar(150) NOT NULL,
  PRIMARY KEY (`PreviousUnitID`),
  KEY `CollectionObjectID` (`CollectionObjectID`)
) ENGINE=MyISAM AUTO_INCREMENT=154196 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `abcd_sequence`
--

DROP TABLE IF EXISTS `abcd_sequence`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `abcd_sequence` (
  `SequenceID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `CollectionObjectID` int(10) unsigned NOT NULL,
  `Database` varchar(32) DEFAULT 'GenBank',
  `IDInDatabase` varchar(32) NOT NULL,
  `URI` varchar(128) NOT NULL,
  `SequencedPart` varchar(128) DEFAULT NULL,
  `SequencingAgent` varchar(128) DEFAULT NULL,
  `Length` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`SequenceID`),
  KEY `CollectionObjectID` (`CollectionObjectID`),
  KEY `IDInDatabase` (`IDInDatabase`)
) ENGINE=MyISAM AUTO_INCREMENT=566 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `abcd_sitemeasurementorfact`
--

DROP TABLE IF EXISTS `abcd_sitemeasurementorfact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `abcd_sitemeasurementorfact` (
  `SiteMeasurementOrFactId` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `CollectionObjectID` int(11) unsigned NOT NULL,
  `Parameter` varchar(32) NOT NULL,
  `LowerValue` varchar(256) NOT NULL,
  `UpperValue` varchar(32) DEFAULT NULL,
  `UnitOfMeasurement` varchar(32) DEFAULT NULL,
  `IsQuantitative` tinyint(1) unsigned DEFAULT NULL,
  PRIMARY KEY (`SiteMeasurementOrFactId`),
  UNIQUE KEY `CollectionObjectID` (`CollectionObjectID`,`Parameter`)
) ENGINE=MyISAM AUTO_INCREMENT=4962395 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `abcd_unit`
--

DROP TABLE IF EXISTS `abcd_unit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `abcd_unit` (
  `CollectionObjectID` int(11) unsigned NOT NULL,
  `MetaDataID` int(11) unsigned NOT NULL,
  `UnitID` varchar(10) NOT NULL DEFAULT '',
  `UnitGUID` varchar(64) NOT NULL,
  `SourceID` varchar(10) NOT NULL DEFAULT 'MELISR',
  `SourceInstitutionID` varchar(6) NOT NULL DEFAULT 'MEL',
  `KindOfUnit` varchar(32) DEFAULT NULL,
  `RecordBasis` varchar(32) DEFAULT 'PreservedSpecimen',
  `CollectorsFieldNumber` varchar(15) DEFAULT NULL,
  `DateLastEdited` datetime DEFAULT NULL,
  `AltitudeIsQuantitative` tinyint(1) unsigned DEFAULT NULL,
  `AltitudeLowerValue` int(11) unsigned DEFAULT NULL,
  `AltitudeUpperValue` int(11) unsigned DEFAULT NULL,
  `AltitudeUnitOfMeasurement` varchar(10) DEFAULT NULL,
  `DepthIsQuantitative` tinyint(1) unsigned DEFAULT NULL,
  `DepthLowerValue` int(11) unsigned DEFAULT NULL,
  `DepthUpperValue` int(11) unsigned DEFAULT NULL,
  `DepthUnitOfMeasurement` varchar(10) DEFAULT NULL,
  `BiotopeText` text,
  `AssociatedTaxa` text,
  `CountryName` varchar(64) DEFAULT NULL,
  `CountryISO3166Code` varchar(2) DEFAULT NULL,
  `GatheringISODateTimeBegin` varchar(10) DEFAULT NULL,
  `GatheringISODateTimeEnd` varchar(10) DEFAULT NULL,
  `GatheringDateText` varchar(48) DEFAULT NULL,
  `LocalityText` text,
  `NearNamedPlace` varchar(128) DEFAULT NULL,
  `NearNamedPlaceRelationTo` varchar(128) DEFAULT NULL,
  `NearNamedPlaceRelationDerivedFlag` tinyint(1) DEFAULT NULL,
  `GatheringNotes` text,
  `UnitNotes` text,
  `CoordinateMethod` varchar(32) DEFAULT NULL,
  `GeoreferencedBy` varchar(255) DEFAULT NULL,
  `GeoreferencedDate` datetime DEFAULT NULL,
  `GeoreferenceSources` text,
  `GeoreferenceVerificationStatus` varchar(64) DEFAULT NULL,
  `GeoreferenceRemarks` text,
  `CoordinateErrorDistanceInMeters` int(11) unsigned DEFAULT NULL,
  `CoordinatePrecision` double DEFAULT NULL,
  `LatitudeDecimal` decimal(13,10) DEFAULT NULL,
  `LongitudeDecimal` decimal(13,10) DEFAULT NULL,
  `VerbatimLatitude` varchar(50) DEFAULT NULL,
  `VerbatimLongitude` varchar(50) DEFAULT NULL,
  `CoordinatesText` varchar(100) DEFAULT NULL,
  `SpatialDatum` varchar(10) DEFAULT NULL,
  `VerbatimCoordinateSystem` varchar(32) DEFAULT NULL,
  `VerbatimSRS` varchar(32) DEFAULT NULL,
  `Disposition` varchar(64) DEFAULT NULL,
  `DuplicatesDistributedTo` varchar(153) DEFAULT NULL,
  `LoanIdentifier` varchar(24) DEFAULT NULL,
  `LoanDestination` varchar(16) DEFAULT NULL,
  `LoanForBotanist` varchar(128) DEFAULT NULL,
  `LoanDate` date DEFAULT NULL,
  `LoanReturnDate` date DEFAULT NULL,
  `TypeStatus` varchar(20) DEFAULT NULL,
  `DoubtfulFlag` varchar(20) DEFAULT NULL,
  `TypifiedName` varchar(100) DEFAULT NULL,
  `TypeStatusVerifier` varchar(64) DEFAULT NULL,
  `TypeStatusVerificationDate` varchar(32) DEFAULT NULL,
  `NomenclaturalTypeDesignationNotes` varchar(256) DEFAULT NULL,
  `PreviousUnitSourceID` varchar(45) DEFAULT NULL,
  `PreviousUnitSourceInstitutionID` varchar(45) DEFAULT NULL,
  `PreviousUnitUnitID` varchar(45) DEFAULT NULL,
  `AcquiredFrom` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`CollectionObjectID`),
  KEY `DateLastEdited` (`DateLastEdited`),
  KEY `UnitID` (`UnitID`),
  KEY `LoanIdentifier` (`LoanIdentifier`),
  KEY `GeoreferencedByIDX` (`GeoreferencedBy`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `abcd_unitmeasurementorfact`
--

DROP TABLE IF EXISTS `abcd_unitmeasurementorfact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `abcd_unitmeasurementorfact` (
  `UnitMeasurementOrFactId` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `CollectionObjectID` int(11) unsigned NOT NULL,
  `Parameter` varchar(32) NOT NULL,
  `LowerValue` varchar(256) NOT NULL,
  `UpperValue` varchar(32) DEFAULT NULL,
  `UnitOfMeasurement` varchar(32) DEFAULT NULL,
  `IsQuantitative` tinyint(1) unsigned DEFAULT NULL,
  PRIMARY KEY (`UnitMeasurementOrFactId`),
  UNIQUE KEY `CollectionObjectID` (`CollectionObjectID`,`Parameter`)
) ENGINE=MyISAM AUTO_INCREMENT=2840935 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aux_geography`
--

DROP TABLE IF EXISTS `aux_geography`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aux_geography` (
  `GeographyID` int(11) NOT NULL DEFAULT '0',
  `Continent` varchar(64) DEFAULT NULL,
  `ContinentCode` varchar(4) DEFAULT NULL,
  `Country` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
  `CountryISO` varchar(64) DEFAULT NULL,
  `CountryCode` varchar(4) DEFAULT NULL,
  `State` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
  `County` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`GeographyID`),
  KEY `Country` (`Country`),
  KEY `State` (`State`),
  KEY `County` (`County`),
  KEY `Continent` (`Continent`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aux_highertaxon`
--

DROP TABLE IF EXISTS `aux_highertaxon`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aux_highertaxon` (
  `HigherTaxonID` int(11) NOT NULL AUTO_INCREMENT,
  `TaxonID` int(11) NOT NULL,
  `HigherTaxonName` varchar(80) NOT NULL,
  `HigherTaxonRank` varchar(20) NOT NULL,
  PRIMARY KEY (`HigherTaxonID`),
  UNIQUE KEY `TaxonID` (`TaxonID`,`HigherTaxonRank`)
) ENGINE=MyISAM AUTO_INCREMENT=1454024 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aux_iso_countries`
--

DROP TABLE IF EXISTS `aux_iso_countries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aux_iso_countries` (
  `IsoCountriesID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Continent` varchar(64) DEFAULT NULL,
  `ContinentCode` varchar(4) DEFAULT NULL,
  `Country` varchar(64) DEFAULT NULL,
  `CountryCode` varchar(4) DEFAULT NULL,
  PRIMARY KEY (`IsoCountriesID`),
  KEY `Country` (`Country`),
  KEY `CountryCode` (`CountryCode`)
) ENGINE=InnoDB AUTO_INCREMENT=251 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aux_taxonname`
--

DROP TABLE IF EXISTS `aux_taxonname`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aux_taxonname` (
  `TaxonID` int(11) unsigned NOT NULL,
  `FullScientificNameString` varchar(256) NOT NULL,
  `GenusOrMonomial` varchar(30) DEFAULT NULL,
  `FirstEpithet` varchar(128) DEFAULT NULL,
  `Rank` varchar(12) DEFAULT NULL,
  `InfraspecificEpithet` varchar(128) DEFAULT NULL,
  `AuthorTeam` varchar(128) DEFAULT NULL,
  `HybridFlag` varchar(1) DEFAULT NULL,
  `HybridFlagInsertionPoint` tinyint(1) unsigned DEFAULT NULL,
  PRIMARY KEY (`TaxonID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `multidets`
--

DROP TABLE IF EXISTS `multidets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `multidets` (
  `CollectionObjectID` int(11) unsigned NOT NULL,
  `UnitID` varchar(10) CHARACTER SET utf8 NOT NULL DEFAULT '',
  KEY `CollectionObjectID` (`CollectionObjectID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `records_in_avh`
--

DROP TABLE IF EXISTS `records_in_avh`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `records_in_avh` (
  `RecordID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `UnitID` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`RecordID`),
  KEY `UnitID` (`UnitID`)
) ENGINE=MyISAM AUTO_INCREMENT=826939 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `temp_lackingcollectors`
--

DROP TABLE IF EXISTS `temp_lackingcollectors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `temp_lackingcollectors` (
  `CollectionObjectID` int(11) unsigned NOT NULL,
  `UnitID` varchar(10) CHARACTER SET utf8 NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `temp_missingidentifications`
--

DROP TABLE IF EXISTS `temp_missingidentifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `temp_missingidentifications` (
  `CollectionObjectID` int(11) unsigned NOT NULL,
  `UnitID` varchar(10) CHARACTER SET utf8 NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-05-26 18:20:04
