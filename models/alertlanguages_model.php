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
    $result = $this->db->delete('module_alert_languages');
    
    // TODO: Remove associated alerts as well once implemented
    
    return [true, 'Successfully removed alert language.', $result];
  }
}