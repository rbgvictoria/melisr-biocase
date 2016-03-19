<?php

require_once('includes/pdoconnect.php');

$fields = array_keys((array) new Unit());
array_pop($fields);


$update = "UPDATE biocase.abcd_unit ";
foreach ($fields as $index=>$field) {
    $update .= ($index > 0) ? ',' : 'SET ';
    $update .= $field . '=?';
}
$update .= ' WHERE CollectionObjectID=?';
$updateStmt = $db->prepare($update);

$offset = 0;

$count = "SELECT COUNT(*)
    FROM locality l
    JOIN collectingevent ce ON l.LocalityID=ce.LocalityID
    JOIN collectionobject co ON ce.CollectingEventID=co.CollectingEventID
    WHERE l.Long1Text IS NOT NULL AND Lat1Text IS NOT NULL
      AND !(l.Latitude1=0 AND l.Longitude2=0)";
$countStmt = $db->prepare($count);
$countStmt->execute();
$numrec = $countStmt->fetch(PDO::FETCH_NUM);
$numrec = $numrec[0];
echo $numrec . "\n";

while ($offset < $numrec) {
    $select = "SELECT co.CollectionObjectID, l.Lat1Text, l.Long1Text, l.OriginalLatLongUnit, l.Datum
        FROM locality l
        JOIN collectingevent ce ON l.LocalityID=ce.LocalityID
        JOIN collectionobject co ON ce.CollectingEventID=co.CollectingEventID
        WHERE l.Long1Text IS NOT NULL AND Lat1Text IS NOT NULL
          AND !(l.Latitude1=0 AND l.Longitude2=0)
        LIMIT $offset, 1000";
    //echo $select . "\n\n";
    $stmt = $db->prepare($select);
    $stmt->execute();

    /*$handle = fopen('csv/precision.csv', 'w');

    fputcsv($handle, array('CollectionObjectID','verbatimLatitude', 'verbatimLongitude', 'verbatimCoordinateSystem',
            'verbatimSRS', 'coordinatePrecision'));
    */
    while ($row = $stmt->fetch(5)) {
        $unit = new Unit();
        $unit->CollectionObjectID = $row->CollectionObjectID; 
        $unit->VerbatimLatitude = $row->Lat1Text;
        $unit->VerbatimLongitude = $row->Long1Text;
        switch ($row->OriginalLatLongUnit) {
            case 0:
                $unit->VerbatimCoordinateSystem = 'decimal degrees';
                break;

            case 1:
                $unit->VerbatimCoordinateSystem = 'degrees minutes seconds';
                break;

            case 2:
                $unit->VerbatimCoordinateSystem = 'degrees decimal minutes';
                break;

            default:
                break;
        }

        $latbits = explode(' ', substr($row->Lat1Text, 0, strlen($row->Lat1Text)-2));
        $longbits = explode(' ', substr($row->Long1Text, 0, strlen($row->Long1Text)-2));

        $clat = count($latbits);
        $clong = count($longbits);

        switch ($clat) {
            case 1: // degrees
                $unit->VerbatimCoordinateSystem = ($clat >= $clong) ? 'decimal degrees' : NULL;
                $latitudePrecision = decimalPrecision(substr($latbits[0], 0, strlen($latbits[0])-1));
                break;

            case 2: // minutes
                $unit->VerbatimCoordinateSystem = ($clat >= $clong) ? 'degrees decimal minutes' : NULL;
                $latitudePrecision = 0.01667 * decimalPrecision(substr($latbits[1], 0, strlen($latbits[1])-1));
                break;

            case 3: // seconds
                $unit->VerbatimCoordinateSystem = ($clat >= $clong) ? 'degrees minutes seconds' : NULL;
                $latitudePrecision = 0.000278 * decimalPrecision(substr($latbits[2], 0, strlen($latbits[2])-1));
                break;
        }

        switch ($clong) {
            case 1: // degrees
                if ($clong > $clat) $unit->VerbatimCoordinateSystem =  'decimal degrees';
                $longitudePrecision = decimalPrecision(substr($longbits[0], 0, strlen($longbits[0])-1));
                break;

            case 2: // minutes
                if ($clong > $clat) $unit->VerbatimCoordinateSystem =  'degrees decimal minutes';
                $longitudePrecision = 0.01667 * decimalPrecision(substr($longbits[1], 0, strlen($longbits[1])-1));
                break;

            case 3: // seconds
                if ($clong > $clat) $unit->VerbatimCoordinateSystem =  'degrees minutes seconds';
                $longitudePrecision = 0.000278 * decimalPrecision(substr($longbits[2], 0, strlen($longbits[2])-1));
                break;
        }

        $unit->CoordinatePrecision = ($latitudePrecision <= $longitudePrecision) ? $latitudePrecision : $longitudePrecision;

        $datums = array(
            'GDA94' => 'EPSG:4283',
            'WGS84' => 'EPSG:4326',
            'AGD66' => 'EPSG:4202',
            'AGD84' => 'EPSG:4203',
            'NAD84' => 'EPSG:????',
            'NAD83' => 'EPSG:4269',
            'NAD27' => 'EPSG:4267'
        );

        if ($row->Datum)
            $unit->VerbatimSRS = $datums[$row->Datum];

        $values = array_values((array) $unit);
        $updateStmt->execute($values);
        if ($updateStmt->errorCode() != '00000') {
            print_r($updateStmt->errorinfo());
        }

        //fputcsv($handle, array_values((array) $unit));
    }
    echo $offset . "\n";
    $offset += 1000;

}
//fclose($handle);

class Unit {
    var $VerbatimLatitude = NULL;
    var $VerbatimLongitude = NULL;
    var $VerbatimCoordinateSystem = NULL;
    var $VerbatimSRS = NULL;
    var $CoordinatePrecision = NULL;
    var $CollectionObjectID = NULL;
}

function decimalPrecision($number) {
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

?>
