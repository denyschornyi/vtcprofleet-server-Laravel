<?php
include_once( 'geoPHP.inc' );
//$point = geoPHP::load("POINT(33.52350035752557 -111.92668491312168)","wkt");  //1
//$point = geoPHP::load("POINT(33.50905690936032 -111.93339056937259)","wkt");
//$point = geoPHP::load("POINT(33.523655040738305 -111.92583746878665)","wkt");
$point = geoPHP::load("POINT(33.52350035752557 -111.92668491312168)");
//$point = geoPHP::load("POINT(33.493454223025736 -111.92703909842533)","wkt");//0

$polygon  =
	geoPHP::load( "POLYGON((33.5362475 -111.9267386,33.5104882 -111.9627875,33.5104886 -111.9627875,33.5004686 -111.902761))");

$point_is_polygoon = $polygon->pointInPolygon($point);

var_dump($point_is_polygoon);
