ALTER TABLE biocase.abcd_unit ADD COLUMN the_geom GEOMETRY;

UPDATE biocase.abcd_unit
SET the_geom=ST_GeomFromText('POINT('||longitude_decimal||' '||latitude_decimal||')', 4283);

CREATE INDEX abcd_unit_the_geom ON biocase.abcd_unit USING GIST (the_geom);
CREATE INDEX ibra61_sub_the_geom ON spatial.ibra61_sub USING GIST (the_geom);
CREATE INDEX ibra61_reg_shape_the_geom ON spatial.ibra61_reg_shape USING GIST (the_geom);

INSERT INTO geometry_columns (f_table_catalog, f_table_schema, f_table_name, f_geometry_column, coord_dimension, srid, type)
VALUES ('', 'biocase', 'abcd_unit', 'the_geom', '2', '4283', 'POINT');