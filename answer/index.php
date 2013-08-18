<?php

wp_enqueue_style('forms-font_awesome', "//netdna.bootstrapcdn.com/font-awesome/3.1.1/css/font-awesome.css");
wp_enqueue_style('forms-style_wp', plugins_url()."/mf_form/include/style_wp.css");
wp_enqueue_script('jquery-forms', plugins_url()."/mf_form/include/script_wp.js", array('jquery'), '1.0', true);

$intQueryID = check_var('intQueryID');
$intAnswerID = check_var('intAnswerID');

if(!($intQueryID > 0))
{
	$intQueryID = $wpdb->get_var("SELECT queryID FROM ".$wpdb->prefix."query LEFT JOIN ".$wpdb->prefix."query2answer USING (queryID) ORDER BY answerCreated DESC, queryCreated DESC LIMIT 0, 1");
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

$strQueryName = $wpdb->get_var("SELECT queryName FROM ".$wpdb->prefix."query WHERE queryID = '".$intQueryID."'");

echo "<h1>Answers in ".$strQueryName."</h1>
<table class='table_list'>";

	$result = $wpdb->get_results("SELECT queryTypeID, queryTypeText FROM ".$wpdb->prefix."query2type INNER JOIN ".$wpdb->prefix."query_type USING (queryTypeID) WHERE queryID = '".$intQueryID."' AND queryTypeResult = '1' ORDER BY query2TypeOrder ASC");

	foreach($result as $r)
	{
		$intQueryTypeID = $r->queryTypeID;
		$strQueryTypeText = $r->queryTypeText;

		if($intQueryTypeID == 2)
		{
			list($strQueryTypeText, $rest) = explode("|", $strQueryTypeText);
		}

		else if($intQueryTypeID == 10 || $intQueryTypeID == 11)
		{
			list($strQueryTypeText, $rest) = explode(":", $strQueryTypeText);
		}

		$arr_header[] = $strQueryTypeText;
	}

	$arr_header[] = "Created";
	//$arr_header[] = "";
	$arr_header[] = "";

	echo show_table_header($arr_header);

	$result = $wpdb->get_results("SELECT answerID, queryID, answerCreated, answerIP FROM ".$wpdb->prefix."query2answer INNER JOIN ".$wpdb->prefix."query_answer USING (answerID) WHERE queryID = '".$intQueryID."'".$strQuerySearch." GROUP BY answerID ORDER BY answerCreated DESC");
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

				$resultText = $wpdb->get_results("SELECT query2TypeID, queryTypeID, queryTypeText, checkCode FROM ".$wpdb->prefix."query_check RIGHT JOIN ".$wpdb->prefix."query2type USING (checkID) INNER JOIN ".$wpdb->prefix."query_type USING (queryTypeID) WHERE queryID = '".$intQueryID."' AND queryTypeResult = '1' ORDER BY query2TypeOrder ASC");

				foreach($resultText as $r)
				{
					$intQuery2TypeID = $r->query2TypeID;
					$intQueryTypeID = $r->queryTypeID;
					$strQueryTypeText = $r->queryTypeText;
					$strCheckCode = $r->checkCode;

					$value = 0;
					$xtra = "";

					$resultAnswer = $wpdb->get_results("SELECT answerText FROM ".$wpdb->prefix."query_answer WHERE query2TypeID = '".$intQuery2TypeID."' AND answerID = '".$intAnswerID."'");
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

				echo "<td>".$strAnswerCreated."</td>
				<td>
					<a href='#delete/answer/".$intAnswerID."' class='ajax_link confirm_link icon-trash'></a>
				</td>
			</tr>";

			/*<td>
					<a href='?page=mf_form/view/index.php&intQueryID=".$intQueryID."&intAnswerID=".$intAnswerID."' class='icon-edit'></a>
				</td>*/
		}
	}

echo "</table>";