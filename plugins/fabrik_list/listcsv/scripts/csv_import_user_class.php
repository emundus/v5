<?php

/**
 * Here is an example class file for creating users when importing a CSV file in Fabrik.
 * It was written for a Fabrik subscriber, and is in daily use on several busy sites.
 *
 * To use this, you need to find (and rename) create_client_user.php, and follow the
 * instructions in that file.  There is nothing in this file that needs changing.
 *
 * @author hugh.messenger@gmail.com
 *
 */
class ImportCSVCreateUser
{

	/**
	 * DO NOT set these class variables here.  Instead, set them in your copy of create_client_user.php
	 *
	 **/

	/*
	 * REQUIRED
	*
	* The full Fabrik element names for the username, email, name and J! userid.
	* The plugin will write the newly created J! userid to the userid element.
	* These four are REQUIRED and the code will fail if they are missing or wrong.
	*/
	/**
	 * Username field - tablename___elementname
	 * @var string
	 */
	public $username_element = 'changethis___username';

	/**
	 * Email field - tablename___elementname
	 * @var string
	 */
	public $email_element = 'changethis___email';

	/**
	 * Name field - tablename___elementname
	 * @var string
	 */
	public $name_element = 'changethis___name';

	/**
	 * Userid field - tablename___elementname
	 * @var string
	 */
	public $userid_element = 'changethis_userid';

	/*
	 * OPTIONAL
	*
	* The following are optional:
	*
	* password_element - if specified, plugin we will use this as the clear text password
	* for creating a new user.  This value will be cleared and not saved in the table.
	* If not specified, plugin will generate a random password when creating new users.
	*
	* first_password_element - if specified, the clear text password used to create the
	* user will be stored in this field, whether it came from a specified password_element
	* or was randomly generated.  Can be same as password_element if you want.
	*
	* user_created_element - if specified, this element will be set to specified value
	* if a user is created.
	*
	* user_created_value - value to use when setting user_created_element above.
	*/

	/**
	 * Password field - tablename___elementname
	 * @var string
	 */
	public $password_element = '';

	/**
	 * First password field - tablename___elementname
	 * @var string
	 */
	public $first_password_element = '';

	/**
	 * User created field - tablename___elementname
	 * @var string
	 */
	public $user_created_element = '';

	/**
	 * Username created value - tablename___elementname
	 * @var string
	 */
	public $user_created_value = '1';

	/**
	 * NO USER SERVICABLE PARTS BEYOND THIS POINT!
	 *
	 * Feel free to modify this code to suit your needs ... but there's nothing "configurable" beyond here
	 *
	 * @param   number  $length  Random string length
	 * @param   string  $chars   Possible string characters
	 *
	 * @return  string
	 */

	private function rand_str($length = 8, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890')
	{
		$chars_length = (strlen($chars) - 1);
		$string = $chars{rand(0, $chars_length)};
		for ($i = 1; $i < $length; $i = strlen($string))
		{
			$r = $chars{rand(0, $chars_length)};
			if ($r != $string{$i - 1})
			{
				$string .= $r;
			}
		}
		return $string;
	}

	/**
	 * Create user
	 *
	 * @param   FabrikFEModelList  &$listModel  List Model
	 *
	 * @return boolean
	 */
	public function createUser(&$listModel)
	{
		jimport('joomla.mail.helper');
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$log = JTable::getInstance('Log', 'Table');
		$log->id = null;
		$log->message = "";
		$log->referring_url = $_SERVER['HTTP_REFERER'];
		$log->message_type = 'plg.list.listcsv.csv_import_user.information';

		$formModel = $listModel->getFormModel();
		$data = $formModel->_formData;

		$clear_passwd = '';

		// Load in the com_user language file
		$lang = JFactory::getLanguage();
		$lang->load('com_user');

		// Grab username, name and email
		// @TODO - sanity check these config vars (plus userid) to make sure they have been edited.
		$userdata['username'] = $data[$this->username_element];
		$userdata['email'] = $data[$this->email_element];
		$userdata['name'] = $data[$this->name_element];

		if (!JMailHelper::isEmailAddress($userdata['email']))
		{
			if ($app->isAdmin())
			{
				$app->enqueueMessage("No email for {$userdata['username']}");
			}
			$log->message_type = 'plg.table.tablecsv.csv_import_user.warning';
			$log->message = "No email for {$userdata['username']}";
			$log->store();
			return false;
		}

		$query->select('*')->from('#__users')->where('username = ' . $db->quote($userdata['username']));
		$db->setQuery($query);
		$existing_user = $db->loadObject();

		if (!empty($existing_user))
		{
			$user_id = $existing_user->id;
			$isNew = false;
		}
		else
		{
			$query->clear()->select('*')->from('#__users')->where('username != ' . $db->quote($userdata['username']) . ' AND email = ' . $db->quote($userdata['email']));
			$db->setQuery($query);
			$existing_email = $db->loadObject();
			if (!empty($existing_email))
			{
				if ($app->isAdmin())
				{
					$app->enqueueMessage("Email {$userdata['email']} for {$userdata['username']} already in use by {$existing_email->username}");
				}
				$log->message_type = 'plg.table.tablecsv.csv_import_user.warning';
				$log->message = "Email {$userdata['email']} for {$userdata['username']} already in use by {$existing_email->username}";
				$log->store();
				return false;
			}
			$user_id = 0;
			$isNew = true;
			if (!empty($this->password_element))
			{
				$clear_passwd = $userdata['password'] = $userdata['password2'] = $data[$this->password_element];
				$data[$this->password_element] = '';
			}
			else
			{
				$clear_passwd = $userdata['password'] = $userdata['password2'] = $this->rand_str();
			}
		}

		$user = new JUser($user_id);

		//$userdata['gid'] = 18;
		$userdata['block'] = 0;
		$userdata['id'] = $user_id;

		if ($isNew)
		{
			$now = JFactory::getDate();
			$user->set('registerDate', $now->toSql());
		}

		if (!$user->bind($userdata))
		{
			if ($app->isAdmin())
			{
				$app->enqueueMessage(JText::_('CANNOT SAVE THE USER INFORMATION'), 'message');
				$app->enqueueMessage($user->getError(), 'error');
			}
			$log->message_type = 'plg.table.tablecsv.csv_import_user.error';
			$log->message = "Error storing user info for: {$userdata['username']}";
			$log->store();
			return false;
		}

		if (!$user->save())
		{
			if ($app->isAdmin())
			{
				$app->enqueueMessage(JText::_('CANNOT SAVE THE USER INFORMATION'), 'message');
				$app->enqueueMessage($user->getError(), 'error');
			}
			$log->message_type = 'plg.table.tablecsv.csv_import_user.error';
			$log->message = "Error storing user info for: {$userdata['username']}";
			$log->store();
			return false;
		}

		// Save clear text password if requested
		if ($isNew && !empty($this->first_password_element))
		{
			$data[$this->first_password_element] = $clear_passwd;
		}

		// Store the userid
		$data[$this->userid_element] = $user->get('id');

		// Optionally set 'created' flag
		if (!empty($this->user_created_element))
		{
			$data[$this->user_created_element] = $this->user_created_value;
		}

		if ($isNew)
		{
			$log->message = "Created user: {$userdata['username']}";
		}
		else
		{
			$log->message = "Modified user: {$userdata['username']}";
		}
		$log->store();
		return true;
	}
}
