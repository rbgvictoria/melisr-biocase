<?php

class BioCASeLoad {
    var $db;
    var $config;
    var $collectionobjectid;
    
    public function __construct($db, $collectionobjectid) {
        $this->db = $db;
        $this->CollectionObjectID = $collectionobjectid;
        $this->config = array();
        $this->config();
    }
    
    private function config() {
        $this->config['unit'] = (object) array(
            'tablename' => 'unit',
            'type' => 'replace',
            'skipprimary' => FALSE,
            'prefix' => 'abcd'
        );
        $this->config['identification'] = (object) array(
            'tablename' => 'identification',
            'type' => 'delete and insert',
            'skipprimary' => FALSE,
            'prefix' => 'abcd'

        );
        $this->config['abcd_highertaxon'] = (object) array(
            'tablename' => 'highertaxon',
            'type' => 'delete and insert',
            'skipprimary' => TRUE,
            'prefix' => 'abcd'

        );
        $this->config['dwc_taxon'] = (object) array(
            'tablename' => 'taxon',
            'type' => 'delete and insert',
            'skipprimary' => FALSE,
            'prefix' => 'dwc'

        );
        $this->config['collector'] = (object) array(
            'tablename' => 'collector',
            'type' => 'delete and insert',
            'skipprimary' => TRUE,
            'prefix' => 'abcd'

        );
        $this->config['previousunit'] = (object) array(
            'tablename' => 'previousunit',
            'type' => 'delete and insert',
            'skipprimary' => TRUE,
            'prefix' => 'abcd'

        );
        $this->config['unitmeasurementorfact'] = (object) array(
            'tablename' => 'unitmeasurementorfact',
            'type' => 'delete and insert',
            'skipprimary' => TRUE,
            'prefix' => 'abcd'
        );
        $this->config['namedarea'] = (object) array(
            'tablename' => 'namedarea',
            'type' => 'delete and insert',
            'skipprimary' => TRUE,
            'prefix' => 'abcd'
        );
        $this->config['taxonname'] = (object) array(
            'tablename' => 'taxonname',
            'type' => 'replace',
            'skipprimary' => FALSE,
            'prefix' => 'aux'
        );
        $this->config['highertaxon'] = (object) array(
            'tablename' => 'highertaxon',
            'type' => 'delete and insert',
            'skipprimary' => TRUE,
            'prefix' => 'aux'
        );
        $this->config['abcd_sequence'] = (object) array(
            'tablename' => 'sequence',
            'type' => 'delete and insert',
            'skipprimary' => FALSE,
            'prefix' => 'abcd'
        );
        
        
    }
    
    public function load($dbtable, $data, $reindex=FALSE) {
        $config = $this->config[$dbtable];
        $errors = array();
        $failed = array();

        if (is_array($data)) {
            if ($config->type == 'delete and insert' && !$reindex) {
                if ($dbtable == 'highertaxon') 
                    $delete = "DELETE FROM biocase.{$config->prefix}_{$config->tablename}
                        WHERE TaxonID=$this->CollectionObjectID";
                else
                    $delete = "DELETE FROM biocase.{$config->prefix}_{$config->tablename}
                        WHERE CollectionObjectID=$this->CollectionObjectID";
                $stmt = $this->db->prepare($delete);
                $stmt->execute();
                if ($stmt->errorCode() != '00000') {
                    $error = array(
                        date('Y-m-d h:i:s'),
                        'DELETE',
                        $config->prefix . '_' . $config->tablename,
                        $this->CollectionObjectID,
                    );
                    $errors[] = array_merge($error, $stmt->errorInfo());
                }
            }
            foreach ($data as $item)
                $this->load ($dbtable, $item);
        }
        elseif (is_object($data)) {
            $fields = array_keys((array) $data);
            if ($config->skipprimary)
                array_shift ($fields);
            
            $values = array();
            foreach ($fields as $index => $field) {
                $fields[$index] = '`' . $field . '`';
                $values[] = '?';
            }
            $fields = implode(',', $fields);
            $values = implode(',', $values);
            
            if ($config->type == 'replace' && !$reindex) {
                $sql = "REPLACE INTO biocase.{$config->prefix}_{$config->tablename} ($fields)
                    VALUES($values)";
            }
            else {
                $sql = "REPLACE INTO biocase.{$config->prefix}_{$config->tablename} ($fields)
                    VALUES($values)";
            }
            
            $stmt = $this->db->prepare($sql);
            
            $values = array_values((array) $data);
            if ($config->skipprimary)
                array_shift ($values);
            
            $stmt->execute($values);
            if ($stmt->errorCode() != '00000') {
                $error = array(
                    date('Y-m-d h:i:s'),
                    'INSERT',
                    $config->prefix . '_' . $config->tablename,
                    $this->CollectionObjectID,
                );
                $errors[] = array_merge($error, $stmt->errorInfo());
                print_r($values);
                $failed[] = $values;
            }
        }
        
        if ($errors) {
            $handle = fopen('errors/dberrors.csv', 'a');
            foreach ($errors as $error)
                fputcsv($handle, $error);
            fclose($handle);
        }
        if ($failed) {
            $handle = fopen('errors/failedidentifications.csv', 'a');
            foreach ($failed as $failure) {
                print_r($failure);
                fputcsv($handle, $failure);
            }
            fclose($handle);
        }
    }
}
?>
