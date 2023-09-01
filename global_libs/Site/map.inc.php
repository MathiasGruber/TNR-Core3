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

abstract class mapfunctions {

    // Definitions needed for drawing on map / i.e. sizes of blocks etc
    public static $bsX = 16.3;
    public static $bsY = 14.5;
    public static $lw = 1;
    public static $topX = 18;
    public static $topY = 15;

    // Array with all the territory information
    public static function getMapInformation(){
        return array(

            // Single Spots
            array( "id" => "ruin", "name" => "Ruins of Current", "positions" => array("12.14") ),
            array( "id" => "ruin", "name" => "Ruins of Glacier", "positions" => array("9.19") ),
            array( "id" => "ruin", "name" => "Ruins of Horizon", "positions" => array("18.20") ),
            array( "id" => "shop", "name" => "Ramen Shop", "positions" => array("6.7") ),
            array( "id" => "shop", "name" => "Ramen Shop", "positions" => array("16.5") ),
            array( "id" => "shop", "name" => "Ramen Shop", "positions" => array("21.8") ),
            array( "id" => "shop", "name" => "Ramen Shop", "positions" => array("18.17") ),
            array( "id" => "shop", "name" => "Ramen Shop", "positions" => array("8.14") ),

            // Villages
            array( "id" => "village", "owner" => "Konoki", "name" => "Konoki", "positions" => array("8.3"), "connect" => "Fireheart Forest" ),
            array( "id" => "village", "owner" => "Silence", "name" => "Silence", "positions" => array("23.4"), "connect" => "Plateau of Quietude" ),
            array( "id" => "village", "owner" => "Shroud", "name" => "Shroud", "positions" => array("4.12"), "connect" => "Misty Morass" ),
            array( "id" => "village", "owner" => "Shine", "name" => "Shine", "positions" => array("21.12"), "connect" => "Shining Dunes" ),
            array( "id" => "village", "owner" => "Samui", "name" => "Samui", "positions" => array("11.17"), "connect" => "Tornado Valley" ),
            array( "id" => "village", "owner" => "Syndicate", "name" => "City of Mei", "positions" => array("26.11") ),

            // Villages Lands (unclaimable)
            array( "id" => "villageLand", "owner" => "Konoki", "startOwner" => "Konoki", "name" => "Fireheart Forest", "positions" =>
                array("8.1","7.2","8.2","9.2","6.3","7.3","8.3","9.3","10.3","6.4","7.4","8.4","9.4","10.4","9.5"),
                "connect" => "Konoki"),
            array( "id" => "villageLand", "owner" => "Silence", "startOwner" => "Silence", "name" => "Plateau of Quietude", "positions" =>
                array("25.1","24.2","25.2","22.3","23.3","24.3","25.3","22.4","23.4","24.4","25.4","22.5","23.5","24.5","24.6"),
                "connect" => "Silence" ),
            array( "id" => "villageLand", "owner" => "Shroud", "startOwner" => "Shroud", "name" => "Misty Morass", "positions" =>
                array("3.10","4.10","1.11","2.11","3.11","4.11","2.12","3.12","4.12","5.12","2.13","3.13","4.13","3.14","4.14"),
                "connect" => "Shroud" ),
            array( "id" => "villageLand", "owner" => "Shine", "startOwner" => "Shine", "name" => "Shining Dunes", "positions" =>
                array("20.11","21.11","22.11","20.12","21.12","22.12","20.13","21.13","22.13","23.13","22.14","23.14","24.14","23.15","24.15"),
                "connect" => "Shine" ),
            array( "id" => "villageLand", "owner" => "Samui", "startOwner" => "Samui", "name" => "Tornado Valley", "positions" =>
                array("11.15","9.16","10.16","11.16","12.16","8.17","9.17","10.17","11.17","12.17","13.17","9.18","10.18","11.18","12.18"),
                "connect" => "Samui" ),

            // Territories
            array( "id" => "1", "name" => "Forests End", "startOwner" => "Konoki",  "positions" =>
                array("1.1","2.1","1.2","2.2","3.2","4.2","1.3","2.3","3.3","1.4","2.4","3.4","1.5","2.5","1.6") ),
            array( "id" => "2", "name" => "Wildwonder Forest", "startOwner" => "Konoki", "positions" =>
                array("3.1","4.1","5.1","6.1","7.1","5.2","6.2","4.3","5.3","4.4","5.4","4.5","5.5","6.5","5.6") ),
            array( "id" => "3", "name" => "Verdant Woodlands", "startOwner" => "Konoki", "positions" =>
                array("9.1","10.1","11.1","12.1","13.1","14.1","10.2","11.2","12.2","13.2","11.3","12.3","13.3","11.4","12.4") ),
            array( "id" => "4", "name" => "Blackpeak Mountains", "startOwner" => "Silence", "positions" =>
                array("15.1","16.1","17.1","18.1","19.1","16.2","17.2","18.2","19.2","16.3","17.3","18.3","16.4","17.4","17.5") ),
            array( "id" => "5", "name" => "Fortune Mountains", "startOwner" => "Silence", "positions" =>
                array("20.1","21.1","22.1","23.1","24.1","20.2","21.2","22.2","23.2","19.3","20.3","21.3","19.4","20.4","21.4") ),
            array( "id" => "6", "name" => "Oakwood Forest", "startOwner" => "Konoki", "positions" =>
                array("14.2","15.2","14.3","15.3","13.4","14.4","15.4","10.5","11.5","12.5","13.5","14.5","10.6","11.6","12.6") ),
            array( "id" => "7", "name" => "Deadwood Forest", "startOwner" => "Syndicate", "positions" =>
                array("18.4","15.5","18.5","13.6","14.6","15.6","16.6","17.6","18.6","13.7","14.7","15.7","16.7","18.7","16.8") ),
            array( "id" => "8", "name" => "Ironwood Forest", "startOwner" => "Syndicate", "positions" =>
                array("3.5","2.6","3.6","4.6","1.7","2.7","3.7","4.7","1.8","2.8","3.8","1.9","2.9","1.10","2.10") ),
            array( "id" => "9", "name" => "Misty Marshland", "startOwner" => "Konoki", "positions" =>
                array("7.5","8.5","6.6","7.6","8.6","9.6","7.7","8.7","9.7","10.7","11.7","12.7","9.8","10.8","11.8") ),
            array( "id" => "10", "name" => "Grey Hills", "startOwner" => "Silence", "positions" =>
                array("19.5","20.5","21.5","19.6","20.6","21.6","22.6","23.6","19.7","20.7","21.7","22.7","23.7","20.8","22.8") ),
            array( "id" => "11", "name" => "Grey Desert", "startOwner" => "Silence", "positions" =>
                array("25.5","25.6","24.7","25.7","23.8","24.8","25.8","22.9","23.9","24.9","25.9","22.10","23.10","24.10","24.11") ),
            array( "id" => "12", "name" => "Shrouded Savanah", "startOwner" => "Syndicate", "positions" =>
                array("5.7","4.8","5.8","6.8","7.8","8.8","3.9","4.9","5.9","6.9","7.9","5.10","6.10","5.11","6.11") ),
            array( "id" => "13", "name" => "Deadwood Hillside", "startOwner" => "Syndicate", "positions" =>
                array("17.7","17.8","18.8","19.8","17.9","18.9","19.9","20.9","21.9","17.10","18.10","19.10","20.10","21.10","19.11") ),
            array( "id" => "14", "name" => "Gamblers Valley", "startOwner" => "Syndicate", "positions" =>
                array("12.8","13.8","14.8","15.8","12.9","13.9","14.9","15.9","16.9","11.10","12.10","13.10","14.10","15.10","13.11") ),
            array( "id" => "15", "name" => "Darkland Savannah", "startOwner" => "Syndicate", "positions" =>
                array("8.9","9.9","10.9","11.9","7.10","8.10","9.10","10.10","7.11","8.11","9.11","10.11","11.11","8.12","9.12") ),
            array( "id" => "16", "name" => "Solance Valley", "startOwner" => "Syndicate", "positions" =>
                array("16.10","15.11","16.11","17.11","18.11","15.12","16.12","17.12","18.12","19.12","15.13","16.13","18.13","19.13","16.14") ),
            array( "id" => "17", "name" => "Northern Desert", "startOwner" => "Shine", "positions" =>
                array("25.10","23.11","25.11","23.12","24.12","25.12","24.13","25.13","25.14","25.15","23.16","24.16","25.16","24.17","25.17") ),
            array( "id" => "18", "name" => "Windswept Grasslands", "startOwner" => "Samui", "positions" =>
                array("12.11","14.11","10.12","11.12","12.12","13.12","14.12","10.13","11.13","12.13","13.13","14.13","10.14","11.14","10.15")),
            array( "id" => "19", "name" => "Swamp of Sorrow", "startOwner" => "Shroud", "positions" =>
                array("6.12","7.12","5.13","6.13","7.13","8.13","9.13","5.14","6.14","7.14","9.14","5.15","7.15","8.15","9.15") ),
            array( "id" => "20", "name" => "Spirit Lagoon", "startOwner" => "Shroud", "positions" =>
                array("1.12","1.13","1.14","2.14","1.15","2.15","3.15","4.15","1.16","2.16","3.16","4.16","2.17","3.17","2.18") ),
            array( "id" => "21", "name" => "Savage Hills", "startOwner" => "Shine", "positions" =>
                array("17.13","17.14","18.14","19.14","20.14","21.14","17.15","18.15","19.15","20.15","21.15","22.15","17.16","18.16","20.16") ),
            array( "id" => "22", "name" => "Whirling Valley", "startOwner" => "Samui", "positions" =>
                array("12.14","13.14","14.14","15.14","12.15","13.15","14.15","15.15","16.15","13.16","14.16","15.16","16.16","14.17","15.17") ),
            array( "id" => "23", "name" => "Mistmire", "startOwner" => "Shroud", "positions" =>
                array("6.15","5.16","6.16","7.16","8.16","5.17","6.17","7.17","6.18","7.18","8.18","6.19","7.19","5.20","6.20") ),
            array( "id" => "24", "name" => "Southern Desert", "startOwner" => "Shine", "positions" =>
                array("21.16","22.16","22.17","23.17","22.18","23.18","24.18","25.18","23.19","24.19","25.19","22.20","23.20","24.20","25.20") ),
            array( "id" => "25", "name" => "Sunrise Canyon", "startOwner" => "Shine", "positions" =>
                array("19.16","19.17","20.17","21.17","19.18","20.18","21.18","19.19","20.19","21.19","22.19","18.20","19.20","20.20","21.20") ),
            array( "id" => "26", "name" => "Hyuogaan Mountains", "startOwner" => "Samui", "positions" =>
                array("16.17","17.17","15.18","16.18","17.18","18.18","14.19","15.19","16.19","17.19","18.19","14.20","15.20","16.20","17.20") ),
            array( "id" => "27", "name" => "Savage Lakes", "startOwner" => "Shroud", "positions" =>
                array("1.17","4.17","1.18","3.18","4.18","5.18","1.19","2.19","3.19","4.19","5.19","1.20","2.20","3.20","4.20") ),
            array( "id" => "28", "name" => "Frozen Highlands", "startOwner" => "Samui", "positions" =>
                array("13.18","14.18","8.19","9.19","10.19","11.19","12.19","13.19","7.20","8.20","9.20","10.20","11.20","12.20","13.20")
            )

        );
    }

    // Get ocean tiles
    // Rows in the array correspond to latitude coordinates.
    // Ranges for each latitude correspond to the tiles that
    // are ocean.
    public static function getOceanTiles(){
        return array(
	 1 => array("-100|100"),
	 2 => array("-100|100"),
	 3 => array("-100|100"),
	 4 => array("-100|-99", "-93|100"),
	 5 => array("-92|100"),
	 6 => array("-92|86", "88|91", "93|100"),
	 7 => array("-100|-99", "-86|86", "94|100"),
	 8 => array("-83|86", "97|100"),
	 9 => array("-100|-97", "-83|83", "97|100"),
	 10 => array("-100|-97", "-83|33", "38|80", "81|83", "97|100"),
	 11 => array("-100|-97", "-83|33", "38|80", "97|100"),
	 12 => array("-100|-92", "-82|30", "45|81", "99|100"),
	 13 => array("-100|-91", "-79|24", "45|81", "99|100"),
	 14 => array("-100|-91", "-79|20", "45|81", "97|100"),
	 15 => array("-100|-91", "-79|18", "46|83", "86|92", "97|100"),
	 16 => array("-100|-91", "-79|18", "46|83", "86|92", "97|100"),
	 17 => array("-100|-91", "-79|16", "46|92", "93|94", "97|100"),
	 18 => array("-100|-93", "-82|15", "46|90", "93|94", "97|100"),
	 19 => array("-100|-93", "-82|15", "46|100"),
	 20 => array("-100|-93", "-82|14", "46|100"),
	 21 => array("-100|-91", "-82|14", "46|100"),
	 22 => array("-100|-91", "-82|14", "46|100"),
	 23 => array("-100|-89", "-82|14", "49|100"),
	 24 => array("-100|-89", "-81|14", "49|100"),
	 25 => array("-100|-89", "-81|14", "49|100"),
	 26 => array("-100|-89", "-81|14", "49|100"),
	 27 => array("-100|-89", "-81|-46", "-41|15", "50|100"),
	 28 => array("-100|-89", "-81|-51", "-41|15", "50|100"),
	 29 => array("-100|-89", "-81|-51", "-39|15", "50|100"),
	 30 => array("-100|-89", "-81|-51", "-39|16", "50|100"),
	 31 => array("-100|-89", "-81|-51", "-35|-32", "-28|17", "53|100"),
	 32 => array("-100|-89", "-81|-54", "-34|-32", "-26|17", "53|100"),
	 33 => array("-100|-89", "-81|-54", "-26|17", "53|100"),
	 34 => array("-100|-89", "-81|-54", "-26|17", "57|100"),
	 35 => array("-100|-89", "-81|-57", "-26|17", "58|100"),
	 36 => array("-100|-89", "-81|-57", "-20|17", "59|100"),
	 37 => array("-100|-89", "-81|-57", "-20|17", "59|100"),
	 38 => array("-100|-88", "-81|-57", "-18|17", "59|100"),
	 39 => array("-100|-88", "-82|-57", "-18|17", "59|100"),
	 40 => array("-100|-88", "-83|-57", "-18|17", "59|100"),
	 41 => array("-100|-86", "-83|-60", "-18|17", "59|100"),
	 42 => array("-100|-86", "-83|-60", "-7|17", "59|100"),
	 43 => array("-100|-86", "-83|-60", "-2|17", "59|100"),
	 44 => array("-100|-86", "-83|-60", "3|17", "62|100"),
	 45 => array("-100|-86", "-83|-60", "3|17", "62|100"),
	 46 => array("-100|-86", "-84|-60", "4|17", "62|100"),
	 47 => array("-100|-61", "4|17", "68|100"),
	 48 => array("-100|-61", "4|16", "69|100"),
	 49 => array("-100|-61", "6|16", "71|100"),
	 50 => array("-100|-61", "6|16", "71|100"),
	 51 => array("-100|-61", "6|16", "71|100"),
	 52 => array("-100|-63", "6|16", "71|100"),
	 53 => array("-100|-62", "6|16", "71|100"),
	 54 => array("-100|-59", "6|16", "73|100"),
	 55 => array("-100|-59", "6|16", "73|100"),
	 56 => array("-100|-58", "6|16", "73|100"),
	 57 => array("-100|-58", "6|16", "73|100"),
	 58 => array("-100|-58", "6|16", "73|100"),
	 59 => array("-100|-56", "6|16", "73|100"),
	 60 => array("-100|-55", "6|15", "77|100"),
	 61 => array("-100|-55", "6|15", "77|100"),
	 62 => array("-100|-51", "6|15", "79|100"),
	 63 => array("-100|-48", "6|15", "79|100"),
	 64 => array("-100|-48", "6|15", "79|100"),
	 65 => array("-100|-48", "6|15", "81|100"),
	 66 => array("-100|-48", "6|13", "81|100"),
	 67 => array("-100|-48", "6|13", "81|100"),
	 68 => array("-100|-48", "6|13", "81|100"),
	 69 => array("-100|-47", "6|13", "81|100"),
	 70 => array("-100|-47", "6|12", "81|100"),
	 71 => array("-100|-47", "6|12", "81|100"),
	 72 => array("-100|-47", "9|12", "81|100"),
	 73 => array("-100|-44", "9|12", "81|100"),
	 74 => array("-100|-44", "11|12", "81|100"),
	 75 => array("-100|-44", "11|12", "81|100"),
	 76 => array("-100|-43", "11|12", "81|100"),
	 77 => array("-100|-43", "85|100"),
	 78 => array("-100|-43", "85|100"),
	 79 => array("-100|-43", "85|100"),
	 80 => array("-100|-41", "85|100"),
	 81 => array("-100|-42", "85|100"),
	 82 => array("-100|-42", "85|100"),
	 83 => array("-100|-42", "85|100"),
	 84 => array("-100|-42", "85|100"),
	 85 => array("-100|-42", "85|100"),
	 86 => array("-100|-42", "87|100"),
	 87 => array("-100|-95", "-94|-42", "87|100"),
	 88 => array("-100|-96", "-93|-42", "87|100"),
	 89 => array("-100|-97", "-92|-42", "87|100"),
	 90 => array("-100|-96", "-93|-42", "87|100"),
	 91 => array("-100|-95", "-94|-42", "87|100"),
	 92 => array("-100|-43", "87|100"),
	 93 => array("-100|-43", "88|100"),
	 94 => array("-100|-43", "88|100"),
	 95 => array("-100|-45", "88|100"),
	 96 => array("-100|-46", "88|100"),
	 97 => array("-100|-48", "88|100"),
	 98 => array("-100|-49", "88|100"),
	 99 => array("-100|-49", "88|100"),
	 100 => array("-100|-49", "88|100"),
	 101 => array("-100|-49", "88|100"),
	 102 => array("-100|-49", "88|100"),
	 103 => array("-100|-49", "88|100"),
	 104 => array("-100|-49", "88|100"),
	 105 => array("-100|-50", "88|100"),
	 106 => array("-100|-50", "88|100"),
	 107 => array("-100|-50", "88|100"),
	 108 => array("-100|-50", "88|100"),
	 109 => array("-100|-50", "88|100"),
	 110 => array("-100|-50", "92|100"),
	 111 => array("-100|-50", "92|100"),
	 112 => array("-100|-50", "29|30", "93|100"),
	 113 => array("-100|-50", "93|100"),
	 114 => array("-100|-51", "93|100"),
	 115 => array("-100|-51", "93|100"),
	 116 => array("-100|-51", "93|100"),
	 117 => array("-100|-51", "93|100"),
	 118 => array("-100|-51", "91|100"),
	 119 => array("-100|-51", "91|100"),
	 120 => array("-100|-51", "91|100"),
	 121 => array("-100|-51", "91|100"),
	 122 => array("-100|-52", "91|100"),
	 123 => array("-100|-52", "19|20", "91|100"),
	 124 => array("-100|-52", "91|100"),
	 125 => array("-100|-52", "91|100"),
	 126 => array("-100|-52", "91|100"),
	 127 => array("-100|-52", "91|100"),
	 128 => array("-100|-52", "91|100"),
	 129 => array("-100|-52", "91|100"),
	 130 => array("-100|-52", "91|100"),
	 131 => array("-100|-52", "91|100"),
	 132 => array("-100|-52", "91|100"),
	 133 => array("-100|-51", "91|100"),
	 134 => array("-100|-51", "91|100"),
	 135 => array("-100|-51", "91|100"),
	 136 => array("-100|-51", "91|100"),
	 137 => array("-100|-51", "91|100"),
	 138 => array("-100|-48", "-46|-45", "95|100"),
	 139 => array("-100|-45", "95|100"),
	 140 => array("-100|-43", "95|100"),
	 141 => array("-100|-41", "95|100"),
	 142 => array("-100|-41", "95|100"),
	 143 => array("-100|-41", "95|100"),
	 144 => array("-100|-40", "95|100"),
	 145 => array("-100|-40", "95|100"),
	 146 => array("-100|-38", "95|100"),
	 147 => array("-100|-37", "95|100"),
	 148 => array("-100|-37", "96|100"),
	 149 => array("-100|-35", "96|100"),
	 150 => array("-100|-35", "96|100"),
	 151 => array("-100|-35", "96|100"),
	 152 => array("-100|-22", "93|100"),
	 153 => array("-100|-22", "93|100"),
	 154 => array("-100|-22", "93|100"),
	 155 => array("-100|-18", "92|100"),
	 156 => array("-100|-18", "92|100"),
	 157 => array("-100|-18", "92|100"),
	 158 => array("-100|-18", "89|100"),
	 159 => array("-100|-18", "89|100"),
	 160 => array("-100|-17", "89|100"),
	 161 => array("-100|-17", "89|100"),
	 162 => array("-100|-17", "89|100"),
	 163 => array("-100|-80", "-72|-17", "89|100"),
	 164 => array("-100|-83", "-71|-19", "32|53", "89|100"),
	 165 => array("-100|-84", "-71|-19", "22|53", "89|100"),
	 166 => array("-100|-84", "-70|-17", "22|59", "89|100"),
	 167 => array("-100|-84", "-70|-17", "22|70", "89|100"),
	 168 => array("-100|-81", "-69|-17", "22|70", "89|100"),
	 169 => array("-100|-78", "-76|-70", "-69|-17", "20|72", "89|100"),
	 170 => array("-100|-12", "20|72", "87|100"),
	 171 => array("-100|-12", "11|79", "87|100"),
	 172 => array("-100|-97", "-94|-7", "11|100"),
	 173 => array("-100|-98", "-92|-7", "11|100"),
	 174 => array("-91|-7", "3|24", "31|100"),
	 175 => array("-100|-99", "-91|23", "31|100"),
	 176 => array("-100|-99", "-94|-83", "-80|23", "32|100"),
	 177 => array("-100|-99", "-94|-84", "-79|7", "17|22", "29|100"),
	 178 => array("-100|-98", "-95|-85", "-77|4", "19|21", "30|100"),
	 179 => array("-100|-86", "-78|-67", "-65|7", "17|21", "30|100"),
	 180 => array("-100|-87", "-79|-67", "-65|0", "5|9", "17|22", "29|100"),
	 181 => array("-100|-87", "-79|-67", "-65|0", "5|22", "29|100"),
	 182 => array("-100|-87", "-78|-68", "-66|0", "5|25", "28|100"),
	 183 => array("-100|-88", "-79|-68", "-66|3", "7|16", "21|100"),
	 184 => array("-100|-89", "-72|-70", "-66|3", "12|16", "22|100"),
	 185 => array("-100|-89", "-74|-70", "-66|3", "22|100"),
	 186 => array("-100|-89", "-66|11", "21|100"),
	 187 => array("-100|-95", "-94|-89", "-67|12", "21|100"),
	 188 => array("-100|-95", "-93|-87", "-67|100"),
	 189 => array("-100|-97", "-92|-87", "-67|100"),
	 190 => array("-100|-95", "-92|-87", "-71|100"),
	 191 => array("-100|-82", "-75|100"),
	 192 => array("-100|-70", "-68|100"),
	 193 => array("-100|-70", "-68|100"),
	 194 => array("-100|-84", "-83|-70", "-68|100"),
	 195 => array("-100|-86", "-80|100"),
	 196 => array("-100|-87", "-80|100"),
	 197 => array("-100|-88", "-79|100"),
	 198 => array("-100|-90", "-81|100"),
	 199 => array("-100|-86", "-81|100"),
	 200 => array("-100|100")
        );
    }

    // Check if x/y position is in the ocean
    public static function isOceanTile( $oceanTiles, $x, $y ){


        //aldin
        if($x <= -100 || $x >= 100 || $y <= -100 || $y >= 100)
        {
            return true;
        }

        $latitudeIndex = $y + 100;
        if( isset($oceanTiles[$latitudeIndex]) ){
            foreach( $oceanTiles[$latitudeIndex] as $entry ){
                $minMax = explode("|", $entry);
                if( $x > $minMax[0] && $x < $minMax[1] ){
                    return true;
                }
            }

        }
        return false;
    }

    // Location manager
    public static function create_map( $callDir = "." ) {

        // Create image
        $image = imagecreatefromjpeg($callDir."/images/maps/core3mapV1.jpg");

        // Block Size in pixels for each square
        $bsX = self::$bsX;
        $bsY = self::$bsY;
        $lw = self::$lw;
        $topX = self::$topX;
        $topY = self::$topY;


        // Get color of village
        function getcolor( $owner ,$image) {
            global $red, $grey, $yellow, $lblue, $blue, $green, $sand, $neutral;

            switch ($owner) {
                case "Konoki": return imagecolorallocatealpha($image, 0, 120, 0, 80); // green
                    break;
                case "Silence": return imagecolorallocatealpha($image, 66, 66, 66, 50); // grey
                    break;
                case "Shroud": return imagecolorallocatealpha($image, 0, 0, 120, 80); //blue
                    break;
                case "Shine": return imagecolorallocatealpha($image, 210, 0, 0, 80); //red
                    break;
                case "Samui": return  imagecolorallocatealpha($image, 189, 148, 70, 80); // sand
                    break;
                case "Syndicate": return imagecolorallocatealpha($image, 0, 0, 0, 50); // dark
                    break;
                case "Constant": return imagecolorallocatealpha($image, 146, 226, 226, 80); //lblue
                    break;
                default: return imagecolorallocatealpha($image, 0, 0, 0, 100); //neutral
                    break;
            }
        }

        // Top Left Reference Block Coordinates of Map
        $corner1 = array($topX          , $topY             ); // Top Left Corner (X, Y)
        $corner2 = array($corner1[0]+$bsX, $corner1[1]    ); // Top Right Corner (X, Y)
        $corner3 = array($corner1[0]+$bsX, $corner1[1]+$bsY); // Bottom Right Corner (X, Y)
        $corner4 = array($corner1[0]    , $corner1[1]+$bsY); // Bottom Left Corner (X, Y)

        // Data
        $mapInformation = self::getMapInformation();
        $locationInformation = cachefunctions::getLocations(true);
        $alliances = cachefunctions::getAlliances(true);

        // If vassals are set, overwrite owners
        $owners = array();
        foreach( $alliances as $alliance ){
            if( !empty($alliance['vassal']) ){
                $owners[ $alliance['vassal'] ] = $alliance['name'];
            }
        }
        $newLocationInformation = array();
        foreach( $locationInformation as $key => $location ){
            if( array_key_exists($location['owner'], $owners) ){
                $location['owner'] = $owners[ $location['owner'] ];
            }
            $newLocationInformation[ $key ] = $location;
        }
        $locationInformation = $newLocationInformation;

        // Location Boxes by Pixel Dimensions
        for ($x = 1; $x <= 25; $x++) { // Longitudes (East/West) X
            for ($y = 1; $y <= 20; $y++) { // Latitudes (North/South) Y

                // Temporary Array Storing Cornes for this Block
                $temp = array(
                    $corner1[0], $corner1[1],
                    $corner2[0], $corner2[1],
                    $corner3[0], $corner3[1],
                    $corner4[0], $corner4[1],
                );

                // Information on this spot:
                if( $locInfo = self::getTerritoryInformation( array( "x.y" => $x.".".$y ) , $mapInformation, $locationInformation ) ){
                    imagefilledpolygon(
                            $image,
                            $temp,
                            count($temp) * 0.5,
                            getcolor( $locInfo['owner'] , $image)
                    );
                }

                $corner1[1] = $corner2[1] += $bsY+$lw; // Increment Top Latitude Y Coordinates
                $corner3[1] = $corner4[1] += $bsY+$lw; // Increment Bottom Latitude Y Coordinates
            }
            $corner1[0] = $corner4[0] += $bsX+$lw; // Increment Left Longitude X Coordinates
            $corner2[0] = $corner3[0] += $bsX+$lw; // Increment Right Longitude X Coordinates
            $corner1[1] = $corner2[1] = $topY; // Reset Top Latitude Y Coordinates
            $corner3[1] = $corner4[1] = $topY+$bsY; // Reset Bottom Latitude Y Coordinates
        }

        // Finish it off
        $gold = imagecolorallocate($image, 255, 240, 00);
        imagejpeg($image, $callDir."/images/maps/core3mapV1.jpeg");
        imagedestroy($image);
    }

    // Function for changing the owner of a location
    public static function changeOwner( $terrID, $terrName, $newOwner  ){
        if(
            $GLOBALS['database']->execute_query("UPDATE `locations` SET `owner` = '" . $newOwner . "' WHERE `id` = '" . $terrID . "' LIMIT 1") &&
            $GLOBALS['database']->execute_query(" UPDATE `users` 
                        SET `notifications` = CONCAT('id:16;duration:none;text:Todays global event: ".functions::store_content(" The territory '" . $terrName . "' was claimed by " . $newOwner . ". ").";dismiss:yes;buttons:none;select:none;//',`notifications`)"
                )
        ){
            return true;
        }
        return false;
    }

    // Function which returns the ID, name and owner of a territory.
    // SearchTerm is an array with following entires "x.y" => position, and/or "id" => territory ID
    public static function getTerritoryInformation( $searchTerm , $mapInformation, $locationInformation ){


        foreach( $mapInformation as $location ){
            if(
                    ( !isset($searchTerm['x.y']) || in_array( $searchTerm['x.y'], $location['positions'], true)  ) &&
                    ( !isset($searchTerm['id']) || $searchTerm['id'] == $location['id'] )
            ){
                // Determine Owner
                if( ctype_digit($location['id']) || $location['id'] == "villageLand" ){
                    $locationDbResult = $locationInformation[ $location['name'] ];
                    $owner = $locationDbResult['owner'];
                }
                elseif( $location['id'] == "village" ){
                    $owner = $location['owner'];
                }
                else{
                    $owner = "Constant";
                }

                // Get the location traits
                $location["trait_1"] = $location["trait_2"] = "";
                if( ctype_digit($location['id']) || $location['id'] == "villageLand"  ){
                    $location["trait_1"] = $locationInformation[ $location['name'] ]['trait_1'];
                    $location["trait_2"] = $locationInformation[ $location['name'] ]['trait_2'];
                }

                if( isset( $owner ) ){
                    return array(
                        "id"=>$location['id'],
                        "name"=>$location['name'] ,
                        "trait_1"=>$location['trait_1'] ,
                        "trait_2"=>$location['trait_2'] ,
                        "owner" => $owner );
                }
            }
        }

        return false;
    }

    // Return a query selection for the territory of the given position
    public static function getLocationSelectionQuery($positions , $alliance) {
        // Get information
        $mapInformation = self::getMapInformation();
        $locationInformation = cachefunctions::getLocations();

        // If this area is owned by the user village or ally village
        $information = self::getTerritoryInformation( array("x.y" => $positions[0]) , $mapInformation, $locationInformation);

        if(isset($information, $information['owner'])) {
            if($information['owner'] === $GLOBALS['userdata'][0]['village'] ||
                (isset($alliance[0][$information['owner']]) && (int)$alliance[0][ $information['owner'] ] === 1)
                || $information['owner'] === $GLOBALS['userdata'][0]['vassal']) {
                // Add all the positions
                foreach($mapInformation as $location) {
                    if($location['name'] === $GLOBALS['userdata'][0]['location']
                        || (isset($location['connect']) && $location['connect'] === $GLOBALS['userdata'][0]['location'])) {
                        foreach($location['positions'] as $position) { $positions[] = $position; }
                    }
                }
            }
        }

        // Create search query
        $search = "";
        foreach( $positions as $position ){
            $coord = explode(".", $position);
            $search .= empty($search) ? "(latitude = '" . $coord[1] . "' AND  longitude = '" . $coord[0] . "')"
                : " OR (latitude = '" . $coord[1] . "' AND  longitude = '" . $coord[0] . "')";
        }

        // Return the result
        return $search;
    }
}