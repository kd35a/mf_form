<?php

wp_enqueue_style('forms-font_awesome', "//netdna.bootstrapcdn.com/font-awesome/3.1.1/css/font-awesome.css");
wp_enqueue_style('forms-style_wp', plugins_url()."/mf_form/include/style_wp.css");
wp_enqueue_script('jquery-forms', plugins_url()."/mf_form/include/script_wp.js", array('jquery'), '1.0', true);

$folder = str_replace("plugins/mf_form/list", "", dirname(__FILE__));

//$intQueryID = check_var('intQueryID');
$intQueryID = isset($_REQUEST['intQueryID']) ? $_REQUEST['intQueryID'] : "";

if(isset($_GET['btnQueryExport']))
{
	echo "<h1>Export</h1>
	<a href='javascript:history.go(-1)'>&laquo; Back</a><br/>
	<br/>";

	$strExportDate = date("Y-m-d H:i:s");

	$result = $wpdb->get_results($wpdb->prepare("SELECT query2TypeID, queryTypeID, queryTypeText FROM ".$wpdb->base_prefix."query2type INNER JOIN ".$wpdb->base_prefix."query_type USING (queryTypeID) WHERE queryID = '%d' AND queryTypeResult = '1' ORDER BY query2TypeOrder ASC", $intQueryID));

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

		if($intQueryTypeID == 2)
		{
			list($strQueryTypeText, $rest) = explode("|", $strQueryTypeText);
		}

		else if($intQueryTypeID == 10 || $intQueryTypeID == 11)
		{
			list($strQueryTypeText, $rest) = explode(":", $strQueryTypeText);
		}

		$out .= ($i > 0 ? $field_separator : "").$strQueryTypeText;

		$i++;
	}

	$out .= $field_separator."Created".$row_separator;

	$result = $wpdb->get_results($wpdb->prepare("SELECT answerID, queryID, answerCreated, answerIP FROM ".$wpdb->base_prefix."query2answer INNER JOIN ".$wpdb->base_prefix."query_answer USING (answerID) WHERE queryID = '%d' GROUP BY answerID ORDER BY answerCreated DESC", $intQueryID));
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

			$resultText = $wpdb->get_results("SELECT query2TypeID, queryTypeID, queryTypeText FROM ".$wpdb->base_prefix."query2type INNER JOIN ".$wpdb->base_prefix."query_type USING (queryTypeID) WHERE queryID = '".$intQueryID."' AND queryTypeResult = '1' ORDER BY query2TypeOrder ASC");

			$i = 0;

			foreach($resultText as $r)
			{
				$intQuery2TypeID = $r->query2TypeID;
				$intQueryTypeID = $r->queryTypeID;
				$strQueryTypeText = $r->queryTypeText;

				$resultAnswer = $wpdb->get_results("SELECT answerText FROM ".$wpdb->base_prefix."query_answer WHERE query2TypeID = '".$intQuery2TypeID."' AND answerID = '".$intAnswerID."'");
				$rowsAnswer = count($resultAnswer);

				if($i > 0){$out .= $field_separator;}

				if($rowsAnswer > 0)
				{
					$r = $resultAnswer[0];

					if($intQueryTypeID == 8)
					{
						$strAnswerText = 1;
					}

					else
					{
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
					}

					$strAnswerText = preg_replace("/(\r\n|\r|\n|".$field_separator.")/", " ", $strAnswerText);

					$out .= $strAnswerText;
				}

				$i++;
			}

			$out .= $field_separator.$strAnswerCreated.$row_separator;
		}

		$out .= $row_separator."Row count: ".$rows.$row_separator."Date: ".$strExportDate;

		$strQueryName = $wpdb->get_var($wpdb->prepare("SELECT queryName FROM ".$wpdb->base_prefix."query WHERE queryID = '%d'", $intQueryID));

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
}

else
{
	if(isset($_GET['btnQueryCopy']))
	{
		$inserted = true;

		$result_temp = $wpdb->get_results($wpdb->prepare("SELECT queryID FROM ".$wpdb->base_prefix."query WHERE queryID = '%d'", $intQueryID));
		$rows = count($result_temp);

		if($rows > 0)
		{
			$fields = ", queryAnswerName, queryAnswer, queryDeadline";

			$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->base_prefix."query (queryName".$fields.", queryCreated, userID) (SELECT CONCAT(queryName, ' (copy)')".$fields.", NOW(), '".get_current_user_id()."' FROM ".$wpdb->base_prefix."query WHERE queryID = '%d')", $intQueryID));
			$intQueryID_new = mysql_insert_id();

			if($intQueryID_new > 0)
			{
				$result = $wpdb->get_results($wpdb->prepare("SELECT query2TypeID FROM ".$wpdb->base_prefix."query2type WHERE queryID = '%d' ORDER BY query2TypeID DESC", $intQueryID));

				foreach($result as $r)
				{
					$intQuery2TypeID = $r->query2TypeID;

					$fields = "queryTypeID, queryTypeText, checkID, queryTypeForced, query2TypeOrder";

					$wpdb->query("INSERT INTO ".$wpdb->base_prefix."query2type (queryID, ".$fields.", query2TypeCreated, userID) (SELECT '".$intQueryID_new."', ".$fields.", NOW(), '".get_current_user_id()."' FROM ".$wpdb->base_prefix."query2type WHERE query2TypeID = '".$intQuery2TypeID."')");

					if(!(mysql_insert_id() > 0))
					{
						$inserted = false;
					}
				}
			}

			else
			{
				$inserted = false;
			}
		}

		if($inserted == false)
		{
			echo "Something went wong. Contact your admin and add this URL as reference";
			exit;
		}
	}

	echo "<h1>All Forms</h1>
	<table class='table_list'>";

		$arr_header[] = "Name";
		$arr_header[] = "Shortcode";
		$arr_header[] = "";
		$arr_header[] = "Answers";
		//$arr_header[] = "Deadline";
		$arr_header[] = "Export";
		$arr_header[] = "";
		$arr_header[] = "";
		$arr_header[] = "";

		echo show_table_header($arr_header);

		$result = $wpdb->get_results("SELECT queryID, queryName, queryDeadline, queryCreated FROM ".$wpdb->base_prefix."query GROUP BY queryID ORDER BY queryCreated DESC");

		if(count($result) == 0)
		{
			echo "<tr><td colspan='".count($arr_header)."'>There is nothing to show</td></tr>";
		}

		else
		{
			foreach($result as $r)
			{
				$intQueryID = $r->queryID;
				$strQueryName = $r->queryName;
				//$dteQueryDeadline = $r->queryDeadline;
				$strQueryCreated = $r->queryCreated;

				$resultContent = $wpdb->get_results("SELECT query2TypeID, queryTypeID, queryTypeText FROM ".$wpdb->base_prefix."query2type INNER JOIN ".$wpdb->base_prefix."query_type USING (queryTypeID) WHERE queryID = '".$intQueryID."' ORDER BY query2TypeCreated ASC");

				$result_temp = $wpdb->get_results("SELECT answerID FROM ".$wpdb->base_prefix."query2answer INNER JOIN ".$wpdb->base_prefix."query_answer USING (answerID) WHERE queryID = '".$intQueryID."' GROUP BY answerID");
				$intQueryTotal = count($result_temp);

				$result_temp = $wpdb->get_results("SELECT queryID FROM ".$wpdb->base_prefix."query2type WHERE queryID = '".$intQueryID."' LIMIT 0, 1");
				$rowsQuery = count($result_temp);

				$strQueryShortcode = "[mf_form id=".$intQueryID."]";

				echo "<tr id='query_".$intQueryID."'>
					<td><a href='?page=mf_form/create/index.php&intQueryID=".$intQueryID."'>".$strQueryName."</a></td>
					<td>".$strQueryShortcode."</td>
					<td>";

						$result = $wpdb->get_results("SELECT * FROM ".$wpdb->posts." WHERE (post_content LIKE '%".addslashes($strQueryShortcode)."%' OR post_content LIKE '%".addslashes("[form_shortcode id='".$intQueryID."']")."%') AND post_type != 'revision'");

						$i = 0;

						foreach($result as $r)
						{
							$post_id = $r->ID;
							$post_type = $r->post_type;

							$post_edit_url = "/wp-admin/post.php?post=".$post_id."&action=edit";
							$post_url = get_permalink($r);

							if($i > 0)
							{
								echo " | ";
							}

							echo "<a href='".$post_edit_url."' class='icon-edit'></a> <a href='".$post_url."' class='icon-globe'></a>";

							$i++;
						}

					echo "</td>
					<td><a href='?page=mf_form/answer/index.php&intQueryID=".$intQueryID."'>".$intQueryTotal."</a></td>";

					//<td>".($dteQueryDeadline > "1982-08-04 23:15:00" ? $dteQueryDeadline : "")."</td>

					echo "<td>";

						if($intQueryTotal > 0)
						{
							echo "<a href='?page=mf_form/list/index.php&btnQueryExport&intQueryID=".$intQueryID."' class='icon-table'></a>";
						}

					echo "</td>
					<td>
						<a href='?page=mf_form/create/index.php&intQueryID=".$intQueryID."' class='icon-edit'></a>
					</td>
					<td>
						<a href='?page=mf_form/list/index.php&btnQueryCopy&intQueryID=".$intQueryID."' class='icon-copy'></a>
					</td>
					<td>
						<a href='#delete/query/".$intQueryID."' class='ajax_link confirm_link icon-trash'></a>
					</td>
				</tr>";
			}
		}

	echo "</table>";
}