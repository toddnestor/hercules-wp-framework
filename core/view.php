<?php

class HercView extends HercAbstract
{
    function __construct()
    {
        $this->template = !property_exists( $this, 'template' ) || empty( $this->template ) ? 'template.php' : $this->template;
        $this->name = !property_exists( $this, 'name' ) || empty( $this->name ) ? '' : $this->name;
        $this->class_name = !property_exists( $this, 'class_name' ) || empty( $this->class_name ) ? __CLASS__ : $this->class_name;
        $this->model = !property_exists( $this, 'model' ) || empty( $this->model ) ? $this->CurrentSlug() : $this->model;
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

            $template = $this->Helper( 'handlebars' )->Render( $template, ( !empty( $this->data ) ? $this->data : array() ) );

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
        return 'name="' . $this->Model( $this->CurrentSlug() )->class_name . '[' . $matches[1] . ']"';
    }

    function EnqueueScript( $script )
    {

    }

    function EnqueueStyleSheet( $style, $handle = '' )
    {
        wp_enqueue_style( ( empty( $handle ) ? __CLASS__ . '_' . sanitize_title( $style ) : $handle ), $this->GetUrl( $style ) );
    }

    function EnqueueBootstrap()
    {
        $this->EnqueueStyleSheet( 'framework/assets/css/bootstrap.css', sanitize_title( $this->GetPluginFolderName() . '_bootstrap' ) );
    }

    function IncludeBootstrap()
    {
        add_action( 'wp_enqueue_scripts', array( $this, 'EnqueueBootstrap' ) );
        add_action( 'admin_print_styles', array( $this, 'EnqueueBootstrap' ) );
    }

    function RegisterMetaboxes()
    {
        $this->data['class_name'] = $this->Model( $this->CurrentSlug() )->class_name;

        foreach( $this->metabox_positions as $key=>$val )
        {
            if( !empty( $val['post_type'] ) )
            {
                if( empty( $val['position'] ) )
                    $val['position'] = 'normal';
                if( empty( $val['priority'] ) )
                    $val['position'] = 'default';

                add_meta_box( 'metabox_' . __CLASS__, $this->name, array( $this, 'Render' ), $val['post_type'], $val['position'], $val['priority'] );
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

            if ( !empty( $post ) && is_object( $post ) && property_exists( $post, 'ID' ) )
            {
                $meta_data = $this->Model('post-settings')->GetMeta($post->ID);

                if( !is_array( $meta_data ) )
                    $meta_data = array( $meta_data );

                if( empty( $meta_data ) )
                    $meta_data = array();

                $this->data = array_merge($this->data, $meta_data );
            }
        }

        $this->posts_data_generated = true;
    }

    function Initialize()
    {
        if( $this->type == 'metabox' && !empty( $this->metabox_positions ) )
            add_action( 'add_meta_boxes', array( $this, 'RegisterMetaboxes' ) );
        elseif( $this->type == 'post-add-on' )
            add_filter( 'the_content', array( $this, 'PostFilter' ) );
    }
}