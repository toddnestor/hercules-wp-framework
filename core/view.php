<?php

class HercView extends HercAbstract
{
    function __construct()
    {
        $this->template   = !property_exists( $this, 'template' ) || empty( $this->template ) ? 'template.php' : $this->template;
        $this->name       = !property_exists( $this, 'name' ) || empty( $this->name ) ? '' : $this->name;
        $this->menu_name  = !property_exists( $this, 'menu_name' ) || empty( $this->menu_name ) ? $this->name : $this->menu_name;
        $this->class_name = !property_exists( $this, 'class_name' ) || empty( $this->class_name ) ? __CLASS__ : $this->class_name;
        $this->model      = !property_exists( $this, 'model' ) || empty( $this->model ) ? $this->CurrentSlug() : $this->model;

        add_action( 'wp_enqueue_scripts', array( $this, 'RegisterAllScripts' ) );
    }

    function Render( $data = array(), $return = false )
    {
        if( !is_bool( $return ) )
            $return = false;

        if( !empty( $data ) )
        {
            if( is_object( $data ) && property_exists( $data, 'post_title' ) && property_exists( $data, 'ID' ) )
                $meta_data = $this->Model( $this->CurrentSlug() )->GetMeta( $data->ID );

            if( !array( $meta_data ) )
                $meta_data = array( $meta_data );

            if( empty( $meta_data ) )
                $meta_data = array();

            $this->data = array_merge( $this->data, $meta_data );
        }

        if( file_exists( $this->directory . DIRECTORY_SEPARATOR . $this->template ) )
        {
            $template = file_get_contents( $this->directory . DIRECTORY_SEPARATOR . $this->template );

            $template = $this->Helper( 'handlebars' )
                ->Render( $template, ( !empty( $this->data ) ? $this->data : array() ) );

            $template = preg_replace_callback(
                '`name="([^"]*)"`',
                array( $this, 'AddClassNameToPostNames' ),
                $template
            );

            if( !$return )
                echo $template;
            else
                return $template;
        }
    }

    function AddClassNameToPostNames( $matches )
    {
        if( $this->Model( $this->CurrentSlug() ) )
            return 'name="' . $this->Model( $this->CurrentSlug() )->class_name . '[' . $matches[ 1 ] . ']"';
        else
            return 'name="' . $this->class_name . '[' . $matches[ 1 ] . ']"';
    }

    function EnqueueScript( $handle = '', $style, $dependencies = array() )
    {
        wp_enqueue_script( ( empty( $handle ) ? __CLASS__ . '_' . sanitize_title( $style ) : $handle ), $this->GetUrl( $style ), $dependencies );
    }

    function RegisterScript( $style, $handle = '', $dependencies = array() )
    {
        wp_register_script( $style, $handle, $dependencies );
    }

    function RegisterAllScripts()
    {
        if( is_dir( $this->directory . DIRECTORY_SEPARATOR . 'js' ) )
            $scripts = scandir( $this->directory . DIRECTORY_SEPARATOR . 'js' );

        if( !empty( $scripts ) )
        {
            foreach( $scripts as $key=>$val )
            {
                if( $val == '.' || $val == '..' )
                    continue;

                if( strpos( $val, '.js' ) !== false )
                    $this->RegisterScript( 'herc-' . str_replace('.js','',$val), $this->GetUrl( str_replace( (property_exists( $this, 'plugin_directory' ) ? $this->plugin_directory : dirname( dirname( dirname( __FILE__ ) ) ) ) . DIRECTORY_SEPARATOR, '', $this->directory . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . $val ) ) );
            }
        }
    }

    function RegisterAllStyles()
    {

    }

    function EnqueueStyleSheet( $style, $handle = '' )
    {
        wp_enqueue_style( ( empty( $handle ) ? __CLASS__ . '_' . sanitize_title( $style ) : $handle ), $this->GetUrl( $style ) );
    }

    function RegisterStyleSheet( $style, $handle = '' )
    {
        wp_register_style( $handle, $style, array() );
    }

    function EnqueueBootstrap()
    {
        $this->EnqueueStyleSheet( 'assets/css/bootstrap.css', sanitize_title( $this->GetPluginFolderName() . '_bootstrap' ) );
    }

    function IncludeBootstrap()
    {
        add_action( 'wp_enqueue_scripts', array( $this, 'EnqueueBootstrap' ) );
        add_action( 'admin_print_styles', array( $this, 'EnqueueBootstrap' ) );
    }

    function RegisterMetaboxes()
    {
        $this->data[ 'class_name' ] = $this->Model( $this->CurrentSlug() )->class_name;

        foreach( $this->metabox_positions as $key => $val )
        {
            if( !empty( $val[ 'post_type' ] ) )
            {
                if( empty( $val[ 'position' ] ) )
                    $val[ 'position' ] = 'normal';
                if( empty( $val[ 'priority' ] ) )
                    $val[ 'position' ] = 'default';

                add_meta_box( 'metabox_' . $this->class_name, $this->name, array( $this, 'Render' ), $val[ 'post_type' ], $val[ 'position' ], $val[ 'priority' ] );
            }
        }
    }

    function PostFilter( $content )
    {
        if( method_exists( $this, 'GenerateData' ) && ( !property_exists( $this, 'posts_data_generated' ) || $this->posts_data_generated != true ) )
            $this->GenerateData();

        $html = $this->Render( array(), true );

        if( $this->location == 'before' )
            return $html . $content;
        else
            return $content . $html;
    }

    function GenerateData()
    {
        if( is_object( $this->Model( $this->model ) ) )
        {
            global $post;

            if( !property_exists( $this, 'data' ) )
                $this->data = array();

            if( !is_array( $this->data ) )
                $this->data = array( $this->data );

            if( !empty( $post ) && is_object( $post ) && property_exists( $post, 'ID' ) )
            {
                $meta_data = $this->Model( 'post-settings' )->GetMeta( $post->ID );

                if( !is_array( $meta_data ) )
                    $meta_data = array( $meta_data );

                if( empty( $meta_data ) )
                    $meta_data = array();

                $this->data = array_merge( $this->data, $meta_data );
            }
        }

        $this->posts_data_generated = true;
    }

    function AddOptionsPage()
    {
        add_options_page(
            $this->name,
            $this->menu_name,
            ( property_exists( $this, 'capability' ) && !empty( $this->capability ) ? $this->capability : 'manage_options' ),
            $this->class_name, array( $this, 'Render' )
        );
    }

    function AddPostColumns( $columns )
    {
        $new_columns = $this->PostsColumns();

        return array_merge( $columns, $new_columns );
    }

    function PostColumnValues( $colname, $post_id )
    {
        $meta_data = $this->Model( $this->CurrentSlug() )->GetMeta( $post_id );

        $custom_columns = $this->PostsColumns();

        if( !empty( $custom_columns[ $colname ] ) )
        {
            $method_name = $this->UpperCamelCaseIt( $colname ) . 'Filter';

            if( method_exists( $this, $method_name ) )
                if( !empty( $meta_data[ $colname ] ) )
                    echo $this->$method_name( $meta_data[ $colname ] );
                else
                    echo $this->$method_name( '' );
            else
                echo $meta_data[ $colname ];
        }
    }

    public function RegisterShortcode( $attributes = array() )
    {
        return $this->Render( $attributes, true );
    }

    function Initialize()
    {
        if( $this->type == 'metabox' && !empty( $this->metabox_positions ) )
            add_action( 'add_meta_boxes', array( $this, 'RegisterMetaboxes' ) );
        elseif( $this->type == 'post-add-on' )
            add_filter( 'the_content', array( $this, 'PostFilter' ) );
        elseif( $this->type == 'admin_menu' )
            add_action( 'admin_menu', array( $this, 'Menu' ) );
        elseif( $this->type == 'options_page' )
            add_action( 'admin_menu', array( $this, 'AddOptionsPage' ) );
        elseif( $this->type == 'shortcode' )
            add_shortcode( property_exists( $this, 'shortcode' ) ? $this->shortcode : $this->class_name, array( $this, 'RegisterShortcode' ) );

        if( method_exists( $this, 'PostsColumns' ) )
        {
            if( property_exists( $this, 'post_type' )  && !empty( $this->post_type ) )
                $post_type = $this->post_type;
            else
                $post_type = 'post';

            add_filter('manage_edit-' . $post_type . '_columns', array($this, 'AddPostColumns'));
            add_action('manage_' . $post_type . 's_custom_column', array($this, 'PostColumnValues'), 10, 2);
        }
    }
}