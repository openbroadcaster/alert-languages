<?php

class AlertLanguages extends OBFController
{
  public function __construct()
  {
    parent::__construct();
    $this->model = $this->load->model('AlertLanguages');
    $this->user->require_permission('alert_languages_module');
  }
  
  public function save_language () {
    if (!$this->user->check_permission('alert_languages_module')) {
      return [false, 'User does not have permission to add alert language.'];
    }
    
    $data = array(
      'name' => $this->data('name'),
      'code' => $this->data('code')
    );
    
    $result = $this->model('validate_language', $data);
    if (!$result[0]) {
      return $result;
    }
    
    return $this->model('save_language', $data);
  }
  
  public function get_languages () {
    if (!$this->user->check_permission('alert_languages_module')) {
      return [false, 'User does not have permission to list alert languages.'];
    }
    
    return $this->model('get_languages');
  }
  
  public function delete_language () {
    if (!$this->user->check_permission('alert_languages_module')) {
      return [false, 'User does not have permission to delete alert language.'];
    }
    
    return $this->model('delete_language', $this->data('lang_id'));
  }
  
  public function view_language () {
    if (!$this->user->check_permission('alert_languages_module')) {
      return [false, 'User does not have permission to view language alerts.'];
    }
    
    return $this->model('view_language', $this->data('lang_id'));
  }
  
  public function update_alerts () {
    if (!$this->user->check_permission('alert_languages_module')) {
      return [false, 'User does not have permission to update alerts.'];
    }
    
    $data = array(
      'language' => $this->data['language'],
      'alerts'   => $this->data['alerts']
    );
    
    $result = $this->model('validate_alerts', $data);
    if (!$result[0]) {
      return $result;
    }
    
    return $this->model('update_alerts', $data);
  }
}