<?php
/**
 * Class BoundaryCrossing
 *
 * Require PolyLine :
 * - https://github.com/emcconville/google-map-polyline-encoding-tool
 */
require_once 'Polyline.php';

class BoundaryCrossing
{

  /**
   * @var array $levels
   */
  public $levels = ['0'];
  public $queries = 0;
  public $adminlevel_defs = array();
  /**
   * PostgreSQL connection string
   *
   * @var string $pg_conection_string
   */
  public $pg_connection_string = "host=localhost";
  
  /**
   *
   * @parms array $lines
   * return @array
   */
  public function TerritoriesCrossed($route_geometry) {

    // Decode the polyline to points
    $points = $this->Decode($route_geometry);
    
    // Build the SQL Query for LINESTRING
    $lines = $this->SqlLine($points, 10);

    return $this->FetchTerritories($lines);
  }
  /**
   *
   * @parms array $lines
   *
   */
  private function FetchTerritories($lines) {
    
    $boundaries_arr = array();
    
    foreach($this->levels as &$admin_level) {
      
      $territories = array();
      $boundaries = array();
      $parms = $this->adminlevel_defs[$admin_level];
      
      foreach($lines as &$line) {
        $data = $this->SqlQuery($parms, $line);

        if ($data['status'] == 0) {
          $terr = $data['data'];
          foreach ($terr as &$val) {
            $territories[$val[0]] = $val[1];
          }
        } else {
          $territories['last_error'] = $data['error'];
        }
      }

      $xlevel = sprintf('level_%d', $admin_level);
      $boundaries_arr[$xlevel] =  $territories;
    }

    return $boundaries_arr;    
  }
  /**
   *
   * @parms integer $nb_points
   * @parms integer $points_per_segments
   * @return integer
   *
   */
  public static function nbsegments($nb_points, $points_per_segments) 
  {
    if ($points_per_segments > $nb_points ) {
      $points_per_segments = $nb_points;
    }
    
    if ($nb_points ==  $points_per_segments) {
      $nb = 1;
    } else {
      $nb = ceil($nb_points / $points_per_segments);
      
      if (fmod($nb_points, $points_per_segments) == 0) {
        $nb = $nb + 1;
      }
    }
    
    return $nb;
  }

  /**
   * Build a point
   *
   * @parms array $points
   * @return string
   *
   * x: latitude
   * x+1 : longitude
   */

  public static function SqlMakePoints($points) {
    $iter = sizeof($points) / 2;
    $i = 0;
    $coords = array();
    while ($i < $iter) {
      // Be careful of the reverse of Lon/Lat to Lat/Lon
      $point = sprintf("ST_MakePoint(%f, %f)", $points[$i*2+1], $points[$i*2]);
      array_push($coords, $point);
      $i = $i + 1;
    }
    return $coords;
  }


  public function SqlLine($points,  $nbpoints_ps = 2) {
    // Build the SQL part of LineString
    $lines = array();
    $add_last = 0;
    $coords = $this->SqlMakePoints($points);

    $nb_points = sizeof($coords);

    $iter = $this->nbsegments($nb_points, $nbpoints_ps);

    if (($iter * $nbpoints_ps) > $nb_points) {
      $iter = $iter - 1;
      $add_last = 1;
    }

    $i = 0;

    while ($i < $iter) {
      $points = array();
      $p = 0;
      while ($p < $nbpoints_ps) {
        array_push($points, $coords[($i * ($nbpoints_ps - 1)) + $p]);
        $p = $p + 1;
      }
      $line = sprintf("ST_MakeLine(Array[%s])", implode(", ", $points));

      array_push($lines, $line);
      $i = $i + 1;
    }

    if ($add_last) {
      $points = array();
      $p = ($i  * $nbpoints_ps) - 1;
      while ($p < $nb_points) {
        array_push($points, $coords[$p]);
        $p = $p + 1;
      }

      // Add a final segment shorter than other
      $line = sprintf("ST_MakeLine(Array[%s])", implode(", ", $points));
      array_push($lines, $line);
    }

    return $lines;
  }

  private function pgconn() {

    $dbconn = pg_connect($this->pg_connection_string);

    if (!pg_connection_busy($dbconn)) {
      return $dbconn;
    } else {
      return False;
    }

  }

  private function pgquery($dbconn, $query) {
    $res = @pg_query($dbconn, $query);
    return $res;
  }

  /**
   * Execute an SQL query on database
   *
   * @parms array $parms
   * @parms string $line
   * @return array
   */  
  public function SqlQuery($parms, $line) {
    $datas = array();

    $dbconn= $this->pgconn();
    
    $query = sprintf('SELECT %s FROM %s WHERE ST_Intersects(%s, %s) = true;',
                     $parms['label_name'], $parms['table_name'], $parms['geom_name'], $line);
    
    if ($dbconn) {
      // Prepare a query for execution
      $res2 = $this->pgquery($dbconn, $query);
      $this->queries++;
      if ($res2) {
        $status = 0;
        
        while ($row = pg_fetch_row($res2)) {
          $key = $row[0] ;
          $val = $row[0] ;
          array_push($datas, [ $key, $val ]);
        }        
      } else {
        $status = 1;
        $errmsg = pg_last_error();
      }
    } else {
      print "Connect error";
    }
    return ['status' => $status,
            'data' => &$datas,
            'error' => $errmsg ];
  }  

  /**
   * Decode encoded Polyline with Google algorithm
   *
   * @parms string $encoded_line
   * @return array
   */
  public static function Decode($encoded_line) {
    
    $points = Polyline::Decode($encoded_line);
    
    return $points;
  }

}
?>