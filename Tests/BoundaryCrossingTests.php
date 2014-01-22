<?php
/*
 * Unit tests for BoundaryCrossing
 *
 *
 */
require_once "PHPUnit/Framework/TestCase.php";
require_once( dirname(__FILE__).'/../BoundaryCrossing.php' );

class BoundaryCrossingTest extends PHPUnit_Framework_TestCase {

	public function setUp() {
      $this->bc = new BoundaryCrossing();

	}

	public function test_nbsegments32() {
      $nbpoints = 3;
      $points_per_segments = 2;
      $this->assertEquals(2, $this->bc->nbsegments($nbpoints, $points_per_segments));
    }

	public function test_nbsegments42() {
      $nbpoints = 4;
      $points_per_segments = 2;
      $this->assertEquals(3, $this->bc->nbsegments($nbpoints, $points_per_segments));
    }

	public function test_nbsegments33() {
      $nbpoints = 3;
      $points_per_segments = 3;
      $this->assertEquals(1, $this->bc->nbsegments($nbpoints, $points_per_segments));
    }

	public function test_nbsegments53() {
      $nbpoints = 5;
      $points_per_segments = 3;
      $this->assertEquals(2, $this->bc->nbsegments($nbpoints, $points_per_segments));
    }

	public function test_nbsegments63() {
      $nbpoints = 6;
      $points_per_segments = 3;
      $this->assertEquals(3, $this->bc->nbsegments($nbpoints, $points_per_segments));
    }

	public function test_nbsegments57() {
      $nbpoints = 5;
      $points_per_segments = 7;
      $this->assertEquals(1, $this->bc->nbsegments($nbpoints, $points_per_segments));
    }


	/**
	 * @brief test that an XML object has been returned by the OSRM engine
	*/
	public function test_SqlLine() {
      $points = [5, 40, 3.2, 51.4];

      $results = $this->bc->SqlLine($points);

      $attend = ["ST_MakeLine(Array[ST_MakePoint(40.000000, 5.000000), ST_MakePoint(51.400000, 3.200000)])"];
      $this->assertEquals($attend, $results);
	}

	public function test_SqlLineOdd() {
      $points = [5, 40, 3.2, 51.4, 4, 60];
      $results = $this->bc->SqlLine($points);

      $attend = ["ST_MakeLine(Array[ST_MakePoint(40.000000, 5.000000), ST_MakePoint(51.400000, 3.200000)])",
                 "ST_MakeLine(Array[ST_MakePoint(51.400000, 3.200000), ST_MakePoint(60.000000, 4.000000)])"];
      $this->assertEquals($attend, $results);
	}

	public function test_SqlLineThreePoints() {
      $points = [5, 40, 3.2, 51.4, 4, 60];
      $results = $this->bc->SqlLine($points, 3);

      $attend = ["ST_MakeLine(Array[ST_MakePoint(40.000000, 5.000000), ST_MakePoint(51.400000, 3.200000), ST_MakePoint(60.000000, 4.000000)])"];
      $this->assertEquals($attend, $results);
	}

	/**
	 * @brief build array of points
	*/
	public function test_SqlMakePoints() {

      $points = [5, 40, 3.2, 51.4, 4, 60, 4, 50];

      $results = $this->bc->SqlMakePoints($points);

      $attend = ["ST_MakePoint(40.000000, 5.000000)",
                 "ST_MakePoint(51.400000, 3.200000)",
                 "ST_MakePoint(60.000000, 4.000000)",
                 "ST_MakePoint(50.000000, 4.000000)"];

      $this->assertEquals( sizeof($attend), sizeof($results));
	}

	/**
	 * @brief build array of points
	*/
	public function test_SqlMakePointsOdd() {

      $points = [5, 40, 3.2, 51.4, 4, 60];

      $results = $this->bc->SqlMakePoints($points);

      $attend = ["ST_MakePoint(40.000000, 5.000000)",
                 "ST_MakePoint(51.400000, 3.200000)",
                 "ST_MakePoint(60.000000, 4.000000)"];

      $this->assertEquals( sizeof($attend), sizeof($results));
	}
}

?>