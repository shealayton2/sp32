<?php

namespace jslayton\sportspredictions\migrations;

class install_acp_module extends \phpbb\db\migration\migration
{
    /**
     * If our config variable already exists in the db
     * skip this migration.
     */
    public function effectively_installed()
    {
        return isset($this->config['jslayton_sportspredictions_installed']);
    }

    /**
     * This migration depends on phpBB's v314 migration
     * already being installed.
     */
    static public function depends_on()
    {
        return array('\phpbb\db\migration\data\v31x\v314');
    }

    public function update_data()
    {
        return array(

            // Add the config variable we want to be able to set
            array('config.add', array('jslayton_sportspredictions_installed', 0)),

            // Add a parent module (ACP_DEMO_TITLE) to the Extensions tab (ACP_CAT_DOT_MODS)
            array('module.add', array(
                'acp',
                'ACP_CAT_DOT_MODS',
                'ACP_SPORTS_PREDICTIONS'
            )),

            // Add our main_module to the parent module (ACP_DEMO_TITLE)
            array('module.add', array(
                'acp',
                'ACP_SPORTS_PREDICTIONS',
                array(
                    'module_basename'       => '\jslayton\sportspredictions\acp\main_module',
                    'modes'                 => array('overview', 'configuration', 'leagues', 'teams', 'games', 'scores', 'predictions'),
                ),
            )),
        );
    }
}
