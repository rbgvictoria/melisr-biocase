--
-- Table structure for table abcd_collector
--

DROP TABLE IF EXISTS biocase.abcd_collector;
CREATE TABLE biocase.abcd_collector (
  collector_id SERIAL NOT NULL, 
  collection_object_id INTEGER NOT NULL, 
  primary_collector SMALLINT NOT NULL, 
  sequence int2 NOT NULL, 
  agent_text VARCHAR(255) NOT NULL, 
  PRIMARY KEY (collector_id),
  UNIQUE (collection_object_id, sequence)
);

--
-- Table structure for table abcd_higher_taxon
--

DROP TABLE IF EXISTS biocase.abcd_higher_taxon;
CREATE TABLE biocase.abcd_higher_taxon (
  higher_taxon_id SERIAL NOT NULL,
  determination_id INTEGER NOT NULL,
  collection_object_id INTEGER NOT NULL,
  higher_taxon_name VARCHAR(80) NOT NULL,
  higher_taxon_rank VARCHAR(20) NOT NULL,
  PRIMARY KEY (higher_taxon_id),
  UNIQUE (determination_id,higher_taxon_rank)
);

DROP INDEX IF EXISTS abcd_higher_taxon_collection_object_id;
CREATE INDEX abcd_higher_taxon_collection_object_id ON biocase.abcd_higher_taxon(collection_object_id);

--
-- Table structure for table abcd_identification
--

DROP TABLE IF EXISTS biocase.abcd_identification;
CREATE TABLE biocase.abcd_identification (
  determination_id INTEGER NOT NULL,
  collection_object_id INTEGER NOT NULL,
  preferred_flag SMALLINT NOT NULL,
  identification_iso_date_time_begin VARCHAR(10),
  identifier_role VARCHAR(30),
  identifiers_text VARCHAR(255),
  identification_notes TEXT,
  full_scientific_name_string VARCHAR(256) NOT NULL,
  identification_qualifier varchar(20),
  identification_qualifier_insertion_point SMALLINT,
  name_addendum VARCHAR(50),
  author_team VARCHAR(128),
  genus_or_monomial VARCHAR(64),
  first_epithet VARCHAR(128),
  rank VARCHAR(12),
  infraspecific_epithet VARCHAR(128),
  hybrid_flag VARCHAR(1),
  hybrid_flag_insertion_point SMALLINT,
  PRIMARY KEY (determination_id)
);

DROP INDEX IF EXISTS abcd_identification_collection_object_id;
CREATE INDEX abcd_identification_collection_object_id ON biocase.abcd_identification (collection_object_id);


--
-- Table structure for table abcd_meta_data
--

DROP TABLE IF EXISTS biocase.abcd_meta_data;
  CREATE TABLE biocase.abcd_meta_data (
  meta_data_id SERIAL NOT NULL,
  data_set_title VARCHAR(100),
  technical_contact_name VARCHAR(100),
  date_modified DATE,
  owner VARCHAR(100),
  source_id VARCHAR(10),
  PRIMARY KEY (meta_data_id)
);

DROP INDEX IF EXISTS abcd_meta_data_meta_data_id;
CREATE INDEX abd_meta_data_meta_data_id ON biocase.abcd_meta_data (source_id);


--
-- Table structure for table abcd_named_area
--

DROP TABLE IF EXISTS biocase.abcd_named_area;
CREATE TABLE biocase.abcd_named_area (
  named_area_id SERIAL NOT NULL,
  collection_object_id INTEGER NOT NULL,
  area_class VARCHAR(30) NOT NULL,
  area_name VARCHAR(150) NOT NULL,
  PRIMARY KEY (named_area_id),
  UNIQUE (collection_object_id,area_class)
);


--
-- Table structure for table abcd_previous_unit
--

DROP TABLE IF EXISTS biocase.abcd_previous_unit;
CREATE TABLE biocase.abcd_previous_unit (
  previous_unit_id SERIAL NOT NULL,
  collection_object_id INTEGER NOT NULL,
  source_id VARCHAR(50) NOT NULL,
  source_institution_id VARCHAR(150) NOT NULL,
  unit_id VARCHAR(150) NOT NULL,
  PRIMARY KEY (previous_unit_id)
);

DROP INDEX IF EXISTS abcd_previous_unit_collection_object_id;
CREATE INDEX abcd_previous_unit_collection_object_id ON biocase.abcd_previous_unit (collection_object_id);


--
-- Table structure for table abcd_unit
--

DROP TABLE IF EXISTS biocase.abcd_unit;
CREATE TABLE biocase.abcd_unit (
  collection_object_id INTEGER NOT NULL,
  meta_data_id INTEGER NOT NULL,

  -- unit
  unit_id VARCHAR(10) NOT NULL,
  unit_guid VARCHAR(64) NOT NULL,
  source_id VARCHAR(10) NOT NULL DEFAULT 'MELISR',
  source_institution_id VARCHAR(6) NOT NULL DEFAULT 'MEL',
  kind_of_unit VARCHAR(32) DEFAULT NULL,
  record_basis VARCHAR(32) DEFAULT 'PreservedSpecimen',
  collectors_field_number VARCHAR(64) DEFAULT NULL,
  date_last_edited TIMESTAMP DEFAULT NULL,

  -- Gathering
  altitude_is_quantitative SMALLINT DEFAULT NULL,
  altitude_lower_value INTEGER DEFAULT NULL,
  altitude_upper_value INTEGER DEFAULT NULL,
  altitude_unit_of_measurement VARCHAR(10) DEFAULT NULL,
  depth_is_quantitative SMALLINT DEFAULT NULL,
  depth_lower_value INTEGER DEFAULT NULL,
  depth_upper_value INTEGER DEFAULT NULL,
  depth_unit_of_measurement VARCHAR(10) DEFAULT NULL,
  biotope_text TEXT DEFAULT NULL,
  country_name VARCHAR(64) DEFAULT NULL,
  country_iso3166_code VARCHAR(2) DEFAULT NULL,
  gathering_iso_date_time_begin VARCHAR(10) DEFAULT NULL,
  gathering_iso_date_time_end VARCHAR(10) DEFAULT NULL,
  gathering_date_text VARCHAR(48) DEFAULT NULL,
  locality_text TEXT DEFAULT NULL,
  near_named_place VARCHAR(128) DEFAULT NULL,
  near_named_place_relation_to VARCHAR(128) DEFAULT NULL,
  near_named_place_relation_derived_flag SMALLINT DEFAULT NULL,
  gathering_notes TEXT DEFAULT NULL,
  coordinate_method VARCHAR(32) DEFAULT NULL,
  coordinate_error_distance_in_meters INTEGER DEFAULT NULL,
  latitude_decimal FLOAT DEFAULT NULL,
  longitude_decimal FLOAT DEFAULT NULL,
  coordinates_text VARCHAR(100) DEFAULT NULL,
  spatial_datum VARCHAR(10) DEFAULT NULL,

  -- herbarium unit
  duplicates_distributed_to VARCHAR(153) DEFAULT NULL,
  loan_identifier VARCHAR(24) DEFAULT NULL,
  loan_destination VARCHAR(16) DEFAULT NULL,
  loan_for_botanist VARCHAR(128) DEFAULT NULL,
  loan_date DATE DEFAULT NULL,
  loan_return_date DATE DEFAULT NULL,

  -- nomenclatural type designation
  type_status VARCHAR(20) DEFAULT NULL,
  doubtful_flag VARCHAR(20) DEFAULT NULL,
  typified_name VARCHAR(100) DEFAULT NULL,
  type_status_verifier VARCHAR(64) DEFAULT NULL,
  type_status_verification_date VARCHAR(32) DEFAULT NULL,
  nomenclatural_type_designation_notes TEXT DEFAULT NULL,

  PRIMARY KEY (collection_object_id),
  UNIQUE (unit_id)
);

DROP INDEX IF EXISTS abcd_unit_date_last_edited;
CREATE INDEX abcd_unit_date_last_edited ON biocase.abcd_unit (date_last_edited);

DROP INDEX IF EXISTS abcd_unit_loan_identifier;
CREATE INDEX abcd_unit_loan_identifier ON biocase.abcd_unit (loan_identifier);


--
-- Table structure for table abcd_unit_measurement_or_fact
--

DROP TABLE IF EXISTS biocase.abcd_unit_measurement_or_fact;
CREATE TABLE biocase.abcd_unit_measurement_or_fact (
  unit_measurement_or_fact_id SERIAL NOT NULL,
  collection_object_id INTEGER NOT NULL,
  parameter VARCHAR(32) NOT NULL,
  lower_value VARCHAR(256) NOT NULL,
  upper_value VARCHAR(32) DEFAULT NULL,
  unit_of_measurement VARCHAR(32) DEFAULT NULL,
  is_quantitative SMALLINT DEFAULT NULL,
  PRIMARY KEY (unit_measurement_or_fact_id),
  UNIQUE (collection_object_id,parameter)
);

DROP TABLE IF EXISTS biocase.import_errors;
CREATE TABLE biocase.import_errors (
  import_errors_id SERIAL NOT NULL,
  collection_object_id INTEGER,
  table_name VARCHAR(64),
  sqlstate_error_code VARCHAR(32),
  error_message VARCHAR(255),
  timestamp_created TIMESTAMP,
  PRIMARY KEY (import_errors_id)
);


