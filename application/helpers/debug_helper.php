<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('dump')) {
    function dump()
    {
        $numargs = func_num_args();
        $number_of_argument = $numargs;

        echo '<div style="border: 2px solid #f00; padding: 0px 10px; font-size: 1.1em; position: relative;padding-top:  10px;"><pre class="debug" style="white-space: pre-wrap;">';
        echo '<b style="background-color: rgba(0,0,0,1);line-height: 2em;color: #fff;display: block;position: absolute;top: 0;left:  0;right: 0;border-bottom:  1px solid #f00; padding: 0px 10px;">Line ' . __LINE__ . ' in File ' . __FILE__ . '</b><br/>';
        for ($i = 0; $i < $numargs; $i++) {
            echo '<b style="background-color: rgba(0,0,0,0.4); line-height: 2em; width: 100%; color: #fff; display: block;">&nbsp;Argument ' . ($i + 1) . ' of ' . $number_of_argument . ' :</b><br/>';
            echo '<div style="padding: 0px 10px;">';
            var_dump(func_get_arg($i));
            echo '</div>';
            echo '<br/>';
        }
        echo '</pre></div>';
    }
}

// if (!function_exists('dd')) {
//     function dd()
//     {
//         $numargs = func_num_args();
//         $number_of_argument = $numargs;
//
//         echo '<div style="border: 2px solid #f00; padding: 0px 10px; font-size: 1.1em; position: relative;padding-top:  10px;"><pre class="debug" style="white-space: pre-wrap;">';
//         echo '<b style="background-color: rgba(0,0,0,1);line-height: 2em;color: #fff;display: block;position: absolute;top: 0;left:  0;right: 0;border-bottom:  1px solid #f00; padding: 0px 10px;">Line ' . __LINE__ . ' in File ' . __FILE__ . '</b><br/>';
//         for ($i = 0; $i < $numargs; $i++) {
//             echo '<b style="background-color: rgba(0,0,0,0.4); line-height: 2em; width: 100%; color: #fff; display: block;">&nbsp;Argument ' . ($i + 1) . ' of ' . $number_of_argument . ' :</b><br/>';
//             echo '<div style="padding: 0px 10px;">';
//             var_dump(func_get_arg($i));
//             echo '</div>';
//             echo '<br/>';
//         }
//         echo '</pre></div>';
//         die();
//     }
// }

if ( ! function_exists('dd'))
{
    function dd($data='usage: fred("any type of data")')
    {
        $data_type = '';
        // $data objects do not display as an array so...
        /**
        if (is_object($data))
        {
            $data = get_object_vars($data); // returns with $data = array();
        }*/

        // maybe find the $data type
        if (empty($data))
        {
            $data_type     = "empty()";
        }else{
            switch($data)
            {
                case ('' == $data)     :
                    $data_type     = "empty()";
                    break;
                case is_array($data)     :
                    $data_count    = count($data);
                    $data_type     = "array($data_count)";
                    break;
                case is_bool($data) :
                    $data_type    = 'boolean';
                    $data         = $data ? 'TRUE' : 'FALSE';
                    break;
                case is_numeric($data) :
                    $data_type    = 'numeric';
                    break;
                case is_object($data) :
                    $data_type    = 'object';
                    $data        =    $data;
                    break;
                case is_resource($data) :
                    $data_type    = 'resource';
                    $data_count    = mysql_num_rows($data);
                    $tmp                = array();
                    while ($row = mysql_fetch_assoc($data))
                    {
                        $tmp[] = $row;
                    }
                    $data = $tmp;
                    break;
                case is_string($data) :
                    $data_type    = 'string';
                    $data_count    = strlen($data);

                    break;
                default:
                    $data_type     = 'oddball';
                    $data_value    = $data;
            }//end switch
        }//endif

        $dub = debug_backtrace();

        // $data must now be an array or a string, numeric, or...
        $style = 'width:96%; margin:1em; overflow:auto;text-align:left; font-family:Courier; font-size:0.86em; background:#efe none; color:#000; text-align:left; border:solid 1px;padding:0.42em';
        echo "<fieldset style='$style'>";
                echo    '<legend>Developer Print Debug data:</legend>';
                echo    '<br /><b style="color:#f00">Type &nbsp; &nbsp;&nbsp; ==> </b>'        .$data_type;
                echo    '<br />';
                echo    '<b>location</b>&nbsp; ==> ' . $dub[0]['file'] . " On Line Number ";
                echo $dub[0]['line'] . "</span>";
                if (isset($data_count))
                {
                    echo    '<br /><b>Count &nbsp;&nbsp;&nbsp; ==> </b>'        .$data_count;
                }
                echo    '<br /><b>Value &nbsp;&nbsp;==> </b>';
                echo    "<pre style='width:58.88%; margin:-1.2em 0 1em 9.0em;overflow:auto'>";
                    if($data_type == 'string' ? print($data) : print_r($data));
                echo '</pre>';
        echo '</fieldset>';
        die();
    }//endfunc
}
