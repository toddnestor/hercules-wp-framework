<?php
/**
 * Created by PhpStorm.
 * User: Todd
 * Date: 7/21/2015
 * Time: 2:29 PM
 */

class HercModel extends HercAbstract
{
    function __construct()
    {
        $this->class_name = empty( $this->class_name ) ? __CLASS__ : $this->class_name;
    }

    function RegisterPostMetaSave( $post_id )
    {
        if( !empty( $_POST[ $this->class_name ] ) )
            if( is_array( $_POST[ $this->class_name ] ) )
                update_post_meta( $post_id, $this->class_name, serialize( $_POST[ $this->class_name ] ) );
            else
                update_post_meta( $post_id, $this->class_name, $_POST[ $this->class_name ] );
    }

    function Initialize()
    {
        if( $this->View( $this->CurrentSlug() )->type == 'metabox' && !empty( $this->View( $this->CurrentSlug() )->metabox_positions ) )
            add_action( 'save_post', array( $this, 'RegisterPostMetaSave' ) );
    }

    function GetMeta( $post_id )
    {
        return maybe_unserialize( get_post_meta( $post_id, $this->class_name, true ) );
    }
}