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

/*
$GLOBALS['DebugTool']->push(VARIABLE, MESSAGE, __METHOD__, __FILE__, __LINE__);
 */

class DebugTool
{
    public $variable_stack = array();

    public function __destruct()
    {
        $this->popAll();
    }

    public function popAll()
    {
        $message = "";

        foreach($this->variable_stack as $key => $variable)
        {
            $item    = $variable[0];
            $msg     = $variable[1];
            $method  = $variable[2];
            $file    = $variable[3];
            $line    = $variable[4];

            if($msg == "")
                $msg = " (\/)=(^)o(^)=(\/) ";

            //header
            $message .= "}-=<<||>>=<<||>>=<[".$method."]>=<<||>>=<<||>>=-{";
            $message .= "\n";

            //title
            $message .= "File: ".$file;
            $message .= "\n";

            $message .= "Line: ".$line;
            $message .= "\n";

            $message .= "Entry #".$key.": ".$msg;
            $message .= "\n";

            //body
            ob_start();
            var_dump($item);
            $message .= ob_get_clean();
            

            //footer
            $message .= "\n";
            $message .= "}-=<<||>>=<<||>>=<[".$method."]>=<<||>>=<<||>>=-{";
            $message .= "\n";
            $message .= "\n";
            $message .= "\n";
            $message .= "\n";
        }

        if($message != "")
        {
            echo "<script type='text/javascript'>alert('".$message."');</script>";
            error_log(' DEBUG TOOL: '.$message);
        }

        $this->variable_stack = array();
    }

    public function push($item, $message, $method, $file, $line)
    {
        $this->variable_stack[] = array($item,$message,$method,$file,$line);
    }

}