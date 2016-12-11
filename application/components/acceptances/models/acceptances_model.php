<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Acceptances_model extends CI_Model {
  
  function __construct() {
    parent::__construct();
    
    $this->load->model('cities/models/cities_model');
    $this->load->model('clients/models/clients_model');
    $this->load->model('products/models/products_model');
  }

  function get_acceptances($limit = 0, $offset = 0, $where = array(), $order_by = array(), $product_id = array()) {
    $this->db->select('client_acceptances.*');
    //для проверки прав на работу по всем клиентам
    if(is_array($where) && @$where['clients.admin_id']){
      $this->db->join('clients','clients.id = client_acceptances.client_id');      
    }
    if ($where) {
      $this->db->where($where);
    }
    if ($product_id) {
      if(!is_array($product_id)){
        $product_id = array($product_id);
      }
      $this->db->join('client_acceptances t2','t2.parent_id = client_acceptances.id');
      // join-им чтобы вывести отчет по группе продукции
      $this->db->join('products t3','t3.id = t2.product_id');
      $product_where = '';
      if ($where) {
        $product_where .= '(';
      }
      foreach ($product_id as $key => $value) {
        if($key != 0){
          $product_where .= ' OR ';
        }
        $product_where .= 't3.id = '.$value.' OR t3.parent_id = '.$value;
      }
      if ($where) {
        $product_where .= ')';
      }
      $this->db->where($product_where);
    }
    if ($limit) {
      $this->db->limit($limit, $offset);
    }
    if ($order_by) {
      foreach ($order_by as $field => $dest) {
        $this->db->order_by($field,$dest);
      }
    } else {
      $this->db->order_by('date','desc');
      $this->db->order_by('id','asc');
    }
    $this->db->group_by('client_acceptances.id');
    $items = $this->db->get('client_acceptances')->result_array();
    unset($where);
    foreach ($items as $key => &$item) {
      $item['client_title'] = $item['company'];
      if($item['client_id']){
        $item['client'] = $this->clients_model->get_client(array('id'=>$item['client_id']));
        if($item['client']){
          $item['client_title'] = $item['client']['title_full'];
        }
      }
      if(is_null($item['parent_id'])){
        $this->db->select('client_acceptances.*,t2.title_full as product_title');
        // join-им чтобы вывести название товара и отчет по группе продукции
        $this->db->join('products t2','t2.id = client_acceptances.product_id');
        // Делаем запрос на дочерние акты, для отображения видов сырья в акте
        $where = 'client_acceptances.parent_id = '.$item['id'];
        if ($product_id) {          
          $where .= ' AND (';
          foreach ($product_id as $key => $value) {
            if($key != 0){
              $where .= ' OR ';
            }
            $where .= 't2.id = '.$value.' OR t2.parent_id = '.$value;
          }
          $where .= ')';
        }
        $this->db->where($where);
        $this->db->order_by('client_acceptances.order','asc');
        $this->db->order_by('client_acceptances.id','asc');
        $item['childs'] = $this->db->get('client_acceptances')->result_array();

        //считаем общие параметры
        $item['gross'] = $item['net'] = $item['price'] = $item['sum'] = 0;
        foreach ($item['childs'] as $key => &$child) {
          $child['sum'] = $child['price']*$child['net'];
          $item['gross'] += $child['gross'];
          $item['net'] += $child['net'];
          $item['price'] += ($child['price']*$child['net']);
          $item['sum'] = $item['price']-$item['add_expenses'];
        }
        unset($child);
      }
    }
    unset($item);
    
    return $items;
  }
  
  function get_acceptances_cnt($where = '', $product_id = array()) {
    $this->db->select('COUNT(DISTINCT(pr_client_acceptances.id)) as cnt');
    //для проверки прав на работу по всем клиентам
    if(is_array($where) && @$where['clients.admin_id']){
      $this->db->join('clients','clients.id = client_acceptances.client_id');      
    }
    if ($where) {
      $this->db->where($where);
    }
    if ($product_id) {
      if(!is_array($product_id)){
        $product_id = array($product_id);
      }
      $this->db->join('client_acceptances t2','t2.parent_id = client_acceptances.id');
      // join-им чтобы вывести отчет по группе продукции
      $this->db->join('products t3','t3.id = t2.product_id');
      $product_where = '';
      if ($where) {
        $product_where .= '(';
      }
      foreach ($product_id as $key => $value) {
        if($key != 0){
          $product_where .= ' OR ';
        }
        $product_where .= 't3.id = '.$value.' OR t3.parent_id = '.$value;
      }
      if ($where) {
        $product_where .= ')';
      }
      $this->db->where($product_where);
    }
    return $this->db->get('client_acceptances')->row()->cnt;
  }

  function get_acceptance($where = array(), $full = true) {
    $this->db->select('client_acceptances.*');
    $item = $this->db->get_where('client_acceptances', $where)->row_array();
    if($item && $full){
      $item['client_title'] = $item['company'];
      if($item['client_id']){
        $item['client'] = $this->clients_model->get_client(array('id'=>$item['client_id']));
        if($item['client']){
          $item['client_title'] = $item['client']['title'];
          if($item['client']['city_id']){
            $item['city'] = $this->cities_model->get_city(array('id' => $item['client']['city_id']));
          }
        }
      }
      $item['childs'] = $this->get_acceptances(0,0,array('parent_id'=>$item['id']),array('order'=>'asc','id'=>'asc'));
      foreach ($item['childs'] as $key => &$child) {
        $child['product'] = $this->products_model->get_product(array('id' => $child['product_id']));
      }
      unset($child);
    }

    return $item;
  }

  function create_acceptance($params) {
    if ($this->db->insert('client_acceptances', $params)) {
      return $this->db->query("SELECT LAST_INSERT_ID() as id")->row()->id;
    }
    return false;
  }

  function update_acceptance($id, $params) {
    if ($this->db->update('client_acceptances', $params, array('id' => $id))) {
      return true;
    }
    return false;
  }
  
  function delete_acceptance($id) {
    if ($this->db->delete('client_acceptances', array('id' => $id))) {
      return true;
    }
    return false;
  }

  function get_acceptance_emails($where = array(), $order_by = array(), $limit = 0, $offset = 0, $group_by = array()) {
    $this->db->select('client_acceptance_emails.*,admins.username as username');
    if ($order_by) {
      foreach ($order_by as $field => $dest) {
        $this->db->order_by($field,$dest);
      }
    } else {
      $this->db->order_by('tm','desc');
    }
    if ($group_by) {
      foreach ($group_by as $key => $field) {
        $this->db->group_by($field);
      }
    }
    if ($where) {
      $this->db->where($where);
    }
    $this->db->join('admins','admins.id=client_acceptance_emails.admin_id');
    if ($limit) {
      $this->db->limit($limit, $offset);
    }
    $items = $this->db->get('client_acceptance_emails')->result_array();
    // echo $this->db->last_query();

    return $items;
  }

  function create_acceptance_email($params) {
    if ($this->db->insert('client_acceptance_emails', $params)) {
      return $this->db->query("SELECT LAST_INSERT_ID() as id")->row()->id;
    }
    return false;
  }
}