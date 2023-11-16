<?php

if ( ! function_exists('sme_config'))
{
	/**
	 * @return string SQL command
	 */
	function sme_config()
	{
        // Load instance
		$CI = get_instance();
    
    $CI->db->select('*');
    $CI->db->from('tb_setting_sme tss');
    $query = $CI->db->get();

    // Check if the query was successful
    if ($query) {
        // Fetch the result as an array
        $result = $query->result_array();

        if (!empty($result)) {
            return $query->row();
        } else {
            echo "No records found.";
        }
    } else {
        echo "Query failed.";
    }
       
	}
}