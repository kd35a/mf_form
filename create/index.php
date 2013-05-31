<?php

$intQueryID = check_var('intQueryID');

$intQuery2TypeID = check_var('intQuery2TypeID');
$intQuery2TypeOrder = check_var('intQuery2TypeOrder');

$strQueryName = check_var('strQueryName');
$strQueryAnswerName = check_var('strQueryAnswerName');
$strQueryAnswer = check_var('strQueryAnswer');
$strQueryEmail = check_var('strQueryEmail', 'email');
$strQueryEmailName = check_var('strQueryEmailName');
$dteQueryDeadline = check_var('dteQueryDeadline', 'date');
$intQueryTypeID = check_var('intQueryTypeID');
$strQueryTypeText = check_var('strQueryTypeText');
$intCheckID = check_var('intCheckID');

$strQueryTypeSelect = check_var('strQueryTypeSelect', '', true, "0|-- Choose here --,1|Nej,2|Ja");

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
			$wpdb->get_results("UPDATE ".$wpdb->prefix."query SET queryName = '".$strQueryName."', queryAnswerName = '".$strQueryAnswerName."', queryAnswer = '".$strQueryAnswer."', queryEmail = '".$strQueryEmail."', queryEmailName = '".$strQueryEmailName."' WHERE queryID = '".$intQueryID."'");
		}

		else
		{
			$result = $wpdb->get_results("SELECT queryID FROM ".$wpdb->prefix."query WHERE queryName = '".$strQueryName."'");

			if(count($result) > 0)
			{
				echo "Det finns redan ett formul&auml;r med det namnet. Testa med ett annat.";
			}

			else
			{
				$wpdb->get_results("INSERT INTO ".$wpdb->prefix."query SET queryName = '".$strQueryName."', queryAnswerName = '".$strQueryAnswerName."', queryAnswer = '".$strQueryAnswer."', queryEmail = '".$strQueryEmail."', queryEmailName = '".$strQueryEmailName."', queryCreated = NOW(), userID = '".get_current_user_id()."'");
				$intQueryID = mysql_insert_id();
			}
		}

		if(mysql_affected_rows() > 0)
		{
			echo "<script>location.href='/wp-admin/admin.php?page=mf_form/create/index.php&intQueryID=".$intQueryID."'</script>";
		}

		else
		{
			echo "Error creating...";
		}
	}
}

else if(isset($_POST['btnQueryAdd']))
{
	//Tar bort medskickad info om det är "fel" typ
	################
	if($intQueryTypeID == 6) //Mellanrum
	{
		$strQueryTypeText = '';
	}
	################

	if($intQueryTypeID == 10 && $strQueryTypeSelect == "")
	{
		echo "Enter all mandatory fields";
		exit;
	}

	else
	{
		if($intQueryTypeID == 10)
		{
			$strQueryTypeText = str_replace(":", "", $strQueryTypeText).":".str_replace(":", "", $strQueryTypeSelect);
		}

		else if($intQueryTypeID == 14)
		{
			$strQueryTypeText = str_replace(":", "", $strQueryTypeText).":".str_replace(":", "", $strQueryTypeSelect);
		}

		if($intQuery2TypeID > 0)
		{
			if($intQueryTypeID > 0 && ($intQueryTypeID == 6 || $strQueryTypeText != ''))
			{
				$wpdb->get_results("UPDATE ".$wpdb->prefix."query2type SET queryTypeID = '".$intQueryTypeID."', queryTypeText = '".$strQueryTypeText."', checkID = '".$intCheckID."', userID = '".get_current_user_id()."' WHERE query2TypeID = '".$intQuery2TypeID."'");

				$intQuery2TypeID = $intQueryTypeID = $strQueryTypeText = $intCheckID = "";
			}

			else
			{
				echo "Error...";
				exit;
			}
		}

		else
		{
			if($intQueryID > 0 && $intQueryTypeID > 0 && ($intQueryTypeID == 6 || $strQueryTypeText != ''))
			{
				$intQuery2TypeOrder = $wpdb->get_var("SELECT query2TypeOrder + 1 FROM ".$wpdb->prefix."query2type WHERE queryID = '".$intQueryID."' ORDER BY query2TypeOrder DESC");

				$wpdb->get_results("INSERT INTO ".$wpdb->prefix."query2type SET queryID = '".$intQueryID."', queryTypeID = '".$intQueryTypeID."', queryTypeText = '".$strQueryTypeText."', checkID = '".$intCheckID."', query2TypeOrder = '".$intQuery2TypeOrder."', query2TypeCreated = NOW(), userID = '".get_current_user_id()."'");

				if(mysql_affected_rows() > 0)
				{
					$intQueryTypeID = $strQueryTypeText = $intCheckID = "";
				}
			}

			else
			{
				echo "Error...";
				exit;
			}
		}
	}
}

if($intQueryID > 0)
{
	$result = $wpdb->get_results("SELECT queryName, queryAnswerName, queryAnswer, queryEmail, queryEmailName, queryDeadline, queryCreated FROM ".$wpdb->prefix."query WHERE queryID = '".$intQueryID."'");
	$r = $result[0];
	$strQueryName = $r->queryName;
	$strQueryAnswerName = $r->queryAnswerName;
	$strQueryAnswer = $r->queryAnswer;
	$strQueryEmail = $r->queryEmail;
	$strQueryEmailName = $r->queryEmailName;
	$dteQueryDeadline = $r->queryDeadline;
	$strQueryCreated = $r->queryCreated;
}

if($intQuery2TypeID > 0)
{
	$result = $wpdb->get_results("SELECT queryTypeID, queryTypeText, checkID, queryTypeForced FROM ".$wpdb->prefix."query2type WHERE query2TypeID = '".$intQuery2TypeID."'");
	$r = $result[0];
	$intQueryTypeID = $r->queryTypeID;
	$strQueryTypeText = $r->queryTypeText;
	$intCheckID = $r->checkID;

	if($intQueryTypeID == 10)
	{
		list($strQueryTypeText, $strQueryTypeSelect) = explode(":", $strQueryTypeText);
	}

	else if($intQueryTypeID == 14)
	{
		list($strQueryTypeText, $strQueryTypeSelect) = explode(":", $strQueryTypeText);
	}
}

echo "<link href='//netdna.bootstrapcdn.com/font-awesome/3.1.1/css/font-awesome.css' rel='stylesheet'>
<link href='".plugins_url()."/mf_form/include/style.css' rel='stylesheet'/>
<link href='".plugins_url()."/mf_form/include/style_wp.css' rel='stylesheet'/>
<h1>".($intQueryID > 0 ? "Uppdatera ".$strQueryName : "Skapa formul&auml;r")."</h1>";

echo "<form method='post' action=''>
	<div class='alignleft'>"
		.show_textfield('strQueryName', "Namn", $strQueryName, 100, 0, true)
		."<h2>Svarsmeddelande till bes&ouml;kare</h2>"
		.show_textfield('strQueryAnswerName', "Rubrik", $strQueryAnswerName, 50, 0, true)
		.show_textarea(array('name' => 'strQueryAnswer', 'text' => "Text", 'value' => $strQueryAnswer, 'size' => 'small'))
	."</div>
	<div class='alignright'>
		<h2>Skicka e-post till</h2>"
		.show_textfield('strQueryEmail', "Adress", $strQueryEmail, 100)
		.show_textfield('strQueryEmailName', "&Auml;mne", $strQueryEmailName, 100)
		//.show_textfield('dteQueryDeadline', "Deadline", $dteQueryDeadline, 10)
	."</div>
	<div class='clear'>"
		.show_submit('btnQueryCreate', ($intQueryID > 0 ? "Uppdatera" : "Skapa"))
		.input_hidden('intQueryID', $intQueryID)
	."</div>
</form>";

if($intQueryID > 0)
{
	echo "<form method='post' action=''>"
		."<h2>Inneh&aring;ll</h2>";

		//Tar fram senast använda typ
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

		$arr_data = array();

		$arr_data[] = array("", "-- Choose here --");

		$result = $wpdb->get_results("SELECT checkID, checkLang FROM ".$wpdb->prefix."query_check WHERE checkPublic = '1' ORDER BY checkLang ASC");
		$rows = count($result);

		foreach($result as $r)
		{
			$strCheckName = $r->checkLang;

			$arr_data[] = array($r->checkID, $strCheckName);
		}

		echo show_select(array('data' => $arr_data, 'name' => "intCheckID", 'compare' => $intCheckID, 'text' => "Kolla", 'class' => "tr_check"))
		.show_textfield('strQueryTypeSelect', 'V&auml;rde', $strQueryTypeSelect, '', 0, false, "", "", " tr_select")
		.show_submit('btnQueryAdd', ($intQuery2TypeID > 0 ? "Uppdatera" : "Skapa"))
		.input_hidden('intQueryID', $intQueryID)
		.input_hidden('intQuery2TypeID', $intQuery2TypeID)
	."</form>
	<h2>F&aouml;rhandsgranskning</h2>"
	.show_query_form(array('query_id' => $intQueryID, 'edit' => true));
}

echo "<script src='".plugins_url()."/mf_form/include/jquery-1.9.1.min.js'></script>
<script src='".plugins_url()."/mf_form/include/script.js'></script>
<script src='".plugins_url()."/mf_form/include/script_create.js'></script>";