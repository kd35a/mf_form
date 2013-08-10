<?php
/*
Plugin Name: Forms
Version: 1.3.0
Author: Martin Fors
Author URI: www.martinfors.se
*/

/* External */
add_shortcode('form_shortcode', 'form_shortcode');
add_action('wp_head', 'form_head');

include("include/functions.php");

if(isset($_POST['btnQuerySubmit']))
{
	$folder = str_replace("plugins/mf_form", "themes/theme_2013", dirname(__FILE__));

	include($folder."/functions.php");

	$intQueryID = check_var('intQueryID');

	$str_referer_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "";

	$strAnswerIP = $_SERVER['REMOTE_ADDR'];

	$send_text = "";

	$result = $wpdb->get_results("SELECT queryName, queryEmail, queryEmailName FROM ".$wpdb->prefix."query WHERE queryID = '".$intQueryID."'");
	$r = $result[0];
	$strQueryName = $r->queryName;
	$strQueryEmail = $r->queryEmail;
	$strQueryEmailName = $r->queryEmailName;

	$result = $wpdb->get_results("SELECT query2TypeID, queryTypeID, queryTypeText, checkCode, queryTypeForced FROM ".$wpdb->prefix."query_check RIGHT JOIN ".$wpdb->prefix."query2type USING (checkID) INNER JOIN ".$wpdb->prefix."query_type USING (queryTypeID) WHERE queryID = '".$intQueryID."' ORDER BY query2TypeOrder ASC, query2TypeCreated ASC");

	foreach($result as $r)
	{
		$intQuery2TypeID2 = $r->query2TypeID;
		$intQueryTypeID2 = $r->queryTypeID;
		$strQueryTypeText = $r->queryTypeText;
		$strCheckCode = $r->checkCode != '' ? $r->checkCode : "char";
		$intQueryTypeRequired = $r->queryTypeForced;

		$var = check_var($intQuery2TypeID2, $strCheckCode, true, '', false, 'post');

		$send_text .= $strQueryTypeText;

		//Dold info
		if($intQueryTypeID2 == 13)
		{
			$regexp1 = "/\[var=(.*?)]/";
			$regexp2 = "/\[(x*?)]/";

			if(preg_match($regexp1, $strQueryTypeText))
			{
				$query_var_name = get_match($regexp1, $strQueryTypeText, false);

				$var = check_var($query_var_name);
			}

			else if(preg_match($regexp2, $strQueryTypeText))
			{
				$query_counter_value = get_match($regexp2, $strQueryTypeText, false);
				$query_counter_label = str_replace("[".$query_counter_value."]", "", $strQueryTypeText);

				$strQueryCounter = $wpdb->get_var("SELECT answerText FROM ".$wpdb->prefix."query2answer INNER JOIN ".$wpdb->prefix."query_answer USING (answerID) INNER JOIN ".$wpdb->prefix."query2type USING (query2TypeID) WHERE ".$wpdb->prefix."query2answer.queryID = '".$intQueryID."' AND queryTypeID = '".$intQueryTypeID2."' AND answerText LIKE '%".$query_counter_label."%' ORDER BY answerCreated DESC");

				$query_counter_value_old = str_replace($query_counter_label, "", $strQueryCounter);

				if($query_counter_value_old == "")
				{
					$query_counter_value_old = 0;
				}

				$query_counter_value_old++;

				$var = $query_counter_label.zeroise($query_counter_value_old, strlen($query_counter_value));
			}

			else
			{
				$var = $strQueryTypeText;
			}

			$arr_query[] = "INSERT INTO ".$wpdb->prefix."query_answer (answerID, query2TypeID, answerText) VALUES ([answer_id], '".$intQuery2TypeID2."', '".$var."')";

			$send_text .= " ".$var."\n";
		}

		//Kopplad textruta
		###################################
		else if($intQueryTypeID2 == 14)
		{
			list($strQueryTypeText, $arr_content1) = explode(":", $strQueryTypeText);
			list($intQueryID_temp, $intQuery2TypeID2_temp) = explode("|", $arr_content1);

			$result = $wpdb->get_results("SELECT answerID FROM ".$wpdb->prefix."query2answer INNER JOIN ".$wpdb->prefix."query_answer USING (answerID) WHERE queryID = '".$intQueryID_temp."' AND query2TypeID = '".$intQuery2TypeID2_temp."' AND answerText = '".$var."'");
			$rows = count($result);

			if($rows == 0)
			{
				$var = "";

				echo "Error (".$strQueryTypeText.")";
				exit;
			}
		}
		###################################

		//Kolla om det är en e-postadress och validera isf (samt lösen, förnamn och efternamn)
		############################
		/*if($var != '' && $intQueryAccountEmail == $intQuery2TypeID2)
		{
			$var_test = check_var($var, 'email', false, '', true);

			if($var_test != $var)
			{
				$globals['error_text'] = $arr_lang['email_not_correct']." (".$strQueryTypeText.")";

				$var = "";
			}
		}*/
		############################

		if($var != '')
		{
			$arr_query[] = "INSERT INTO ".$wpdb->prefix."query_answer (answerID, query2TypeID, answerText) VALUES ([answer_id], '".$intQuery2TypeID2."', '".$var."')";

			$send_text .= " ".$var."\n";
		}

		/*else if($intQueryTypeID2 == 2)
		{
			if($query_send_every_submit == true)
			{
				$var_radio = isset($_POST['radio']) ? check_var($_POST['radio'], 'char', false) : '';

				$strQueryTypeText_temp = run_query(3, "SELECT queryTypeText FROM ".$wpdb->prefix."query2type WHERE query2TypeID = '".$var_radio."'");

				$send_text .= ($strQueryTypeText_temp == $strQueryTypeText ? " x" : "")."\n[br]";
			}
		}*/

		else if($intQueryTypeID2 == 8)
		{
			$var_radio = isset($_POST['radio_'.$intQuery2TypeID2]) ? check_var($_POST['radio_'.$intQuery2TypeID2], 'int', false) : '';

			if($var_radio != '')
			{
				$arr_query[] = "INSERT INTO ".$wpdb->prefix."query_answer (answerID, query2TypeID, answerText) VALUES ([answer_id], '".$var_radio."', '')";
			}

			$strQueryTypeText_temp = run_query(3, "SELECT queryTypeText FROM ".$wpdb->prefix."query2type WHERE query2TypeID = '".$var_radio."'");

			$send_text .= ($strQueryTypeText_temp == $strQueryTypeText ? " x" : "")."\n";
		}

		else
		{
			if($intQueryTypeRequired == true && $globals['error_text'] == '')
			{
				echo "You have to enter all mandatory fields (".$strQueryTypeText.")";
				exit;
			}

			$send_text .= "\n";
		}
	}

	//Speciallösning tills radio buttons fungerar helt bra
	#################
	/*$var = isset($_POST['radio']) ? check_var($_POST['radio'], 'char', false) : '';

	if($var != '')
	{
		$arr_query[] = "INSERT INTO ".$wpdb->prefix."query_answer (answerID, query2TypeID, answerText) VALUES ([answer_id], '".$var."', '')";
	}*/
	#################

	//Lägger in alla rader
	if(isset($arr_query))
	{
		$updated = true;

		$wpdb->get_results("INSERT INTO ".$wpdb->prefix."query2answer (queryID, answerIP, answerCreated) VALUES ('".$intQueryID."', '".$strAnswerIP."', NOW())");

		$intAnswerID = mysql_insert_id();

		if($intAnswerID > 0)
		{
			foreach($arr_query as $query)
			{
				$wpdb->get_results(str_replace("[answer_id]", $intAnswerID, $query));

				if(mysql_affected_rows() == 0)
				{
					$updated = false;
				}
			}
		}

		else
		{
			$updated = false;
		}

		if($updated == true)
		{
			if($strQueryEmail != '' && isset($send_text) && $send_text != '')
			{
				$headers = "From: ".get_bloginfo('name')." <".get_bloginfo('admin_email').">\r\n";

				//add_filter('wp_mail_content_type', 'set_html_content_type');
				wp_mail($strQueryEmail, $strQueryEmailName, $send_text, $headers);
			}

			echo "<script>location.href = '?sent';</script>";
		}

		else
		{
			echo "There was an error...";
		}

		exit;
	}

	else
	{
		echo "You have to enter all mandatory fields";
		exit;
	}
}

function form_shortcode($atts)
{
	extract(shortcode_atts(array(
		'id' => ''
	), $atts));

	$sent = isset($_GET['sent']) ? true : false;

	return show_query_form(array('query_id' => $id, 'sent' => $sent));
}

function form_head()
{
	echo "<link href='".plugins_url()."/mf_form/include/style.css' rel='stylesheet'/>";
}

add_action('widgets_init', 'form_load_widgets');

function form_load_widgets()
{
	register_widget('form_Widget');
}

class form_Widget extends WP_Widget
{
	function form_Widget()
	{
		$widget_ops = array('classname' => 'form');

		$control_ops = array('id_base' => 'form-widget'); //'width' => 300, 'height' => 350, 

		$this->WP_Widget('form-widget', __('Form Widget', 'form'), $widget_ops, $control_ops);
	}

	function widget($args, $instance)
	{
		global $wpdb;

		extract($args);

		//wp_enqueue_script('jquery-form', "/wp-content/plugins/mf_form/jquery-form.js", array('jquery'), '1.0', true);
		//wp_enqueue_script('jquery-form');

		$sent = isset($_GET['sent']) ? true : false;

		echo show_query_form(array('query_id' => $instance['form_id'], 'sent' => $sent));
	}

	function update($new_instance, $old_instance)
	{
		$instance = $old_instance;

		$instance['form_id'] = strip_tags($new_instance['form_id']);

		return $instance;
	}

	function form($instance)
	{
		global $wpdb;

		$defaults = array('form_id' => "");
		$instance = wp_parse_args((array)$instance, $defaults);

		echo "<p>
			<label for='".$this->get_field_id('form_id')."'>Form</label>
			<select name='".$this->get_field_name('form_id')."' id='".$this->get_field_id('form_id')."' class='widefat'>
				<option value=''>-- Choose here --</option>";

				$result = $wpdb->get_results("SELECT queryID, queryName FROM ".$wpdb->prefix."query ORDER BY queryName ASC");

				foreach($result as $r)
				{
					echo "<option value='".$r->queryID."'".($instance['form_id'] == $r->queryID ? " selected" : "").">".$r->queryName."</option>";
				}

			echo "</select>
		</p>";
	}
}

/* Internal */
register_activation_hook(__FILE__, 'form_activate');
register_deactivation_hook(__FILE__, 'form_deactivate');
register_uninstall_hook(__FILE__, 'form_uninstall');

function form_activate()
{
	global $wpdb;

	$arr_create_tables = array();

	$arr_create_tables[$wpdb->prefix."query"] = "CREATE TABLE ".$wpdb->prefix."query (
		queryID int(4) unsigned NOT NULL AUTO_INCREMENT,
		queryName varchar(100) DEFAULT NULL,
		queryAnswerName varchar(50) DEFAULT NULL,
		queryAnswer text,
		queryEmail varchar(100) DEFAULT NULL,
		queryEmailName varchar(100) DEFAULT NULL,
		queryDeadline date DEFAULT NULL,
		queryCreated datetime DEFAULT NULL,
		userID int(4) unsigned DEFAULT '0',
		PRIMARY KEY (queryID)
	)";

	$arr_create_tables[$wpdb->prefix."query2answer"] = "CREATE TABLE ".$wpdb->prefix."query2answer (
	  answerID int(4) unsigned NOT NULL AUTO_INCREMENT,
	  queryID int(4) unsigned NOT NULL,
	  answerIP varchar(15) DEFAULT NULL,
	  userID int(4) unsigned DEFAULT NULL,
	  answerCreated datetime DEFAULT NULL,
	  PRIMARY KEY (answerID),
	  KEY queryID (queryID)
	  )";

	$arr_create_tables[$wpdb->prefix."query2type"] = "CREATE TABLE ".$wpdb->prefix."query2type (
	  query2TypeID int(4) unsigned NOT NULL AUTO_INCREMENT,
	  queryID int(4) unsigned DEFAULT '0',
	  queryTypeID int(2) unsigned DEFAULT '0',
	  queryTypeText text,
	  checkID int(1) unsigned DEFAULT NULL,
	  queryTypeForced enum('0','1') NOT NULL DEFAULT '0',
	  query2TypeOrder int(2) unsigned NOT NULL DEFAULT '0',
	  query2TypeCreated datetime DEFAULT NULL,
	  userID int(4) unsigned DEFAULT NULL,
	  PRIMARY KEY (query2TypeID),
	  KEY queryID (queryID),
	  KEY queryTypeID (queryTypeID)
	  )";
	
	$arr_create_tables[$wpdb->prefix."query_answer"] = "CREATE TABLE ".$wpdb->prefix."query_answer (
	  answerID int(4) unsigned DEFAULT NULL,
	  query2TypeID int(4) unsigned DEFAULT '0',
	  answerText text,
	  KEY query2TypeID (query2TypeID),
	  KEY answerID (answerID)
	) ENGINE=MyISAM DEFAULT CHARSET=latin1";

	$arr_create_tables[$wpdb->prefix."query_check"] = "CREATE TABLE ".$wpdb->prefix."query_check (
	  checkID int(1) unsigned NOT NULL AUTO_INCREMENT,
	  checkPublic enum('0','1') NOT NULL DEFAULT '1',
	  checkLang varchar(20) DEFAULT NULL,
	  checkCode varchar(10) DEFAULT NULL,
	  PRIMARY KEY (checkID)
	)";

	$arr_create_tables[$wpdb->prefix."query_type"] = "CREATE TABLE ".$wpdb->prefix."query_type (
	  queryTypeID int(2) unsigned NOT NULL AUTO_INCREMENT,
	  queryTypePublic enum('0','1') NOT NULL DEFAULT '1',
	  queryTypeLang varchar(30) DEFAULT NULL,
	  queryTypeOrder int(2) unsigned DEFAULT NULL,
	  queryTypeResult enum('0','1') NOT NULL DEFAULT '1',
	  PRIMARY KEY (queryTypeID)
	)";

	foreach($arr_create_tables as $key => $value)
	{
		$result = $wpdb->get_results("SHOW TABLES LIKE ".$key);

		if(count($result) == 0)
		{
			$wpdb->query($value);
		}
	}

	$arr_update_tables = array();

	$arr_update_tables[$wpdb->prefix."query"]['queryEmail'] = "ALTER TABLE ".$wpdb->prefix."query ADD queryEmail VARCHAR(100) AFTER queryAnswer";
	$arr_update_tables[$wpdb->prefix."query"]['queryEmailName'] = "ALTER TABLE ".$wpdb->prefix."query ADD queryEmailName VARCHAR(100) AFTER queryEmail";

	foreach($arr_update_tables as $table => $arr_col)
	{
		foreach($arr_col as $col => $value)
		{
			$result = $wpdb->get_results("SHOW COLUMNS FROM ".$table." WHERE Field = '".$col."'");

			if(count($result) == 0)
			{
				$wpdb->query($value);
			}
		}
	}

	$arr_insert_tables = array();

	$arr_insert_tables[$wpdb->prefix."query_check"] = "INSERT INTO ".$wpdb->prefix."query_check VALUES('1','1','integer','int'),
	('2','1','short_date','shortDate2'),
	('3','1','date','date'),
	('5','1','email','email'),
	('6','1','phone','telno'),
	('7','1','float','float'),
	('8','1','url','url')";

	$arr_insert_tables[$wpdb->prefix."query_type"] = "INSERT INTO ".$wpdb->prefix."query_type VALUES('1','1','checkbox','5','1'),
	('2','0','radio_button','3','1'),
	('3','1','input_field','6','1'),
	('4','1','textarea','7','1'),
	('5','1','text','2','0'),
	('6','1','space','1','0'),
	('7','0','textarea_advanced','8','1'),
	('8','1','radio_button','4','1'),
	('9','0','file_upload','9','1'),
	('10','1','select_box','10','1'),
	('13','0','hidden_info','13','1'),
	('14','0','input_field_connected','13','1')";

	foreach($arr_insert_tables as $key => $value)
	{
		$result = $wpdb->get_results("SELECT * FROM ".$key);

		if(count($result) == 0)
		{
			$wpdb->query($value);
		}
	}
}

function form_deactivate()
{

}

function form_uninstall()
{

}

add_action('admin_menu', 'edit_form');

function edit_form()
{
	global $wpdb;

	$menu_root = 'mf_form/';
	$menu_start = $menu_root.'list/index.php';

	/*$user_levels = array('subscriber', 'author', 'contributor', 'editor', 'administrator');

	foreach($user_levels as $level)
	{
		if(current_user_can($level))
		{
			$menu_label_1 = $level;
		}
	}*/

	$user_levels = array('editor', 'administrator');

	foreach($user_levels as $level)
	{
		if(current_user_can($level))
		{
			$menu_label_2 = $level;
		}
	}

	add_menu_page(__('Form'), __('Form'), $menu_label_2, $menu_start);
	
	add_submenu_page($menu_start, __('Create new'), __('Create new'), $menu_label_2, $menu_root.'create/index.php');
	add_submenu_page($menu_start, __('List'), __('List'), $menu_label_2, $menu_root.'list/index.php');
	add_submenu_page($menu_start, __('All answers'), __(''), $menu_label_2, $menu_root.'answer/index.php');
	add_submenu_page($menu_start, __('Latest answer'), __(''), $menu_label_2, $menu_root.'view/index.php');
	add_submenu_page($menu_start, __('Export'), __(''), $menu_label_2, $menu_root.'export/index.php');
}