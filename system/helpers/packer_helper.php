<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use ScssPhp\ScssPhp\Compiler;
use MatthiasMullie\Minify;

if ( !function_exists('pack_css')) {
	
	function pack_get_scss_path($scss_filename){
		
		$return = [];
		
		if (stristr($scss_filename, 'modules/')){
				
			list($m_start, $m_path) = explode('modules/', $scss_filename);
			list($m_module, $m_rest) = explode('/', $m_path, 2);
				
			$module_scss = $m_start.'modules/'.$m_module.'/'.$m_module.'.scss';
			if (file_exists($module_scss)){
				$return['module_scss'] = $module_scss;
			}
			
			$return['css_cache'] = 'cache/'.$m_module.'__'.pathinfo($scss_filename, PATHINFO_FILENAME).'.css';
				
		}
		
		return $return;
		
	}
	
	function pack_css($scsss){
		
		$csss = [];

		// compile scss
		foreach($scsss as $scsss_item){
			
			if (!is_array($scsss_item)){
				$scsss_item = ['script' => $scsss_item];
			}
	
			// put together related scss files
			if (!empty($scsss_item['related'])){
				$scss_set = array_merge($scsss_item['related'], array($scsss_item['script']));
			} else {
				
				$scsss_item['related'] = [];
				$scss_set = [$scsss_item['script']];
				
				// get module name and check if module scss exists
				$m_path = pack_get_scss_path($scsss_item['script']);
				if (!empty($m_path['module_scss'])){
					$scsss_item['related'][] = $m_path['module_scss'];
					$scss_set[] = $m_path['module_scss'];
				}
				
			}
			
			if (empty($scsss_item['css'])){
				$m_path = pack_get_scss_path($scsss_item['script']);
				if (!empty($m_path['css_cache'])){
					$scsss_item['css'] = $m_path['css_cache'];
				} else {
					$scsss_item['css'] = $scsss_item['script'];
				}
			}
				
			// check if needed to compile, happens when any of files in set is newer than cache
			$css_file_update_needed = false;
			foreach($scss_set as $scss_file){
				$css_file = $GLOBALS['config']['base_path'].$scsss_item['css'];
				// if needs update
				
				if(!file_exists($css_file) || (filemtime($GLOBALS['config']['base_path'].$scss_file) > filemtime($css_file))){
					$css_file_update_needed = true;
				}
			}
	
			if ($css_file_update_needed){

				// load files
				$scss_string = '';
				foreach($scsss_item['related'] as $scss_file){
					$scss_string .= file_get_contents($GLOBALS['config']['base_path'].$scss_file);
				}
				$prelines_count = substr_count($scss_string, "\n");
				$scss_string .= '/* CUT HERE */'.file_get_contents($GLOBALS['config']['base_path'].$scsss_item['script']);
	
				// compile scss
				require_once($GLOBALS['config']['base_path'].'system/vendor/scss/scss.inc.php');
		   
				$scss_compiler = new Compiler();
		   
				try {
					
					$compiled = $scss_compiler->compileString($scss_string)->getCss();

					list($throwaway, $css_string) = explode('/* CUT HERE */', $compiled);

					// if has resources
					if(!empty($scsss_item['module_path'])){
						
						// fonts x 2, background
						$css_string = str_replace(
								[
										"src: url('", 
										", url('",
										"image: url('../",
								], 
								[
										"src: url('".$GLOBALS['config']['base_url'].$scsss_item['module_path'].'css/', 
										", url('".$GLOBALS['config']['base_url'].$scsss_item['module_path'].'css/', 
										"image: url('".$GLOBALS['config']['base_url'].$scsss_item['module_path'],
								], 
								$css_string);
						
					}
		
					file_put_contents($GLOBALS['config']['base_path'].$scsss_item['css'], 
							"/* \n".
							"    THIS FILE IS AUTOMATICALLY GENERATED\n".
							"    PLEASE DO NOT EDIT THIS FILE\n".
							"    LOCATION OF SOURCE:\n".
							"    ".$scsss_item['script']."\n".
							"*/\n".
							$css_string);

				} catch (Exception $e) {
					if (!empty($GLOBALS['config']['errors_visible'])){
						$error_str = $e->getMessage();
						if (stristr($error_str, ': line: ')){
							list($error, $line_no) = explode(': line: ', $error_str);
						} else if (stristr($error_str, ' on line ')){
							list($error, $line_no) = explode(' on line ', $error_str);
						} else {
							$error = $error_str;
							$line_no = 0;
						}
						_html_error('SCSS error:<br>Message: '.$error.'<br>Filename: '.$scsss_item['script'].'<br>Line number: '.($line_no > 0 ? $line_no - $prelines_count : $line_no));
					}
				}

			}

			// remove from css array and put new cache css file there
			foreach($csss as $key => $css_item){
				if (is_array($css_item) && $css_item['script'] == str_replace('.scss', '.css', $scsss_item['script'])){
					unset($csss[$key]);
				}
			}
			$csss[] = array(
					'script' => $scsss_item['css'],
					'top' => !empty($scsss_item['top']) ? $scsss_item['top'] : 0,
			);
	
		}
		 
		$css_arr = array();
	
		// normalise css array
		foreach($csss as $key => $value){
			if (!is_array($value)){ // if just name, no properties
				$csss[$key] = array(
						'script' => $value,
						'top' => 0,
				);
			}
		}
		
		foreach($csss as $key => $value){
			if (empty($value['top'])){
				$csss[$key]['top'] = 0;
			}
		}
// print_r($csss);	
		// get unique
		$csss = array_intersect_key($csss, array_unique(array_map('serialize', $csss)));
	
		$css_string = '';
	
		// sort by top
		if (!function_exists('_css_to_top')){
			function _css_to_top($a, $b){
				
				if ($a['top'] > $b['top']){
					return -1;
				} elseif ($a['top'] < $b['top']){
					return 1;
				} else {
					return 0;
				}
	
			}
		}

		if (!empty($csss)){
				
			usort($csss, '_css_to_top');
			
			if ($GLOBALS['config']['cache']['pack_css']){
	
				$hash = substr(md5(serialize($csss)), 0, 8);
				// check if any of files is changed
				$filename = $GLOBALS['config']['base_path'].'cache/'.$hash.'.css';
				$fileurl = $GLOBALS['config']['base_url'].'cache/'.$hash.'.css';
	
				if (file_exists($filename)){
					$filetime = filemtime($filename);
					$max_scripttime = 0;
					foreach($csss as $key => $css){
						$css = $GLOBALS['config']['base_path'] . trim($css['script'], '/');
						$max_scripttime = max(filemtime($css), $max_scripttime);
					}
				} else {
					$filetime = 0;
				}
	
				// if new css generation needed
				if (!file_exists($filename) || $max_scripttime > $filetime){
						
					touch($filename);
						
					// load all css files
					$css_contents = '';
					foreach($csss as $css){
						$css = $GLOBALS['config']['base_path'] . trim($css['script'], '/');
						$css_contents .= file_get_contents($css)."\n";
					}
						
					// TODO: needs minifier
					$css_contents = trim(preg_replace('/[ \t]+/', ' ', $css_contents));
					$css_contents = trim(preg_replace('/\r/', '', $css_contents));
					$css_contents = trim(preg_replace('/\n /', "\n", $css_contents));
					$css_contents = trim(preg_replace('/[\n]+/', "\n", $css_contents));
					file_put_contents($filename, $css_contents);
						
				}
	
				$css_string .= '<link rel="stylesheet" type="text/css" href="'.$fileurl.
				(!empty($GLOBALS['config']['cache']['force_download']) ? '?v='.time() : '').'"/>'."\n";
	
			} else {
	
				foreach($csss as $css){
					$css_string .= '<link rel="stylesheet" type="text/css" href="'.$GLOBALS['config']['base_url'].$css['script'].
					(!empty($GLOBALS['config']['cache']['force_download']) ? '?v='.time() : '').'"/>'."\n";
				}
			  
			}
				
		}

		if ($GLOBALS['config']['cache']['pack_css'] && !empty($fileurl)){
			$csss = ['0' => [
					'script' => $fileurl.( (!empty($GLOBALS['config']['cache']['force_download']) && empty($GLOBALS['config']['inline_css'])) ? '?v='.time() : ''), 
					'filename' => $filename,
					'filemtime' => filemtime($filename),
			]];
		} else {
			foreach($csss as $key => $css){
				$csss[$key]['script'] = $GLOBALS['config']['base_url'].$css['script'].((!empty($GLOBALS['config']['cache']['force_download']) && empty($GLOBALS['config']['inline_css'])) ? '?v='.time() : '');
				$csss[$key]['filename'] = $GLOBALS['config']['base_path'].$css['script'];
				$csss[$key]['filemtime'] = file_exists($csss[$key]['filename']) ? filemtime($csss[$key]['filename']) : 0;
			}
		}

		return $csss; // as array
		 
	}
	
	function pack_js($js){
	
		// normalise js array
		foreach($js as $key => $value){
			if (!is_array($value)){
				$js[$key] = array(
						'script' => $value,
						'sync' => 'defer',
						'no_pack' => 0,
				);
			} else {
				if (empty($value['no_pack'])){
					$js[$key]['no_pack'] = 0;
				}
				if (!isset($value['sync'])){
					$js[$key]['sync'] = 'defer';
				}
			}
		}
		 
		// get unique
		$js = array_intersect_key($js, array_unique(array_map('serialize', $js)));
	
		$js_strs = array();
		$js_to_cache = array();
		foreach($js as $_js){
	
			$_js['script'] = str_replace('\\', '/', $_js['script']);
	
			if ($_js['sync'] == 'defer' && $GLOBALS['config']['cache']['pack_js'] && empty($_js['no_pack']) && substr($_js['script'], 0, 4) !== 'http'){
				$js_to_cache[] = $_js['script'];
			} else if (substr($_js['script'], 0, 4) !== 'http'){ // local script
				$js_strs[] = '<script type="text/javascript" src="'.$GLOBALS['config']['base_url'].$_js['script'].
				(!empty($GLOBALS['config']['cache']['force_download']) ? '?v='.time() : '').'" '.$_js['sync'].'></script>';
			} else { // outside script
				$js_strs[] = '<script type="text/javascript" src="'.$_js['script'].'" '.$_js['sync'].'></script>';
			}
	
		}
	
		$js_cache = '';
		if (!empty($js_to_cache)){
				
			$hash = substr(md5(implode(' ', $js_to_cache)), 0, 8);
			// check if any of files is changed
			$filename = $GLOBALS['config']['base_path'].'cache/'.$hash.'.js';
			$fileurl = $GLOBALS['config']['base_url'].'cache/'.$hash.'.js';
				
			if (file_exists($filename)){
				$filetime = filemtime($filename);
				$max_scripttime = 0;
				foreach($js_to_cache as $js_file){
					$js_file = $GLOBALS['config']['base_path'] . trim($js_file, '/');
					$max_scripttime = max(filemtime($js_file), $max_scripttime);
				}
			} else {
				$filetime = 0;
			}
				
			// if new js cache generation needed
			if (!file_exists($filename) || $max_scripttime > $filetime){
	
				touch($filename);
	
				// load all js files
				$js_string = '';
				foreach($js_to_cache as $js_file){
					$js_file = $GLOBALS['config']['base_path'] . trim($js_file, '/');
					$js_file_content = trim(file_get_contents($js_file));
						
					// if file ends with ) add ;
					if (mb_substr($js_file_content, -1) == ')'){
						$js_file_content .= ';';
					}
						
					$js_string .= $js_file_content."\n";
				}

				// js (css) minifier
				require_once($GLOBALS['config']['base_path'].'system/vendor/minify/src/Minify.php');
				require_once($GLOBALS['config']['base_path'].'system/vendor/minify/src/JS.php');
				
				$minifier = new Minify\JS($js_string);
				 
				try {
					
					$minifier->minify($filename);
				
				} catch (Exception $e) {
					
					if (!empty($GLOBALS['config']['errors_visible'])){
						_html_error('JS Minifier error:<br>Message: Error minifying JavaScript!');
					}
					
					file_put_contents($filename, $js_string);
					
				}
	
			}
				
			$js_cache = '<script type="text/javascript" src="'.$fileurl.
			(!empty($GLOBALS['config']['cache']['force_download']) ? '?v='.time() : '').'" defer></script>';
	
		}
	
		return implode("\n", $js_strs)."\n".$js_cache;
	
	}
	
	function add_css($file){

		if (!empty($file['script'])){
			$filename = $file['script'];
		} else {
			$filename = $file;
		}
		
		// for module/file.scss format
		if (!is_array($file) && substr_count($filename, '/') == 1){
			
			$file = [
					'script' => 'modules/'.str_replace('/', '/css/', $filename),
			];
			
			list($module, $file_short) = explode('/', $filename);
			
			if (file_exists($GLOBALS['config']['base_path'].'modules/'.$module.'/css/'.$module.'.scss')){
				$file['related'] = ['modules/'.$module.'/css/'.$module.'.scss'];
			}
			
		}
		
		if (empty($GLOBALS['_panel_scss'])){
			$GLOBALS['_panel_scss'] = [];
			$GLOBALS['_panel_scss_names'] = [];
		}
		
		if (!in_array($file, $GLOBALS['_panel_scss_names'])){
			$GLOBALS['_panel_scss'][] = $file;
			$GLOBALS['_panel_scss_names'][] = $filename;
		}
		
	}
	
}
