<?php

$folder = str_replace("plugins/mf_form/export", "", dirname(__FILE__));

$intQueryID = check_var('intQueryID');

if(!($intQueryID > 0))
{
	$intQueryID = $wpdb->get_var("SELECT queryID FROM ".$wpdb->prefix."query LEFT JOIN ".$wpdb->prefix."query2answer USING (queryID) ORDER BY answerCreated DESC, queryCreated DESC LIMIT 0, 1");
}

echo "<h1>Export</h1>
<a href='javascript:history.go(-1)'>&laquo; Back</a><br/>
<br/>";

$strExportDate = date("Y-m-d H:i:s");

$result = $wpdb->get_results("SELECT query2TypeID, queryTypeID, queryTypeText FROM ".$wpdb->prefix."query2type INNER JOIN ".$wpdb->prefix."query_type USING (queryTypeID) WHERE queryID = '".$intQueryID."' AND queryTypeResult = '1' ORDER BY query2TypeOrder ASC");

$file_type = "csv";
$field_separator = ",";
$row_separator = "\n";

$i = 0;
$out = "";

foreach($result as $r)
{
	$intQuery2TypeID = $r->query2TypeID;
	$intQueryTypeID = $r->queryTypeID;
	$strQueryTypeText = preg_replace("/(\r\n|\r|\n|".$field_separator.")/", " ", $r->queryTypeText);

	if($intQueryTypeID == 10)
	{
		list($strQueryTypeText, $rest) = explode(":", $strQueryTypeText);
	}

	$out .= ($i > 0 ? $field_separator : "").$strQueryTypeText;

	$i++;
}

$out .= $field_separator."Skapad".$row_separator;

$result = $wpdb->get_results("SELECT answerID, queryID, answerCreated, answerIP FROM ".$wpdb->prefix."query2answer INNER JOIN ".$wpdb->prefix."query_answer USING (answerID) WHERE queryID = '".$intQueryID."' GROUP BY answerID ORDER BY answerCreated DESC");
$rows = count($result);

if($rows == 0)
{
	echo "There were no answers to export";
}

else
{
	foreach($result as $r)
	{
		$intAnswerID = $r->answerID;
		$intQueryID = $r->queryID;
		$strAnswerCreated = $r->answerCreated;
		$strAnswerIP = $r->answerIP;

		$resultText = $wpdb->get_results("SELECT query2TypeID, queryTypeID, queryTypeText FROM ".$wpdb->prefix."query2type INNER JOIN ".$wpdb->prefix."query_type USING (queryTypeID) WHERE queryID = '".$intQueryID."' AND queryTypeResult = '1' ORDER BY query2TypeOrder ASC");

		$i = 0;

		foreach($resultText as $r)
		{
			$intQuery2TypeID = $r->query2TypeID;
			$intQueryTypeID = $r->queryTypeID;
			$strQueryTypeText = $r->queryTypeText;

			$resultAnswer = $wpdb->get_results("SELECT answerText FROM ".$wpdb->prefix."query_answer WHERE query2TypeID = '".$intQuery2TypeID."' AND answerID = '".$intAnswerID."'");
			$rowsAnswer = count($resultAnswer);

			if($i > 0){$out .= $field_separator;}

			if($rowsAnswer > 0)
			{
				$r = $resultAnswer[0];

				if($intQueryTypeID == 2 || $intQueryTypeID == 8)
				{
					$strAnswerText = 1;
				}

				else
				{
					$strAnswerText = $r->answerText;

					if($intQueryTypeID == 10)
					{
						$arr_content1 = explode(":", $strQueryTypeText);
						$arr_content2 = explode(",", $arr_content1[1]);

						foreach($arr_content2 as $str_content)
						{
							$arr_content3 = explode("|", $str_content);

							if($strAnswerText == $arr_content3[0])
							{
								$strAnswerText = $arr_content3[1];
							}
						}
					}
				}

				$strAnswerText = preg_replace("/(\r\n|\r|\n|".$field_separator.")/", " ", $strAnswerText);

				$out .= $strAnswerText;
			}

			$i++;
		}

		$out .= $field_separator.$strAnswerCreated.$row_separator;
	}

	$out .= $row_separator."Row count: ".$rows.$row_separator."Date: ".$strExportDate;

	$strQueryName = $wpdb->get_var("SELECT queryName FROM ".$wpdb->prefix."query WHERE queryID = '".$intQueryID."'");

	$file = sanitize_title_with_dashes(sanitize_title($strQueryName))."_".date("YmdHis").".".$file_type;

	$success = set_file_content(array('file' => $folder."/uploads/".$file, 'mode' => 'a', 'content' => trim($out)));

	if($success == true)
	{
		echo "<a href='../wp-content/uploads/".$file."'>".$file."</a>";
	}

	else
	{
		echo "It was not possible to export all answers from ".$strQueryName;
	}
}