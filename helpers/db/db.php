<?php
/**
 * Created by PhpStorm.
 * User: Todd
 * Date: 7/22/2015
 * Time: 12:14 PM
 */
use Illuminate\Database\Capsule\Manager as Capsule;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use WeDevs\ORM\Eloquent\Database;

class HercHelper_Db extends HercHelper
{
    function __construct()
    {
        require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php' );

//        $this->db = \WeDevs\ORM\Eloquent\Database::instance();

        $this->CreateMigrationsTable();
    }

    function CreateMigrationsTable()
    {
        if( get_option('herc_migration_table_created' ) != true )
        {
            $capsule = new Capsule;

            $capsule->addConnection(array(
                'driver'    => 'mysql',
                'host'      => DB_HOST,
                'database'  => DB_NAME,
                'username'  => DB_USER,
                'password'  => DB_PASSWORD,
                'charset'   => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix'    => '',
            ));

            $capsule->setAsGlobal();

            $capsule->bootEloquent();

            Capsule::schema()->create('migrations', function (Blueprint $table) {
                $table->string('migration', 255);
                $table->bigInteger('batch');
            });

            update_option('herc_migration_table_created', true );
        }
    }


}
