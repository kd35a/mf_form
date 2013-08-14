<?php

wp_register_style('forms-font_awesome', "//netdna.bootstrapcdn.com/font-awesome/3.1.1/css/font-awesome.css");
wp_enqueue_style('forms-font_awesome');

wp_register_style('forms-style_wp', plugins_url()."/mf_form/include/style_wp.css");
wp_enqueue_style('forms-style_wp');

wp_enqueue_script('jquery-forms', plugins_url()."/mf_form/include/script_wp.js", array('jquery'), '1.0', true);
wp_enqueue_script('jquery-forms');

$intQueryID = check_var('intQueryID');

$intQuery2TypeID = check_var('intQuery2TypeID');
$intQuery2TypeOrder = check_var('intQuery2TypeOrder');

$strQueryName = check_var('strQueryName');
$strQueryAnswerName = check_var('strQueryAnswerName');
$strQueryAnswer = check_var('strQueryAnswer');
$strQueryEmail = check_var('strQueryEmail', 'email');
$strQueryEmailName = check_var('strQueryEmailName');
$strQueryButtonText = check_var('strQueryButtonText');
$dteQueryDeadline = check_var('dteQueryDeadline', 'date');
$intQueryTypeID = check_var('intQueryTypeID');
$strQueryTypeText = check_var('strQueryTypeText');
$intCheckID = check_var('intCheckID');

$strQueryTypeSelect = check_var('strQueryTypeSelect', '', true, "0|-- Choose here --,1|Nej,2|Ja");
$strQueryTypeMin = check_var('strQueryTypeMin', '', true, "0");
$strQueryTypeMax = check_var('strQueryTypeMax', '', true, 100);
$intQueryTypeForced = isset($_POST['intQueryTypeForced']) ? 1 : 0;

if(isset($_POST['btnQueryCreate']))
{
	if($strQueryName == '')
	{
		echo "Enter all mandatory fields";
		exit;
	}

	else
	{
		if($intQueryID > 0)
		{
			$wpdb->get_results("UPDATE ".$wpdb->prefix."query SET queryName = '".$strQueryName."', queryAnswerName = '".$strQueryAnswerName."', queryAnswer = '".$strQueryAnswer."', queryEmail = '".$strQueryEmail."', queryEmailName = '".$strQueryEmailName."', queryButtonText = '".$strQueryButtonText."' WHERE queryID = '".$intQueryID."'");
		}

		else
		{
			$result = $wpdb->get_results("SELECT queryID FROM ".$wpdb->prefix."query WHERE queryName = '".$strQueryName."'");

			if(count($result) > 0)
			{
				echo "There is already a form with that name. Try with another one.";
			}

			else
			{
				$wpdb->get_results("INSERT INTO ".$wpdb->prefix."query SET queryName = '".$strQueryName."', queryAnswerName = '".$strQueryAnswerName."', queryAnswer = '".$strQueryAnswer."', queryEmail = '".$strQueryEmail."', queryEmailName = '".$strQueryEmailName."', queryButtonText = '".$strQueryButtonText."', queryCreated = NOW(), userID = '".get_current_user_id()."'");
				$intQueryID = mysql_insert_id();
			}
		}

		if(mysql_affected_rows() > 0)
		{
			echo "<script>location.href='/wp-admin/admin.php?page=mf_form/create/index.php&intQueryID=".$intQueryID."#content'</script>";
		}

		else
		{
			echo "Error creating...";
		}
	}
}

else if(isset($_POST['btnQueryAdd']))
{
	//Tar bort medskickad info om det �r "fel" typ
	################
	if($intQueryTypeID == 6) //Mellanrum
	{
		$strQueryTypeText = '';
	}
	################

	if(($intQueryTypeID == 10 || $intQueryTypeID == 11) && $strQueryTypeSelect == "")
	{
		echo "Enter all mandatory fields";
	}

	else
	{
		if($intQueryTypeID == 2)
		{
			$strQueryTypeText = str_replace("|", "", $strQueryTypeText)."|".str_replace("|", "", $strQueryTypeMin)."|".str_replace("|", "", $strQueryTypeMax);
		}

		if($intQueryTypeID == 10 || $intQueryTypeID == 11)
		{
			$strQueryTypeText = str_replace(":", "", $strQueryTypeText).":".str_replace(":", "", $strQueryTypeSelect);
		}

		/*else if($intQueryTypeID == 14)
		{
			$strQueryTypeText = str_replace(":", "", $strQueryTypeText).":".str_replace(":", "", $strQueryTypeSelect);
		}*/

		if($intQuery2TypeID > 0)
		{
			if($intQueryTypeID > 0 && ($intQueryTypeID == 6 || $strQueryTypeText != ''))
			{
				$wpdb->get_results("UPDATE ".$wpdb->prefix."query2type SET queryTypeID = '".$intQueryTypeID."', queryTypeText = '".$strQueryTypeText."', checkID = '".$intCheckID."', queryTypeForced = '".$intQueryTypeForced."', userID = '".get_current_user_id()."' WHERE query2TypeID = '".$intQuery2TypeID."'");

				$intQuery2TypeID = $intQueryTypeID = $strQueryTypeText = $intCheckID = "";
			}

			else
			{
				echo "Error...";
			}
		}

		else
		{
			if($intQueryID > 0 && $intQueryTypeID > 0 && ($intQueryTypeID == 6 || $strQueryTypeText != ''))
			{
				$intQuery2TypeOrder = $wpdb->get_var("SELECT query2TypeOrder + 1 FROM ".$wpdb->prefix."query2type WHERE queryID = '".$intQueryID."' ORDER BY query2TypeOrder DESC");

				$wpdb->get_results("INSERT INTO ".$wpdb->prefix."query2type SET queryID = '".$intQueryID."', queryTypeID = '".$intQueryTypeID."', queryTypeText = '".$strQueryTypeText."', checkID = '".$intCheckID."', queryTypeForced = '".$intQueryTypeForced."', query2TypeOrder = '".$intQuery2TypeOrder."', query2TypeCreated = NOW(), userID = '".get_current_user_id()."'");

				if(mysql_affected_rows() > 0)
				{
					$intQueryTypeID = $strQueryTypeText = $intCheckID = "";
				}
			}

			else
			{
				echo "Error...";
			}
		}
	}

	if($intQueryTypeID == 0)
	{
		echo "<script>location.href='/wp-admin/admin.php?page=mf_form/create/index.php&intQueryID=".$intQueryID."#preview'</script>";
	}
}

if($intQueryID > 0)
{
	$result = $wpdb->get_results("SELECT queryName, queryAnswerName, queryAnswer, queryEmail, queryEmailName, queryButtonText, queryCreated FROM ".$wpdb->prefix."query WHERE queryID = '".$intQueryID."'"); //, queryDeadline
	$r = $result[0];
	$strQueryName = $r->queryName;
	$strQueryAnswerName = $r->queryAnswerName;
	$strQueryAnswer = $r->queryAnswer;
	$strQueryEmail = $r->queryEmail;
	$strQueryEmailName = $r->queryEmailName;
	$strQueryButtonText = $r->queryButtonText;
	//$dteQueryDeadline = $r->queryDeadline;
	$strQueryCreated = $r->queryCreated;
}

if($intQuery2TypeID > 0)
{
	$result = $wpdb->get_results("SELECT queryTypeID, queryTypeText, checkID, queryTypeForced FROM ".$wpdb->prefix."query2type WHERE query2TypeID = '".$intQuery2TypeID."'");
	$r = $result[0];
	$intQueryTypeID = $r->queryTypeID;
	$strQueryTypeText = $r->queryTypeText;
	$intCheckID = $r->checkID;
	$intQueryTypeForced = $r->queryTypeForced;

	if($intQueryTypeID == 2)
	{
		list($strQueryTypeText, $strQueryTypeMin, $strQueryTypeMax) = explode("|", $strQueryTypeText);
	}

	else if($intQueryTypeID == 10 || $intQueryTypeID == 11)
	{
		list($strQueryTypeText, $strQueryTypeSelect) = explode(":", $strQueryTypeText);
	}

	/*else if($intQueryTypeID == 14)
	{
		list($strQueryTypeText, $strQueryTypeSelect) = explode(":", $strQueryTypeText);
	}*/
}

echo "<h1>".($intQueryID > 0 ? "Update ".$strQueryName : "Add New")."</h1>
<form method='post' action=''>
	<div class='alignleft'>"
		.show_textfield('strQueryName', "Name", $strQueryName, 100, 0, true)
		."<h2>Confirmation message</h2>"
		.show_textfield('strQueryAnswerName', "Title", $strQueryAnswerName, 50, 0, true)
		.show_textarea(array('name' => 'strQueryAnswer', 'text' => "Text", 'value' => $strQueryAnswer, 'size' => 'small'))
	."</div>
	<div class='alignright'>
		<h2>Send e-mail to</h2>"
		.show_textfield('strQueryEmail', "Address", $strQueryEmail, 100)
		.show_textfield('strQueryEmailName', "Subject", $strQueryEmailName, 100)
		."<h2>Language</h2>"
		.show_textfield('strQueryButtonText', "Button text", $strQueryButtonText, 100, 0, false, '', "Send")
		//.show_textfield('dteQueryDeadline', "Deadline", $dteQueryDeadline, 10)
	."</div>
	<div class='clear'>"
		.show_submit('btnQueryCreate', ($intQueryID > 0 ? "Update" : "Add"))
		.input_hidden('intQueryID', $intQueryID)
	."</div>
</form>";

if($intQueryID > 0)
{
	echo "<form method='post' action='' id='content'>"
		."<h2>Content</h2>
		<div class='alignleft'>";

			if($intQueryTypeID == '')
			{
				$intQueryTypeID = $wpdb->get_var("SELECT queryTypeID FROM ".$wpdb->prefix."query2type WHERE userID = '".get_current_user_id()."' ORDER BY query2TypeCreated DESC");
			}

			$arr_data = array();

			$arr_data[] = array("", "-- Choose here --");

			$result = $wpdb->get_results("SELECT queryTypeID, queryTypeLang FROM ".$wpdb->prefix."query_type WHERE (queryTypeID = '".$intQueryTypeID."' OR queryTypePublic >= '1') ORDER BY queryTypeOrder ASC");

			foreach($result as $r)
			{
				$arr_data[] = array($r->queryTypeID, $r->queryTypeLang);
			}

			echo show_select(array('data' => $arr_data, 'name' => 'intQueryTypeID', 'compare' => $intQueryTypeID, 'text' => "Typ"))
			.show_textarea(array('name' => 'strQueryTypeText', 'text' => "Text", 'value' => $strQueryTypeText, 'size' => 'small', 'class' => "tr_text"));

		echo "</div>
		<div class='alignright'>"
			.show_checkbox(array('name' => 'intQueryTypeForced', 'text' => 'Required', 'value' => $intQueryTypeForced, 'compare' => 1, 'xtra' => " class='tr_forced'"));

			$arr_data = array();

			$arr_data[] = array("", "-- Choose here --");

			$result = $wpdb->get_results("SELECT checkID, checkLang FROM ".$wpdb->prefix."query_check WHERE checkPublic = '1' ORDER BY checkLang ASC");
			$rows = count($result);

			foreach($result as $r)
			{
				$strCheckName = $r->checkLang;

				$arr_data[] = array($r->checkID, $strCheckName);
			}

			echo show_select(array('data' => $arr_data, 'name' => "intCheckID", 'compare' => $intCheckID, 'text' => "Validate as", 'class' => "tr_check"))
			."<div class='tr_range'>"
				.show_textfield('strQueryTypeMin', 'Min value', $strQueryTypeMin, 3, 5, false)
				.show_textfield('strQueryTypeMax', 'Max value', $strQueryTypeMax, 3, 5, false)
			."</div>
			<div class='tr_select'>
				<label>Value:</label>
				<div class='select_rows'>";

					if($strQueryTypeSelect == '')
					{
						$strQueryTypeSelect = "|";
					}

					$arr_select_rows = explode(",", $strQueryTypeSelect);

					foreach($arr_select_rows as $select_row)
					{
						$arr_select_row_content = explode("|", $select_row);

						echo "<div>"
							.show_textfield('strQueryTypeSelect_id', '', $arr_select_row_content[0])
							.show_textfield('strQueryTypeSelect_value', '', $arr_select_row_content[1])
						."</div>";
					}

				echo "</div>
				<i class='icon-plus-sign'></i>"
				.input_hidden('strQueryTypeSelect', $strQueryTypeSelect)
			."</div>
		</div>
		<div class='clear'>"
			.show_submit('btnQueryAdd', ($intQuery2TypeID > 0 ? "Update" : "Add"))
			.input_hidden('intQueryID', $intQueryID)
			.input_hidden('intQuery2TypeID', $intQuery2TypeID)
		."</div>
	</form>
	<h2 id='preview'>Preview</h2>"
	.show_query_form(array('query_id' => $intQueryID, 'edit' => true));
}