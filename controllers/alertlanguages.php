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
  
  public function save_alerts()
  {
    $alerts = $this->data('alerts');
    
    $this->model('save_alerts', $alerts);
    return [true,'Alerts saved.'];
  }
}