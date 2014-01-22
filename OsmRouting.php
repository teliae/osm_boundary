<?php
/**
 * OsmRouting
 *
 * A simple class to query routing engine based on OpenStreetMap datas
 *

 *
 * @package   OsmRouting
 * @version   @VERSION@
 * @copyright @DATE@ Rodolphe Quiédeville
 * @license   GNU General Public License <http://www.gnu.org/licenses/gpl.html>
 * @link      
 * @author    Rodolphe Quiédeville <rodolphe@quiedeville.org>
 */

class OsmRouting {
    /**
     * Query OSRM API
     *
     * @param array $qrystr
     * @return json
     */
    public static function QueryOSRM($qrystr) {

      $base_url = "http://router.project-osrm.org/viaroute?%s";
      
      $url = sprintf($base_url, $qrystr);
      
      $ch = curl_init();
      // set URL and other appropriate options
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_VERBOSE, FALSE);
      curl_setopt($ch, CURLOPT_HEADER, FALSE);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
      // OSRM need a valid user-agent
      curl_setopt($ch, CURLOPT_USERAGENT, "php/OsrmRouting (X11; U; Linux ppc; en-US; rv:1.7.6) Gecko/20050328 Firefox/1.0.2");      

      // grab URL and pass it to the browser
      $content = curl_exec($ch);
      curl_close($ch);
      
      return json_decode($content, TRUE);
    }
}