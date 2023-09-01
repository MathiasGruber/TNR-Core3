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

class travel
{

    function __construct()
    {
       
        $GLOBALS['template']->assign('x', $GLOBALS['userdata'][0]['longitude']);
        $GLOBALS['template']->assign('x_start', $GLOBALS['userdata'][0]['longitude']);
        $GLOBALS['template']->assign('y', $GLOBALS['userdata'][0]['latitude']);
        $GLOBALS['template']->assign('y_start', $GLOBALS['userdata'][0]['latitude']);
        $GLOBALS['template']->assign('location', $GLOBALS['userdata'][0]['location']);
        $GLOBALS['template']->assign('region', $GLOBALS['userdata'][0]['region']);
        $GLOBALS['template']->assign('map_data', $GLOBALS['map_data']);
        $GLOBALS['template']->assign('contentLoad', './templates/content/travel/main.tpl');
        
        
        if(!isset($_GET['map']) || $_GET['map'] == 'local')
        {
            $GLOBALS['template']->assign('map', 'local');
        }
        else if($_GET['map'] == 'territory')
        {

    /*
    //starting here is some code that is not currently in use, but will need to be used when territory changes can happen in the game again.
    //this code is used to generate the overlay for the map showing the territories.
    //this code should be ran then the results should over write the old file.
    //for now the territory overlay will be hard coded.


            //get all territory information
            $map_region_data = $GLOBALS['database']->fetch_data('SELECT * FROM `map_region_data`');
            
            //changing the keys to be the region for that row for easier addressing
            $map_region_data_temp = array();
            foreach($map_region_data as $key => $row)
            {
                $map_region_data_temp[$row['region']] = $row;
            }
            $map_region_data = $map_region_data_temp;
            
            
            var_dump('MAP DATA HAS CHANGED AND THIS IS NO LONGER VALID');

            //getting all tile information for tiles that are in view.
            $map_data = $GLOBALS['database']->fetch_data('SELECT `y`,   `-25`, `-24`, `-23`, `-22`, `-21`,
                                                                        `-20`, `-19`, `-18`, `-17`, `-16`, `-15`, `-14`, `-13`, `-12`, `-11`,
                                                                        `-10`, `-9`,  `-8`,  `-7`,  `-6`,  `-5`,  `-4`,  `-3`,  `-2`,  `-1`,
                                                                        `0`,   `1`,   `2`,   `3`,   `4`,   `5`,   `6`,   `7`,   `8`,   `9`,
                                                                        `10`,  `11`,  `12`,  `13`,  `14`,  `15`,  `16`,  `17`,  `18`,  `19`,
                                                                        `20`,  `21`,  `22`,  `23`,  `24`,  `25`,  `26`,  `27`,  `28`,  `29`,
                                                                        `30`,  `31`,  `32`,  `33`,  `34`,  `35`,  `36`,  `37`,  `38`,  `39`,
                                                                        `40`,  `41`,  `42`,  `43`,  `44`,  `45`,  `46`,  `47`,  `48`,  `49`,
                                                                        `50`,  `51`,  `52`,  `53`,  `54`,  `55`,  `56`,  `57`,  `58`,  `59`,
                                                                        `60`,  `61`,  `62`,  `63`,  `64`,  `65`,  `66`,  `67`,  `68`,  `69`,
                                                                        `70`,  `71`,  `72`,  `73`,  `74`,  `75`,  `76`,  `77`,  `78`,  `79`,
                                                                        `80`,  `81`,  `82`,  `83`,  `84`,  `85`,  `86`,  `87`,  `88`,  `89`,
                                                                        `90`,  `91`,  `92`,  `93`,  `94`,  `95`,  `96`,  `97`,  `98`,  `99`,
                                                                        `100` FROM `map_data` WHERE `y` >= 0');
            
            //processing the returned data for easier addressing
            $map_data_temp = array();
            foreach($map_data as $key => $row)
            {
                $y = $row['y'];
                $map_data_temp[$y] = $row;
                unset( $map_data_temp[$y]['y']);
            
                foreach($map_data_temp[$y] as $x => $tile)
                {
                    $map_data_temp[$y][$x] = explode(',',$tile);
                    $map_data_temp[$y][$x] = array('name'=>$map_data_temp[$y][$x][0],'region'=>$map_data_temp[$y][$x][1],'owner'=>$map_region_data[$map_data_temp[$y][$x][1]]['owner']);
                }
            }
            $map_data = $map_data_temp;

            //list of location names that should over-ride region and ownership
            $special_names = ['Konoki','Silence','Shroud','Shine','Samui',"Gambler's Den","Bandit's Outpost","Poacher's Camp","Pirate's Hideout","Emiko's Meatery", "Stillwater's Chateau Barge", "Black Lodge", "Skyview Restaurant", "Shaded Rest Inn"];

            //going over every tile loaded in above, scanning horizontally.
            //this creates groups of tiles adjacent to each other horizontally that share the same special name or region.
            $slabs = [];
            $slab_counter = -1;
            for ($y = 100; $y >= 0; $y--) 
            {
            	$current_region = '';
            	$current_slab_origin = ['x'=>'x','y'=>'y'];
                for ($x = -25; $x <= 100; $x++)
            	{
            		if($current_region == '' || $current_region != $map_data[$y][$x]['region'] || ( $map_data[$y][$x]['name'] != $map_data[$y][$x-1]['name'] && ( in_array($map_data[$y][$x]['name'], $special_names) || in_array($map_data[$y][$x-1]['name'],           $special_names) ) ) )
            		{
                		$current_region = $map_data[$y][$x]['region'];
            			$current_slab_origin = ['x'=>$x,'y'=>$y];
            			$slab_counter += 1;
            			$slabs[$slab_counter] = [$current_slab_origin];
            		}
            		else
            		{
            			$slabs[$slab_counter][] = ['x'=>$x,'y'=>$y];
            		}
                
            		$map_data[$y][$x]['slab_id'] = $slab_counter;
            		$map_data[$y][$x]['slab_origin'] = $current_slab_origin;
            	} 
            } 

            //this goes over every slab that was created above in order.
            //this will group the slabs together in collections called bricks.
            //this will group slabs together if they are the same "region" which can be overridden by special name
            //the way this groups slabs together, prevents a brick from wrapping around another brick.
            $bricks = [];
            $brick_counter = -1;
            $slabs_in_bricks = [];
            //foreach slab
            foreach($slabs as $slab_id => $slab)
            {
            	$y = $slab[0]['y'] - 1;
            	$x_start = $slab[0]['x'];
            	$x_end = end($slab)['x'];

                //if this slab is not in a brick
                if(!in_array($slab_id, $slabs_in_bricks))
                {
                    $brick_counter += 1;
                    $bricks[$brick_counter][] = array('slab_id'=>$slab_id, 'slab'=>$slab);
                    $slabs_in_bricks[] = $slab_id;
                
            	    //for each tile under current slab
            	    for($x = $x_start; $x <= $x_end; $x++)
            	    {
                    
                        //if this tile matches the region of the slab above
            	    	if( isset($map_data[$y][$x]) && $map_data[$y][$x]['region'] == $map_data[$y + 1][$x]['region'] && !in_array($map_data[$y][$x]['slab_id'], $slabs_in_bricks) && ($map_data[$y][$x]['name'] == $map_data[$y + 1][$x]['name'] || ( !in_array         ($map_data[$y][$x]['name'],$special_names) && !in_array($map_data[$y + 1][$x]['name'],$special_names) )) )
            	    	{
                        

                            //move your self to the next slab and add it to the brick
                            if(isset($slabs[$map_data[$y][$x]['slab_id']]))
                            {
                                $slab_id = $map_data[$y][$x]['slab_id'];
                                $slab = $slabs[$slab_id];
                                $bricks[$brick_counter][] = array('slab_id'=>$slab_id, 'slab'=>$slab);
                                $slabs_in_bricks[] = $slab_id;
                                $y = $slab[0]['y'] - 1;
                                $x = $slab[0]['x'] - 1;
                                $x_start = $slab[0]['x'];
            	                $x_end = end($slab)['x'];
                            }
                        }
                    }
                }
            }

            //this creates an svg from the bricks made above.
            $svg = "<svg id='territory-map-overlay' viewBox='-25 0 126 101' xmlns='http://www.w3.org/2000/svg'>";
            $owner_region_counter = ['konoki'=>[],'silence'=>[],'shroud'=>[],'shine'=>[],'samui'=>[],'syndicate'=>[]];
            foreach($bricks as $brick_id => $brick_data)
            {
                $class = "";
                $region = $map_data[$brick_data[0]['slab'][0]['y']][$brick_data[0]['slab'][0]['x']]['region'];
                $owner  = $map_data[$brick_data[0]['slab'][0]['y']][$brick_data[0]['slab'][0]['x']]['owner'];
                $name = $map_data[$brick_data[0]['slab'][0]['y']][$brick_data[0]['slab'][0]['x']]['name'];
            
                //checking for ramen
                if(in_array($name, ["Emiko's Meatery", "Stillwater's Chateau Barge", "Black Lodge", "Skyview Restaurant", "Shaded Rest Inn"] ))
                {
                    $class="ramen";
                    $desc="Ramen Stand: ".$name;
                }
            
                //check to see if this has a valid owner
                else if( in_array($owner, ['konoki','silence','shroud','shine','samui','syndicate']) )
                {
                    if(!isset($owner_region_counter[$owner][$region]))
                        $owner_region_counter[$owner][$region] = ((count($owner_region_counter[$owner]) + 1) % 6) + 1;
                
                    $class = $owner.'-'.$owner_region_counter[$owner][$region];
                    $desc = $region;
                
                    if(in_array($name, ['Konoki','Silence','Shroud','Shine','Samui',"Gambler's Den","Bandit's Outpost","Poacher's Camp","Pirate's Hideout"] ))
                    {
                        $class .= " village";
                        $desc = $name;
                    
                        if(in_array($name, ['Konoki','Silence','Shroud','Shine','Samui']))
                            $desc .= ' Village';
                    }
                }
                //falling back on region
                else
                {
                    $class=preg_replace('/\s+/', '_', $region);
                    $desc=$region;
                }
            
                $svg .= "<path class='{$class}' d='";
            
                $slab_count = count($brick_data);
                $walk_back = "";
            
                foreach($brick_data as $slab_number => $slab_data)
                {
                    $slab = $slab_data['slab'];
                    $start = $slab[0];
	        		$start['y'] = 100 - $start['y'];
                    $end = end($slab)['x']+1;
                
                
                    //if first slab is last slab
                    if($slab_count == 1)
                    {
                        $svg.= "M{$start['x']},{$start['y']}H{$end}v1H{$start['x']}";
                    }
                
                    //first slab
                    else if($slab_number == 0)
                    {
                        $svg .= "M{$start['x']},{$start['y']}H{$end}v1";
                        $walk_back .= strrev("H{$start['x']}v");
                    }
                
                    //last slab
                    else if($slab_number == $slab_count - 1)
                    {
                        $svg .= "H{$end}v1H{$start['x']}v-1";
                    }

                    //middle slabs
                    else
                    {
                        $svg .= "H{$end}v1";
                        $walk_back .= strrev("H{$start['x']}v-1");
                    }
                }
            
                $svg .= strrev($walk_back)."z'><title>{$desc}</title></path>";
            }
        
            $svg .= "</svg>";*/

            $svg='<svg id="territory-map-overlay" viewBox="-25 0 126 101" xmlns="http://www.w3.org/2000/svg"><path class="ocean" d="M-25,0H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H-3v1H-5v1H-9v1H-11v1H-13v1H-14v1H-14v1H-15v1H-16v1H-16v1H-23v1H-24v1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25vz"><title>ocean</title></path><path class="shore" d="M-3,10H4v1H7v1H-1v1H-3v1H-7v1H-9v1H-11v1H-12v1H-12v1H-13v1H-14v1H-14v1H-21v1H-22v1H-23v1H-23v1H-21v1H-19v1H-18v1H-19v1H-20v1H-20v1H-24v1H-24v1H-25v-1H-25v-1H-25v-1H-25v-1H-21v-1H-23v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-24v-1H-17v-1H-16v-1H-16v-1H-15v-1H-14v-1H-14v-1H-13v-1H-11v-1H-9v-1H-5v-1H-3vz"><title>shore</title></path><path class="ocean" d="M4,10H101v1H101v1H101v1H23v1H21v1H19v1H16v1H15v-1H15v-1H14v-1H11v-1H10v-1H7v-1H4vz"><title>ocean</title></path><path class="uncharted" d="M-1,12H2v1H5v1H5v1H9v1H12v1H13v1H13v1H29v1H30v1H-8v1H-8v1H-10v-1H-11v-1H-14v-1H-13v-1H-12v-1H-12v-1H-11v-1H-9v-1H-7v-1H-3v-1H-1vz"><title>uncharted</title></path><path class="shore" d="M2,12H10v1H11v1H6v1H5v-1H5v-1H2vz"><title>shore</title></path><path class="shore" d="M23,13H28v1H28v1H24v1H23v1H21v1H18v1H13v-1H13v-1H16v-1H19v-1H21v-1H23vz"><title>shore</title></path><path class="ocean" d="M28,13H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H37v1H35v-1H36v-1H35v-1H35v-1H33v-1H32v-1H32v-1H31v-1H31v-1H31v-1H28v-1H28vz"><title>ocean</title></path><path class="uncharted" d="M6,14H8v1H6z"><title>uncharted</title></path><path class="shore" d="M8,14H14v1H15v1H15v1H12v-1H9v-1H8vz"><title>shore</title></path><path class="uncharted" d="M24,15H26v1H26v1H26v1H29v1H18v-1H21v-1H23v-1H24vz"><title>uncharted</title></path><path class="shore" d="M26,15H31v1H31v1H27v1H26v-1H26v-1H26vz"><title>shore</title></path><path class="uncharted" d="M27,17H29v1H27z"><title>uncharted</title></path><path class="shore" d="M29,17H31v1H32v1H32v1H33v1H35v1H35v1H36v1H35v1H45v1H39v1H37v1H35v1H35v1H34v1H33v1H32v1H31v1H31v1H31v1H30v1H30v1H31v1H31v1H31v1H28v1H27v1H27v1H35v1H35v1H35v1H30v1H29v-1H24v-1H24v-1H24v-1H24v-1H24v-1H24v-1H25v-1H26v-1H26v-1H27v-1H28v-1H28v-1H30v-1H29v-1H28v-1H28v-1H29v-1H31v-1H31v-1H33v-1H34v-1H34v-1H33v-1H33v-1H31v-1H30v-1H30v-1H29v-1H29v-1H29vz"><title>shore</title></path><path class="shore" d="M-23,20H-18v1H-23z"><title>shore</title></path><path class="ocean" d="M-18,20H-17v1H-18z"><title>ocean</title></path><path class="samui-2" d="M-14,21H-11v1H-10v1H1v1H1v1H-13v1H-15v1H-16v1H-17v1H-18v-1H-19v-1H-21v-1H-23v-1H-15v-1H-15v-1H-15v-1H-14vz"><title>Hyuogaan Mountains</title></path><path class="samui-2" d="M-8,21H-6v1H-5v1H-8v-1H-8vz"><title>Hyuogaan Mountains</title></path><path class="uncharted" d="M-6,21H-2v1H-3v1H-5v-1H-6vz"><title>uncharted</title></path><path class="samui-2" d="M-2,21H0v1H1v1H-3v-1H-2vz"><title>Hyuogaan Mountains</title></path><path class="uncharted" d="M0,21H1v1H0z"><title>uncharted</title></path><path class="dead_lake" d="M1,21H2v1H6v1H6v1H8v1H8v1H8v1H8v1H6v1H3v-1H3v-1H2v-1H2v-1H1v-1H1v-1H1v-1H1vz"><title>dead lake</title></path><path class="uncharted" d="M2,21H4v1H2z"><title>uncharted</title></path><path class="dead_lake" d="M4,21H7v1H4z"><title>dead lake</title></path><path class="samui-3" d="M7,21H11v1H13v1H13v1H14v1H15v1H13v1H12v1H11v1H10v1H10v1H9v1H8v1H6v-1H6v-1H2v-1H1v-1H6v-1H8v-1H8v-1H8v-1H8v-1H6v-1H6v-1H7vz"><title>Frozen Highlands</title></path><path class="uncharted" d="M11,21H30v1H31v1H16v1H13v-1H13v-1H11vz"><title>uncharted</title></path><path class="samui-2" d="M-21,22H-20v1H-19v1H-18v1H-23v-1H-22v-1H-21vz"><title>Hyuogaan Mountains</title></path><path class="shore" d="M-20,22H-15v1H-15v1H-15v1H-18v-1H-19v-1H-20vz"><title>shore</title></path><path class="dead_lake" d="M16,23H17v1H18v1H19v1H19v1H17v-1H15v-1H14v-1H16vz"><title>lake</title></path><path class="uncharted" d="M17,23H20v1H19v1H18v-1H17vz"><title>uncharted</title></path><path class="samui-4" d="M20,23H21v1H21v1H21v1H22v1H22v1H23v1H14v1H13v-1H12v-1H12v-1H19v-1H19v-1H19v-1H20vz"><title>Hyuogaan Icesheet</title></path><path class="uncharted" d="M21,23H26v1H26v1H23v1H21v-1H21v-1H21vz"><title>uncharted</title></path><path class="samui-4" d="M26,23H28v1H29v1H29v1H34v1H33v1H31v1H31v1H29v1H28v1H27v1H26v-1H25v-1H22v-1H20v-1H25v-1H25v-1H25v-1H26v-1H26v-1H26vz"><title>Hyuogaan Icesheet</title></path><path class="uncharted" d="M28,23H29v1H28z"><title>uncharted</title></path><path class="dead_lake" d="M29,23H31v1H31v1H30v1H29v-1H29v-1H29vz"><title>lake</title></path><path class="samui-4" d="M31,23H33v1H33v1H34v1H30v-1H31v-1H31vz"><title>Hyuogaan Icesheet</title></path><path class="shore" d="M37,24H45v1H37z"><title>shore</title></path><path class="ocean" d="M45,24H101v1H101v1H101v1H49v1H48v-1H47v-1H45v-1H45vz"><title>ocean</title></path><path class="uncharted" d="M-13,25H-8v1H-8v1H-8v1H-10v1H-12v1H-13v1H-14v1H-14v1H-15v1H-15v1H-15v1H-15v1H-14v1H-14v1H-13v1H-5v1H-3v1H-3v1H-2v1H-2v1H-2v1H-2v1H-2v1H-5v1H-7v1H-9v1H-10v1H-10v1H-10v1H-9v1H-9v1H-9v1H-12v1H-13v1H-14v1H-13v1H-13v1H-12v1H-12v1H-12v1H-12v1H-11v1H-11v1H-6v1H-6v1H-6v1H-6v1H-7v1H-7v1H-8v1H-8v1H-7v1H-6v1H-5v1H-2v1H-2v1H0v1H1v1H3v1H4v1H3v1H2v1H4v1H4v1H48v1H48v1H48v1H49v1H49v1H50v1H50v1H50v1H50v1H50v1H50v1H50v1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-24v-1H-24v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-23v-1H-23v-1H-23v-1H-23v-1H-23v-1H-23v-1H-22v-1H-23v-1H-23v-1H-23v-1H-24v-1H-24v-1H-24v-1H-25v-1H-25v-1H-25v-1H-24v-1H-24v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-20v-1H-19v-1H-20v-1H-20v-1H-20v-1H-19v-1H-17v-1H-16v-1H-15v-1H-13vz"><title>uncharted</title></path><path class="samui-2" d="M-8,25H1v1H-1v1H-2v1H-2v1H-4v-1H-8v-1H-8v-1H-8vz"><title>Hyuogaan Mountains</title></path><path class="samui-3" d="M1,25H2v1H2v1H3v1H3v1H0v1H-1v1H-2v-1H-2v-1H-2v-1H-2v-1H-1v-1H1vz"><title>Frozen Highlands</title></path><path class="dead_lake" d="M23,25H24v1H25v1H25v1H25v1H23v-1H22v-1H22v-1H23vz"><title>lake</title></path><path class="uncharted" d="M24,25H26v1H24z"><title>uncharted</title></path><path class="samui-4" d="M13,26H17v1H13z"><title>Hyuogaan Icesheet</title></path><path class="shroud-2" d="M39,26H43v1H44v1H45v1H46v1H47v1H47v1H47v1H46v1H46v1H47v1H47v1H47v1H44v-1H44v-1H44v-1H42v-1H40v-1H39v-1H35v-1H34v-1H35v-1H35v-1H37v-1H39vz"><title>Savage Lakes</title></path><path class="shore" d="M43,26H47v1H48v1H57v1H51v1H49v1H47v-1H46v-1H45v-1H44v-1H43vz"><title>shore</title></path><path class="shore" d="M49,27H55v1H49z"><title>shore</title></path><path class="ocean" d="M55,27H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H74v1H72v1H71v1H71v1H71v1H71v1H70v1H70v1H70v1H70v1H70v1H70v1H70v1H70v1H70v1H71v1H71v1H71v1H72v1H72v1H73v1H74v1H82v1H82v1H81v1H81v1H81v1H81v1H81v1H76v1H76v1H76v1H76v1H76v1H76v1H76v1H76v1H76v1H76v1H77v1H78v1H78v1H77v1H77v1H76v1H76v1H76v1H76v1H76v1H76v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H52v-1H52v-1H52v-1H52v-1H52v-1H52v-1H52v-1H52v-1H52v-1H52v-1H52v-1H52v-1H52v-1H52v-1H52v-1H52v-1H52v-1H52v-1H52v-1H52v-1H53v-1H53v-1H53v-1H53v-1H54v-1H55v-1H55v-1H55v-1H55v-1H55v-1H55v-1H55v-1H55v-1H56v-1H58v-1H59v-1H59v-1H59v-1H59v-1H59v-1H55v-1H53v-1H52v-1H52v-1H53v-1H54v-1H56v-1H56v-1H56v-1H56v-1H56v-1H56v-1H56v-1H56v-1H57v-1H59v-1H60v-1H61v-1H61v-1H63v-1H63v-1H63v-1H63v-1H63v-1H63v-1H63v-1H63v-1H61v-1H59v-1H58v-1H58v-1H57v-1H57v-1H55vz"><title>ocean</title></path><path class="ocean" d="M-25,28H-23v1H-21v1H-25v-1H-25vz"><title>ocean</title></path><path class="syndicate-2" d="M-10,28H-8v1H-8v1H-9v1H-9v1H-9v1H-9v1H-9v1H-10v1H-9v1H-8v1H-8v1H-8v1H-13v-1H-13v-1H-13v-1H-15v-1H-15v-1H-15v-1H-15v-1H-14v-1H-14v-1H-13v-1H-12v-1H-10vz"><title>Forest\'s End</title></path><path class="syndicate-3" d="M-8,28H-4v1H-2v1H-2v1H-2v1H-2v1H-1v1H1v1H0v1H0v1H-2v1H-2v1H0v1H0v1H-2v1H-3v-1H-5v-1H-6v-1H-8v-1H-8v-1H-9v-1H-10v-1H-9v-1H-9v-1H-9v-1H-9v-1H-9v-1H-8v-1H-8vz"><title>Deadwood Hillside</title></path><path class="samui-5" d="M11,28H12v1H13v1H16v1H14v1H13v1H12v1H11v1H9v1H9v1H5v-1H3v-1H1v-1H-1v-1H8v-1H9v-1H10v-1H10v-1H11vz"><title>Whirling Valley</title></path><path class="samui-5" d="M0,29H1v1H2v1H6v1H6v1H-2v-1H-2v-1H-1v-1H0vz"><title>Whirling Valley</title></path><path class="samui-5" d="M14,29H16v1H14z"><title>Whirling Valley</title></path><path class="samui-4" d="M16,29H17v1H16z"><title>Hyuogaan Icesheet</title></path><path class="samui-6" d="M17,29H20v1H22v1H24v1H24v1H23v1H22v1H22v1H20v1H15v1H11v-1H9v-1H9v-1H11v-1H12v-1H13v-1H19v-1H19v-1H17vz"><title>Tornado Valley</title></path><path class="shroud-3" d="M51,29H54v1H55v1H55v1H56v1H56v1H56v1H56v1H55v1H54v1H53v1H50v1H49v-1H48v-1H47v-1H47v-1H47v-1H46v-1H46v-1H47v-1H47v-1H49v-1H51vz"><title>Spirit Lagoon</title></path><path class="shore" d="M54,29H57v1H58v1H58v1H59v1H61v1H63v1H63v1H63v1H63v1H63v1H63v1H63v1H63v1H61v1H61v1H60v1H59v1H57v1H56v1H56v1H56v1H53v1H52v-1H52v-1H52v-1H52v-1H53v-1H54v-1H55v-1H57v-1H58v-1H59v-1H59v-1H61v-1H61v-1H61v-1H61v-1H59v-1H57v-1H56v-1H56v-1H55v-1H55v-1H54vz"><title>shore</title></path><path class="samui-6" d="M16,30H17v1H17v1H16v-1H16vz"><title>Tornado Valley</title></path><path class="samui-6 village" d="M17,30H19v1H19v1H17v-1H17vz"><title>Samui Village</title></path><path class="samui-6" d="M14,31H15v1H14z"><title>Tornado Valley</title></path><path class="samui-5" d="M15,31H16v1H15z"><title>Whirling Valley</title></path><path class="samui-1" d="M24,31H25v1H26v1H29v1H28v1H28v1H28v1H27v1H26v1H26v1H25v1H24v1H24v1H22v-1H22v-1H20v-1H19v-1H19v-1H19v-1H21v-1H22v-1H22v-1H23v-1H24v-1H24vz"><title>Windswept Grasslands</title></path><path class="shroud-4" d="M33,31H35v1H37v1H37v1H35v1H34v1H34v1H34v1H35v1H34v1H34v1H35v1H36v1H29v1H27v-1H27v-1H28v-1H31v-1H31v-1H31v-1H30v-1H30v-1H31v-1H31v-1H31v-1H32v-1H33vz"><title>Mistmire</title></path><path class="uncharted" d="M-24,32H-22v1H-22v1H-21v1H-25v-1H-24v-1H-24vz"><title>uncharted</title></path><path class="shore" d="M-22,32H-20v1H-19v1H-20v1H-21v-1H-22v-1H-22vz"><title>shore</title></path><path class="samui-1" d="M27,32H28v1H27z"><title>Windswept Grasslands</title></path><path class="shroud-5" d="M37,32H39v1H40v1H42v1H44v1H44v1H40v1H40v1H45v1H44v1H43v1H43v1H39v1H37v-1H36v-1H35v-1H34v-1H34v-1H35v-1H34v-1H34v-1H34v-1H35v-1H37v-1H37vz"><title>Misty Morass</title></path><path class="shore" d="M28,34H29v1H28z"><title>shore</title></path><path class="ramen" d="M29,34H30v1H29z"><title>Ramen Stand: Stillwater\'s Chateau Barge</title></path><path class="shroud-6" d="M56,34H57v1H59v1H61v1H61v1H61v1H61v1H59v1H59v1H58v1H57v1H55v1H54v1H53v1H52v1H52v1H52v1H52v1H54v1H54v1H52v1H51v1H50v-1H50v-1H50v-1H50v-1H50v-1H50v-1H49v-1H49v-1H49v-1H48v-1H46v-1H47v-1H47v-1H48v-1H48v-1H50v-1H53v-1H54v-1H55v-1H56v-1H56vz"><title>Ravaged Sands</title></path><path class="syndicate-4" d="M0,35H3v1H5v1H9v1H7v1H6v1H7v1H7v1H8v1H2v1H2v1H2v1H1v1H-2v-1H-2v-1H-2v-1H-2v-1H-3v-1H-2v-1H0v-1H0v-1H-2v-1H-2v-1H0v-1H0vz"><title>Deadwood Forest</title></path><path class="Elemental_Shrine" d="M20,36H21v1H20z"><title>Elemental Shrine</title></path><path class="syndicate-2 village" d="M-14,37H-13v1H-13v1H-14v-1H-14vz"><title>Bandit\'s Outpost</title></path><path class="konoki-2" d="M9,37H11v1H19v1H19v1H20v1H15v1H14v1H15v1H14v1H11v1H11v1H10v1H8v-1H8v-1H8v-1H8v-1H7v-1H8v-1H7v-1H7v-1H6v-1H8v-1H9vz"><title>Oakwood Forest</title></path><path class="konoki-2" d="M15,37H18v1H15z"><title>Oakwood Forest</title></path><path class="samui-6" d="M18,37H19v1H18z"><title>Tornado Valley</title></path><path class="shroud-5 village" d="M40,37H42v1H42v1H40v-1H40vz"><title>Shroud Village</title></path><path class="shroud-5" d="M42,37H44v1H45v1H42v-1H42vz"><title>Misty Morass</title></path><path class="ramen" d="M7,38H8v1H7z"><title>Ramen Stand: Black Lodge</title></path><path class="Elemental_Shrine" d="M45,38H46v1H45z"><title>Elemental Shrine</title></path><path class="shroud-1" d="M46,38H48v1H49v1H48v1H48v1H47v1H47v1H46v1H46v1H45v1H38v1H38v1H37v-1H36v-1H35v-1H35v-1H36v-1H42v-1H43v-1H43v-1H44v-1H45v-1H46vz"><title>Swamp of Sorrow</title></path><path class="uncharted" d="M-8,39H-6v1H-8z"><title>uncharted</title></path><path class="syndicate-5" d="M15,41H18v1H22v1H24v1H23v1H23v1H22v1H21v1H19v1H18v-1H17v-1H14v-1H11v-1H14v-1H15v-1H14v-1H15vz"><title>Silvergrass Marshland</title></path><path class="konoki-2" d="M18,41H20v1H18z"><title>Oakwood Forest</title></path><path class="syndicate-5" d="M20,41H22v1H20z"><title>Silvergrass Marshland</title></path><path class="shore" d="M74,42H80v1H80v1H76v1H74v1H73v1H73v1H73v1H73v1H72v1H72v1H72v1H72v1H72v1H73v1H73v1H73v1H74v1H74v1H75v1H76v1H82v1H81v1H74v-1H73v-1H72v-1H72v-1H71v-1H71v-1H71v-1H70v-1H70v-1H70v-1H70v-1H70v-1H70v-1H70v-1H70v-1H70v-1H71v-1H71v-1H71v-1H71v-1H72v-1H74vz"><title>shore</title></path><path class="ocean" d="M80,42H101v1H101v1H101v1H101v1H101v1H87v1H86v1H85v1H84v1H83v1H82v-1H82v-1H82v-1H81v-1H81v-1H81v-1H81v-1H81v-1H80v-1H80vz"><title>ocean</title></path><path class="konoki-3" d="M2,43H7v1H8v1H8v1H8v1H8v1H8v1H4v1H4v1H9v1H5v1H-1v-1H-2v-1H-2v-1H-3v-1H-2v-1H-2v-1H1v-1H2v-1H2v-1H2vz"><title>Fireheart Forest</title></path><path class="shore" d="M29,43H33v1H29z"><title>shore</title></path><path class="shroud-4" d="M33,43H36v1H36v1H35v-1H33vz"><title>Mistmire</title></path><path class="shroud-1" d="M36,43H37v1H36z"><title>Swamp of Sorrow</title></path><path class="shroud-1" d="M39,43H40v1H39z"><title>Swamp of Sorrow</title></path><path class="shroud-5" d="M40,43H42v1H40z"><title>Misty Morass</title></path><path class="syndicate-6" d="M23,44H24v1H24v1H24v1H24v1H23v1H23v1H23v1H23v1H20v1H20v1H20v1H18v1H18v1H16v1H16v1H21v1H20v1H14v-1H14v-1H14v-1H14v-1H13v-1H15v-1H13v-1H11v-1H16v-1H15v-1H19v-1H18v-1H19v-1H21v-1H22v-1H23v-1H23vz"><title>Gambler\'s Valley</title></path><path class="syndicate-1" d="M76,44H78v1H78v1H79v1H79v1H78v1H78v1H79v1H80v1H80v1H80v1H79v1H78v1H78v1H78v1H77v1H78v1H80v1H79v1H76v-1H75v-1H74v-1H74v-1H73v-1H73v-1H73v-1H72v-1H72v-1H72v-1H72v-1H72v-1H73v-1H73v-1H73v-1H73v-1H74v-1H76vz"><title>Manatee Island</title></path><path class="shore" d="M78,44H81v1H81v1H81v1H81v1H81v1H82v1H82v1H82v1H86v1H85v1H84v1H89v1H90v1H91v1H83v1H83v1H83v1H83v1H79v-1H80v-1H78v-1H77v-1H78v-1H78v-1H78v-1H79v-1H80v-1H80v-1H80v-1H79v-1H78v-1H78v-1H79v-1H79v-1H78v-1H78vz"><title>shore</title></path><path class="shore" d="M46,45H48v1H49v1H49v1H49v1H46v1H45v1H45v1H40v-1H35v-1H42v-1H43v-1H45v-1H45v-1H46vz"><title>shore</title></path><path class="konoki-4" d="M11,46H14v1H17v1H18v1H18v1H19v1H15v1H16v1H11v1H13v1H15v1H13v1H10v-1H9v-1H8v-1H8v-1H8v-1H9v-1H8v-1H8v-1H8v-1H10v-1H11vz"><title>Black Spruce Bog</title></path><path class="river" d="M24,47H25v1H25v1H24v1H24v1H24v1H24v1H21v1H21v1H21v1H19v1H19v1H17v1H16v-1H16v-1H18v-1H18v-1H20v-1H20v-1H20v-1H23v-1H23v-1H23v-1H23v-1H24vz"><title>river</title></path><path class="syndicate-6" d="M25,47H26v1H26v1H26v1H26v1H26v1H26v1H26v1H25v1H24v1H24v1H23v1H22v1H17v-1H19v-1H19v-1H21v-1H21v-1H21v-1H24v-1H24v-1H25v-1H25v-1H25v-1H25vz"><title>Gambler\'s Valley</title></path><path class="syndicate-2" d="M26,47H29v1H31v1H32v1H32v1H32v1H32v1H32v1H33v1H32v1H31v1H31v1H31v1H29v1H24v-1H22v-1H23v-1H24v-1H24v-1H25v-1H26v-1H26v-1H26v-1H26v-1H26v-1H26v-1H26vz"><title>Wayfinder\'s Refuge</title></path><path class="syndicate-2" d="M30,47H31v1H30z"><title>Wayfinder\'s Refuge</title></path><path class="shore" d="M31,47H36v1H37v1H41v1H33v-1H31v-1H31vz"><title>shore</title></path><path class="shore" d="M38,47H39v1H41v1H38v-1H38vz"><title>shore</title></path><path class="shroud-1" d="M39,47H45v1H43v1H41v-1H39vz"><title>Swamp of Sorrow</title></path><path class="shore" d="M87,47H92v1H92v1H89v1H88v1H87v1H83v-1H84v-1H85v-1H86v-1H87vz"><title>shore</title></path><path class="ocean" d="M92,47H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H101v1H82v-1H83v-1H84v-1H85v-1H85v-1H86v-1H87v-1H87v-1H87v-1H87v-1H87v-1H87v-1H87v-1H87v-1H86v-1H86v-1H95v-1H96v-1H97v-1H97v-1H97v-1H98v-1H98v-1H98v-1H99v-1H99v-1H99v-1H99v-1H99v-1H99v-1H99v-1H99v-1H99v-1H99v-1H98v-1H98v-1H97v-1H96v-1H95v-1H94v-1H93v-1H92v-1H92v-1H92v-1H92vz"><title>ocean</title></path><path class="shore" d="M-25,48H-24v1H-24v1H-25v-1H-25vz"><title>shore</title></path><path class="konoki-5" d="M-5,48H-2v1H-3v1H-2v1H-7v1H-1v1H3v1H2v1H3v1H3v1H1v1H-1v1H-1v1H-5v-1H-8v-1H-9v-1H-9v-1H-9v-1H-9v-1H-10v-1H-10v-1H-10v-1H-9v-1H-7v-1H-5vz"><title>Wildwonder Forest</title></path><path class="konoki-3 village" d="M4,49H6v1H6v1H4v-1H4vz"><title>Konoki Village</title></path><path class="konoki-3" d="M6,49H8v1H8v1H6v-1H6vz"><title>Fireheart Forest</title></path><path class="syndicate-6 village" d="M24,49H25v1H25v1H24v-1H24vz"><title>Gambler\'s Den</title></path><path class="silence-2" d="M32,49H33v1H35v1H37v1H36v1H36v1H36v1H37v1H38v1H38v1H37v1H37v1H36v1H36v1H37v1H37v1H38v1H38v1H39v1H39v1H40v1H40v1H39v-1H37v-1H36v-1H34v-1H34v-1H34v-1H34v-1H34v-1H34v-1H34v-1H34v-1H34v-1H34v-1H34v-1H33v-1H33v-1H32v-1H32v-1H32v-1H32v-1H32vz"><title>Fortune Mountains</title></path><path class="ramen" d="M41,49H42v1H41z"><title>Ramen Stand: Emiko\'s Meatery</title></path><path class="silence-3" d="M46,49H47v1H48v1H48v1H48v1H47v1H46v1H45v1H44v1H44v1H43v1H43v1H43v1H40v1H40v1H39v-1H39v-1H39v-1H39v-1H38v-1H38v-1H38v-1H37v-1H36v-1H36v-1H36v-1H45v-1H45v-1H46vz"><title>Plateau of Quietude</title></path><path class="shore" d="M47,49H50v1H50v1H50v1H50v1H50v1H50v1H54v1H53v1H52v1H52v1H53v1H55v1H59v1H59v1H59v1H59v1H59v1H58v1H56v1H55v1H55v1H55v1H55v1H55v1H55v1H55v1H55v1H54v1H53v1H53v1H53v1H53v1H52v1H52v1H52v1H52v1H52v1H52v1H52v1H52v1H52v1H52v1H52v1H52v1H52v1H52v1H52v1H52v1H52v1H52v1H52v1H52v1H50v-1H50v-1H50v-1H50v-1H50v-1H50v-1H50v-1H50v-1H50v-1H50v-1H50v-1H50v-1H50v-1H50v-1H50v-1H50v-1H50v-1H50v-1H50v-1H50v-1H50v-1H50v-1H51v-1H51v-1H51v-1H51v-1H52v-1H53v-1H53v-1H53v-1H53v-1H52v-1H52v-1H53v-1H53v-1H54v-1H56v-1H57v-1H53v-1H51v-1H50v-1H50v-1H49v-1H47v-1H47v-1H45v-1H46v-1H47v-1H48v-1H48v-1H48v-1H47vz"><title>shore</title></path><path class="syndicate-3" d="M89,49H90v1H90v1H90v1H90v1H91v1H92v1H93v1H94v1H94v1H94v1H94v1H94v1H94v1H94v1H94v1H94v1H94v1H94v1H88v1H88v1H87v1H83v-1H83v-1H84v-1H84v-1H93v-1H93v-1H92v-1H93v-1H93v-1H93v-1H92v-1H92v-1H91v-1H90v-1H89v-1H84v-1H85v-1H86v-1H87v-1H88v-1H89vz"><title>Dolphin Cove</title></path><path class="shore" d="M90,49H92v1H92v1H93v1H94v1H95v1H96v1H97v1H98v1H98v1H99v1H99v1H99v1H99v1H99v1H99v1H99v1H99v1H99v1H99v1H98v1H98v1H98v1H97v1H97v1H97v1H96v1H95v1H90v-1H89v-1H93v-1H94v-1H95v-1H95v-1H95v-1H96v-1H96v-1H97v-1H97v-1H97v-1H97v-1H97v-1H97v-1H97v-1H97v-1H96v-1H95v-1H94v-1H93v-1H92v-1H91v-1H90v-1H90v-1H90v-1H90vz"><title>shore</title></path><path class="shroud-6" d="M53,50H54v1H53z"><title>Ravaged Sands</title></path><path class="shore" d="M54,50H56v1H56v1H56v1H56v1H56v1H51v-1H52v-1H54v-1H54v-1H54vz"><title>shore</title></path><path class="Elemental_Shrine" d="M-7,51H-6v1H-7z"><title>Elemental Shrine</title></path><path class="konoki-5" d="M-6,51H-2v1H-6z"><title>Wildwonder Forest</title></path><path class="silence-3" d="M37,51H40v1H37z"><title>Plateau of Quietude</title></path><path class="konoki-6" d="M5,52H6v1H8v1H8v1H9v1H10v1H6v1H9v1H10v1H10v1H7v1H7v1H0v1H-2v-1H-2v-1H-2v-1H-2v-1H-1v-1H-1v-1H1v-1H3v-1H3v-1H2v-1H3v-1H5vz"><title>Misty Marshland</title></path><path class="konoki-3" d="M6,52H8v1H6z"><title>Fireheart Forest</title></path><path class="shore" d="M-25,53H-24v1H-24v1H-24v1H-23v1H-23v1H-23v1H-22v1H-23v1H-23v1H-23v1H-23v1H-23v1H-23v1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25v-1H-25vz"><title>shore</title></path><path class="shine-2" d="M32,55H33v1H34v1H34v1H34v1H34v1H34v1H34v1H34v1H34v1H34v1H34v1H34v1H35v1H35v1H35v1H35v1H35v1H35v1H35v1H35v1H35v1H34v1H31v1H30v-1H30v-1H30v-1H30v-1H31v-1H31v-1H32v-1H32v-1H31v-1H31v-1H31v-1H31v-1H31v-1H30v-1H30v-1H29v-1H29v-1H28v-1H30v-1H31v-1H31v-1H31v-1H32vz"><title>Savage Hills</title></path><path class="silence-4" d="M44,56H47v1H47v1H49v1H50v1H50v1H51v1H53v1H57v1H56v1H54v1H53v1H53v1H52v1H52v1H53v1H53v1H53v1H53v1H52v1H51v1H51v1H51v1H51v1H50v1H50v1H50v1H50v1H50v1H50v1H50v1H50v1H50v1H50v1H50v1H50v1H50v1H50v1H50v1H49v-1H49v-1H48v-1H48v-1H48v-1H48v-1H48v-1H48v-1H48v-1H48v-1H48v-1H48v-1H48v-1H48v-1H48v-1H48v-1H48v-1H48v-1H49v-1H50v-1H50v-1H50v-1H50v-1H49v-1H49v-1H50v-1H50v-1H50v-1H50v-1H50v-1H50v-1H49v-1H48v-1H46v-1H45v-1H44v-1H44v-1H44vz"><title>Broken Coast</title></path><path class="konoki-1" d="M-12,57H-9v1H-8v1H-5v1H-2v1H-2v1H-2v1H-7v1H-7v1H-8v1H-8v1H-7v1H-11v-1H-11v-1H-12v-1H-12v-1H-12v-1H-12v-1H-13v-1H-13v-1H-14v-1H-13v-1H-12vz"><title>Verdant Woodlands</title></path><path class="syndicate-4" d="M6,57H14v1H13v1H14v1H14v1H16v1H16v1H15v1H13v1H13v1H11v1H9v1H8v-1H6v-1H7v-1H6v-1H6v-1H9v-1H9v-1H10v-1H10v-1H9v-1H6vz"><title>Solace Valley</title></path><path class="syndicate-5" d="M94,57H95v1H96v1H97v1H97v1H97v1H97v1H97v1H97v1H97v1H97v1H96v1H96v1H95v1H95v1H95v1H94v1H93v1H92v-1H91v-1H88v-1H85v-1H87v-1H88v-1H88v-1H94v-1H94v-1H94v-1H94v-1H94v-1H94v-1H94v-1H94v-1H94v-1H94vz"><title>Finny Rocks</title></path><path class="ramen" d="M13,58H14v1H13z"><title>Ramen Stand: Skyview Restaurant</title></path><path class="silence-5" d="M37,58H38v1H39v1H39v1H39v1H39v1H46v1H47v1H48v1H48v1H47v1H47v1H47v1H46v1H45v-1H43v-1H41v-1H39v-1H39v-1H38v-1H38v-1H37v-1H37v-1H36v-1H36v-1H37v-1H37vz"><title>Grey Desert</title></path><path class="silence-5" d="M43,58H44v1H45v1H46v1H47v1H46v1H43v-1H43v-1H43v-1H43v-1H43vz"><title>Grey Desert</title></path><path class="ocean" d="M83,58H86v1H87v1H85v1H84v1H84v1H84v1H81v-1H82v-1H83v-1H83v-1H83v-1H83vz"><title>ocean</title></path><path class="shore" d="M86,58H92v1H92v1H93v1H93v1H93v1H87v1H86v1H86v1H84v1H84v1H83v1H83v1H85v1H88v1H82v1H78v1H78v1H78v1H78v1H78v1H78v1H81v1H81v1H81v1H81v1H81v1H81v1H80v1H80v1H79v1H79v1H78v1H83v1H82v1H76v-1H76v-1H76v-1H76v-1H76v-1H76v-1H77v-1H77v-1H78v-1H78v-1H77v-1H76v-1H76v-1H76v-1H76v-1H76v-1H76v-1H76v-1H76v-1H76v-1H76v-1H81v-1H81v-1H81v-1H81v-1H81v-1H82v-1H82v-1H84v-1H84v-1H84v-1H85v-1H87v-1H86vz"><title>shore</title></path><path class="shine-3" d="M21,59H24v1H28v1H29v1H18v1H16v-1H16v-1H20v-1H21vz"><title>Northern Desert</title></path><path class="ramen" d="M29,59H30v1H29z"><title>Ramen Stand: Shaded Rest Inn</title></path><path class="syndicate-4" d="M7,61H8v1H8v1H7v-1H7vz"><title>Solace Valley</title></path><path class="konoki-6" d="M8,61H9v1H9v1H8v-1H8vz"><title>Misty Marshland</title></path><path class="silence-3 village" d="M40,61H42v1H42v1H40v-1H40vz"><title>Silence Village</title></path><path class="silence-3" d="M42,61H43v1H43v1H42v-1H42vz"><title>Plateau of Quietude</title></path><path class="silence-6" d="M47,61H48v1H49v1H50v1H50v1H50v1H50v1H50v1H50v1H49v1H49v1H50v1H50v1H50v1H50v1H49v1H48v1H48v1H48v1H48v1H48v1H47v-1H47v-1H47v-1H46v-1H45v-1H44v-1H43v-1H42v-1H42v-1H41v-1H46v-1H47v-1H47v-1H47v-1H48v-1H48v-1H47v-1H46v-1H46v-1H47vz"><title>Grey Hills</title></path><path class="shine-4" d="M18,62H19v1H19v1H20v1H21v1H23v1H25v1H21v1H19v-1H20v-1H19v-1H18v-1H17v-1H18v-1H18vz"><title>Sunrise Canyon</title></path><path class="shine-3" d="M19,62H29v1H25v1H30v1H31v1H31v1H27v1H27v1H26v-1H25v-1H23v-1H21v-1H20v-1H19v-1H19vz"><title>Northern Desert</title></path><path class="syndicate-6" d="M-7,63H-6v1H4v1H7v1H6v1H8v1H3v1H3v1H2v1H1v1H0v1H-3v1H-5v-1H-7v-1H-6v-1H-6v-1H-6v-1H-6v-1H-6v-1H-8v-1H-8v-1H-7v-1H-7vz"><title>Ironwood Forest</title></path><path class="konoki-1" d="M-6,63H-4v1H-6z"><title>Verdant Woodlands</title></path><path class="syndicate-6" d="M-4,63H-2v1H-4z"><title>Ironwood Forest</title></path><path class="syndicate-6" d="M0,63H1v1H0z"><title>Ironwood Forest</title></path><path class="konoki-6" d="M1,63H2v1H1z"><title>Misty Marshland</title></path><path class="syndicate-6" d="M2,63H4v1H2z"><title>Ironwood Forest</title></path><path class="konoki-6" d="M4,63H5v1H5v1H4v-1H4vz"><title>Misty Marshland</title></path><path class="syndicate-6" d="M5,63H6v1H6v1H5v-1H5vz"><title>Ironwood Forest</title></path><path class="shine-5" d="M15,63H18v1H17v1H18v1H19v1H16v1H15v1H14v1H14v1H13v1H12v1H12v1H11v1H11v1H10v1H6v-1H6v-1H5v-1H5v-1H5v-1H5v-1H6v-1H6v-1H7v-1H9v-1H11v-1H13v-1H13v-1H15vz"><title>Salient Flats</title></path><path class="Elemental_Shrine" d="M25,63H26v1H25z"><title>Elemental Shrine</title></path><path class="shine-3" d="M26,63H30v1H26z"><title>Northern Desert</title></path><path class="syndicate-3" d="M87,63H88v1H88v1H91v1H86v-1H86v-1H87vz"><title>Dolphin Cove</title></path><path class="shore" d="M88,63H92v1H92v1H88v-1H88vz"><title>shore</title></path><path class="syndicate-3 village" d="M92,64H93v1H93v1H91v-1H92vz"><title>Pirate\'s Hideout</title></path><path class="uncharted" d="M-7,67H-6v1H-7z"><title>uncharted</title></path><path class="shine-6" d="M16,67H20v1H17v1H17v1H25v1H26v1H27v1H22v1H21v1H19v1H11v-1H11v-1H12v-1H12v-1H13v-1H14v-1H14v-1H15v-1H16vz"><title>Shining Dunes</title></path><path class="shine-4" d="M27,67H28v1H28v1H28v1H28v1H29v1H30v1H29v1H29v1H29v1H28v-1H28v-1H28v-1H27v-1H26v-1H25v-1H24v-1H27v-1H27vz"><title>Sunrise Canyon</title></path><path class="shine-3" d="M28,67H31v1H31v1H31v1H32v1H32v1H31v1H31v1H30v-1H30v-1H29v-1H28v-1H28v-1H28v-1H28vz"><title>Northern Desert</title></path><path class="silence-1" d="M35,67H36v1H37v1H39v1H40v1H41v1H42v1H42v1H43v1H44v1H45v1H46v1H47v1H47v1H47v1H47v1H48v1H48v1H48v1H48v1H48v1H48v1H47v-1H47v-1H46v-1H46v-1H45v-1H45v-1H44v-1H44v-1H43v-1H42v-1H41v-1H39v-1H38v-1H37v-1H36v-1H36v-1H35v-1H35v-1H35v-1H35v-1H35vz"><title>Blackpeak Mountains</title></path><path class="syndicate-1" d="M3,68H4v1H6v1H6v1H5v1H5v1H5v1H5v1H6v1H6v1H-3v1H-4v1H-5v-1H-6v-1H-7v-1H-8v-1H-8v-1H-1v-1H0v-1H1v-1H2v-1H3v-1H3vz"><title>Shrouded Savannah</title></path><path class="syndicate-6" d="M4,68H7v1H4z"><title>Ironwood Forest</title></path><path class="shine-6 village" d="M17,68H19v1H19v1H17v-1H17vz"><title>Shine Village</title></path><path class="shine-6" d="M21,68H23v1H24v1H19v-1H21vz"><title>Shining Dunes</title></path><path class="shine-4" d="M23,68H26v1H23z"><title>Sunrise Canyon</title></path><path class="silence-6" d="M40,68H41v1H43v1H45v1H40v-1H40v-1H40vz"><title>Grey Hills</title></path><path class="shore" d="M-25,71H-24v1H-24v1H-25v-1H-25vz"><title>shore</title></path><path class="uncharted" d="M35,72H36v1H36v1H37v1H38v1H39v1H41v1H42v1H43v1H44v1H44v1H45v1H45v1H46v1H46v1H47v1H47v1H48v1H6v-1H7v-1H7v-1H9v-1H34v-1H34v-1H34v-1H35v-1H35v-1H34v-1H33v-1H32v-1H34v-1H35v-1H35v-1H35v-1H35vz"><title>uncharted</title></path><path class="ocean" d="M82,72H83v1H82z"><title>ocean</title></path><path class="shore" d="M83,72H91v1H92v1H86v-1H83vz"><title>shore</title></path><path class="syndicate-1" d="M-7,73H-5v1H-7z"><title>Shrouded Savannah</title></path><path class="syndicate-1" d="M-3,73H-2v1H-3z"><title>Shrouded Savannah</title></path><path class="syndicate-6" d="M-2,73H-1v1H-2z"><title>Ironwood Forest</title></path><path class="shine-1" d="M22,73H24v1H26v1H28v1H30v1H30v1H33v1H34v1H35v1H35v1H34v1H34v1H34v1H32v-1H31v-1H29v-1H28v-1H26v-1H25v-1H23v-1H19v-1H18v-1H19v-1H21v-1H22vz"><title>Southern Desert</title></path><path class="shine-6" d="M24,73H28v1H27v1H26v-1H24vz"><title>Shining Dunes</title></path><path class="shine-1" d="M29,73H30v1H30v1H30v1H29v-1H29v-1H29vz"><title>Southern Desert</title></path><path class="syndicate-2" d="M78,73H79v1H80v1H81v1H82v1H83v1H79v1H78v-1H78v-1H78v-1H78v-1H78v-1H78vz"><title>Banana Bar</title></path><path class="shore" d="M79,73H83v1H84v1H85v1H86v1H86v1H87v1H87v1H87v1H87v1H87v1H87v1H87v1H87v1H86v1H85v1H85v1H84v1H80v-1H81v-1H82v-1H83v-1H83v-1H84v-1H85v-1H85v-1H85v-1H85v-1H84v-1H84v-1H83v-1H82v-1H81v-1H80v-1H79vz"><title>shore</title></path><path class="ocean" d="M83,73H86v1H89v1H90v1H85v-1H84v-1H83vz"><title>ocean</title></path><path class="shine-1" d="M27,74H28v1H27z"><title>Southern Desert</title></path><path class="uncharted" d="M10,76H18v1H19v1H23v1H25v1H26v1H28v1H29v1H31v1H32v1H10v-1H9v-1H9v-1H8v-1H8v-1H8v-1H7v-1H5v-1H10vz"><title>uncharted</title></path><path class="uncharted" d="M-3,77H-2v1H-2v1H-4v-1H-3vz"><title>uncharted</title></path><path class="syndicate-1" d="M-2,77H3v1H1v1H-2v-1H-2vz"><title>Shrouded Savannah</title></path><path class="syndicate-3" d="M3,77H5v1H7v1H8v1H8v1H8v1H9v1H9v1H10v1H9v1H7v1H7v1H4v-1H2v-1H3v-1H4v-1H3v-1H1v-1H0v-1H-2v-1H-2v-1H1v-1H3vz"><title>Darkland Savannah</title></path><path class="shine-1" d="M31,77H32v1H31z"><title>Southern Desert</title></path><path class="shore" d="M79,78H80v1H79z"><title>shore</title></path><path class="syndicate-2" d="M80,78H84v1H84v1H85v1H85v1H85v1H85v1H84v1H83v1H83v1H82v1H81v1H80v1H78v-1H79v-1H79v-1H80v-1H80v-1H81v-1H81v-1H81v-1H81v-1H81v-1H81v-1H80vz"><title>Banana Bar</title></path><path class="Elemental_Shrine" d="M47,81H48v1H47z"><title>Elemental Shrine</title></path><path class="syndicate-3 village" d="M4,88H6v1H4z"><title>Poacher\'s Camp</title></path>';

            $GLOBALS['template']->assign('map_overlay', $svg);
            $GLOBALS['template']->assign('map', 'territory');
        }

    }

}
new travel();