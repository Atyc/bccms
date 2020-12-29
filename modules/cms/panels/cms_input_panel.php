<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class cms_input_panel extends CI_Controller {

	function panel_params($params){
		
		$this->load->model('cms/cms_panel_model');
		
		if (!empty($params['flag'])){
			$params['values'] = $this->cms_panel_model->get_cms_panels($params['flag']);
		} else {
			$params['values'] = $this->cms_panel_model->get_cms_panels();
		}
		
		foreach($params['values'] as $key => $value){
			list($p1, $p2) = explode('/', $key);
			$params['values'][$key] = ucfirst($p1).' / '.$value;
		}
		
		return $params;
		
	}

}
