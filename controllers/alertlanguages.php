<?php

class AlertLanguages extends OBFController
{
  public function __construct()
  {
    parent::__construct();
    $this->model = $this->load->model('AlertLanguages');
    $this->user->require_permission('alert_languages_module');
  }

  public function get_alerts()
  {
    return [true,'Alerts', $this->model('get_alerts')[2]];
  }
  
  public function save_alerts () {
    if (!$this->user->check_permission('alert_languages_module')) {
      return [false, 'User does not have permission to add alert.'];
    }
    
    $alerts = $this->data('alerts');
    
    $this->model('save_alerts', $alerts);
    return [true,'Alerts saved.'];
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
}