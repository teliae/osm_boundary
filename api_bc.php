<?php
/*
 * Detect and return all cross boundaries
 * - query OSRM to fetch a route
 * - query an postgis database storing all boundaires
 * - return a json data as OSRM does plus arrays of countries
 *
 *
 *
 *
 */
require_once 'OsmRouting.php';
require_once 'BoundaryCrossing.php';
include 'api_bc_config.php';

/*
 * call OSRM API
 * May be we can filter the QUERY_STRING to improve security
 */
$osrm = OsmRouting::QueryOSRM($_SERVER['QUERY_STRING']);

/*
 * Instantiate and configure
 */
$dbconf = sprintf("host=$host port=$port dbname=$dbname user=$user password=$password",
                  $config_host, $config_port, $config_dbname, $config_user, $config_password);

$bounCross = new BoundaryCrossing();
$bounCross->levels = $config_levels;
$bounCross->pg_connection_string = $dbconf;
$bounCross->adminlevel_defs = $config_level;

$osrm['boundaries'] = $bounCross->TerritoriesCrossed($osrm['route_geometry']);
// Facultatif, juste pour info
$osrm['sql_queries'] = $bounCross->queries;
print json_encode($osrm);
?>
