<?php
/*
 * Plugin Name: Forms
 * Plugin URI: https://github.com/frostkom/mf_form
 * Description: A Wordpress form creator
 * Version: 1.5.1
 * Author: Martin Fors
 * Author URI: www.martinfors.se
 */

/* External */
add_shortcode('mf_form', 'form_shortcode');
add_shortcode('form_shortcode', 'form_shortcode');
add_action('widgets_init', 'form_load_widgets');

wp_enqueue_style('style-forms', plugins_url()."/mf_form/include/style.css");
wp_enqueue_script('jquery-ui-sortable');
wp_enqueue_script('forms-modernizr', plugins_url()."/mf_form/include/js-webshim/extras/modernizr-custom.js", array('jquery'), '1.0', true);
wp_enqueue_script('forms-webshim', plugins_url()."/mf_form/include/js-webshim/polyfiller.js", array('jquery'), '1.0', true);
wp_enqueue_script('forms-js', plugins_url()."/mf_form/include/script.js", array('jquery'), '1.0', true);

include("include/functions.php");

function form_shortcode($atts)
{
	extract(shortcode_atts(array(
		'id' => ''
	), $atts));

	return show_query_form(array('query_id' => $id));
}

function form_load_widgets()
{
	register_widget('form_Widget');
}

class form_Widget extends WP_Widget
{
	function form_Widget()
	{
		$widget_ops = array('classname' => 'form');

		$control_ops = array('id_base' => 'form-widget');

		$this->WP_Widget('form-widget', __('Forms widget', 'form'), $widget_ops, $control_ops);
	}

	function widget($args, $instance)
	{
		global $wpdb;

		extract($args);

		echo show_query_form(array('query_id' => $instance['form_id']));
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

				$result = $wpdb->get_results("SELECT queryID, queryName FROM ".$wpdb->base_prefix."query ORDER BY queryName ASC");

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

	$arr_create_tables[$wpdb->base_prefix."query"] = "CREATE TABLE ".$wpdb->base_prefix."query (
		queryID int(4) unsigned NOT NULL AUTO_INCREMENT,
		queryName varchar(100) DEFAULT NULL,
		queryAnswerName varchar(50) DEFAULT NULL,
		queryAnswer text,
		queryEmail varchar(100) DEFAULT NULL,
		queryEmailName varchar(100) DEFAULT NULL,
		queryDenyDups ENUM('0', '1') NOT NULL DEFAULT '0',
		queryShowAnswers ENUM('0', '1') NOT NULL DEFAULT '0',
		queryEncrypted ENUM('0', '1') NOT NULL DEFAULT '0',
		queryMandatoryText varchar(100) DEFAULT NULL,
		queryButtonText varchar(100) DEFAULT NULL,
		queryDeadline date DEFAULT NULL,
		queryCreated datetime DEFAULT NULL,
		userID int(4) unsigned DEFAULT '0',
		PRIMARY KEY (queryID)
	)";

	$arr_create_tables[$wpdb->base_prefix."query2answer"] = "CREATE TABLE ".$wpdb->base_prefix."query2answer (
		answerID int(4) unsigned NOT NULL AUTO_INCREMENT,
		queryID int(4) unsigned NOT NULL,
		answerIP varchar(15) DEFAULT NULL,
		userID int(4) unsigned DEFAULT NULL,
		answerCreated datetime DEFAULT NULL,
		PRIMARY KEY (answerID),
		KEY queryID (queryID)
	)";

	$arr_create_tables[$wpdb->base_prefix."query2type"] = "CREATE TABLE ".$wpdb->base_prefix."query2type (
		query2TypeID int(4) unsigned NOT NULL AUTO_INCREMENT,
		queryID int(4) unsigned DEFAULT '0',
		queryTypeID int(2) unsigned DEFAULT '0',
		queryTypeText text,
		checkID int(1) unsigned DEFAULT NULL,
		queryTypeClass varchar(50) DEFAULT NULL,
		queryTypeForced enum('0','1') NOT NULL DEFAULT '0',
		query2TypeOrder int(2) unsigned NOT NULL DEFAULT '0',
		query2TypeCreated datetime DEFAULT NULL,
		userID int(4) unsigned DEFAULT NULL,
		PRIMARY KEY (query2TypeID),
		KEY queryID (queryID),
		KEY queryTypeID (queryTypeID)
	)";
	
	$arr_create_tables[$wpdb->base_prefix."query_answer"] = "CREATE TABLE ".$wpdb->base_prefix."query_answer (
		answerID int(4) unsigned DEFAULT NULL,
		query2TypeID int(4) unsigned DEFAULT '0',
		answerText text,
		KEY query2TypeID (query2TypeID),
		KEY answerID (answerID)
	) ENGINE=MyISAM DEFAULT CHARSET=latin1";

	$arr_create_tables[$wpdb->base_prefix."query_check"] = "CREATE TABLE ".$wpdb->base_prefix."query_check (
		checkID int(1) unsigned NOT NULL AUTO_INCREMENT,
		checkPublic enum('0','1') NOT NULL DEFAULT '1',
		checkLang varchar(20) DEFAULT NULL,
		checkCode varchar(10) DEFAULT NULL,
		PRIMARY KEY (checkID)
	)";

	$arr_create_tables[$wpdb->base_prefix."query_type"] = "CREATE TABLE ".$wpdb->base_prefix."query_type (
		queryTypeID int(2) unsigned NOT NULL AUTO_INCREMENT,
		queryTypePublic enum('0','1') NOT NULL DEFAULT '1',
		queryTypeLang varchar(30) DEFAULT NULL,
		queryTypeOrder int(2) unsigned DEFAULT NULL,
		queryTypeResult enum('0','1') NOT NULL DEFAULT '1',
		PRIMARY KEY (queryTypeID)
	)";

	$arr_create_tables[$wpdb->base_prefix."query_zipcode"] = "CREATE TABLE ".$wpdb->base_prefix."query_zipcode (
		addressZipCode int(5) NOT NULL DEFAULT '0',
		cityName varchar(20) DEFAULT NULL,
		municipalityName varchar(20) DEFAULT NULL,
		countyName varchar(20) DEFAULT NULL,
		PRIMARY KEY (addressZipCode)
	)";

	foreach($arr_create_tables as $key => $value)
	{
		$result = $wpdb->get_results("SHOW TABLES LIKE '".$key."'");

		if(count($result) == 0)
		{
			$wpdb->query($value);
		}
	}

	$arr_update_tables = array();

	$arr_update_tables[$wpdb->base_prefix."query"]['queryEmail'] = "ALTER TABLE ".$wpdb->base_prefix."query ADD queryEmail VARCHAR(100) AFTER queryAnswer";
	$arr_update_tables[$wpdb->base_prefix."query"]['queryEmailName'] = "ALTER TABLE ".$wpdb->base_prefix."query ADD queryEmailName VARCHAR(100) AFTER queryEmail";
	$arr_update_tables[$wpdb->base_prefix."query"]['queryMandatoryText'] = "ALTER TABLE ".$wpdb->base_prefix."query ADD queryMandatoryText VARCHAR(100) AFTER queryEmailName";
	$arr_update_tables[$wpdb->base_prefix."query"]['queryButtonText'] = "ALTER TABLE ".$wpdb->base_prefix."query ADD queryButtonText VARCHAR(100) AFTER queryMandatoryText";
	$arr_update_tables[$wpdb->base_prefix."query"]['queryDenyDups'] = "ALTER TABLE ".$wpdb->base_prefix."query ADD queryDenyDups ENUM('0', '1') NOT NULL DEFAULT '0' AFTER queryEmailName";
	$arr_update_tables[$wpdb->base_prefix."query"]['queryShowAnswers'] = "ALTER TABLE ".$wpdb->base_prefix."query ADD queryShowAnswers ENUM('0', '1') NOT NULL DEFAULT '0' AFTER queryEmailName";
	$arr_update_tables[$wpdb->base_prefix."query"]['queryEncrypted'] = "ALTER TABLE ".$wpdb->base_prefix."query ADD queryEncrypted ENUM('0', '1') NOT NULL DEFAULT '0' AFTER queryEmailName";

	$arr_update_tables[$wpdb->base_prefix."query2type"]['queryTypeClass'] = "ALTER TABLE ".$wpdb->base_prefix."query2type ADD queryTypeClass varchar(50) AFTER checkID";

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

	$arr_insert_tables[$wpdb->base_prefix."query_check1"] = "INSERT IGNORE INTO ".$wpdb->base_prefix."query_check VALUES('1','1','Number','int')";
	$arr_insert_tables[$wpdb->base_prefix."query_check2"] = "INSERT IGNORE INTO ".$wpdb->base_prefix."query_check VALUES('2','1','Zip code (Sv)','zip')";
	$arr_insert_tables[$wpdb->base_prefix."query_check3"] = "INSERT IGNORE INTO ".$wpdb->base_prefix."query_check VALUES('3','1','Social sec. no (Sv)','soc')";
	$arr_insert_tables[$wpdb->base_prefix."query_check4"] = "INSERT IGNORE INTO ".$wpdb->base_prefix."query_check VALUES('5','1','E-mail','email')";
	$arr_insert_tables[$wpdb->base_prefix."query_check5"] = "INSERT IGNORE INTO ".$wpdb->base_prefix."query_check VALUES('6','1','Phone no','telno')";
	$arr_insert_tables[$wpdb->base_prefix."query_check6"] = "INSERT IGNORE INTO ".$wpdb->base_prefix."query_check VALUES('7','1','Decimal number','float')";
	$arr_insert_tables[$wpdb->base_prefix."query_check7"] = "INSERT IGNORE INTO ".$wpdb->base_prefix."query_check VALUES('8','1','URL','url')";

	$arr_insert_tables[$wpdb->base_prefix."query_type1"] = "INSERT IGNORE INTO ".$wpdb->base_prefix."query_type VALUES('1','1','Checkbox','4','1')";
	$arr_insert_tables[$wpdb->base_prefix."query_type2"] = "INSERT IGNORE INTO ".$wpdb->base_prefix."query_type VALUES('2','1','Range','6','1')";
	$arr_insert_tables[$wpdb->base_prefix."query_type3"] = "INSERT IGNORE INTO ".$wpdb->base_prefix."query_type VALUES('3','1','Input field','5','1')";
	$arr_insert_tables[$wpdb->base_prefix."query_type4"] = "INSERT IGNORE INTO ".$wpdb->base_prefix."query_type VALUES('4','1','Textarea','8','1')";
	$arr_insert_tables[$wpdb->base_prefix."query_type5"] = "INSERT IGNORE INTO ".$wpdb->base_prefix."query_type VALUES('5','1','Text','2','0')";
	$arr_insert_tables[$wpdb->base_prefix."query_type6"] = "INSERT IGNORE INTO ".$wpdb->base_prefix."query_type VALUES('6','1','Space','1','0')";
	$arr_insert_tables[$wpdb->base_prefix."query_type7"] = "INSERT IGNORE INTO ".$wpdb->base_prefix."query_type VALUES('7','1','Datepicker','7','1')";
	$arr_insert_tables[$wpdb->base_prefix."query_type8"] = "INSERT IGNORE INTO ".$wpdb->base_prefix."query_type VALUES('8','1','Radio button','3','1')";
	$arr_insert_tables[$wpdb->base_prefix."query_type9"] = "INSERT IGNORE INTO ".$wpdb->base_prefix."query_type VALUES('10','1','Dropdown','9','1')";
	$arr_insert_tables[$wpdb->base_prefix."query_type10"] = "INSERT IGNORE INTO ".$wpdb->base_prefix."query_type VALUES('11', '1', 'Multiple selection', '10', '1')";

	require_once("include/zipcode.php");

	foreach($arr_insert_tables as $key => $value)
	{
		$wpdb->query($value);
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
	$menu_capability = "edit_pages";

	add_menu_page(__('Forms'), __('Forms'), $menu_capability, $menu_start);
	
	add_submenu_page($menu_start, __('Add New'), __('Add New'), $menu_capability, $menu_root.'create/index.php');
	add_submenu_page($menu_start, __('All Forms'), __('All Forms'), $menu_capability, $menu_root.'list/index.php');
	add_submenu_page($menu_start, __('All answers'), __(''), $menu_capability, $menu_root.'answer/index.php');
}
