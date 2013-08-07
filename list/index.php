<?php

$intQueryID = check_var('intQueryID');

if(isset($_GET['btnQueryCopy']))
{
	$inserted = true;

	$result_temp = $wpdb->get_results("SELECT queryID FROM ".$wpdb->prefix."query WHERE queryID = '".$intQueryID."'");
	$rows = count($result_temp);

	if($rows > 0)
	{
		$fields = ", queryAnswerName, queryAnswer, queryDeadline";

		$wpdb->query("INSERT INTO ".$wpdb->prefix."query (queryName".$fields.", queryCreated, userID) (SELECT CONCAT(queryName, ' (kopia)')".$fields.", NOW(), '".get_current_user_id()."' FROM ".$wpdb->prefix."query WHERE queryID = '".$intQueryID."')");
		$intQueryID_new = mysql_insert_id();

		if($intQueryID_new > 0)
		{
			$result = $wpdb->get_results("SELECT query2TypeID FROM ".$wpdb->prefix."query2type WHERE queryID = '".$intQueryID."' ORDER BY query2TypeID DESC");

			foreach($result as $r)
			{
				$intQuery2TypeID = $r->query2TypeID;

				$fields = "queryTypeID, queryTypeText, checkID, queryTypeForced, query2TypeOrder";

				$wpdb->query("INSERT INTO ".$wpdb->prefix."query2type (queryID, ".$fields.", query2TypeCreated, userID) (SELECT '".$intQueryID_new."', ".$fields.", NOW(), '".get_current_user_id()."' FROM ".$wpdb->prefix."query2type WHERE query2TypeID = '".$intQuery2TypeID."')");

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
		echo "N�got gick fel. Kontakta admin ang�ende detta och ange URL till sidan";
		exit;
	}
}

echo "<link href='//netdna.bootstrapcdn.com/font-awesome/3.1.1/css/font-awesome.css' rel='stylesheet'>
<link href='".plugins_url()."/mf_form/include/style.css' rel='stylesheet'/>
<link href='".plugins_url()."/mf_form/include/style_wp.css' rel='stylesheet'/>
<h1>Formul&auml;r</h1>
<table class='table_list'>";

	$arr_header[] = "Namn";
	$arr_header[] = "Shortcode";
	$arr_header[] = "";
	$arr_header[] = "Svar";
	$arr_header[] = "Deadline";
	$arr_header[] = "";
	$arr_header[] = "";
	$arr_header[] = "";
	$arr_header[] = "";

	echo show_table_header($arr_header);

	$result = $wpdb->get_results("SELECT queryID, queryName, queryDeadline, queryCreated FROM ".$wpdb->prefix."query GROUP BY queryID ORDER BY queryCreated DESC");

	if(count($result) == 0)
	{
		echo "<tr><td colspan='9'>Det finns inget att visa</td></tr>";
	}

	else
	{
		foreach($result as $r)
		{
			$intQueryID = $r->queryID;
			$strQueryName = $r->queryName;
			$dteQueryDeadline = $r->queryDeadline;
			$strQueryCreated = $r->queryCreated;

			$resultContent = $wpdb->get_results("SELECT query2TypeID, queryTypeID, queryTypeText FROM ".$wpdb->prefix."query2type INNER JOIN ".$wpdb->prefix."query_type USING (queryTypeID) WHERE queryID = '".$intQueryID."' ORDER BY query2TypeCreated ASC");

			$result_temp = $wpdb->get_results("SELECT answerID FROM ".$wpdb->prefix."query2answer INNER JOIN ".$wpdb->prefix."query_answer USING (answerID) WHERE queryID = '".$intQueryID."' GROUP BY answerID");
			$intQueryTotal = count($result_temp);

			$result_temp = $wpdb->get_results("SELECT queryID FROM ".$wpdb->prefix."query2type WHERE queryID = '".$intQueryID."' LIMIT 0, 1");
			$rowsQuery = count($result_temp);

			if($intQueryTotal > 0)
			{
				$a_start = "<a href='?page=mf_form/answer/index.php&intQueryID=".$intQueryID."'>";
				$a_end = "</a>";
			}

			else
			{
				$a_start = $a_end = "";
			}

			$strQueryShortcode = "[form_shortcode id='".$intQueryID."']";

			echo "<tr id='query_".$intQueryID."'>
				<td>".$a_start.$strQueryName.$a_end."</td>
				<td>".$strQueryShortcode."</td>
				<td>";

					$result = $wpdb->get_results("SELECT * FROM ".$wpdb->posts." WHERE post_content LIKE '%".addslashes($strQueryShortcode)."%' AND post_status = 'publish'");

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
				<td>".$a_start.$intQueryTotal.$a_end."</td>
				<td>".($dteQueryDeadline > "1982-08-04 23:15:00" ? $dteQueryDeadline : "")."</td>
				<td>";

					if($intQueryTotal > 0)
					{
						echo "<a href='?page=mf_form/export/index.php&intQueryID=".$intQueryID."' class='icon-table'></a>";
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

echo "</table>
<script src='/wp-content/plugins/mf_form/include/script.js'></script>";