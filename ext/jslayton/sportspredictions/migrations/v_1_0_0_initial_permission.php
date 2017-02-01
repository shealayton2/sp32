<?php
/**
*
* Sports predictions extension for the phpBB Forum Software package.
*
* @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace jslayton\sportspredictions\migrations\v10x;

/**
* Migration stage 3: Initial permission
*/
class v1_0_0_initial_permission extends \phpbb\db\migration\migration
{
	/**
	* Assign migration file dependencies for this migration
	*
	* @return array Array of migration files
	* @static
	* @access public
	*/
	static public function depends_on()
	{
		return array('\jslayton\sportspredictions\migrations\v10x\v_1_0_0_initial_schema');
	}

	/**
	* Add or update data in the database
	*
	* @return array Array of table data
	* @access public
	*/
	public function update_data()
	{
		return array(
			// Add permission
			array('permission.add', array('a_sportspredictions', true)),

			// Set permissions
			array('permission.permission_set', array('ROLE_ADMIN_FULL', 'a_sportspredictions')),
			array('permission.permission_set', array('ROLE_ADMIN_STANDARD', 'a_sportspredictions')),
		);
	}
}
