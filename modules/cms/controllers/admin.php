<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class admin extends MY_Controller {

	public function __construct() {
		 
		parent::__construct();

		// check if user
		if(empty($_SESSION['cms_user']['cms_user_id'])){
			header('Location: '.$GLOBALS['config']['base_url'].'cms_login/', true, 302);
			exit();
		}

		$this->js[] = array('script' => 'js/jquery-ui.min.js', );

		$this->js[] = 'js/preloader.js';
		$this->js[] = 'js/md5.js';
		$this->js[] = 'js/panels.js';

		$this->js[] = 'modules/cms/js/cms.js';

		$GLOBALS['_panel_titles'][] = 'ADMIN';
		
	}

	function index(){

		// set page config
		$page_config = array(
				array('position' => 'header', 'panel' => 'cms_user', 'module' => 'cms', ),
				array('position' => 'header', 'panel' => 'cms_menu', 'module' => 'cms', ),
				array('position' => 'main', 'panel' => 'cms_welcome', 'module' => 'cms', ),
		);

		// render panels
		$panel_data = $this->render($page_config);

		$this->output('admin', $panel_data);

	}

	function pages(){
		 
		// set page config
		$page_config = array(
				array('position' => 'header', 'panel' => 'cms_user', 'module' => 'cms', ),
				array('position' => 'header', 'panel' => 'cms_menu', 'module' => 'cms', ),
				array('position' => 'main', 'panel' => 'admin_pages', 'module' => 'cms', ),
		);

		// render panels
		$panel_data = $this->render($page_config);

		$this->output('admin', $panel_data);

	}

	function page($page_id){
		 
		// set page config
		$page_config = array(
				array('position' => 'header', 'panel' => 'cms_user', 'module' => 'cms', ),
				array('position' => 'header', 'panel' => 'cms_menu', 'module' => 'cms', ),
				array('position' => 'main', 'panel' => 'cms_page', 'module' => 'cms', 'params' => array('page_id' => $page_id, ), ),
		);

		// render panels
		$panel_data = $this->render($page_config);

		$this->output('admin', $panel_data);

	}

	/**
	 * Edit page panel
	 */
	function cms_page_panel($cms_page_panel_id, $cms_page_id = 0, $parent_id = 0, $parent_field_name = ''){
		
		$params = [
				'cms_page_panel_id' => $cms_page_panel_id,
				'cms_page_id' => $cms_page_id,
				'parent_id' => $parent_id,
				'parent_field_name' => $parent_field_name,
		];
		
		// set page config
		$page_config = array(
				array('position' => 'header', 'panel' => 'cms_user', 'module' => 'cms', ),
				array('position' => 'header', 'panel' => 'cms_menu', 'module' => 'cms', ),
				array('position' => 'main', 'panel' => 'cms_page_panel', 'module' => 'cms', 'params' => $params, ),
		);

		// render panels
		$page_panel_data = $this->render($page_config);

		$this->output('admin', $page_panel_data);

	}

	function menu($menu_id = 0){

		// set page config
		$page_config = array(
				array('position' => 'header', 'panel' => 'cms_user', 'module' => 'cms', ),
				array('position' => 'header', 'panel' => 'cms_menu', 'module' => 'cms', ),
				array('position' => 'main', 'panel' => 'cms_main_menu', 'module' => 'cms', 'params' => array('menu_id' => $menu_id, ), ),
		);

		// render panels
		$panel_data = $this->render($page_config);

		$this->output('admin', $panel_data);

	}

	function menu_main(){
		 
		// set page config
		$page_config = array(
				array('position' => 'header', 'panel' => 'cms_user', 'module' => 'cms', ),
				array('position' => 'header', 'panel' => 'cms_menu', 'module' => 'cms', ),
				array('position' => 'main', 'panel' => 'cms_page_panel', 'module' => 'cms', 'params' => array('filter' => array('panel_name' => 'menu_main', ), ), ),
		);

		// render panels
		$panel_data = $this->render($page_config);

		$this->output('admin', $panel_data);

	}

	function table($table){

		// set page config
		$page_config = array(
				array('position' => 'header', 'panel' => 'cms_user', 'module' => 'cms', ),
				array('position' => 'header', 'panel' => 'cms_menu', 'module' => 'cms', ),
				array('position' => 'main', 'panel' => 'cms_table', 'module' => 'cms', 'params' => array('table' => $table, ), ),
		);

		// render panels
		$panel_data = $this->render($page_config);

		$this->output('admin', $panel_data);

	}

	function keywords(){

		// set page config
		$page_config = array(
				array('position' => 'header', 'panel' => 'cms_user', 'module' => 'cms', ),
				array('position' => 'header', 'panel' => 'cms_menu', 'module' => 'cms', ),
				array(
						'position' => 'main',
						'panel' => 'cms_list',
						'module' => 'cms',
						'params' => array(
								'title' => 'Keywords',
								'edit_base' => 'admin/keyword/',
								'source' => array('model' => 'cms_keyword_model', 'method' => 'get_cms_keywords', ),
								'title_field' => 'cms_keyword_id',
								'id_field' => 'cms_keyword_id',
								'no_sort' => 1,
						),
				),
		);

		// render panels
		$panel_data = $this->render($page_config);

		$this->output('admin', $panel_data);

	}

	function cms_list($list_item){

		// get list item params
		$this->load->model('cms_panel_model');
		$item_config = $this->cms_panel_model->get_cms_panel_config($list_item);

		if (!empty($item_config['list'])){

			$params = array(
					'title' => $item_config['list']['list_title'],
					'edit_base' => 'admin/cms_page_panel/',
					'filter' => array('panel_name' => $list_item, 'page_id' => [999999,0], ),
			);
				
			if (!empty($item_config['list']['filter_fields'])){
				$params['filter_fields'] = $item_config['list']['filter_fields'];
			}
				
			if (!empty($item_config['list']['title_panel'])){
				$params['title_panel'] = $item_config['list']['title_panel'];
			} else {
				$params['title_field'] = $item_config['list']['title_field'];
			}

			// set page config
			$page_config = array(
					array('position' => 'header', 'panel' => 'cms_user', 'module' => 'cms', ),
					array('position' => 'header', 'panel' => 'cms_menu', 'module' => 'cms', ),
					array(
							'position' => 'main',
							'panel' => 'cms_list',
							'module' => 'cms',
							'params' => $params,
					),
			);
			 
			// render panels
			$panel_data = $this->render($page_config);
			 
			$this->output('admin', $panel_data);
			 
		}

	}

	function panel_settings($panel_name, $title = ''){
		
		$this->load->model('cms_page_panel_model');
		$this->load->model('cms_panel_model');
		
		// with module name
		if (stristr($panel_name, '__')){
			
			$panel_name = str_replace('__', '/', $panel_name);
			
		}
		
		// check if exists
		if (stristr($panel_name, 'settings')){ // for backwards compatibility with cms settings and feed settings
			$settings_a = $this->cms_page_panel_model->get_cms_page_panels_by(['panel_name' => $panel_name, 'page_id' => 0, 'parent_id' => 0, ]);
		} else {
			$settings_a = $this->cms_page_panel_model->get_cms_page_panels_by(['panel_name' => $panel_name, 'page_id' => 0, 'parent_id' => 0, 'sort' => 0, ]);
		}
		
		if (!count($settings_a)){
			
			$params = ['panel_name' => $panel_name, ];
			
			// load definition
			$panel_config = $this->cms_panel_model->get_cms_panel_config($panel_name);
			
			$params['title'] = (!empty($panel_config['label']) ? $panel_config['label'] : $panel_name).' settings';
			$params['sort'] = 0;
			$params['parent_id'] = 0;
			
			$cms_page_panel_id = $this->cms_page_panel_model->create_cms_page_panel($params);
			
		} else {
			
			$settings_a = array_values($settings_a);
			$cms_page_panel_id = $settings_a[0]['cms_page_panel_id'];
			
		}

		// set page config
		$page_config = array(
				array('position' => 'header', 'panel' => 'cms_user', 'module' => 'cms', ),
				array('position' => 'header', 'panel' => 'cms_menu', 'module' => 'cms', ),
				array('position' => 'main', 'panel' => 'cms_page_panel', 'module' => 'cms', 'params' => [
						'cms_page_panel_id' => $cms_page_panel_id,
				], ),
		);

		// render panels
		$panel_data = $this->render($page_config);

		$this->output('admin', $panel_data);

	}

	function update($param = ''){

		// set page config
		$page_config = array(
				array('position' => 'header', 'panel' => 'cms_user', 'module' => 'cms', ),
				array('position' => 'header', 'panel' => 'cms_menu', 'module' => 'cms', ),
				array('position' => 'main', 'panel' => 'cms_update', 'module' => 'cms', 'params' => array(
						'do' => $param,
				), ),
		);

		// render panels
		$panel_data = $this->render($page_config);

		$this->output('admin', $panel_data);
		 
	}

	function users($param = ''){

		// set page config
		$page_config = array(
				array('position' => 'header', 'panel' => 'cms_user', 'module' => 'cms', ),
				array('position' => 'header', 'panel' => 'cms_menu', 'module' => 'cms', ),
				array('position' => 'main', 'panel' => 'cms/cms_user_settings', ),
		);

		// render panels
		$panel_data = $this->render($page_config);

		// output to layout
		$this->output('admin', $panel_data);
		 
	}
	
	function cssjs($param = ''){
	
		// set page config
		$page_config = array(
				array('position' => 'header', 'panel' => 'cms_user', 'module' => 'cms', ),
				array('position' => 'header', 'panel' => 'cms_menu', 'module' => 'cms', ),
				array('position' => 'main', 'panel' => 'cms/cms_cssjs_settings', ),
		);
	
		// render panels
		$panel_data = $this->render($page_config);
	
		// output to layout
		$this->output('admin', $panel_data);
			
	}
	
	function export($filename){
		 
		if ($filename && file_exists($GLOBALS['config']['base_path'].'cache/'.$filename.'.zip')){
	
			header('Content-Disposition: attachment; filename="'.$filename.'.zip"');
			header('Content-Type: application/zip');
			header('Content-Length: ' . filesize($GLOBALS['config']['base_path'].'cache/'.$filename.'.zip'));
			
			ini_set('memory_limit','1G');
			
			readfile($GLOBALS['config']['base_path'].'cache/'.$filename.'.zip');

			exit();
	
		} else {

			print('Problem accessing file!');
			
//			print($filename);
//			print($GLOBALS['config']['base_path'].$filename.'.zip');
//			var_dump(file_exists($GLOBALS['config']['base_path'].$filename.'.zip'));
		
		}

	}
	
	function dump($param = ''){
		
		if (isset($_POST['do'])){
			$param = $_POST['do'];
		}
		
		$what = '';
		if (isset($_POST['what'])){
			$what = $_POST['what'];
		}
		
		// set page config
		$page_config = array(
				array('position' => 'header', 'panel' => 'cms_user', 'module' => 'cms', ),
				array('position' => 'header', 'panel' => 'cms_menu', 'module' => 'cms', ),
				array('position' => 'main', 'panel' => 'cms/cms_dump', 'params' => ['do' => $param, 'what' => $what, ]),
		);
		
		// render panels
		$panel_data = $this->render($page_config);
		
		// output to layout
		$this->output('admin', $panel_data);
		
	}

}
