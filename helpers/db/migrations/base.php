<?php

use Illuminate\Database\Capsule\Manager as Capsule;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use WeDevs\ORM\Eloquent\Database;

class BaseMigration
{
    function __construct()
    {
        require_once( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php' );
        $this->Migrate();
    }

    function Migrate()
    {
        global $wpdb;

        $this->table_name = $wpdb->prefix . $this->table_name;

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

        $migration = $this;

        if( method_exists( $this, 'Migration' ) )
        {
            Capsule::schema()->create($this->table_name, function (Blueprint $table) use ($migration)
            {
                $migration->Migration($table);
            });
        }
        elseif( method_exists( $this, 'Alteration' ) )
        {
            Capsule::schema()->table($this->table_name, function (Blueprint $table) use ($migration){
                $migration->Alteration($table);
            });
        }
    }


}
