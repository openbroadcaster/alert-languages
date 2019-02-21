<?php

class AlertLanguagesModel extends OBFModel
{

  public function save_alerts($alerts)
  {
    // TODO only allow audio media?
  
    if(!$alerts || !is_array($alerts)) return [false, 'Invalid alerts.'];
  
    // create our demo language if needed
    if(!$this->db->id_exists('module_alert_languages',1))
    {
      $this->db->insert('module_alert_languages', ['id'=>1, 'name'=>'Demo Language']);
    }
    
    // delete current alerts for this language
    $this->db->where('language_id',1);
    $this->db->delete('module_alert_languages_alerts');
    
    // make sure media ID is valid, and save alert.
    foreach($alerts as $alert)
    {
      if($alert['name']!='' && $this->db->id_exists('media',$alert['id']))
      {
        $row = [];
        $row['language_id'] = 1;
        $row['alert_name'] = $alert['name'];
        $row['media_id'] = $alert['id'];
        $this->db->insert('module_alert_languages_alerts',$row);
      }
    }
    
    return [true,'Alerts saved.'];
  }
  
  public function get_alerts($data = null)
  {
    $this->db->where('language_id',1);
    $rows = $this->db->get('module_alert_languages_alerts');
    
    $media_model = $this->load->model('media');
    
    $alerts = [];
    
    foreach($rows as $row)
    {
      $media = $media_model('get_by_id',$row['media_id']);
      if(!$media) continue;
      $alerts[] = ['alert_name'=>$row['alert_name'],'media_id'=>$row['media_id'],'media_type'=>$media['type'],'media_format'=>$media['format']];
    }
    return [true,'Alerts.',$alerts];
  }

}