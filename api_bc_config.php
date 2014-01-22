<?php
$host = "localhost";
$port = 5432;
$user = "web";
$dbname = "boundary";
$password = "Ojiw4uc8";

/*
 * Define wich admin level to include in answer
 */
$config_levels = ['0','6']; 

/* Not necessary needed to change below
 *
 * The 2 levels defined are based on following shapefiles imported in
 * postgis database as is
 *
 * Level 0 : Natural Earth admin countries
 * http://www.naturalearthdata.com/http//www.naturalearthdata.com/download/10m/cultural/ne_10m_admin_0_countries.zip
 *
 * Level 6 : Geofla
 * http://www.data.gouv.fr/DataSet/30383060
 *
 *
 * label_name : the postgresql field name returned
 * geom_name  : Name of field contains the geometric object
 * table_name : the postgresql table name to query
 */
$config_level = array();

$config_level['0'] = ['label_name' => 'name', 
                      'geom_name' => 'the_geom', 
                      'table_name' => 'ne_10m_admin_0_countries'];

$config_level['6'] = ['label_name' => 'nom_dept',
                      'geom_name' => 'the_geom', 
                      'table_name' => 'departement'];


?>