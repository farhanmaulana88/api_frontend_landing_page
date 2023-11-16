<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once 'dompdf/autoload.inc.php';
use Dompdf\Dompdf;

function pdf_create($html, $filename='', $paper, $orientation, $stream=TRUE) 
{
    // require_once("dompdf/dompdf_config.inc.php");
    $dompdf = new DOMPDF();
    $dompdf->setPaper($paper,$orientation);
    $dompdf->loadHtml($html);
    $dompdf->setCallbacks(
        array(
            'myCallbacks' => array(
            'event' => 'end_frame', 'f' => function ($infos) {
                $frame = $infos["frame"];
                $GLOBALS["array_coord"][] = $frame->get_padding_box();
            }
            )
        )
    );
    $dompdf->render();
    $ligne_sign = sizeof($GLOBALS["array_coord"]) - 2;
    if(!isset($GLOBALS['dompdf_generated_info'])) $GLOBALS['dompdf_generated_info'] = [];
    $key_name = explode('.',$filename);
    $GLOBALS['dompdf_generated_info'][$key_name[0]."_pdf_info"] = array(
        "last_position" => $GLOBALS["array_coord"][$ligne_sign]["y"],
        "paper" => $paper,
        "orientation" => $orientation,
    );
    if ($stream) {
        $dompdf->stream($filename.".pdf");
    } else {
        return $dompdf->output();
    }
}

function pdf_create2($output, $filename, $paper_type) 
{
    $dompdf = new Dompdf(['isRemoteEnabled' => true]);
    $dompdf->loadHtml($output);
    $dompdf->setPaper('A4', $paper_type);
    $dompdf->render();
    $dompdf->setCallbacks(
        array(
            'myCallbacks' => array(
            'event' => 'end_frame', 'f' => function ($infos) {
                $frame = $infos["frame"];
                $GLOBALS["array_coord"][] = $frame->get_padding_box();
            }
            )
        )
    );
    $ligne_sign = sizeof($GLOBALS["array_coord"]) - 2;
    if(!isset($GLOBALS['dompdf_generated_info'])) $GLOBALS['dompdf_generated_info'] = [];
    $key_name = explode('.',$filename);
    $GLOBALS['dompdf_generated_info'][$key_name[0]."_pdf_info"] = array(
        "last_position" => $GLOBALS["array_coord"][$ligne_sign]["y"],
        "paper" => 'A4',
        "orientation" => $paper_type,
    );
    $dompdf->stream($filename);
}

function pdf_create3($html, $filename='', $paper, $orientation, $config = array(), $stream=TRUE) 
{
    // require_once("dompdf/dompdf_config.inc.php");

    $dompdf = new DOMPDF(['isRemoteEnabled' => true]);

    if (count($config) !== 0) {
        foreach ( $config as $key => $value) {
            $dompdf->set_option($key, $value);
        }
    }

    $dompdf->set_paper($paper,$orientation);
    $dompdf->load_html($html);
    $dompdf->render();
    $dompdf->setCallbacks(
        array(
            'myCallbacks' => array(
            'event' => 'end_frame', 'f' => function ($infos) {
                $frame = $infos["frame"];
                $GLOBALS["array_coord"][] = $frame->get_padding_box();
            }
            )
        )
    );
    $ligne_sign = sizeof($GLOBALS["array_coord"]) - 2;
    if(!isset($GLOBALS['dompdf_generated_info'])) $GLOBALS['dompdf_generated_info'] = [];
    $key_name = explode('.',$filename);
    $GLOBALS['dompdf_generated_info'][$key_name[0]."_pdf_info"] = array(
        "last_position" => $GLOBALS["array_coord"][$ligne_sign]["y"],
        "paper" => $paper,
        "orientation" => $orientation,
    );
    
    if ($stream) {
        $dompdf->stream($filename.".pdf");
    } else {
        return $dompdf->output();
    }
}
?>