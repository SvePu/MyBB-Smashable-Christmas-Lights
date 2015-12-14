<?php
/**
 * Main plugin file Smashable Christmas Lights for MyBB 1.8
 * Created by SvePu (https://github.com/SvePu)
 *
 * This plugin based on the christmaslights script by Scott Schiller. (https://github.com/scottschiller/Snowstorm/tree/master/lights)
 */

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.");
}

$plugins->add_hook("global_start", "smash_christmas_lights_run");

function smash_christmas_lights_info()
{
    return array(
        "name"          => "Smashable Christmas Lights",
        "description"   => "Displays christmas lights on the top of your forum. You can even smash them with or without sound effects!<br />This plugin based on the <a href=\"https://github.com/scottschiller/Snowstorm/tree/master/lights\" target=\"_blank\">christmaslights</a> script by <a href=\"https://github.com/scottschiller\" target=\"_blank\">Scott Schiller</a>.",
        "website"       => "https://github.com/SvePu/MyBB-Smashable-Christmas-Lights",
        "author"        => "SvePu",
        "authorsite"    => "https://github.com/SvePu",
        "version"       => "1.0",
        "codename"      => "smashchristmaslights",
        "compatibility" => "18*"
    );
}

function smash_christmas_lights_install()
{
	global $db;
	$query_add = $db->simple_select("settinggroups", "COUNT(*) as rows");
	$rows = $db->fetch_field($query_add, "rows");
    $scl_settingsgroup = array(
		"name" 			=>	'smash_christmas_lights_settings',
		"title" 		=>	'Smashable Christmas Lights',
		"description" 	=>	'Settings for Smashable Christmas Lights.',
		"disporder"		=> 	$rows+1,
		"isdefault" 	=>  0
	);
    $db->insert_query("settinggroups", $scl_settingsgroup);
	$gid = $db->insert_id();
	
	$scl_setting_1 = array(

		'name'			=> 'scl_lights_smashable',
		'title'			=> 'Smashable lights?',
		'description'	=> 'Choose YES to make them smashable!',
		'optionscode'	=> 'yesno',
		'value'			=> 1,
		'disporder'		=> 1,
		'gid'			=> (int)$gid
	);

	$db->insert_query('settings', $scl_setting_1);

	$scl_setting_2 = array(
		'name'			=> 'scl_lights_size',
		'title'			=> 'Size of lights',
		'description'	=> 'Choose the size of lights.',
		'optionscode'	=> 'select \npico=xx-small \ntiny=x-small \nsmall=small \nmedium=medium \nlarge=large',
		'value'			=> 'small',
		'disporder'		=> 2,
		"gid"			=> (int)$gid
	);

	$db->insert_query("settings", $scl_setting_2);

	$scl_setting_3 = array(

		'name'			=> 'scl_sounds_enable',
		'title'			=> 'Smash sounds ON?',
		'description'	=> 'Choose YES to enable smashing sound!',
		'optionscode'	=> 'yesno',
		'value'			=> 1,
		'disporder'		=> 3,
		'gid'			=> (int)$gid
	);

	$db->insert_query('settings', $scl_setting_3);

	rebuild_settings();
}

function smash_christmas_lights_is_installed()
{
	global $mybb;
	if(isset($mybb->settings['scl_lights_size']))
	{
		return true;
	}

	return false;
}

function smash_christmas_lights_uninstall()
{
	global $db;

	$db->delete_query('settings', "name IN ('scl_lights_smashable','scl_lights_size','scl_sounds_enable')");
	$db->delete_query('settinggroups', "name = 'smash_christmas_lights_settings'");
	rebuild_settings();
}

function smash_christmas_lights_activate()
{
	require_once MYBB_ROOT."inc/adminfunctions_templates.php";
	find_replace_templatesets("headerinclude", '#{\$stylesheets}(\r?)\n#', "{\$stylesheets}\n{\$scl_headerinclude}\n");
	find_replace_templatesets("header", '#'.preg_quote('<div id="container">').'#',	'<div id="container"><div id="lights" {\$scl_smashtatus}></div>');
}

function smash_christmas_lights_deactivate()
{
	require_once MYBB_ROOT."/inc/adminfunctions_templates.php";
	find_replace_templatesets("headerinclude", '#{\$scl_headerinclude}(\r?)\n#', "", 0);
	find_replace_templatesets("header", '#'.preg_quote('<div id="container"><div id="lights" {$scl_smashtatus}></div>').'#','<div id="container">');
}

function smash_christmas_lights_run()
{
	global $mybb, $scl_headerinclude, $scl_smashtatus;
	$scl_smashtatus = 'class="lightsActive"  onclick="makeInact()"';
	if($mybb->settings['scl_lights_smashable'] != 1)
	{
		$scl_smashtatus = 'class="lightsInactive"';
	}
	
	$scl_base_url = $mybb->settings['bburl'].'/inc/plugins/smash_christmas_lights/';
	
	$scl_headerinclude ='<link rel="stylesheet" media="screen" href="'.$scl_base_url.'christmaslights.css" />
<script type="text/javascript" src="'.$scl_base_url.'soundmanager2-nodebug-jsmin.js"></script>
<script type="text/javascript" src="http://yui.yahooapis.com/combo?2.9.0/build/yahoo-dom-event/yahoo-dom-event.js&2.9.0/build/animation/animation-min.js"></script>
<script type="text/javascript" src="'.$scl_base_url.'christmaslights.js.php?bburl='.$mybb->settings['bburl'].'&size='.$mybb->settings['scl_lights_size'].'&sound='.$mybb->settings['scl_sounds_enable'].'"></script>';	
}
