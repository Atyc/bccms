<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

function _html_error($error, $exit = 0, $extra = []){

  	$formatted = str_replace(['#br#', '#b#', '#bb#'], ['<br>', '<b>', '</b>'], 
  			htmlentities(str_replace(['<br>', '<b>', '</b>'], ['#br#', '#b#', '#bb#'], $error)));

    if (empty($extra['location'])){
		$backtrace = debug_backtrace();
		if (empty($extra['backtrace'])){
			$extra['backtrace'] = 0; 
		}
		$extra['location'] = basename($backtrace[$extra['backtrace']]['file']).':'.$backtrace[$extra['backtrace']]['line'];
    }

  	$return = ('<pre style="background-color: white; color: black; display: block; border: 0.1rem solid red; '.
   			'font-size: 0.8rem; line-height: 0.9rem; letter-spacing: 0; font-family: monospace; text-align: left; ">');
   	$return .= ('<div style="line-height: 0.6rem;  padding: 0.4rem; color: red; font-weight: bold; ">'.
   			strtoupper($extra['location']??'').'</div><div style="padding: 0.6rem 1.0rem; ">');
    	
   	$return .= ($formatted);
   	$return .= ('</div></pre>');
    			 
   	if(!empty($GLOBALS['config']['errors_visible'])){
   		print($return);
   	}
    	
   	if ($exit){
   		set_status_header($exit);
   		exit();
   	}

   	return $return;
    	
}

// static system config (CI heritage, deprecated)
$config['system']['charset'] = 'UTF-8';
$config['system']['log_path'] = '';
$config['system']['log_date_format'] = 'Y-m-d H:i:s';

/*
 * LOAD CONFIG
 */

$config['config_file'] = $working_directory.'config/'.strtolower($_SERVER['SERVER_NAME']).'.json';

if (file_exists($config['config_file'])){
	
	$config = array_merge($config, json_decode(file_get_contents($config['config_file']), true));
	if (json_last_error()){
		print('Config file error: '.json_last_error_msg());
		die();
	}
	
	if ($config['base_path'] == '_auto_'){
		$config['base_path'] = rtrim(str_replace("\\", "/", trim(getcwd(), " \\")), '/').'/';
	}
	
	if (substr($config['upload_path'], 0, 1) !== '/' && substr($config['upload_path'], 1, 1) !== ':'){
		$config['upload_path'] = $config['base_path'].$config['upload_path'];
	}
	
	if (substr($config['upload_url'], 0, 2) !== '//' && substr($config['upload_path'], 1, 4) !== 'http'){
		$config['upload_url'] = $config['base_url'].$config['upload_url'];
	}
	
} else {
	
	$config['config_file'] = $working_directory.'config/'.strtolower($_SERVER['SERVER_NAME']).'.php';
	if (file_exists($config['config_file'])){

		// if config file for host exists, load config file
		include_once($config['config_file']);
		
		// check if base_path is set correctly
		if (!file_exists($config['base_path'].'config/'.strtolower($_SERVER['SERVER_NAME']).'.php')){
			print('Bad config base path: "'.$config['base_path'].'"');
			die();
		}
	
	} else {
			
		// check if install script is present
		if (file_exists($working_directory.'_install/install.php')){
			include($working_directory.'_install/install.php');
		} else {
			print('No config file for this host found: '.$working_directory.'config/'.strtolower($_SERVER['SERVER_NAME']).'.json or '.$config['config_file']);
		}
		
		die();
	
	}

}

/*
 * common config for all project environments:
 */

// what css and js to load on all pages
// TODO: to be refactored like css to cms cssjs settings
$config['js'] = array(
		array(
				'script' => 'modules/cms/js/jquery/jquery-3.6.1.min.js',
				'no_pack' => 1,
				'sync' => (!empty($config['jquery_blocks']) ? '' : 'defer'),
		),
		array(
				'script' => 'modules/cms/js/jquery/jquery-ui.min.js',
				'sync' => 'defer',
		),
		array(
				'script' => 'modules/cms/js/cms_site_main.js',
				'sync' => 'defer',
		),
);

$config['protocol'] = (empty($_SERVER['HTTPS']) OR strtolower($_SERVER['HTTPS']) === 'off') ? 'http' : 'https';

$GLOBALS['config'] = $config;

// connect to db (if mysqli)
if ($GLOBALS['config']['database']['dbdriver'] = 'mysqli') {
	
	$conn_hash = md5($GLOBALS['config']['database']['hostname'].$GLOBALS['config']['database']['username'].
			$GLOBALS['config']['database']['password'].$GLOBALS['config']['database']['database']);
	
	try {
		$GLOBALS['dbconnections'][$conn_hash] = @mysqli_connect($GLOBALS['config']['database']['hostname'],
				$GLOBALS['config']['database']['username'], $GLOBALS['config']['database']['password'], $GLOBALS['config']['database']['database']);
	} catch (Exception $e) {
		print('Can\'t connect database');
		die();
	}
	
}

// load config from db
$db = $GLOBALS['dbconnections'][md5($GLOBALS['config']['database']['hostname'].$GLOBALS['config']['database']['username'].
		$GLOBALS['config']['database']['password'].$config['database']['database'])];

if ($db === false){
	
	// check if install script is present
	if (file_exists($working_directory.'_install/install.php')){
		include($working_directory.'_install/install.php');
	} else {
		print('Can\'t connect database!');
	}
	
	die();

}

$sql = "select b.name, b.value from cms_page_panel a join cms_page_panel_param b on a.cms_page_panel_id = b.cms_page_panel_id ".
		" where a.panel_name = 'cms/cms_settings' and b.name != ''";
try {
	$query = mysqli_query($db, $sql);
} catch (Exception $e) {
	_html_error($e->getMessage());
}

if ($query === false){
	print('Database error: '.$db->error);
	die();
}

// if empty result, rename old settings
if (!mysqli_num_rows($query)){
	
	$sql = "select * from cms_page_panel where panel_name = 'cms_settings'";
	$query = mysqli_query($db, $sql);
	
	if (mysqli_num_rows($query)){
		
		$sql = "update cms_page_panel set panel_name = 'cms/cms_settings' where panel_name = 'cms_settings'";
		$query = mysqli_query($db, $sql);
		
		// try to load again
		$sql = "select b.name, b.value from cms_page_panel a join cms_page_panel_param b on a.cms_page_panel_id = b.cms_page_panel_id where a.panel_name = 'cms/cms_settings' and b.name != ''";
		$query = mysqli_query($db, $sql);
		
	}
	
}

while($result = mysqli_fetch_assoc($query)){
	
	if (!stristr($result['name'], '.')){
		$GLOBALS['config'][$result['name']] = $result['value'];
	} else {
		list($ra, $rb) = explode('.', $result['name']);
		$GLOBALS['config'][$ra][$rb] = $result['value'];
	}
	
}

// load module configs
if (empty($GLOBALS['config']['modules']) || !is_array($GLOBALS['config']['modules'])){
	$GLOBALS['config']['modules'] = ['cms'];
}

array_unshift($GLOBALS['config']['modules'], 'cms');

$GLOBALS['config']['modules'] = array_values(array_unique($GLOBALS['config']['modules']));

foreach($GLOBALS['config']['modules'] as $module_name){
	
	$filename = $GLOBALS['config']['base_path'].'modules/'.$module_name.'/config.json';
	if (file_exists($filename)){
		$GLOBALS['config']['module'][$module_name] = json_decode(file_get_contents($filename), true);
		if (json_last_error() !== JSON_ERROR_NONE) {
			print('Module config bad json: '.$filename);
			die();
		}
	} else {
		$GLOBALS['config']['module'][$module_name] = [];
	}
	
	if (empty($GLOBALS['config']['module'][$module_name]['panels'])){
		$GLOBALS['config']['module'][$module_name]['panels'] = [];
	}
	
}