<?php

$wp_root = '../../../..';

if(file_exists($wp_root.'/wp-load.php'))
{
	require_once($wp_root.'/wp-load.php');
}

else
{
	require_once($wp_root.'/wp-config.php');
}

$json_output = array();

$type = check_var('type', 'char');

$arr_input = explode("/", $type);

$type_action = $arr_input[0];
$type_table = $arr_input[1];
$type_id = $arr_input[2];

if($type_action == "delete" && get_current_user_id() > 0)
{
	if($type_table == "query")
	{
		$wpdb->query("DELETE FROM ".$wpdb->base_prefix."query2type WHERE queryID = '".$type_id."'");

		$intAnswerID = $wpdb->get_var("SELECT answerID FROM ".$wpdb->base_prefix."query2answer WHERE queryID = '".$type_id."'");
		$wpdb->query("DELETE FROM ".$wpdb->base_prefix."query_answer WHERE answerID = '".$intAnswerID."'");

		$wpdb->query("DELETE FROM ".$wpdb->base_prefix."query2answer WHERE queryID = '".$type_id."'");
		$wpdb->query("DELETE FROM ".$wpdb->base_prefix."query WHERE queryID = '".$type_id."'");

		if(mysql_affected_rows() > 0)
		{
			$json_output['success'] = true;
			$json_output['dom_id'] = $type_table."_".$type_id;
		}
	}

	else if($type_table == "answer")
	{
		$wpdb->query("DELETE FROM ".$wpdb->base_prefix."query_answer WHERE answerID = '".$type_id."'");
		$wpdb->query("DELETE FROM ".$wpdb->base_prefix."query2answer WHERE answerID = '".$type_id."'");

		if(mysql_affected_rows() > 0)
		{
			$json_output['success'] = true;
			$json_output['dom_id'] = $type_table."_".$type_id;
		}
	}
	
	else if($type_table == "type")
	{
		$wpdb->query("DELETE FROM ".$wpdb->base_prefix."query2type WHERE query2TypeID = '".$type_id."'");

		if(mysql_affected_rows() > 0)
		{
			$json_output['success'] = true;
			$json_output['dom_id'] = $type_table."_".$type_id;
		}
	}
}

else if($type_action == "require" && get_current_user_id() > 0)
{
	if($type_table == "type")
	{
		$intQueryTypeRequired = $wpdb->get_var("SELECT queryTypeForced FROM ".$wpdb->base_prefix."query2type WHERE query2TypeID = '".$type_id."'");

		$wpdb->query("UPDATE ".$wpdb->base_prefix."query2type SET queryTypeForced = '".($intQueryTypeRequired == 1 ? 0 : 1)."' WHERE query2TypeID = '".$type_id."'");

		if(mysql_affected_rows() > 0)
		{
			$json_output['success'] = true;
		}

		else
		{
			$json_output['error'] = mysql_error();
		}
	}
}

else if($type_action == "sortOrder" && get_current_user_id() > 0)
{
	$updated = false;

	$strOrder = check_var('strOrder');

	$json_output['strOrder'] = $strOrder;

	$arr_ids = explode(",", trim($strOrder, ","));

	$i = 0;

	foreach($arr_ids as $str_id)
	{
		list($type, $sort_id) = explode("_", $str_id);

		$json_output['sort_id'] = $sort_id;

		if($sort_id > 0)
		{
			$wpdb->query("UPDATE ".$wpdb->base_prefix."query2type SET query2TypeOrder = '".$i."' WHERE query2TypeID = '".$sort_id."'");

			$i++;

			if(mysql_affected_rows() > 0)
			{
				$updated = true;
			}
		}
	}

	if($updated == true)
	{
		$json_output['success'] = true;
	}
}

else if($type_action == "zipcode")
{
	$search = str_replace(" ", "", $type_id);

	$result = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$wpdb->base_prefix."query_zipcode WHERE addressZipCode = '%d'", $search));

	foreach($result as $r)
	{
		$strCityName = $r->cityName;
		$strMunicipalityName = $r->municipalityName;

		$json_output['success'] = true;
		$json_output['response'] = $strCityName.($strMunicipalityName != $strCityName ? ", ".$strMunicipalityName : "");
	}
}

echo json_encode($json_output);