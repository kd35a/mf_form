<?php
/*
Plugin Name: Forms
Version: 1.3.2
Author: Martin Fors
Author URI: www.martinfors.se
*/

/* External */
add_shortcode('form_shortcode', 'form_shortcode');
add_action('widgets_init', 'form_load_widgets');

wp_enqueue_style('forms-style', plugins_url()."/mf_form/include/style.css");
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

	on_post_query_form();

	$sent = isset($_GET['sent']) ? true : false;

	return show_query_form(array('query_id' => $id, 'sent' => $sent));
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

		on_post_query_form();

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
		queryButtonText varchar(100) DEFAULT NULL,
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
		queryTypeClass varchar(50) DEFAULT NULL,
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
	$arr_update_tables[$wpdb->prefix."query"]['queryButtonText'] = "ALTER TABLE ".$wpdb->prefix."query ADD queryButtonText VARCHAR(100) AFTER queryEmailName";

	$arr_update_tables[$wpdb->prefix."query2type"]['queryTypeClass'] = "ALTER TABLE ".$wpdb->prefix."query2type ADD queryTypeClass varchar(50) AFTER checkID";

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

	$arr_insert_tables[$wpdb->prefix."query_check"] = "INSERT INTO ".$wpdb->prefix."query_check VALUES('1','1','Number','int'),
	('5','1','E-mail','email'),
	('6','1','Phone no','telno'),
	('7','1','Decimal number','float'),
	('8','1','URL','url')";

	/*
		('2','1','Short date (YYMMDD)','shortDate2'),
		('3','1','Date (YYYY-MM-DD)','date'),
	*/

	$arr_insert_tables[$wpdb->prefix."query_type"] = "INSERT INTO ".$wpdb->prefix."query_type VALUES('1','1','Checkbox','4','1'),
	('2','1','Range','6','1'),
	('3','1','Input field','5','1'),
	('4','1','Textarea','8','1'),
	('5','1','Text','2','0'),
	('6','1','Space','1','0'),
	('7','1','Datepicker','7','1'),
	('8','1','Radio button','3','1'),
	('10','1','Dropdown','9','1'),
	('11', '1', 'Multiple selection', '10', '1')";

	/*
		('9','0','File upload','','1'),
		('13','0','hidden_info','','1'),
		('14','0','input_field_connected','','1'),
	*/

	foreach($arr_insert_tables as $key => $value)
	{
		$wpdb->query("DELETE FROM ".$key);
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

	$menu_label = "edit_pages";

	add_menu_page(__('Forms'), __('Forms'), $menu_label, $menu_start);
	
	add_submenu_page($menu_start, __('Add New'), __('Add New'), $menu_label, $menu_root.'create/index.php');
	add_submenu_page($menu_start, __('All Forms'), __('All Forms'), $menu_label, $menu_root.'list/index.php');
	add_submenu_page($menu_start, __('All answers'), __(''), $menu_label, $menu_root.'answer/index.php');
	add_submenu_page($menu_start, __('Latest answer'), __(''), $menu_label, $menu_root.'view/index.php');
	add_submenu_page($menu_start, __('Export'), __(''), $menu_label, $menu_root.'export/index.php');
}