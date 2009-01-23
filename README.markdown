
Snitch
======================================================================

Snitch enables you to send email notifications when your
entries are created, updated, and deleted, as well as customize
the email template.

It is developed by [Brandon Kelly](http://brandon-kelly.com/).


Installation
----------------------------------------------------------------------

1. Download and unzip the latest version
2. Upload `extensions/ext.snitch.php` to `system/extensions`
3. Upload `language/english/lang.snitch.php` to
   `system/language/english`
4. Enable Snitch in the Extensions Manager


Configuration
----------------------------------------------------------------------

From Snitch’s Settings page, you can set the following preferences:

####  Notification Settings  #########################################
Define when Snitch should send out email notifications (on create,
update, and/or delete). You can also choose to have the active user’s
notification skipped over.

####  Email Template  ################################################
Customize the email subject and body using the same syntax as
ExpressionEngine templates.

####  Check for Snitch updates?  #####################################
Powered by [LG Addon Updater](http://leevigraham.com/cms-customisation/expressionengine/lg-addon-updater/),
Snitch can call home and check to see if there’s a new
update available.


Usage
----------------------------------------------------------------------

####  Enable notifications and define recipients  ####################
Snitch sits on top of ExpressionEngine’s built-in notification
settings. Make sure that you’ve enabled notifications and defined the
recipients for each weblog.

####  Customizing your email template  ###############################
The following variable tags are available to the email’s Subject and

* 
##### `{action}`
  The action that just took place (“created”, “updated”, or “deleted”)

* 
##### `{email}`
  The active user’s email address

* 
##### `{entry_id}`
  The entry’s ID

* 
##### `{entry_status}`
  The entry’s status

* 
##### `{entry_title}`
  The entry’s title

* 
##### `{name}`
  The active user’s username

* 
##### `{url}`
  A combination of the `{weblog_url}` and the `{url_title}`

* 
##### `{url_title}`
  The entry’s URL Title

* 
##### `{weblog_name}`
  The name of the entry’s weblog

* 
##### `{weblog_url}`
  The URL of the entry’s weblog


Requirements
----------------------------------------------------------------------
Snitch requires ExpressionEngine 1.6+


Change Log
----------------------------------------------------------------------

####  1.0.0  #########################################################
- Initial release

####  1.1.0  #########################################################
- LG Addon Updater support
- Per-MSM-site settings


Onward
----------------------------------------------------------------------

- [Snitch documentation](http://brandon-kelly.com/apps/snitch)
- [Snitch’s thread on EE Forums](http://expressionengine.com/forums/viewthread/76075/)
- [Snitch support on Get Satisfaction](http://getsatisfaction.com/brandonkelly/products/brandonkelly_snitch)
