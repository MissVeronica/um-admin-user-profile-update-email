<?php

//  Version 2.0 2022-09-20
//  An email template for sending an email to the site admin when an UM User Profile is updated
//  Source: https://github.com/MissVeronica/UM-Admin-User-Profile-Update-Email

add_filter( 'um_email_notifications', 'custom_email_notifications_profile_is_updated', 10, 1 );
add_action( 'um_user_after_updating_profile', 'custom_profile_is_updated_email', 10, 3 );
add_action( 'profile_update', 'custom_profile_is_updated_email_backend', 10, 3 );
add_filter( 'um_admin_settings_email_section_fields', 'um_admin_settings_email_section_fields_custom_forms', 10, 2 );

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

function um_admin_settings_email_section_fields_custom_forms( $section_fields, $email_key ) {

    if( $email_key == 'profile_is_updated_email' ) {
        $section_fields[] = array(
                'id'            => $email_key . '_custom_forms',
                'type'          => 'text',
                'label'         => __( 'Include these UM Profile Forms for sending emails:', 'ultimate-member' ),
                'conditional'   => array( $email_key . '_on', '=', 1 ),
                'tooltip'       => __( 'Comma separated UM Profile Form IDs, empty send emails always.', 'ultimate-member' )
                );
    }
    return $section_fields;
}

function custom_profile_is_updated_email_backend( $user_id, $old_data, $user_data ) {

    if( isset( $_REQUEST['action']) && $_REQUEST['action'] == 'update' ) {
        custom_profile_is_updated_email( $user_data, $user_id );
    }
}

function custom_profile_is_updated_email( $to_update, $user_id, $args = array() ) {

    global $current_user;

    $forms = UM()->options()->get( 'profile_is_updated_email_custom_forms' );

    if( !empty( $forms )) {
        $forms = explode( ',', $forms ); 
        if( is_array( $forms ) && !in_array( $args['form_id'], $forms )) return;
    }

    $submitted = um_user( 'submitted' );    
    foreach( $to_update as $key => $value ) {
        $submitted[$key] = $value;
    }

    $registration_form_id = $submitted['form_id'];
    $registration_timestamp = um_user( 'timestamp' );
    $submitted['form_id'] = $args['form_id'];
    
    update_user_meta( $user_id, 'submitted', $submitted );
    update_user_meta( $user_id, 'timestamp', current_time( 'timestamp' ) );
    UM()->user()->remove_cache( $user_id );
    um_fetch_user( $user_id );

    $time_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
    um_fetch_user( $user_id );
    
    $args['tags'] = array(  '{profile_url}',
                            '{current_date}',
                            '{updating_user}' );

    $args['tags_replace'] = array(  um_user_profile_url( $user_id ), 
                                    date_i18n( $time_format, current_time( 'timestamp' )), 
                                    $current_user->user_login );

    UM()->mail()->send( get_bloginfo( 'admin_email' ), 'profile_is_updated_email', $args );

    $submitted['form_id'] = $registration_form_id;

    update_user_meta( $user_id, 'submitted', $submitted );
    update_user_meta( $user_id, 'timestamp', $registration_timestamp );
    UM()->user()->remove_cache( $user_id );
    um_fetch_user( $user_id );
}
