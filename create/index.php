<?php

wp_enqueue_style('forms-font_awesome', "//netdna.bootstrapcdn.com/font-awesome/3.1.1/css/font-awesome.css");
wp_enqueue_style('forms-style_wp', plugins_url()."/mf_form/include/style_wp.css");
//wp_enqueue_style('style-theme', get_bloginfo('template_url')."/style.css");
wp_enqueue_script('jquery-forms', plugins_url()."/mf_form/include/script_wp.js", array('jquery'), '1.0', true);

$folder = str_replace("plugins/mf_form/create", "", dirname(__FILE__));

$intQueryID = check_var('intQueryID');

$intQuery2TypeID = check_var('intQuery2TypeID');
$intQuery2TypeOrder = check_var('intQuery2TypeOrder');

$strQueryName = check_var('strQueryName');
$strQueryAnswerName = check_var('strQueryAnswerName');
$strQueryAnswer = check_var('strQueryAnswer');
$strQueryEmail = check_var('strQueryEmail', 'email');
$strQueryEmailName = check_var('strQueryEmailName');
$strQueryButtonText = check_var('strQueryButtonText');
//$dteQueryDeadline = check_var('dteQueryDeadline', 'date');
$intQueryTypeID = check_var('intQueryTypeID');
//$strQueryTypeText = check_var('strQueryTypeText');
$strQueryTypeText = isset($_POST['strQueryTypeText']) ? $_POST['strQueryTypeText'] : "";
$intCheckID = check_var('intCheckID');
$strQueryTypeClass = check_var('strQueryTypeClass');

$strQueryTypeSelect = check_var('strQueryTypeSelect', '', true, "0|-- Choose here --,1|Nej,2|Ja");
$strQueryTypeMin = check_var('strQueryTypeMin', '', true, "0");
$strQueryTypeMax = check_var('strQueryTypeMax', '', true, 100);
$strQueryTypeDefault = check_var('strQueryTypeDefault', '', true, 1);
//$intQueryTypeForced = isset($_POST['intQueryTypeForced']) ? 1 : 0;

$error_text = $done_text = "";

if(isset($_POST['btnFormExport']))
{
	$db_info = "";

	$arr_cols = array("queryTypeID", "queryTypeText", "checkID", "queryTypeClass", "queryTypeForced", "query2TypeOrder");

	$result = $wpdb->get_results("SELECT ".implode(", ", $arr_cols)." FROM ".$wpdb->base_prefix."query2type WHERE queryID = '".$intQueryID."'");

	foreach($result as $r)
	{
		$db_info .= "queryID = '[query_id]'";

			foreach($arr_cols as $str_col)
			{
				//$r->$str_col = str_replace("\n", "\\n", addslashes($r->$str_col));

				$db_info .= ", ".$str_col." = ".(isset($r->$str_col) ? "'".str_replace("\n", "[nl]", $r->$str_col)."'" : "'NULL'");
			}

		$db_info .= ", query2TypeCreated = NOW(), userID = [user_id]\n";
	}

	if($db_info != '')
	{
		$strQueryName = $wpdb->get_var("SELECT queryName FROM ".$wpdb->base_prefix."query WHERE queryID = '".$intQueryID."'");

		$file = sanitize_title_with_dashes(sanitize_title($strQueryName))."_".date("YmdHis").".sql";

		$success = set_file_content(array('file' => $folder."/uploads/".$file, 'mode' => 'a', 'content' => trim($db_info)));

		$done_text = "Download exported file at <a href='../wp-content/uploads/".$file."'>".$file."</a>";
	}

	else
	{
		echo "It was not possible to export the form";
	}
}

else if(isset($_POST['btnFormImport']))
{
	if(isset($_FILES['strFileForm']))
	{
		$file_name = $_FILES['strFileForm']['name'];
		$file_location = $_FILES['strFileForm']['tmp_name'];

		if($file_name == '')
		{
			$error_text = "You have to submit a file";
		}

		else if(!is_uploaded_file($file_location))
		{
			$error_text = "Could not upload the file for import";
		}

		else
		{
			$inserted = 0;

			$content = get_file_content(array('file' => $file_location));

			$content = str_replace("[query_id]", $intQueryID, $content);
			$content = str_replace("[user_id]", get_current_user_id(), $content);

			$arr_row = explode("\n", trim($content));

			foreach($arr_row as $str_row)
			{
				if($str_row != '')
				{
					$wpdb->query("INSERT INTO ".$wpdb->base_prefix."query2type SET ".str_replace("[nl]", "\n", $str_row));

					if(mysql_affected_rows() > 0)
					{
						$inserted++;
					}
				}
			}

			if($inserted > 0)
			{
				$done_text = $inserted." fields imported to the form";
			}

			else
			{
				$error_text = "No fields were imported";
			}
		}
	}

	else
	{
		$error_text = "There is no file to import";
	}
}

else if(isset($_POST['btnQueryCreate']))
{
	if($strQueryName == '')
	{
		$error_text = "Enter all mandatory fields";
	}

	else
	{
		if($intQueryID > 0)
		{
			$wpdb->get_results("UPDATE ".$wpdb->base_prefix."query SET queryName = '".$strQueryName."', queryAnswerName = '".$strQueryAnswerName."', queryAnswer = '".$strQueryAnswer."', queryEmail = '".$strQueryEmail."', queryEmailName = '".$strQueryEmailName."', queryButtonText = '".$strQueryButtonText."' WHERE queryID = '".$intQueryID."'");
		}

		else
		{
			$result = $wpdb->get_results("SELECT queryID FROM ".$wpdb->base_prefix."query WHERE queryName = '".$strQueryName."'");

			if(count($result) > 0)
			{
				$error_text = "There is already a form with that name. Try with another one.";
			}

			else
			{
				$wpdb->get_results("INSERT INTO ".$wpdb->base_prefix."query SET queryName = '".$strQueryName."', queryAnswerName = '".$strQueryAnswerName."', queryAnswer = '".$strQueryAnswer."', queryEmail = '".$strQueryEmail."', queryEmailName = '".$strQueryEmailName."', queryButtonText = '".$strQueryButtonText."', queryCreated = NOW(), userID = '".get_current_user_id()."'");
				$intQueryID = mysql_insert_id();
			}
		}

		if(mysql_affected_rows() > 0)
		{
			echo "<script>location.href='/wp-admin/admin.php?page=mf_form/create/index.php&intQueryID=".$intQueryID."#content'</script>";
		}
	}
}

else if(isset($_POST['btnQueryAdd']))
{
	//Clean up settings if not used for the specific type of field
	################
	if($intQueryTypeID == 6) //Space
	{
		$strQueryTypeText = '';
	}

	if($intQueryTypeID != 3)
	{
		$intCheckID = '';
	}
	################

	if(($intQueryTypeID == 10 || $intQueryTypeID == 11) && $strQueryTypeSelect == "")
	{
		$error_text = "Enter all mandatory fields";
	}

	else
	{
		if($intQueryTypeID == 2)
		{
			$strQueryTypeText = str_replace("|", "", $strQueryTypeText)."|".str_replace("|", "", $strQueryTypeMin)."|".str_replace("|", "", $strQueryTypeMax)."|".str_replace("|", "", $strQueryTypeDefault);
		}

		else if($intQueryTypeID == 10 || $intQueryTypeID == 11)
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
				$wpdb->query($wpdb->prepare("UPDATE ".$wpdb->base_prefix."query2type SET queryTypeID = '".$intQueryTypeID."', queryTypeText = %s, checkID = '".$intCheckID."', queryTypeClass = '".$strQueryTypeClass."', userID = '".get_current_user_id()."' WHERE query2TypeID = '".$intQuery2TypeID."'", $strQueryTypeText)); //, queryTypeForced = '".$intQueryTypeForced."'

				$intQuery2TypeID = $intQueryTypeID = $strQueryTypeText = $intCheckID = $strQueryTypeClass = "";
			}

			else
			{
				$error_text = "Couldn't update the field";
			}
		}

		else
		{
			if($intQueryID > 0 && $intQueryTypeID > 0 && ($intQueryTypeID == 6 || $strQueryTypeText != ''))
			{
				$intQuery2TypeOrder = $wpdb->get_var("SELECT query2TypeOrder + 1 FROM ".$wpdb->base_prefix."query2type WHERE queryID = '".$intQueryID."' ORDER BY query2TypeOrder DESC");

				$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->base_prefix."query2type SET queryID = '".$intQueryID."', queryTypeID = '".$intQueryTypeID."', queryTypeText = %s, checkID = '".$intCheckID."', queryTypeClass = '".$strQueryTypeClass."', query2TypeOrder = '".$intQuery2TypeOrder."', query2TypeCreated = NOW(), userID = '".get_current_user_id()."'", $strQueryTypeText)); //, queryTypeForced = '".$intQueryTypeForced."'

				if(mysql_affected_rows() > 0)
				{
					$intQueryTypeID = $strQueryTypeText = $intCheckID = $strQueryTypeClass = "";
				}
			}

			else
			{
				$error_text = "Couldn't insert the new field";
			}
		}
	}

	if($intQueryTypeID == 0)
	{
		echo "<script>location.href='/wp-admin/admin.php?page=mf_form/create/index.php&intQueryID=".$intQueryID."#content'</script>";
	}
}

if($intQueryID > 0)
{
	$result = $wpdb->get_results("SELECT queryName, queryAnswerName, queryAnswer, queryEmail, queryEmailName, queryButtonText, queryCreated FROM ".$wpdb->base_prefix."query WHERE queryID = '".$intQueryID."'"); //, queryDeadline
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
	$result = $wpdb->get_results("SELECT queryTypeID, queryTypeText, checkID, queryTypeClass FROM ".$wpdb->base_prefix."query2type WHERE query2TypeID = '".$intQuery2TypeID."'"); //, queryTypeForced
	$r = $result[0];
	$intQueryTypeID = $r->queryTypeID;
	$strQueryTypeText = $r->queryTypeText;
	$intCheckID = $r->checkID;
	$strQueryTypeClass = $r->queryTypeClass;
	//$intQueryTypeForced = $r->queryTypeForced;

	if($intQueryTypeID == 2)
	{
		list($strQueryTypeText, $strQueryTypeMin, $strQueryTypeMax, $strQueryTypeDefault) = explode("|", $strQueryTypeText);
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

echo "<h1>".($intQueryID > 0 ? "Update ".$strQueryName : "Add New")."</h1>";

if($error_text != '')
{
	echo "<div id='notification'><div class='error'>".$error_text."</div></div>";
}

if($done_text != '')
{
	echo "<div id='notification'><div class='done'>".$done_text."</div></div>";
}

echo "<form method='post' action='' class='mf_form'>
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
	echo "<form method='post' action='' id='content' class='mf_form'>"
		."<h2>Content</h2>
		<div class='alignleft'>";

			if($intQueryTypeID == '')
			{
				$intQueryTypeID = $wpdb->get_var("SELECT queryTypeID FROM ".$wpdb->base_prefix."query2type WHERE userID = '".get_current_user_id()."' ORDER BY query2TypeCreated DESC");
			}

			$arr_data = array();

			$arr_data[] = array("", "-- Choose here --");

			$result = $wpdb->get_results("SELECT queryTypeID, queryTypeLang FROM ".$wpdb->base_prefix."query_type WHERE (queryTypeID = '".$intQueryTypeID."' OR queryTypePublic >= '1') ORDER BY queryTypeOrder ASC");

			foreach($result as $r)
			{
				$arr_data[] = array($r->queryTypeID, $r->queryTypeLang);
			}

			echo show_select(array('data' => $arr_data, 'name' => 'intQueryTypeID', 'compare' => $intQueryTypeID, 'text' => "Type"))
			.show_textarea(array('name' => 'strQueryTypeText', 'text' => "Text", 'value' => $strQueryTypeText, 'size' => 'small', 'class' => "tr_text"));

		echo "</div>
		<div class='alignright'>"
			//.show_checkbox(array('name' => 'intQueryTypeForced', 'text' => 'Required', 'value' => $intQueryTypeForced, 'compare' => 1, 'xtra' => " class='tr_forced'"))
			.show_textfield('strQueryTypeClass', "Custom CSS class", $strQueryTypeClass, 50);

			$arr_data = array();

			$arr_data[] = array("", "-- Choose here --");

			$result = $wpdb->get_results("SELECT checkID, checkLang FROM ".$wpdb->base_prefix."query_check WHERE checkPublic = '1' ORDER BY checkLang ASC");
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
				.show_textfield('strQueryTypeDefault', 'Default value', $strQueryTypeDefault, 3, 5, false)
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
	</form>";

	$form_output = show_query_form(array('query_id' => $intQueryID, 'edit' => true));

	if($form_output != '')
	{
		echo "<h2>Preview</h2>"
		.$form_output;

		echo "<h2>Export to file</h2>
		<form method='post' action='' class='mf_form'>"
			.show_submit('btnFormExport', "Export")
			.input_hidden('intQueryID', $intQueryID)
		."</form>";
	}

	echo "<h2>Import from file</h2>
	<form method='post' action='' enctype='multipart/form-data' class='mf_form'>"
		.show_file_field(array('name' => 'strFileForm', 'text' => "File"))
		.show_submit('btnFormImport', "Import")
		.input_hidden('intQueryID', $intQueryID)
	."</form>";
}