<?php

class AlertLanguagesModel extends OBFModel
{

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
  
  public function delete_language ($lang_id) {
    $this->db->where('id', $lang_id);
    $this->db->delete('module_alert_languages');
    
    $this->db->where('language_id', $lang_id);
    $this->db->delete('module_alert_languages_alerts');
    
    return [true, 'Successfully removed alert language.'];
  }
  
  public function view_language ($lang_id) {
    $this->db->where('language_id', $lang_id);
    $result = $this->db->get('module_alert_languages_alerts');
    
    return [true, 'Successfully loaded alerts.', $result];
  }
  
  public function validate_alerts ($data) {
    $this->db->where('id', $data['language']);
    if (!$this->db->get('module_alert_languages')) {
      return [false, 'Invalid language ID provided.'];
    }
  
    foreach ($data['alerts'] as $alert) {
      $this->db->where('id', $alert['media']);
      if (!$this->db->get('media')) {
        return [false, 'Invalid media ID provided to alert.'];
      }
    }
        
    // TODO: Possibly ensure that all event codes provided are valid.
    
    return [true, 'Successfully validated alerts.'];
  }
  
  public function update_alerts ($data) {
    $this->db->where('language_id', $data['language']);
    $this->db->delete('module_alert_languages_alerts');
    
    foreach ($data['alerts'] as $alert) {
      $item = array(
        'language_id' => $data['language'],
        'alert_name'  => $alert['event'],
        'media_id'    => $alert['media']
      );
      $this->db->insert('module_alert_languages_alerts', $item);
    }
    
    return [true, 'Successfully updated alerts.'];
  }
}