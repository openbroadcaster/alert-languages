<?php

class AlertLanguagesModule extends OBFModule
{

	public $name = 'Alert Languages v1.0';
	public $description = 'Add multi-language support to OBPlayer CAP alerts.';

	public function callbacks()
	{

	}

	public function install()
	{
      // add permissions data for this module
      $this->db->insert('users_permissions', [
        'category'=>'administration',
        'description'=>'alert languages module',
        'name'=>'alert_languages_module'
      ]);

      // table to store languages
      $this->db->query('CREATE TABLE IF NOT EXISTS `module_alert_languages` (
  			`id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  			`name` varchar(255) NOT NULL,
  			`code` varchar(50) NOT NULL UNIQUE,
  			PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');

      // table to store alert media id's
      $this->db->query('CREATE TABLE IF NOT EXISTS `module_alert_languages_alerts` (
  			`id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  			`language_id` int(10) UNSIGNED NOT NULL,
  			`alert_name` varchar(255) NOT NULL,
  			`media_id` int(10) UNSIGNED NOT NULL,
  			PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');

      return true;
	}

	public function uninstall()
	{
      // remove permissions data for this module
      $this->db->where('name','alert_languages_module');
      $permission = $this->db->get_one('users_permissions');

      $this->db->where('permission_id',$permission['id']);
      $this->db->delete('users_permissions_to_groups');

      $this->db->where('id',$permission['id']);
      $this->db->delete('users_permissions');

      return true;
	}
}
