<?php

/**
 * This file is part of the Sports Prediction package.
 *
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the license.txt file.
 */

namespace jslayton\sportspredictions\migrations\v10x;

use phpbb\db\migration\migration;

/**
 * @package jslayton\sportspredictions\migrations\v10x
 */
class v_1_0_0_initial_data extends migration
{
	/**
	 * {@inheritdoc}
	 */
	public function effectively_installed()
	{
		return isset($this->config['sportspredictions_mod_version']) && version_compare($this->config['sportspredictions_mod_version'], '1.0.0', '>=');
	}

	/**
	 * {@inheritdoc}
	 */
	static public function depends_on()
	{
		return array('\jslayton\sportspredictions\migrations\v10x\v_1_0_0_initial_schema');
	}

	/**
	 * {@inheritdoc}
	 */
	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'insert_default_sp_data'))),
		);
	}
	
	/**
	* Custom function to install sample rule data to the boardrules table in the database
	*
	* @return void
	* @access public
	*/
	public function insert_default_sp_data()
	{
		// Define sample rule data
		$default_sp_data = array(
			array(
				'config_name'	=> 'league_name',
				'config_value'	=> 'DEFAULT LEAGUE',
			),
			array(
				'config_name'	=> 'leaderboard_limit',
				'config_value'	=> '30',
			),
			array(
				'config_name'	=> 'upcoming_games_limit',
				'config_value'	=> '9',
			),
			array(
				'config_name'	=> 'exact_prediction_points',
				'config_value'	=> '5',
			),
			array(
				'config_name'	=> 'correct_prediction_points',
				'config_value'	=> '1',
			),
			array(
				'config_name'	=> 'incorrect_prediction_points',
				'config_value'	=> '0',
			),
			array(
				'config_name'	=> 'draw_prediction_points',
				'config_value'	=> '1',
			),
			array(
				'config_name'	=> 'logo_path',
				'config_value'	=> 'images/sp_logos',
			),
			array(
				'config_name'	=> 'logo_max_width',
				'config_value'	=> '600',
			),
			array(
				'config_name'	=> 'logo_max_height',
				'config_value'	=> '600',
			),
			array(
				'config_name'	=> 'logo_max_width_tn',
				'config_value'	=> '50',
			),
			array(
				'config_name'	=> 'logo_max_height_tn',
				'config_value'	=> '50',
			),
			array(
				'config_name'	=> 'default_league',
				'config_value'	=> '1',
			),
			array(
				'config_name'	=> 'weekly_bonus_points',
				'config_value'	=> '5',
			),
		);

		// Insert sample rule data
		$this->db->sql_multi_insert($this->table_prefix . 'sp_config', $default_sp_data);
	}
}
