<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class do_send extends MY_Controller{
	
	function panel_action($params){

		$do = $this->input->post('do');
        if ($do == 'send_form'){

			$this->load->model('cms/cms_page_panel_model');
			$this->load->model('cms/cms_page_model');
        	$this->load->model('form/form_model');
        	
        	$cms_page_panel_id = $this->input->post('id');
        	
        	// collect data
        	$data = $this->input->post();
        	
        	if (!empty($cms_page_panel_id)){
				$params = array_merge($params, $this->cms_page_panel_model->get_cms_page_panel($cms_page_panel_id));
        	}
        	
        	unset($data['do']);
        	if (isset($data['id'])){
	        	unset($data['id']);
        	}
            if (isset($data['panel_id'])){
	        	unset($data['panel_id']);
        	}
        	if (isset($data['no_html'])){
        		unset($data['no_html']);
        	}
        	if (isset($data['cache'])){
        		unset($data['cache']);
        	}
        	
        	// if settings
        	if(!empty($params['cms_page_id'])){
        	
	        	$page = $this->cms_page_model->get_page($params['cms_page_id']);
	        	
	        	$title_parts = [trim(str_replace('#page#', '', $GLOBALS['config']['site_title']), $GLOBALS['config']['site_title_delimiter'].' ')];
	        	if (!empty($page['title'])){
	        		$title_parts[] = $page['title'];
	        	}
	        	
	        	$title = (!empty($GLOBALS['config']['environment']) ? '['.$GLOBALS['config']['environment'].'] ' : '') . 'New form "'.$params['title'].'" submission on "'.implode(' - ', $title_parts).'"';
	
	        	// send notification
				if(!empty($params['emails']) && count($params['emails'])){
					
					if (!empty($params['noreply_notification'])){
						$from = 'noreply@bytecrackers.com';
					} else {
						if (!empty($data['email']) && stristr($data['email'], '@') && stristr($data['email'], '.')){
							
							$from = $data['email'];
							
							if (!empty($data['name'])){
								$from = $data['name'].' <'.$from.'>';
							}
						
						} else {
						
							$from = 'noreply@bytecrackers.com';
						
						}
					}
					
					$this->form_model->send_contact_request($params['emails'], $data, $title, $from);
				
				}
				
				if(!empty($params['autoreply'])){
					$this->form_model->send_autoreply($data, $params['autoreply_text'], $params['autoreply_email'], $params['autoreply_name'], $params['autoreply_subject']);
				}
				
        	}
			
			$this->form_model->create_form_data($cms_page_panel_id, !empty($data['email']) ? $data['email'] : '', $data);
			
			$return = [];

			// add to cm or mailchimp
        	if (!empty($params['add_mailchimp']) && !empty($data['email']) && !empty($params['mailchimp_api_key']) && !empty($params['mailchimp_list_id'])){
				$result = $this->form_model->create_mailchimp_subscriber($data, $params);
			}
			
			if (!empty($params['add_cm']) && !empty($data['email']) && !empty($params['cm_api_key']) && !empty($params['cm_api_url']) && !empty($params['cm_list_id'])){
				$result = $this->form_model->create_cm_subscriber($data, $params);
			}
				
			$return['message'] = 'ok';
			
			if (!empty($GLOBALS['config']['errors_visible']) && !empty($result)){
				$return['result'] = $result;
			}
			
			return $return;
        
        }
        
        return $params;
	
	}
	
}
