<?php

//  Version 1.2 2022-05-04
//  Source: https://github.com/MissVeronica/UM-Admin-User-Profile-Update-Email

add_filter( 'um_email_notifications', 'custom_email_notifications_profile_is_updated', 10, 1 );
add_action( 'um_user_after_updating_profile', 'custom_profile_is_updated_email', 10, 3 );
add_action( 'profile_update', 'custom_profile_is_updated_email_backend', 10, 2 );

function custom_email_notifications_profile_is_updated( $emails ) {

    $custom_emails = array(
                'profile_is_updated_email' => array(
                'key'			 => 'profile_is_updated_email',
                'title'			 => __( 'Profile is updated email', 'ultimate-member' ),
                'subject'		 => 'Profile Update {username}',
                'body'			 => '',
                'description'	 => __( 'To send an email to the site admin when a user profile is updated', 'ultimate-member' ),
                'recipient'		 => 'admin',
                'default_active' => true ));

    UM()->options()->options = array_merge( array(  'profile_is_updated_email_on'  => 1,
                                                    'profile_is_updated_email_sub' => 'Profile Update {username}', ), 
                                            UM()->options()->options );

    return array_merge( $custom_emails, $emails );
}

function custom_profile_is_updated_email_backend( $user_id, $old_data ) {

    if( isset( $_REQUEST['action']) && $_REQUEST['action'] == 'update' ) {
        custom_profile_is_updated_email( $old_data, $user_id );
    }
}

function custom_profile_is_updated_email( $to_update, $user_id, $args = array() ) {

    global $current_user;

    $time_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
    um_fetch_user( $user_id );
    
    $args['tags'] = array(  '{profile_url}', 
                            '{current_date}', 
                            '{updating_user}' );

    $args['tags_replace'] = array(  um_user_profile_url( $user_id ), 
                                    date_i18n( $time_format, current_time( 'timestamp' )), 
                                    $current_user->user_login );

    UM()->mail()->send( get_bloginfo( 'admin_email' ), 'profile_is_updated_email', $args );
}



