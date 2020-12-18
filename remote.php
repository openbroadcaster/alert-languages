<?php

require('../../components.php');

class AlertLanguagesRemote
{
  private $io;
  private $load;
  private $user;
  private $db;

  public function __construct()
  {    
    $this->io = OBFIO::get_instance();
    $this->load = OBFLoad::get_instance();
    $this->user = OBFUser::get_instance();
    $this->db = OBFDB::get_instance();
    
    // if this module is not enabled, return 404.
    $modules_model = $this->load->model('Modules');
    $installed_modules = $modules_model('get_installed');
    
    $installed = false;
    foreach($installed_modules as $module)
    {
      if($module['dir']=='alert_languages')
      {
        $installed = true;
        break;
      }
    }
    
    if(!$installed)
    {
      http_response_code(404);
      echo '<html><body><h1>Error 404</h1><p>Alert Languages module not installed.</p></body></html>';
      return;
    }
    
    // validate player id/password
    $error = json_encode(['status'=>false,'msg'=>'Invalid player ID or password.']);

    $player_id = $_POST['id'] ?? null;
    $player_password = $_POST['pw'] ?? null;
    
    if(!$player_id || !$player_password)
    {
      echo $error;
      return;
    }
    
    $players_model = $this->load->model('Players');
    $player = $players_model('get_one',$player_id);
    
    if(!$player || !password_verify($player_password.OB_HASH_SALT, $player['password']))
    {
      echo $error;
      return;
    }

    // get languages & media IDs for alerts
    $alert_languages_model = $this->load->model('AlertLanguages');
    echo json_encode([true, 'Alerts', $alert_languages_model('remote_get_alerts')]);
  }
}

new AlertLanguagesRemote();