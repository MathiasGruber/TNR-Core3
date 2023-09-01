<?php 
 /* ============== LICENSE INFO START ==============
  * 2005 - 2016 Studie-Tech ApS, All Rights Reserved
  * 
  * This file is part of the project www.TheNinja-RPG.com.
  * Dissemination of this information or reproduction of this material
  * is strictly forbidden unless prior written permission is obtained
  * from Studie-Tech ApS.
  * ============== LICENSE INFO END ============== */ 
?>
<?php

class MemcachedStatistics {

    public function __construct() {
        if($GLOBALS['memOn'] === true) {
            
            // Get all keys
            $AllCacheKeys = $this->getKeys();
            
            foreach( $AllCacheKeys as $key => $value ){
                $AllCacheKeys[$key]['expire'] = round(($value['expiration'] - time())/60,2);
            }
                        
            // Do some ordering
            $order = (isset($_GET['order'])) ? $_GET['order'] : "";
            if( $order !== "" ){
                switch( $order ){
                    case "size": 
                        usort($AllCacheKeys, function($a, $b) { 
                            if( $_GET['orderType'] == "ASC"){
                                return $a['size'] - $b['size']; 
                            }
                            else{
                                return $b['size'] - $a['size']; 
                            }
                        } ); 
                    break;
                    case "expiration": 
                        usort($AllCacheKeys, function($a, $b) { 
                            if( $_GET['orderType'] == "ASC"){
                                return $a['expiration'] - $b['expiration']; 
                            }
                            else{
                                return $b['expiration'] - $a['expiration']; 
                            }
                        } ); 
                    break;
                    case "age": 
                        usort($AllCacheKeys, function($a, $b) { 
                            if( $_GET['orderType'] == "ASC"){
                                return $a['age'] - $b['age']; 
                            }
                            else{
                                return $b['age'] - $a['age']; 
                            }
                        } ); 
                   break;
                   case "expire": 
                        usort($AllCacheKeys, function($a, $b) { 
                            if( $_GET['orderType'] == "ASC"){
                                return $a['expire'] - $b['expire']; 
                            }
                            else{
                                return $b['expire'] - $a['expire']; 
                            }
                        } ); 
                   break;
                }
            }
            
            // Adjust the ages
            foreach( $AllCacheKeys as $key => $value ){
                if( $value['age'] > 24*3600 ){
                    $AllCacheKeys[ $key ]['age'] = round( $AllCacheKeys[ $key ]['age']/(24*3600) ,2 )." days";
                }
                elseif( $value['age'] > 3600 ){
                    $AllCacheKeys[ $key ]['age'] = round( $AllCacheKeys[ $key ]['age']/3600 ,2 )." hours";
                }                
                elseif( $value['age'] > 60 ){
                    $AllCacheKeys[ $key ]['age'] = round( $AllCacheKeys[ $key ]['age']/60 ,2 )." mins";
                }
                else{
                    $AllCacheKeys[ $key ]['age'] = round( $AllCacheKeys[ $key ]['age'] ,2 )." seconds";
                }               
            }
            
            // Show the table
            tableParser::show_list(
                'memcached',
                'MemcacheD Entries', 
                $AllCacheKeys,
                array(
                    'key' => "Key", 
                    'size' => "Size",
                    'expiration' => "Expiration Time",
                    'expire' => "Expire [min]",
                    'age' => "Age"
                ), 
                false ,
                true, // Send directly to contentLoad
                true,
                false,
                true
            );            
        }
        else{
            $GLOBALS['page']->Message("Memcached not enabled", 'Memcached System', 'id='.$_GET['id']); 
        }
    }
    
    private function getKeys(){
        $list = array();
        $allSlabs = $GLOBALS['cache']->getstats('slabs');
        $items = $GLOBALS['cache']->getstats('items');
        foreach($allSlabs as $server => $slabs) {
            foreach($slabs AS $slabId => $slabMeta) {
                if(ctype_digit(trim($slabId)) ){
                    $cdump = $GLOBALS['cache']->getstats('cachedump',(int)$slabId);
                    foreach($cdump AS $server => $entries) {
                        if($entries) {
                            foreach($entries AS $eName => $eData) {
                                $list[$eName] = array(
                                     'key' => $eName,
                                     'server' => $server,
                                     'slabId' => $slabId,
                                     'size' => $eData[0],
                                     'expiration' => $eData[1],
                                     'age' => $items[$server]['items'][$slabId]['age'],
                                );
                            }
                        }
                    }
                }
            }
        }
        ksort($list);
        return $list;
    }
}

new MemcachedStatistics();