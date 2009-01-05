<?php

/**
 * Snitch
 *
 * This extension enables you to send email notifications on create, edit, and delete
 *
 * @package   Snitch
 * @author    Brandon Kelly <me@brandon-kelly.com>
 * @link      http://brandon-kelly.com/apps/snitch
 * @copyright Copyright (c) 2008-2009 Brandon Kelly
 * @license   http://creativecommons.org/licenses/by-sa/3.0/   Attribution-Share Alike 3.0 Unported
 */
class Snitch
{
	/**
	 * Extension Settings
	 *
	 * @var array
	 */
	var $settings		= array();
	
	/**
	 * Extension Name
	 *
	 * @var string
	 */
	var $name			= 'Snitch';
	
	/**
	 * Extension Class Name
	 *
	 * @var string
	 */
	var $class_name		= 'Snitch';
	
	/**
	 * Extension Version
	 *
	 * @var string
	 */
	var $version		= '1.1.0';
	
	/**
	 * Extension Description
	 *
	 * @var string
	 */
	var $description	= 'Send email notifications on create, edit, and delete';
	
	/**
	 * Extension Settings Exist
	 *
	 * If set to 'y', a settings page will be shown in the Extensions Manager
	 *
	 * @var string
	 */
	var $settings_exist	= 'y';
	
	/**
	 * Documentation URL
	 *
	 * @var string
	 */
	var $docs_url		= 'http://brandon-kelly.com/apps/snitch?utm_campaign=snitch_em';
	
	
	
	/**
	 * Extension Constructor
	 *
	 * @param array   $settings
	 * @since version 1.0.0
	 */
	function Snitch($settings=array())
	{
		$this->settings = $this->get_site_settings($settings);
	}
	
	
	
	/**
	 * Get All Settings
	 *
	 * @return array   All extension settings
	 * @since  version 1.1.0
	 */
	function get_all_settings()
	{
		global $DB;

		$query = $DB->query("SELECT settings
		                     FROM exp_extensions
		                     WHERE class = '{$this->class_name}'
		                       AND settings != ''
		                     LIMIT 1");

		return $query->num_rows
			? unserialize($query->row['settings'])
			: array();
	}



	/**
	 * Get Default Settings
	 * 
	 * @return array   Default settings for site
	 * @since 1.1.0
	 */
	function get_default_settings()
	{
		$settings = array(
			'notify_on_create'   => 'y',
			'notify_on_update'   => 'y',
			'notify_on_delete'   => 'y',
			'skip_self'          => 'n',
			'email_tit_template' => 'Entry {action}: {entry_title}',
			'email_msg_template' => "Weblog:  {weblog_name}\n"
			                      . "Title:  {entry_title}\n"
			                      . "ID:  {entry_id}\n"
			                      . "Status:  {entry_status}\n"
			                      . "Performed by:  {name}\n"
			                      . "E-mail:  {email}\n\n"
			                      . "URL:  {url}",
			
			'check_for_extension_updates' => 'y'
		);

		return $settings;
	}



	/**
	 * Get Site Settings
	 *
	 * @param  array   $settings   Current extension settings (not site-specific)
	 * @return array               Site-specific extension settings
	 * @since  version 1.1.0
	 */
	function get_site_settings($settings=array())
	{
		global $PREFS;
		
		$site_settings = $this->get_default_settings();
		
		$site_id = $PREFS->ini('site_id');
		if (isset($settings[$site_id]))
		{
			$site_settings = array_merge($site_settings, $settings[$site_id]);
		}

		return $site_settings;
	}
	
	
	
	/**
	 * Settings Form
	 *
	 * Construct the custom settings form.
	 *
	 * @param  array   $current   Current extension settings (not site-specific)
	 * @see    http://expressionengine.com/docs/development/extensions.html#settings
	 * @since  version 1.1.0
	 */
	function settings_form($current)
	{
	    $current = $this->get_site_settings($current);

	    global $DB, $DSP, $LANG, $IN, $PREFS;

		// Breadcrumbs

		$DSP->crumbline = TRUE;

		$DSP->title = $LANG->line('extension_settings');
		$DSP->crumb = $DSP->anchor(BASE.AMP.'C=admin'.AMP.'area=utilities', $LANG->line('utilities'))
		            . $DSP->crumb_item($DSP->anchor(BASE.AMP.'C=admin'.AMP.'M=utilities'.AMP.'P=extensions_manager', $LANG->line('extensions_manager')))
		            . $DSP->crumb_item($this->name);

	    $DSP->right_crumb($LANG->line('disable_extension'), BASE.AMP.'C=admin'.AMP.'M=utilities'.AMP.'P=toggle_extension_confirm'.AMP.'which=disable'.AMP.'name='.$IN->GBL('name'));

		// Donations button

		$DSP->body = '';
		
		// Donations button
	    $DSP->body .= '<div style="float:right;">'
	                . '<a style="display:block; margin:-2px 10px 0 0; padding:5px 0 5px 70px; width:190px; height:15px; font-size:12px; line-height:15px;'
	                . ' background:url(http://brandon-kelly.com/images/shared/donations.png) no-repeat 0 0; color:#000; font-weight:bold;"'
	                . ' href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=2181794" target="_blank">'
	                . $LANG->line('donate')
	                . '</a>'
	                . '</div>'

		// Form header

		           . "<h1>{$this->name} <small>{$this->version}</small></h1>"

		           . $DSP->form_open(
		                                 array(
		                                     'action' => 'C=admin'.AMP.'M=utilities'.AMP.'P=save_extension_settings',
		                                     'name'   => 'settings_example',
		                                     'id'     => 'settings_example'
		                                 ),
		                                 array(
		                                     'name' => strtolower($this->class_name)
		                                 )
		                             )

		// Notifications

		           . $DSP->table_open(
		                                  array(
		                                      'class'  => 'tableBorder',
		                                      'border' => '0',
		                                      'style'  => 'margin-top:18px; width:100%'
		                                  )
		                              )

		           . $DSP->tr()
		           . $DSP->td('tableHeading', '', '2')
		           . $LANG->line('notify_title')
		           . $DSP->td_c()
		           . $DSP->tr_c()

		           . $DSP->tr()
		           . $DSP->td('', '', '2')
		           . '<div class="box" style="border-width:0 0 1px 0; margin:0; padding:10px 5px"><p>'.$LANG->line('notify_info').'</p></div>'
		           . $DSP->td_c()
		           . $DSP->tr_c()

		             // notify_on_create
		           . $DSP->tr()
		           . '<td class="tableCellOne" style="width:60%; padding-top:8px; vertical-align:top;">'
		           . $DSP->qdiv('defaultBold', $LANG->line('notify_on_create_label'))
		           . $DSP->td_c()
		           . $DSP->td('tableCellOne')
		           . $DSP->input_select_header('notify_on_create')
		           . $DSP->input_select_option('y', $LANG->line('yes'), ($current['notify_on_create'] == 'y' ? 'y' : ''))
		           . $DSP->input_select_option('n', $LANG->line('no'),  ($current['notify_on_create'] != 'y' ? 'y' : ''))
		           . $DSP->input_select_footer()
		           . $DSP->td_c()
		           . $DSP->tr_c()
		           
		             // notify_on_update
		           . $DSP->tr()
		           . '<td class="tableCellTwo" style="width:60%; padding-top:8px; vertical-align:top;">'
		           . $DSP->qdiv('defaultBold', $LANG->line('notify_on_update_label'))
		           . $DSP->td_c()
		           . $DSP->td('tableCellTwo')
		           . $DSP->input_select_header('notify_on_update')
		           . $DSP->input_select_option('y', $LANG->line('yes'), ($current['notify_on_update'] == 'y' ? 'y' : ''))
		           . $DSP->input_select_option('n', $LANG->line('no'),  ($current['notify_on_update'] != 'y' ? 'y' : ''))
		           . $DSP->input_select_footer()
		           . $DSP->td_c()
		           . $DSP->tr_c()
		           
		             // notify_on_delete
		           . $DSP->tr()
		           . '<td class="tableCellOne" style="width:60%; padding-top:8px; vertical-align:top;">'
		           . $DSP->qdiv('defaultBold', $LANG->line('notify_on_delete_label'))
		           . $DSP->td_c()
		           . $DSP->td('tableCellOne')
		           . $DSP->input_select_header('notify_on_delete')
		           . $DSP->input_select_option('y', $LANG->line('yes'), ($current['notify_on_delete'] == 'y' ? 'y' : ''))
		           . $DSP->input_select_option('n', $LANG->line('no'),  ($current['notify_on_delete'] != 'y' ? 'y' : ''))
		           . $DSP->input_select_footer()
		           . $DSP->td_c()
		           . $DSP->tr_c()
		           
		             // skip_self
		           . $DSP->tr()
		           . $DSP->td('', '', '2')
		           . '<div class="box" style="border-width:0 0 1px 0; margin:0; padding:10px 5px"><p>'.$LANG->line('skip_self_info').'</p></div>'
		           . $DSP->td_c()
		           . $DSP->tr_c()
		           
		           . $DSP->tr()
		           . '<td class="tableCellTwo" style="width:60%; padding-top:8px; vertical-align:top;">'
		           . $DSP->qdiv('defaultBold', $LANG->line('skip_self_label'))
		           . $DSP->td_c()
		           . $DSP->td('tableCellTwo')
		           . $DSP->input_select_header('skip_self')
		           . $DSP->input_select_option('y', $LANG->line('yes'), ($current['skip_self'] == 'y' ? 'y' : ''))
		           . $DSP->input_select_option('n', $LANG->line('no'),  ($current['skip_self'] != 'y' ? 'y' : ''))
		           . $DSP->input_select_footer()
		           . $DSP->td_c()
		           . $DSP->tr_c()

		           . $DSP->table_c()
		
		// Templates

		           . $DSP->table_open(
		                                  array(
		                                      'class'  => 'tableBorder',
		                                      'border' => '0',
		                                      'style'  => 'margin-top:18px; width:100%'
		                                  )
		                              )

		           . $DSP->tr()
		           . $DSP->td('tableHeading', '', '2')
		           . $LANG->line('templates_title')
		           . $DSP->td_c()
		           . $DSP->tr_c()

		           . $DSP->tr()
		           . $DSP->td('', '', '2')
		           . '<div class="box" style="border-width:0 0 1px 0; margin:0; padding:10px 5px"><p>'.$LANG->line('templates_info').'</p></div>'
		           . $DSP->td_c()
		           . $DSP->tr_c()

		             // email_tit_template
		           . $DSP->tr()
		           . '<td class="tableCellOne" style="width:60%; padding-top:8px; vertical-align:top;">'
		           . $DSP->qdiv('defaultBold', $LANG->line('email_tit_template_label'))
		           . $DSP->td_c()
		           . $DSP->td('tableCellOne')
		           . $DSP->input_text('email_tit_template', $current['email_tit_template'])
		           . $DSP->td_c()
		           . $DSP->tr_c()
		           
		             // email_msg_template
		           . $DSP->tr()
		           . '<td class="tableCellTwo" style="width:60%; padding-top:8px; vertical-align:top;">'
		           . $DSP->qdiv('defaultBold', $LANG->line('email_msg_template_label'))
		           . $DSP->td_c()
		           . $DSP->td('tableCellTwo')
		           . $DSP->input_textarea('email_msg_template', $current['email_msg_template'], 15)
		           . $DSP->td_c()
		           . $DSP->tr_c()

		           . $DSP->table_c();

		// Updates Setting

		$lgau_query = $DB->query("SELECT class
		                          FROM exp_extensions
		                          WHERE class = 'Lg_addon_updater_ext'
		                            AND enabled = 'y'
		                          LIMIT 1");
		$lgau_enabled = $lgau_query->num_rows ? TRUE : FALSE;
		$check_for_extension_updates = ($lgau_enabled AND $current['check_for_extension_updates'] == 'y') ? TRUE : FALSE;

		$DSP->body .= $DSP->table_open(
		                                   array(
		                                       'class'  => 'tableBorder',
		                                       'border' => '0',
		                                       'style' => 'margin-top:18px; width:100%'
		                                   )
		                               )

		            . $DSP->tr()
		            . $DSP->td('tableHeading', '', '2')
		            . $LANG->line("check_for_extension_updates_title")
		            . $DSP->td_c()
		            . $DSP->tr_c()

		            . $DSP->tr()
		            . $DSP->td('', '', '2')
		            . '<div class="box" style="border-width:0 0 1px 0; margin:0; padding:10px 5px"><p>'.$LANG->line('check_for_extension_updates_info').'</p></div>'
		            . $DSP->td_c()
		            . $DSP->tr_c()

		            . $DSP->tr()
		            . $DSP->td('tableCellOne', '60%')
		            . $DSP->qdiv('defaultBold', $LANG->line("check_for_extension_updates_label"))
		            . $DSP->td_c()

		            . $DSP->td('tableCellOne')
		            . '<select name="check_for_extension_updates"'.($lgau_enabled ? '' : ' disabled="disabled"').'>'
		            . $DSP->input_select_option('y', $LANG->line('yes'), ($current['check_for_extension_updates'] == 'y' ? 'y' : ''))
		            . $DSP->input_select_option('n', $LANG->line('no'),  ($current['check_for_extension_updates'] != 'y' ? 'y' : ''))
		            . $DSP->input_select_footer()
		            . ($lgau_enabled ? '' : NBS.NBS.NBS.$LANG->line('check_for_extension_updates_nolgau'))
		            . $DSP->td_c()
		            . $DSP->tr_c()

		            . $DSP->table_c()

		// Close Form

		            . $DSP->qdiv('itemWrapperTop', $DSP->input_submit())
		            . $DSP->form_c();
	}



	/**
	 * Save Settings
	 *
	 * @since version 1.1.0
	 */
	function save_settings()
	{
		global $DB, $PREFS;

		$settings = $this->get_all_settings();
		$current = $this->get_site_settings($settings);

		// Save new settings
		$settings[$PREFS->ini('site_id')] =
			$this->settings = array(
				'notify_on_create'            => $_POST['notify_on_create'],
				'notify_on_update'            => $_POST['notify_on_update'],
				'notify_on_delete'            => $_POST['notify_on_delete'],
				'skip_self'                   => $_POST['skip_self'],
				'email_tit_template'          => $_POST['email_tit_template'],
				'email_msg_template'          => $_POST['email_msg_template'],
				'check_for_extension_updates' => $_POST['check_for_extension_updates']
			);

		$DB->query("UPDATE exp_extensions
		            SET settings = '".addslashes(serialize($settings))."'
		            WHERE class = '{$this->class_name}'");
	}
	
	
	
	/**
	 * Activate Extension
	 *
	 * Resets all Snitch exp_extensions rows
	 *
	 * @since version 1.0.0
	 */
	function activate_extension($settings='')
	{
		global $DB;

		// Get settings
		if ( ! (is_array($settings) AND $settings))
		{
			$settings = $this->get_all_settings();
		}

		// Delete old hooks
		$DB->query("DELETE FROM exp_extensions
		            WHERE class = '{$this->class_name}'");

		// Add new extensions
		$ext_template = array(
			'class'    => $this->class_name,
			'settings' => addslashes(serialize($settings)),
			'priority' => 10,
			'version'  => $this->version,
			'enabled'  => 'y'
		);

		$extensions = array(
			// LG Addon Updater
			array('hook'=>'lg_addon_update_register_source', 'method'=>'register_my_addon_source'),
			array('hook'=>'lg_addon_update_register_addon',  'method'=>'register_my_addon_id'),

			// on create / modify
			array('hook'=>'submit_new_entry_start',          'method'=>'override_notification_pref'),
			array('hook'=>'submit_new_entry_end',            'method'=>'send_submit_notification'),

			// on delete
			array('hook'=>'delete_entries_start',            'method'=>'gather_deleted_entries'),
			array('hook'=>'delete_entries_loop',             'method'=>'send_delete_notification')
		);

		foreach($extensions as $extension)
		{
			$ext = array_merge($ext_template, $extension);
			$DB->query($DB->insert_string('exp_extensions', $ext));
		}
	}



	/**
	 * Update Extension
	 *
	 * @param string   $current   Previous installed version of the extension
	 * @since version 1.0.0
	 */
	function update_extension($current='')
	{
		global $DB;

		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}

		if ($current < '1.1.0')
		{
			// make settings site-specific
			$settings = $this->get_all_settings();
			$all_settings = array();
			$sites = $DB->query('SELECT site_id FROM exp_sites');
			foreach($sites->result as $site)
			{
				$all_settings[$site['site_id']] = $settings;
			}
			
			// Add new hooks
			$this->activate_extension($all_settings);
			return;
		}
		
		// update the version
		$DB->query("UPDATE exp_extensions
		            SET version = '".$DB->escape_str($this->version)."'
		            WHERE class = '{$this->class_name}'");
	}



	/**
	 * Disable Extension
	 *
	 * @since version 1.0.0
	 */
	function disable_extension()
	{
		global $DB;

		$DB->query("UPDATE exp_extensions
		            SET enabled='n'
		            WHERE class='{$this->class_name}'");
	}



	/**
	 * Get Last Call
	 *
	 * @param  mixed   $param   Parameter sent by extension hook
	 * @return mixed            Return value of last extension call if any, or $param
	 * @since  version 1.1.0
	 */
	function get_last_call($param='')
	{
		global $EXT;

		return ($EXT->last_call !== FALSE) ? $EXT->last_call : $param;
	}
	
	
	
	/**
	 * Register a New Addon Source
	 *
	 * @param  array   $sources   The existing sources
	 * @return array              The new source list
	 * @see    http://leevigraham.com/cms-customisation/expressionengine/lg-addon-updater/
	 * @since  version 1.1.0
	 */
	function register_my_addon_source($sources)
	{
		$sources = $this->get_last_call($sources);

		if ($this->settings['check_for_extension_updates'] == 'y')
		{
		    $sources[] = 'http://brandon-kelly.com/downloads/versions.xml';
		}
		return $sources;

	}



	/**
	 * Register a New Addon ID
	 *
	 * @param  array   $addons   The existing sources
	 * @return array             The new addon list
	 * @see    http://leevigraham.com/cms-customisation/expressionengine/lg-addon-updater/
	 * @since  version 1.1.0
	 */
	function register_my_addon_id($addons)
	{
		$addons = $this->get_last_call($addons);

	    if ($this->settings['check_for_extension_updates'] == 'y')
	    {
	        $addons[$this->class_name] = $this->version;
	    }
	    return $addons;
	}
	
	
	
	/**
	 * Override Notification Preferences
	 *
	 * To avoid notifications getting sent twice, we first set each weblog's
	 * notification preference to something that's not meaningful to EE
	 *
	 * @see   http://expressionengine.com/developers/extension_hooks/submit_new_entry_start/
	 * @since version 1.0.0
	 */
	function override_notification_pref()
	{
		global $DB;
		
		$DB->query("UPDATE exp_weblogs
		            SET weblog_notify = 'o'
		            WHERE weblog_notify = 'y'");
	}
	
	
	
	/**
	 * Send Submit Notification
	 *
	 * Sends 'create' and 'update' notifications, and then returns
	 * each weblog's notification preferences back to normal
	 *
	 * @see   http://expressionengine.com/developers/extension_hooks/submit_new_entry_end
	 * @since version 1.0.0
	 */
	function send_submit_notification($entry_id, $data, $ping_message)
	{
		global $DB;
		
		if (array_key_exists('entry_id', $data))
		{
			if ($this->settings['notify_on_create'] == 'y')
			{
				$data['entry_id'] = $entry_id;
				$this->send_notification('created', $data);
			}
		}
		else
		{
			if ($this->settings['notify_on_update'] == 'y')
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
	
	
	
	/**
	 * Gather Deleted Entries
	 *
	 * Since the delete_entries_loop hook doesn't send the entry's data,
	 * we need to grab it here first and store it as a global variable.
	 *
	 * @see   http://expressionengine.com/developers/extension_hooks/delete_entries_start/
	 * @since version 1.0.0
	 */
	function gather_deleted_entries()
	{
		global $DB, $snitch_deleted_entries;
		
		if ($this->settings['notify_on_delete'] == 'y')
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
	
	
	
	/**
	 * Send Delete Notification
	 *
	 * @see   http://expressionengine.com/developers/extension_hooks/delete_entries_loop/
	 * @since version 1.0.0
	 */
	function send_delete_notification($val, $weblog_id)
	{
		global $snitch_deleted_entries;
		
		if ($this->settings['notify_on_delete'] == 'y')
		{
			if (isset($snitch_deleted_entries[$val]))
			{
				$data = $snitch_deleted_entries[$val];
				$data['entry_id'] = $val;
				
				$this->send_notification('deleted', $data);
			}
		}
	}
	
	
	
	/**
	 * Send Notification
	 *
	 * This is the function that ultimately sends all notifications.
	 *
	 * @see   http://expressionengine.com/developers/extension_hooks/delete_entries_loop/
	 * @since version 1.0.0
	 */
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
		
		if ($this->settings['skip_self'] == 'y')
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
			
			$email_tit = $FNS->var_swap($this->settings['email_tit_template'], $swap);
			$email_msg = $FNS->var_swap($this->settings['email_msg_template'], $swap);
			
			
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
}

?>