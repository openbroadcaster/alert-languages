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
      
      // get filesize
      if(!empty($media['is_archived'])) $filerootdir=OB_MEDIA_ARCHIVE;
      elseif(!empty($media['is_approved'])) $filerootdir=OB_MEDIA;
      else $filerootdir=OB_MEDIA_UPLOADS;
      $fullfilepath=$filerootdir.'/'.$media['file_location'][0].'/'.$media['file_location'][1].'/'.$media['filename'];
      $filesize=filesize($fullfilepath);
      
      $alerts[] = ['alert_name'=>$row['alert_name'],'media_id'=>$row['media_id'],'media_type'=>$media['type'],'media_format'=>$media['format'],'media_filesize'=>$filesize,'media_hash'=>$media['file_hash']];
    }
    return [true,'Alerts.',$alerts];
  }

  public function validate_language ($data) {
    if (empty($data['name'])) {
      return [false, 'Language name needs to be set.'];
    }
    
    if (empty($data['code'])) {
      return [false, 'Language code needs to be set.'];
    }
    
    $this->db->where('code', $data['code']);
    if (!empty($this->db->get_one('module_alert_languages'))) {
      return [false, 'Language code already exists.'];
    }
    
    return [true, 'Validation successful.'];
  }
  
  public function save_language ($data) {
    $language = [
      'name' => $data['name'],
      'code' => $data['code']
    ];
    
    if (!$this->db->insert('module_alert_languages', $language)) {
      return [false, 'Failed to insert language into database.'];
    }
    return [true, 'Successfully added alert language.'];
  }
  
  public function get_languages () {
    $result = $this->db->get('module_alert_languages');
    return [true, 'Successfully loaded alert languages.', $result];
  }
  
}