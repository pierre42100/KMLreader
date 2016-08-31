<?php

/* 
 * The MIT License
 *
 * Copyright 2016 Pierre.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * Initialisizing file
 */
require_once('core/KMLreader.php');

/**
 * Getting sample file
 */
$source = file_get_contents("trajet_st-andre_stpierre.kml");

/**
 * Building object
 */
$reader = new KMLreader($source);

/**
 * Parsing file
 */
$reader->parseKML();

/**
 * Getting datas
 */
$startPoint = $reader->getStartPoint(true);
$endPoint = $reader->getEndPoint(true);
$travelDatas = $reader->getTravelCoordinates(true);
?><!DOCTYPE html>
<html>
    <head>
        <title>KMLReader test</title>
        <script src="3rdparty/leaflet/leaflet.js"></script>
        <link rel="stylesheet" href="3rdparty/leaflet/leaflet.css" />
        
        <style type="text/css">
            #mapid { height: 180px; }
        </style>
    </head>
    <body>
        <div id="mapid" style="height: 250px;"></div>
        <link rel="stylesheet" src="3rdparty/leaflet/leaflet.js" />
        <script type="text/javascript">
            //Creating map
            var map = L.map('mapid').setView([<?php echo $startPoint; ?>], 14);
            
            //Initialisating map
            L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);
            
            //Adding markers for the first and the last point
            var startPoint = L.marker([<?php echo $startPoint; ?>]).addTo(map);
            startPoint.bindPopup("startPoint").openPopup();
            
            var endPoint = L.marker([<?php echo $endPoint; ?>]).addTo(map);
            endPoint.bindPopup("endPoint");
            
            //Adding lines for road datas
            var dataLine = {
                "type": "LineString",
                "coordinates": [<?php
            foreach($travelDatas as $n => $show) {
                if($n != 0) echo ", ";
                echo "[" . $show . "]";
            }
            ?>]
            };
            
            //Style of line
            var myStyle = {
                "color": "#ff7800",
                "weight": 5,
                "opacity": 0.65
            };
            
            L.geoJson(dataLine, {
                style: myStyle
            }).addTo(map);
        </script>
    </body>
</html>