<?php

require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'base.php' );

class CreateHercUsersTable extends BaseMigration
{
    function __construct()
    {
        $this->table_name = 'herc_users';
        parent::__construct();
    }

    function Migration($table)
    {
        $table->increments('id');
        $table->string('name', 255);
        $table->string('access_token', 64);
        $table->integer('herc_user_id');
        $table->timestamps();
        $table->softDeletes();
    }
}