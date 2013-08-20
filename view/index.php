<?php

wp_enqueue_style('forms-font_awesome', "//netdna.bootstrapcdn.com/font-awesome/3.1.1/css/font-awesome.css");
wp_enqueue_style('forms-style_wp', plugins_url()."/mf_form/include/style_wp.css");

$intQueryID = check_var('intQueryID');
$intAnswerID = check_var('intAnswerID');

if(isset($_POST['btnQueryUpdate']))
{
	$updated = true;

	$result = $wpdb->get_results("SELECT query2TypeID FROM ".$wpdb->base_prefix."query2type INNER JOIN ".$wpdb->base_prefix."query_type USING (queryTypeID) WHERE queryID = '".$intQueryID."' AND queryTypeID != '13' ORDER BY query2TypeOrder ASC, query2TypeCreated ASC");

	$strAnswerIP = $_SERVER['REMOTE_ADDR'];

	foreach($result as $r)
	{
		$intQuery2TypeID2 = $r->query2TypeID;

		$var = isset($_POST[$intQuery2TypeID2]) ? check_var($_POST[$intQuery2TypeID2], 'char', false) : '';

		if($var != '')
		{
			$result_temp = $wpdb->get_results("SELECT answerID FROM ".$wpdb->base_prefix."query_answer WHERE answerID = '".$intAnswerID."' AND query2TypeID = '".$intQuery2TypeID2."' LIMIT 0, 1");
			$rowsCheck = count($result_temp);

			if($rowsCheck > 0)
			{
				$result_temp = $wpdb->get_results("SELECT answerID FROM ".$wpdb->base_prefix."query_answer WHERE answerID = '".$intAnswerID."' AND query2TypeID = '".$intQuery2TypeID2."' AND answerText = '".$var."' LIMIT 0, 1");
				$rowsCheck = count($result_temp);

				if($rowsCheck == 0)
				{
					$wpdb->get_results("UPDATE ".$wpdb->base_prefix."query_answer SET answerText = '".$var."' WHERE answerID = '".$intAnswerID."' AND query2TypeID = '".$intQuery2TypeID2."'");

					if(mysql_affected_rows() == 0)
					{
						$updated = false;
					}
				}
			}

			else
			{
				$wpdb->get_results("INSERT INTO ".$wpdb->base_prefix."query_answer (answerID, query2TypeID, answerText) VALUES ('".$intAnswerID."', '".$intQuery2TypeID2."', '".$var."')");

				if(mysql_affected_rows() == 0)
				{
					$updated = false;
				}
			}
		}

		else
		{
			$result_temp = $wpdb->get_results("SELECT answerID FROM ".$wpdb->base_prefix."query_answer WHERE answerID = '".$intAnswerID."' AND query2TypeID = '".$intQuery2TypeID2."' LIMIT 0, 1");
			$rowsCheck = count($result_temp);

			if($rowsCheck > 0)
			{
				$wpdb->get_results("DELETE FROM ".$wpdb->base_prefix."query_answer WHERE answerID = '".$intAnswerID."' AND query2TypeID = '".$intQuery2TypeID2."'");
			}
		}
	}

	//Speciallösning tills radio buttons fungerar helt bra
	###############################################
	$var = isset($_POST['radio']) ? check_var($_POST['radio'], 'char', false) : '';

	if($var != '')
	{
		$result_temp = $wpdb->get_results("SELECT answerID FROM ".$wpdb->base_prefix."query_answer WHERE answerID = '".$intAnswerID."' AND query2TypeID = '".$var."' LIMIT 0, 2");
		$rowsCheck = count($result_temp);

		if($rowsCheck == 1)
		{
			$wpdb->get_results("UPDATE ".$wpdb->base_prefix."query_answer SET answerText = '' WHERE answerID = '".$intAnswerID."' AND query2TypeID = '".$var."'");

			if(mysql_affected_rows() == 0)
			{
				$updated = false;
			}
		}

		else
		{
			$resultRadios = $wpdb->get_results("SELECT query2TypeID FROM ".$wpdb->base_prefix."query2type WHERE queryID = '".$intQueryID."' AND queryTypeID = '2'");

			foreach($resultRadios as $r)
			{
				$intQuery2TypeID = $r->query2TypeID;

				$wpdb->get_results("DELETE FROM ".$wpdb->base_prefix."query_answer WHERE answerID = '".$intAnswerID."' AND query2TypeID = '".$intQuery2TypeID."'");
			}

			$wpdb->get_results("INSERT INTO ".$wpdb->base_prefix."query_answer (answerID, query2TypeID, answerText) VALUES ('".$intAnswerID."', '".$var."', '')");

			if(mysql_affected_rows() == 0)
			{
				$updated = false;
			}
		}
	}
	###############################################

	if($updated == true)
	{
		echo "<script>location.href = '?page=mf_form/answer/index.php&queryID=".$intQueryID."';</script>";
	}

	else
	{
		echo "Something went wrong...";
	}

	exit;
}

if(!($intAnswerID > 0))
{
	$result = $wpdb->get_results("SELECT queryID, answerID FROM ".$wpdb->base_prefix."query INNER JOIN ".$wpdb->base_prefix."query2answer USING (queryID) ORDER BY answerCreated DESC LIMIT 0, 1");

	foreach($result as $r)
	{
		$intQueryID = $r->queryID;
		$intAnswerID = $r->answerID;
	}
}

$strQueryName = $wpdb->get_var("SELECT queryName FROM ".$wpdb->base_prefix."query WHERE queryID = '".$intQueryID."'");

echo "<h1>Visa svar fr&aring;n ".$strQueryName."</h1>";

if($intAnswerID > 0)
{
	echo "<form action='' method='post'>"
		.show_query_form(array('query_id' => $intQueryID))
	."</form>";
}