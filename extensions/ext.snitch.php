<?php

/**
 * Snitch
 *
 * @author Brandon Kelly
 * @link   http://brandon-kelly.com/apps/snitch/
 */

class Snitch
{
	var $settings		= array();
	
	var $name			= 'Snitch';
	var $class_name		= 'Snitch';
	var $version		= '1.0.0';
	var $description	= 'Sends e-mail notifications when weblog entries are created, updated and deleted';
	var $settings_exist	= 'y';
	var $docs_url		= 'http://brandon-kelly.com/apps/snitch/';
	
	var $default_email_tit_template = 'Entry {action}: {entry_title}';
	var $default_email_msg_template = "Weblog:  {weblog_name}
Title:  {entry_title}
ID:  {entry_id}
Status:  {entry_status}

Performed by:  {name}
E-mail:  {email}

URL:  {url}";
	
	
	
	
	// -------------------------------
	//	Constructor - Extensions use this for settings
	// -------------------------------
	
	
	function Snitch($settings='')
	{
		$this->settings = $settings;
	}
	// END
	
	
	
	
	// --------------------------------
	//  Activate Extension
	// --------------------------------
	
	
	function activate_extension()
	{
		global $DB;
		
		$extensions = array(
			'submit_new_entry_start' => 'override_notification_pref',
			'submit_new_entry_end'   => 'send_submit_notification',
			'delete_entries_start'   => 'gather_deleted_entries',
			'delete_entries_loop'    => 'send_delete_notification'
		);
		
		foreach($extensions as $hook => $method)
		{
			$ext = array(
				'extension_id' => '',
				'class'        => $this->class_name,
				'method'       => (( ! is_array($method)) ? $method : ''),
				'hook'         => $hook,
				'settings'     => '',
				'priority'     => 10,
				'version'      => $this->version,
				'enabled'      => 'y'
			);
			
			if (is_array($method))
			{
				foreach($method as $name => $value)
				{
					$ext[$name] = $value;
				}
			}
			
			$DB->query($DB->insert_string('exp_extensions', $ext));
		}
	}
	// END
	
	
	
	
	// --------------------------------
	//  Update Extension
	// --------------------------------  
	
	
	function update_extension($current='')
	{
		global $DB;
		
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}
		
		$DB->query("UPDATE exp_extensions
		            SET version = '".$DB->escape_str($this->version)."'
		            WHERE class = '".$this->class_name."'");
	}
	// END
	
	
	
	
	// --------------------------------
	//  Disable Extension
	// --------------------------------
	
	
	function disable_extension()
	{
		global $DB;
		
		$DB->query("DELETE FROM exp_extensions
		            WHERE class = '".$this->class_name."'");
	}
	// END
	
	
	
	
	// --------------------------------
	//  Settings
	// --------------------------------
	
	
	function settings()
	{
		$settings = array();
		
		$settings['notify_on_create']   = array('r', array('y' => 'yes', 'n' => 'no'), 'y');
		$settings['notify_on_update']   = array('r', array('y' => 'yes', 'n' => 'no'), 'y');
		$settings['notify_on_delete']   = array('r', array('y' => 'yes', 'n' => 'no'), 'y');
		
		$settings['email_tit_template'] = $this->default_email_tit_template;
		$settings['email_msg_template'] = array('t', $this->default_email_msg_template);
		
		$settings['skip_self']          = array('r', array('y' => 'yes', 'n' => 'no'), 'y');
		
		// Complex:
		// [variable_name] => array(type, values, default value)
		// variable_name => short name for setting and used as the key for language file variable
		// type:  t - textarea, r - radio buttons, s - select, ms - multiselect, f - function calls
		// values:  can be array (r, s, ms), string (t), function name (f)
		// default:  name of array member, string, nothing
		//
		// Simple:
		// [variable_name] => 'Butter'
		// Text input, with 'Butter' as the default.
		
		return $settings;
	}
	// END
	
	
	
	
	// --------------------------------
	//  Override Notification Preferences
	//  - Prevents system from sending the default notification email on create
	// --------------------------------
	
	function override_notification_pref()
	{
		global $DB;
		
		$DB->query("UPDATE exp_weblogs
		            SET weblog_notify = 'o'
		            WHERE weblog_notify = 'y'");
	}
	// END
	
	
	
	
	// --------------------------------
	//  Send Submission Notification
	// --------------------------------
	
	
	function send_submit_notification($entry_id, $data, $ping_message)
	{
		global $DB;
		
		if (array_key_exists('entry_id', $data))
		{
			if ( ! isset($this->settings['notify_on_create']) OR $this->settings['notify_on_create'] == 'y')
			{
				$data['entry_id'] = $entry_id;
				$this->send_notification('created', $data);
			}
		}
		else
		{
			if ( ! isset($this->settings['notify_on_update']) OR $this->settings['notify_on_update'] == 'y')
			{
				$data['entry_id'] = $entry_id;
				$this->send_notification('updated', $data);
			}
		}
		
		// Revert overridden notifcation preferences
		
		$DB->query("UPDATE exp_weblogs
		            SET weblog_notify = 'y'
		            WHERE weblog_notify = 'o'");
	}
	// END
	
	
	
	
	// --------------------------------
	//  Gather Deleted Entries
	// --------------------------------
	
	
	function gather_deleted_entries()
	{
		global $DB, $snitch_deleted_entries;
		
		if ( ! isset($this->settings['notify_on_delete']) OR $this->settings['notify_on_delete'] == 'y')
		{
			$snitch_deleted_entries = array();
			
			
			foreach ($_POST as $key => $val)
			{        
				if (strstr($key, 'delete') AND ! is_array($val) AND is_numeric($val))
				{                    
					$entry_id = $DB->escape_str($val);
					
					$query = $DB->query("SELECT weblog_id, title, url_title, status
					                       FROM exp_weblog_titles
					                      WHERE entry_id = {$entry_id}
					                      LIMIT 1");
					
					if ($query->num_rows)
					{
						$snitch_deleted_entries[$entry_id] = $query->row;
					}
				}
			}
		}
	}
	
	
	
	
	// --------------------------------
	//  Send Deletion Notification
	// --------------------------------
	
	
	function send_delete_notification($val, $weblog_id)
	{
		global $snitch_deleted_entries;
		
		if ( ! isset($this->settings['notify_on_delete']) OR $this->settings['notify_on_delete'] == 'y')
		{
			if (isset($snitch_deleted_entries[$val]))
			{
				$data = $snitch_deleted_entries[$val];
				$data['entry_id'] = $val;
				
				$this->send_notification('deleted', $data);
			}
		}
	}
	// END
	
	
	
	
	// --------------------------------
	//  Send Notification
	// --------------------------------
	
	
	function send_notification($action, $data)
	{
		global $PREFS, $FNS, $DB, $SESS, $REGX;
		
		
		$query = $DB->query("SELECT blog_title, blog_url, weblog_notify, weblog_notify_emails
		                     FROM exp_weblogs
		                     WHERE weblog_id = '".$data['weblog_id']."'");
		
		$weblog_name    = $REGX->ascii_to_entities($query->row['blog_title']);
		$weblog_url     = $query->row['blog_url'];
		$notify_address = (($query->row['weblog_notify'] == (($action == 'deleted') ? 'y' : 'o'))
		                       AND
		                   ($query->row['weblog_notify_emails'] != ''))
		                       ? $query->row['weblog_notify_emails']
		                       : '';
		
		// If the 'skip_self' setting is selected,
		// remove the current user's e-mail address from the list
		
		if ( ! isset($this->settings['skip_self']) OR $this->settings['skip_self'] == 'y')
		{
			if (eregi($SESS->userdata('email'), $notify_address))
			{
				$notify_address = str_replace($SESS->userdata('email'), '', $notify_address);				
			}
		}
		
		$notify_address = $REGX->remove_extra_commas($notify_address);
		
		if ($notify_address != '')
		{
			$swap = array(
				'action'         => $action,
				'weblog_url'     => $weblog_url,
				'url_title'      => $data['url_title'],
				'url'            => (($weblog_url AND $data['url_title'])
				                         ? $FNS->remove_double_slashes($weblog_url.'/'.$data['url_title'].'/')
				                         : ''),
				
				// User info
				'name'			 => $SESS->userdata('screen_name'),
				'email'			 => $SESS->userdata('email'),
				
				// Entry info
				'entry_id'       => $data['entry_id'],
				'entry_title'	 => $data['title'],
				'entry_status'   => $data['status'],
				
				// Weblog info
				'weblog_id'      => $data['weblog_id'],
				'weblog_name'	 => $weblog_name
			);
			
			$email_tit = $FNS->var_swap((isset($this->settings['email_tit_template'])
			                                ? $this->settings['email_tit_template']
			                                : $this->default_email_tit_template),
			                            $swap);
			
			$email_msg = $FNS->var_swap((isset($this->settings['email_msg_template'])
			                                ? $this->settings['email_msg_template']
			                                : $this->default_email_msg_template),
			                            $swap);
			
			
			if ( ! class_exists('EEmail'))
			{
				require PATH_CORE.'core.email'.EXT;
			}
			
			$email = new EEmail;
			
			foreach (explode(',', $notify_address) as $addy)
			{
				$email->initialize();
				$email->wordwrap = false;
				$email->from($PREFS->ini('webmaster_email'), $PREFS->ini('webmaster_name'));	
				$email->to($addy);
				$email->reply_to($PREFS->ini('webmaster_email'));
				$email->subject($email_tit);	
				$email->message($REGX->entities_to_ascii($email_msg));		
				$email->Send();
			}
		}
	}
	// END
}

?>