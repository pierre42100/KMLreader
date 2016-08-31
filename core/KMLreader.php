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
 * Parsing KML files library
 *
 * @author Pierre
 */
class KMLreader {
    
    /**
     * @var Str Le code source KML
     */
    private $source;
    
    /**
     * @var Str Start Point coordinates
     */
    private $startPoint;
    
    /**
     * @var Str End Point coordinates
     */
    private $endPoint;
    
    /**
     * @var Str Travel datas in string format
     */
    private $routeStr;
    
    /**
     * @var Array   Travel datas as array
     */
    private $arrayRoute;
    
    /**
     * Building class
     * 
     * @param   String  $source KML file source code
     */
    public function __construct($source) {
        //Enregistrement du code source
        $this->source = $source;
    }
     
    /**
     * Décodage du fichier KML. Suite à l'exécution des fonction, les données
     * seront librement exploitables
     */
    public function parseKML(){
        //Décodage du KML
        $XMLarray = array();
        $XMLarray = $this->xml2array($this->source);
        
        //Traitement des informations
        foreach($XMLarray as $traiter){
            //On supprime tout ce qui pourrait être information d'ent-tête et
            // qui est par conséquent inutile
            if(!is_array($traiter))
                continue;
            
            //Si le tableau n'est pas susceptible de contenir toute les
            //informations, on l'ignore
            if(count($traiter) < 3)
                continue;
            
            //On traite la partie qui devrait dès lors être la principale
            foreach($traiter as $values){
                if(!is_array($values))
                    continue;
               
                //On vérifie l'existence des tags requis
                if(!isset($values['tag']) OR !isset($values['attributes']))
                    continue;
                
                //L'ID est requis pour déterminer de quoi il s'agit
                if(!isset($values['attributes']['ID']))
                    continue;
                  
               //Extraction des informations
               $idElem = $values['attributes']['ID'];
                
                //On n'a besoin que des coordonnées
                if($values['tag'] == "PLACEMARK"){
                    //On vérifie de quoi il s'agit
                    if($idElem == 1){
                        //Il s'agit de l'emplacement de départ
                        $this->startPoint = $values[3][0]['value'];
                    }
                    elseif($idElem == 2){
                        //Il s'agit du point d'arrivée
                        $this->endPoint = $values[3][0]['value'];
                    }
                    elseif ($idElem == "route") {
                        //Il s'agit des ccordonnées de la route
                        $this->routeStr = $values[3][3]['value'];
                        $this->arrayRoute = $this->parseRouteDatas($this->routeStr);
                    }
                }
            }
        }
    }

    /**
     * Fonction permettant de décoder les coordonées de la route
     * 
     * @param String $source Les coordonnées de la route au format texte
     * @return Array Les coordonnées de la route dans un tableau
     */
    private function parseRouteDatas($source){
        $returnArray = explode("\n", $source);
        
        //Traitement récursif de change ligne pour les transformer en tableau
        foreach($returnArray as $key=>$value){
            $returnArray[$key] = explode(",", $value);
        }
        
        //Renvoi du résultat
        return $returnArray;
    }
    
    /**
     * Returns result as an array
     *
     * @return  Array  Datas parsed
     */
    public function returnArrayDatas(){
        $return = array();
        $return['startPoint'] = $this->startPoint;
        $return['endPoint'] = $this->endPoint;
        $return['datasTravel'] = $this->arrayRoute;

        //Return result
        return $return;
    }
   
    /**
     * Returns startPoint coordinates
     * 
     * @param Boolean $leafletMode Decide to enable leafletMode or not
     * @return Str Start Point coordinates
     */
    public function getStartPoint($leafletMode = false){
        if ($leafletMode) {
            return $this->adapteToLeafletCoordinates($this->startPoint);
        } else {
            return $this->startPoint;
        }
    }
    
    /**
     * Returns endPoint coordinates
     * 
     * @param Boolean $leafletMode Decide to enable leafletMode or not
     * @return Str End Point coordinates
     */
    public function getEndPoint($leafletMode = false){
        if ($leafletMode) {
            return $this->adapteToLeafletCoordinates($this->endPoint);
        } else {
            return $this->endPoint;
        }
    }
    
    /**
     * Returns travel datas coordinates
     * 
     * @param Boolean $leafletMode Decide to enable leafletMode or not
     */
    public function getTravelCoordinates($leafletMode = false){
        //If we just have to return datas
        if(!$leafletMode)
            return $this->arrayRoute;
        
        //Else we adapt them
        $return = array();
        foreach($this->arrayRoute as $n=>$val){
            
            $return[$n] = $val[0].", ".$val[1];
        }
        
        //Return result
        return $return;
    }
    
    /**
     * Adapt coordinates to Leaflet
     * 
     * @param String $coordinates   The coordinates to adapte
     * @return string The adaptated coordinates
     */
    private function adapteToLeafletCoordinates($coordinates){
        //We create an array if required
        $coordinates = explode(",", $coordinates);
        
        $return = round($coordinates[1], 4).", ".round($coordinates[0], 4);
        return $return;
    }
    
    /**
     * Fonction convertissant du code source XML en tableau PHP
     * 
     * @param String $source Le code source XML
     * @return Array Le tableau décodé
     */
    private function xml2array($xml){ 
        $opened = array(); 
        $opened[1] = 0; 
        $opened[0] = 0; 
        $xml_parser = xml_parser_create(); 
        xml_parse_into_struct($xml_parser, $xml, $xmlarray); 
        $array = array_shift($xmlarray); 
        unset($array["level"]); 
        unset($array["type"]);

        //Taille du tableau
        $arrsize = sizeof($xmlarray); 

        //Traitement récursif du résultat
        for($j=0;$j<$arrsize;$j++){ 
            $val = $xmlarray[$j]; 
            switch($val["type"]){ 
                case "open": 
                    $opened[$val["level"]]=0; 
                case "complete": 
                    $index = ""; 
                    for ($i = 1; $i < ($val["level"]); $i++) {
                       $index .= "[" . $opened[$i] . "]";
                   }
                   $path = explode('][', substr($index, 1, -1)); 
                    $value = &$array; 
                    foreach ($path as $segment) {
                       $value = &$value[$segment];
                   }
                   $value = $val; 
                    unset($value["level"]); 
                    unset($value["type"]); 
                    if ($val["type"] == "complete") {
                       $opened[$val["level"] - 1] ++;
                   }
                   break; 
                case "close": 
                    $opened[$val["level"]-1]++; 
                    unset($opened[$val["level"]]); 
                break; 
            } 
        }

        //Renvoi du résultat
        return $array; 
     }
}
