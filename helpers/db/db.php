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

        $this->db = \WeDevs\ORM\Eloquent\Database::instance();

        $this->CreateMigrationsTable();
        $this->LoadModels();
        $this->CheckAndRunMigrations();
    }

    function LoadModels()
    {
        $models = scandir( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'models' );

        $files_to_skip = array(
            '.',
            '..'
        );

        foreach( $models as $key=>$val )
            if( !in_array( $val, $files_to_skip ) )
                require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . $val );
    }

    function CheckMigrations()
    {
        $migrations = array();

        $files_to_skip = array(
            'migration_template.php',
            'base.php',
            '.',
            '..'
        );

        $migrations_run = HercMigration::all();

        foreach( $migrations_run as $key=>$val )
            $files_to_skip[] = $val->migration . '.php';

        $files = scandir( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'migrations' );

        foreach( $files as $key=>$val )
            if( !in_array( $val, $files_to_skip ) )
                $migrations[] = $val;

        return $migrations;
    }

    function RunMigration( $migration )
    {
        require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR . $migration );

        $class_name = $this->GetClassName( $migration );

        $migration_object = new $class_name;

        $this->db->table('herc_migrations')->insert(array('migration' => str_replace( '.php', '', $migration ), 'batch' => 0));
    }

    function GetClassName( $migration )
    {
        $migration = substr( $migration, strpos( $migration, '_' ) + 1, strpos( $migration, '.' ) - strpos( $migration, '_' ) - 1 );
        return implode( '', explode( ' ', ucwords( str_replace( '_', ' ', $migration ) ) ) );
    }

    function CheckAndRunMigrations()
    {
        $migrations = $this->CheckMigrations();

        if( !empty( $migrations ) )
            foreach( $migrations as $key=>$val )
                $this->RunMigration( $val );
    }

    function CreateMigrationsTable()
    {
        if( get_option('herc_migration_table_created' ) != true )
        {
            global $wpdb;

            $table_name = $wpdb->prefix . 'migrations';

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

            Capsule::schema()->create($table_name, function (Blueprint $table) {
                $table->string('migration', 255);
                $table->bigInteger('batch');
            });

            update_option('herc_migration_table_created', true );
        }
    }


}
