<?php

require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'base.php' );

class AddUserData extends BaseMigration
{
    function __construct()
    {
        $this->table_name = 'herc_users';
        parent::__construct();
    }

    function Alteration($table)
    {
        $table->text('user_data');
    }
}