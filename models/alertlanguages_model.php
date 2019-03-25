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
    
    foreach ($result as $index => $lang) {
      // TODO: Is there a way to use COUNT() in OBDB so I can use a JOIN 
      // instead of this loop?
      $this->db->where('language_id', $lang['id']);
      $media = $this->db->get('module_alert_languages_alerts');
      $result[$index]['media_items'] = count($media);
    }
    
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
    $this->db->leftjoin('media', 'module_alert_languages_alerts.media_id', 'media.id');
    $this->db->where('module_alert_languages_alerts.language_id', $lang_id);
    $this->db->what('module_alert_languages_alerts.language_id');
    $this->db->what('module_alert_languages_alerts.alert_name');
    $this->db->what('module_alert_languages_alerts.media_id');
    $this->db->what('media.title');
    $this->db->what('media.artist');
    $this->db->what('media.id');
    $rows = $this->db->get('module_alert_languages_alerts');
    
    $result = array();
    foreach ($rows as $row) {
      if ($row['title'] == null) continue;
      
      $result[] = array(
        'language_id' => $row['language_id'],
        'media_id'    => $row['media_id'],
        'title'       => $row['title'],
        'artist'      => $row['artist'],
        'alert_name'  => $row['alert_name']
      );
    }

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
  
  public function remote_get_alerts () {
    $langs = $this->db->get('module_alert_languages');
    $result = array();
    foreach ($langs as $lang) {
      $this->db->leftjoin('media', 'module_alert_languages_alerts.media_id', 'media.id');
      $this->db->where('module_alert_languages_alerts.language_id', $lang['id']);
      $this->db->what('module_alert_languages_alerts.alert_name');
      $this->db->what('module_alert_languages_alerts.media_id');
      $this->db->what('media.id');
      $this->db->what('media.type');
      $this->db->what('media.format');
      $this->db->what('media.file_hash');
      $this->db->what('media.is_archived');
      $this->db->what('media.is_approved');
      $this->db->what('media.file_location');
      $this->db->what('media.filename');
      $this->db->what('media.title');
      $rows = $this->db->get('module_alert_languages_alerts');;
      
      $alerts = array();
      foreach ($rows as $row) {
        if ($row['title'] == null) continue;
        
        if (!empty($row['is_archived'])) $filerootdir = OB_MEDIA_ARCHIVE;
        elseif (!empty($row['is_approved'])) $filerootdir = OB_MEDIA;
        else $filerootdir = OB_MEDIA_UPLOADS;
        $fullfilepath = $filerootdir . '/' 
          . $row['file_location'][0] . '/'
          . $row['file_location'][1] . '/'
          . $row['filename'];
        $filesize = filesize($fullfilepath);
        
        $alerts[] = array(
          'alert_name'     => $row['alert_name'],
          'media_id'       => $row['media_id'],
          'media_format'   => $row['format'],
          'media_type'     => $row['type'],
          'media_filesize' => $filesize,
          'media_hash'     => $row['file_hash']
        );
      }
      
      $result[$lang['code']] = array(
        'name'   => $lang['name'],
        'alerts' => $alerts
      );
    }
    
    return $result;
  }
}