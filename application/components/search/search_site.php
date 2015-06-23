<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Search_site extends CI_Component {
  
  function __construct() {
    parent::__construct();
		
    $this->load->model('search/models/search_model');
    $this->load->model('publication/models/publication_model');
  }
  
  function render_template_findings() {
    $vars = array(
      'query_string' => $this->uri->getParam('query_string'),
      'results' => array(),
      'error' => ''
    );

    if (mb_strlen($vars['query_string']) < 3) {
      $vars['error'] = 'Слишком короткий запрос, минимальная длина - 3 символа';
    } else {
      $items = $this->search_model->search($this->search_model->search_prepare($vars['query_string']), array('page'));
      
      foreach ($items as $item) {
        switch ($item['type']) {
          case 'page':
            $result = $this->projects_model->get_page($item['id']);
            if ($result) {
              $parent_page = $this->projects_model->get_page($result['parent_id']);
              $result['show_name'] = $result['params']['name_'. $this->language];
              if ($parent_page) {
                $result['category_name'] = $parent_page['params']['name_'. $this->language];
              }
            }
          break;
          case 'publication':
            $result = $this->publication_model->get_publication_one(array('id' => $item['id']));
            if ($result) {
              $category = $this->publication_model->get_publication_one(array('id' => $result['parent_id']));
              if ($category) {
                $result['category_name'] = $category['params']['name_'. $this->language];
                $publication_page = $this->projects_model->get_page($category['page_id'][0]);
              } else {
                $publication_page = $this->projects_model->get_page($result['page_id'][0]);
              }
              $result['show_name'] = $result['params']['name_'. $this->language];

              $result['path'] = $publication_page['path'] . $result['system_name'] .'/';
            }
          break;
        }
        if (isset($result) && $result) {
          $vars['results'][] = $result;
        }
      }
    }
    return $this->render_template('templates/findings', $vars);    
	} 
  
}