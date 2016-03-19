SELECT u.unit_id, u.latitude_decimal, u.longitude_decimal, i.state AS "State", 
  i.region_name AS "IBRA region", i.subregion_name AS "IBRA subregion" 
FROM spatial.ibra61_sub i
JOIN biocase.abcd_unit u ON i.the_geom && u.the_geom AND ST_Contains(i.the_geom, u.the_geom)
WHERE i.subregion_code='SCP2';

SELECT * FROM geometry_columns;

SELECT * FROM spatial.ibra61_sub WHERE state='VIC';