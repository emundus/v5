<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

class AdmintoolsTableCustomperm extends FOFTable
{
	var $perms = '0644';

	public function __construct( $table, $key, &$db )
	{
		parent::__construct( '#__admintools_customperms', 'id', $db );
	}
}
