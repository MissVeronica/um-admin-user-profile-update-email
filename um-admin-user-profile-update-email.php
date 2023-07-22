<?php
/**
 * Plugin Name:     Ultimate Member - Admin Email Profile Update
 * Description:     Extension to Ultimate Member with an email template for sending an email to the site admin when an UM User Profile is updated.
 * Version:         3.1.0
 * Requires PHP:    7.4
 * Author:          Miss Veronica
 * License:         GPL v2 or later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Author URI:      https://github.com/MissVeronica
 * Text Domain:     ultimate-member
 * Domain Path:     /languages
 * UM version:      2.6.8
 */

if ( ! defined( 'ABSPATH' ) ) exit; 
if ( ! class_exists( 'UM' ) ) return;


Class UM_Admin_Email_Profile_Update {

    public $slug = array();

    function __construct() {

        add_filter( 'um_email_notifications',                 array( $this, 'custom_email_notifications_profile_is_updated' ), 10, 1 );
        add_action( 'um_user_after_updating_profile',         array( $this, 'custom_profile_is_updated_email' ), 10, 3 );
        add_action( 'profile_update',                         array( $this, 'custom_profile_is_updated_email_backend' ), 10, 3 );
        add_filter( 'um_admin_settings_email_section_fields', array( $this, 'um_admin_settings_email_section_fields_custom_forms' ), 10, 2 );
        add_action( 'um_extend_admin_menu',                   array( $this, 'copy_email_notifications_admin_profile_update' ), 10 );

        define( 'Admin_Email_Profile_Update_Path', plugin_dir_path( __FILE__ ) );
    }

    public function custom_email_notifications_profile_is_updated( $emails ) {

        $custom_emails = array(
                    'profile_is_updated_email' => array(
                            'key'            => 'profile_is_updated_email',
                            'title'          => __( 'Profile is updated email', 'ultimate-member' ),
                            'subject'        => 'Profile Update {username}',
                            'body'           => '',
                            'description'    => __( 'To send an email to the site admin when a user profile is updated', 'ultimate-member' ),
                            'recipient'      => 'admin',
                            'default_active' => true ));

        UM()->options()->options = array_merge( array(  'profile_is_updated_email_on'  => 1,
                                                        'profile_is_updated_email_sub' => 'Profile Update {username}', ), 
                                                UM()->options()->options );

        foreach ( $custom_emails as $slug => $custom_email ) {

            if ( ! array_key_exists( $slug . '_on', UM()->options()->options ) ) {

                UM()->options()->options[ $slug . '_on' ]  = empty( $custom_email['default_active'] ) ? 0 : 1;
                UM()->options()->options[ $slug . '_sub' ] = $custom_email['subject'];
            }

            $this->slug[] = $slug;
        }

        return array_merge( $custom_emails, $emails );
    }

    public function um_admin_settings_email_section_fields_custom_forms( $section_fields, $email_key ) {

        if ( $email_key == 'profile_is_updated_email' ) {

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

    public function copy_email_notifications_admin_profile_update() {

        foreach( $this->slug as $slug ) {

            $located = UM()->mail()->locate_template( $slug );

            if ( ! is_file( $located ) || filesize( $located ) == 0 ) {
                $located = wp_normalize_path( get_stylesheet_directory() . '/ultimate-member/email/' . $slug . '.php' );
            }

            clearstatcache();
            if ( ! file_exists( $located ) ) {

                wp_mkdir_p( dirname( $located ) );

                $email_source = file_get_contents( Admin_Email_Profile_Update_Path . $slug . '.php' );
                file_put_contents( $located, $email_source );
            }
        }
    }

    public function custom_profile_is_updated_email_backend( $user_id, $old_data, $user_data ) {

        if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'update' ) {
            custom_profile_is_updated_email( $user_data, $user_id );
        }
    }

    public function custom_profile_is_updated_email( $to_update, $user_id, $args = array() ) {

        global $current_user;

        $forms = UM()->options()->get( 'profile_is_updated_email_custom_forms' );

        if ( ! empty( $forms )) {
            $forms = array_map( 'trim', array_map( 'sanitize_text_field', explode( ',', $forms ))); 
            if ( is_array( $forms ) && ! in_array( $args['form_id'], $forms )) return;
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
}

new UM_Admin_Email_Profile_Update();
