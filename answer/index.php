<?php

wp_enqueue_style('forms-font_awesome', "//netdna.bootstrapcdn.com/font-awesome/3.1.1/css/font-awesome.css");
wp_enqueue_style('forms-style_wp', plugins_url()."/mf_form/include/style_wp.css");
wp_enqueue_script('jquery-forms', plugins_url()."/mf_form/include/script_wp.js", array('jquery'), '1.0', true);
wp_enqueue_script('jquery-flot', plugins_url()."/mf_form/include/jquery.flot.min.js", array('jquery'), '1.0', true);
wp_enqueue_script('jquery-flot-pie', plugins_url()."/mf_form/include/jquery.flot.pie.min.js", array('jquery'), '1.0', true);

$intQueryID = check_var('intQueryID');
$intAnswerID = check_var('intAnswerID');

if(!($intQueryID > 0))
{
	$intQueryID = $wpdb->get_var("SELECT queryID FROM ".$wpdb->base_prefix."query LEFT JOIN ".$wpdb->base_prefix."query2answer USING (queryID) ORDER BY answerCreated DESC, queryCreated DESC LIMIT 0, 1");
}

$dteQueryStartDate = check_var('dteQueryStartDate', 'char', true, date("Y-m-d", strtotime("-2 year")));
$dteQueryEndDate = check_var('dteQueryEndDate', 'char', true, date("Y-m-d", strtotime("+1 day")));

$strQuerySearch = "";
$strAnswerText2 = check_var('strAnswerText2');

if($strAnswerText2 != '')
{
	$strQuerySearch .= " AND answerText LIKE '%".$strAnswerText2."%'";
}

if($dteQueryStartDate > "1982-08-04 23:15:00")
{
	$strQuerySearch .= " AND answerCreated >= '".$dteQueryStartDate."'";
}

if($dteQueryEndDate > "1982-08-04 23:15:00")
{
	$strQuerySearch .= " AND answerCreated <= '".$dteQueryEndDate."'";
}

$result = $wpdb->get_results("SELECT queryName, queryShowAnswers FROM ".$wpdb->base_prefix."query WHERE queryID = '".$intQueryID."'");

foreach($result as $r)
{
	$strQueryName = $r->queryName;
	$intQueryShowAnswers = $r->queryShowAnswers;
}

echo "<h1>Answers in ".$strQueryName."</h1>";

$intTotalAnswers = $wpdb->get_var("SELECT COUNT(answerID) FROM ".$wpdb->base_prefix."query2type INNER JOIN ".$wpdb->base_prefix."query_answer USING (query2TypeID) WHERE queryID = '".$intQueryID."' AND queryTypeID = '8'");

$result = $wpdb->get_results("SELECT query2TypeID, queryTypeText FROM ".$wpdb->base_prefix."query2type WHERE queryID = '".$intQueryID."' AND queryTypeID = '8' ORDER BY query2TypeOrder ASC, query2TypeCreated ASC");
$rows = count($result);

if($intTotalAnswers > 0 && $rows > 0)
{
	$data = "";

	foreach($result as $r)
	{
		$intQuery2TypeID2 = $r->query2TypeID;
		$strQueryTypeText2 = $r->queryTypeText;

		$intAnswerCount = $wpdb->get_var("SELECT COUNT(answerID) FROM ".$wpdb->base_prefix."query2type INNER JOIN ".$wpdb->base_prefix."query_answer USING (query2TypeID) WHERE queryID = '".$intQueryID."' AND queryTypeID = '8' AND query2TypeID = '".$intQuery2TypeID2."'");

		$data .= ($data != '' ? "," : "")."{label: '".$strQueryTypeText2."', data: ".$intAnswerCount."}";
	}

	echo "<div id='flot_pie'></div>
	<script>
		jQuery(function($)
		{
			var data = [".$data."];

			$.plot($('#flot_pie'), data, {
				series: {
					pie: { 
						show: true
					}
				}
			});
		});
	</script>";

	//echo get_poll_results(array('query_id' => $intQueryID));
}

echo "<table class='table_list'>";

	$result = $wpdb->get_results("SELECT queryTypeID, queryTypeText, query2TypeID FROM ".$wpdb->base_prefix."query2type INNER JOIN ".$wpdb->base_prefix."query_type USING (queryTypeID) WHERE queryID = '".$intQueryID."' AND queryTypeResult = '1' ORDER BY query2TypeOrder ASC");

	foreach($result as $r)
	{
		$intQueryTypeID = $r->queryTypeID;
		$strQueryTypeText = $r->queryTypeText;
		$intQuery2TypeID2 = $r->query2TypeID;

		if($intQueryTypeID == 2)
		{
			list($strQueryTypeText, $rest) = explode("|", $strQueryTypeText);
		}

		else if($intQueryTypeID == 8)
		{
			$intAnswerCount = $wpdb->get_var("SELECT COUNT(answerID) FROM ".$wpdb->base_prefix."query2type INNER JOIN ".$wpdb->base_prefix."query_answer USING (query2TypeID) WHERE queryID = '".$intQueryID."' AND queryTypeID = '8' AND query2TypeID = '".$intQuery2TypeID2."'");

			$strQueryTypeText .= " (".$intAnswerCount.")";
		}

		else if($intQueryTypeID == 10 || $intQueryTypeID == 11)
		{
			list($strQueryTypeText, $rest) = explode(":", $strQueryTypeText);
		}

		$arr_header[] = $strQueryTypeText;
	}

	$arr_header[] = "Created";
	//$arr_header[] = "";
	$arr_header[] = "IP";
	$arr_header[] = "";

	echo show_table_header($arr_header);

	$result = $wpdb->get_results("SELECT answerID, queryID, answerCreated, answerIP FROM ".$wpdb->base_prefix."query2answer INNER JOIN ".$wpdb->base_prefix."query_answer USING (answerID) WHERE queryID = '".$intQueryID."'".$strQuerySearch." GROUP BY answerID ORDER BY answerCreated DESC");
	$rows = count($result);

	if($rows > 0)
	{
		foreach($result as $r)
		{
			$intAnswerID = $r->answerID;
			$intQueryID = $r->queryID;
			$strAnswerCreated = $r->answerCreated;
			$strAnswerIP = $r->answerIP;

			echo "<tr id='answer_".$intAnswerID."'>";

				$resultText = $wpdb->get_results("SELECT query2TypeID, queryTypeID, queryTypeText, checkCode FROM ".$wpdb->base_prefix."query_check RIGHT JOIN ".$wpdb->base_prefix."query2type USING (checkID) INNER JOIN ".$wpdb->base_prefix."query_type USING (queryTypeID) WHERE queryID = '".$intQueryID."' AND queryTypeResult = '1' ORDER BY query2TypeOrder ASC");

				foreach($resultText as $r)
				{
					$intQuery2TypeID = $r->query2TypeID;
					$intQueryTypeID = $r->queryTypeID;
					$strQueryTypeText = $r->queryTypeText;
					$strCheckCode = $r->checkCode;

					$value = 0;
					$xtra = "";

					$resultAnswer = $wpdb->get_results("SELECT answerText FROM ".$wpdb->base_prefix."query_answer WHERE query2TypeID = '".$intQuery2TypeID."' AND answerID = '".$intAnswerID."'");
					$rowsAnswer = count($resultAnswer);

					if($rowsAnswer > 0)
					{
						if($intQueryTypeID == 8)
						{
							$strAnswerText = 1;
						}

						else
						{
							$r = $resultAnswer[0];
							$strAnswerText = $r->answerText;

							if($intQueryTypeID == 7)
							{
								$strAnswerText = wp_date_format($strAnswerText);
							}

							else if($intQueryTypeID == 10)
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

							else if($intQueryTypeID == 11)
							{
								$arr_content1 = explode(":", $strQueryTypeText);
								$arr_content2 = explode(",", $arr_content1[1]);

								$arr_answer_text = explode(",", $strAnswerText);

								$strAnswerText = "";

								foreach($arr_content2 as $str_content)
								{
									$arr_content3 = explode("|", $str_content);

									if(in_array($arr_content3[0], $arr_answer_text))
									{
										$strAnswerText .= ($strAnswerText != '' ? ", " : "").$arr_content3[1];
									}
								}
							}

							else
							{
								if($strCheckCode != '')
								{
									if($strCheckCode == "url")
									{
										$strAnswerText = "<a href='".$strAnswerText."' rel='external'>".$strAnswerText."</a>";
									}

									else if($strCheckCode == "email")
									{
										$strAnswerText = "<a href='mailto:".$strAnswerText."'>".$strAnswerText."</a>";
									}
								}
							}
						}
					}

					else
					{
						$strAnswerText = "";
					}

					echo "<td>";

						if($strAnswerText == 1)
						{
							echo $strAnswerText;
						}

						else
						{
							if($value == 2)
							{
								echo "<span class='red'>";
							}

							else if($value == 1)
							{
								echo "<span class='green'>";
							}

								echo $strAnswerText;

							if($value > 0)
							{
								echo "</span>";
							}
						}

					echo "</td>";
				}

				echo "<td>".wp_date_format($strAnswerCreated, true)."</td>
				<td>".$strAnswerIP."</td>
				<td>
					<a href='#delete/answer/".$intAnswerID."' class='ajax_link confirm_link icon-trash'></a>
				</td>
			</tr>";
		}
	}

echo "</table>";