<?php

/**
 * Debugging function that accepts infinite arguments and does a var_dump on each one.
 */
if( !function_exists( 'd' ) )
{
    function d()
    {
        $args = func_get_args();

        if (count($args) > 0)
        {
            echo "\n<pre>";

            foreach ($args as $key => $val)
            {
                echo "\n\n========== Degugging Item " . ($key + 1) . " ==========\n\n";
                var_dump($val);
            }

            echo "\n\n========== END OF DEBUGGING ==========";
            echo "\n</pre>\n\n";
        }
    }

    if( !function_exists( 'GetGravatar' ) )
    {
        function GetGravatar($email, $s = 80, $d = 'mm', $r = 'g', $img = false, $atts = array())
        {
            $url = 'http://www.gravatar.com/avatar/';
            $url .= md5(strtolower(trim($email)));
            $url .= "?s=$s&d=$d&r=$r";
            if ($img)
            {
                $url = '<img src="' . $url . '"';
                foreach ($atts as $key => $val)
                    $url .= ' ' . $key . '="' . $val . '"';
                $url .= ' />';
            }
            return $url;
        }
    }
}