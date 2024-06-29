<?php
/**
 * Plugin Name:     Ultimate Member - Admin Email Profile Update
 * Description:     Extension to Ultimate Member with an email template for sending an email to the site admin when an UM User Profile is updated either by the User or an Admin.
 * Version:         4.7.2
 * Requires PHP:    7.4
 * Author:          Miss Veronica
 * License:         GPL v2 or later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Author URI:      https://github.com/MissVeronica
 * Text Domain:     ultimate-member
 * Domain Path:     /languages
 * UM version:      2.8.5
 */

if ( ! defined( 'ABSPATH' ) ) exit; 
if ( ! class_exists( 'UM' ) ) return;


Class UM_Admin_Email_Profile_Update {

    public $slug            = array();
    public $backend_form_id = '';
    public $profile_email   = false;

    function __construct() {

        add_filter( 'um_email_notifications',                 array( $this, 'custom_email_notifications_profile_is_updated' ), 100, 1 );
        add_action( 'um_user_after_updating_profile',         array( $this, 'custom_profile_is_updated_email' ), 999, 3 );
        add_action( 'profile_update',                         array( $this, 'custom_profile_is_updated_email_backend' ), 999, 3 );
        add_filter( 'um_admin_settings_email_section_fields', array( $this, 'um_admin_settings_email_section_fields_custom_forms' ), 9, 2 );
        add_action(	'um_extend_admin_menu',                   array( $this, 'copy_email_notifications_admin_profile_update' ), 10 );
        add_filter( 'wp_mail',                                array( $this, 'add_email_profile_cc_wp_mail' ), 10, 1 );

        define( 'Admin_Email_Profile_Update_Path', plugin_dir_path( __FILE__ ) );
    }

    public function custom_email_notifications_profile_is_updated( $um_emails ) {

        $um_emails['profile_is_updated_email'] = array(
                                                'key'            => 'profile_is_updated_email',
                                                'title'          => __( 'Profile is updated email', 'ultimate-member' ),
                                                'subject'        => 'Profile Update {username}',
                                                'body'           => '',
                                                'description'    => __( 'To send an email to the site admin when a user profile is updated', 'ultimate-member' ),
                                                'recipient'      => 'admin',
                                                'default_active' => true 
                                            );

        if ( UM()->options()->get( 'profile_is_updated_email_on' ) === '' ) {
        //if ( ! array_key_exists( 'profile_is_updated_email_on', UM()->options()->options ) ) {

			$email_on = empty( $um_emails['profile_is_updated_email']['default_active'] ) ? 0 : 1;
			UM()->options()->update( 'profile_is_updated_email_on', $email_on );
            UM()->options()->update( 'profile_is_updated_email_sub', $um_emails['profile_is_updated_email']['subject'] );

            //UM()->options()->options['profile_is_updated_email_on']  = empty( $um_emails['profile_is_updated_email']['default_active'] ) ? 0 : 1;
            //UM()->options()->options['profile_is_updated_email_sub'] = $um_emails['profile_is_updated_email']['subject'];
        }

        return $um_emails;
    }

    public function add_email_profile_cc_wp_mail( $args ) {

        if ( ! empty( $this->profile_email )) {

            if ( is_array( $args['headers'] )) {
                $args['headers'][] = 'cc: ' . $this->profile_email;

            } else {

                $args['headers'] .= 'cc: ' . $this->profile_email . "\r\n";
            }
        }

        return $args ;
    }

    public function um_admin_settings_email_section_fields_custom_forms( $section_fields, $email_key ) {

        if ( $email_key == 'profile_is_updated_email' ) {

            $section_fields[] = array(
                    'id'            => $email_key . '_custom_forms',
                    'type'          => 'select',
                    'multi'         => true,
                    'label'         => __( 'Admin Email Profile Update - Include these UM Profile Forms', 'ultimate-member' ),
                    'options'       => $this->get_form_ids_profile(),
                    'conditional'   => array( $email_key . '_on', '=', 1 ),
                    'description'   => __( 'Multiple selection of UM Profile Forms for admin email when profile is updated by the User, none selected send emails always.', 'ultimate-member' )
                    );

            $section_fields[] = array(
                    'id'            => $email_key . '_backend_form',
                    'type'          => 'select',
                    'label'         => __( 'Admin Email Profile Update - Backend UM Profile "Form"', 'ultimate-member' ),
                    'options'       => $this->get_form_ids_profile(),
                    'conditional'   => array( $email_key . '_on', '=', 1 ),
                    'description'   => __( 'Select Profile "Form" for mapping of backend submitted WP fields. No selection disable backend emails.', 'ultimate-member' )
                    );

            $section_fields[] = array(
                    'id'            => $email_key . '_profile_cc',
                    'type'          => 'checkbox',
                    'label'         => __( 'Admin Email Profile Update - CC: email to User', 'ultimate-member' ),
                    'conditional'   => array( $email_key . '_on', '=', 1 ),
                    'description'   => __( 'Click for a CC: email to the profile owner address.', 'ultimate-member' )
                    );

            $section_fields[] = array(
                    'id'            => $email_key . '_admin_email',
                    'type'          => 'select',
                    'label'         => __( 'Admin Email Profile Update - Admin User email', 'ultimate-member' ),
                    'options'       => array(   'wp_admin_email' => 'WP: ' . get_bloginfo( 'admin_email' ),
                                                'um_admin_email' => 'UM: ' . um_admin_email(),
                                            ),
                    'conditional'   => array( $email_key . '_on', '=', 1 ),
                    'description'   => __( 'Select UM or WP Admin User email address.', 'ultimate-member' )
                    );

            $section_fields[] = array(
                    'id'            => $email_key . '_debug_log',
                    'type'          => 'checkbox',
                    'label'         => __( 'Admin Email Profile Update - Trace to debug.log', 'ultimate-member' ),
                    'conditional'   => array( $email_key . '_on', '=', 1 ),
                    'description'   => __( 'Click for trace of Form IDs to debug.log.', 'ultimate-member' )
                    );
        }

        return $section_fields;
    }

    public function copy_email_notifications_admin_profile_update() {

        $slug = 'profile_is_updated_email';

        $located = UM()->mail()->locate_template( $slug );
        if ( ! is_file( $located ) || filesize( $located ) == 0 ) {
            $located = wp_normalize_path( STYLESHEETPATH . '/ultimate-member/email/' . $slug . '.php' );
        }

        clearstatcache();
        if ( ! file_exists( $located ) || filesize( $located ) == 0 ) {

            wp_mkdir_p( dirname( $located ) );

            $email_source = file_get_contents( Admin_Email_Profile_Update_Path . $slug . '.php' );
            file_put_contents( $located, $email_source );

            if ( ! file_exists( $located ) ) {
                file_put_contents( um_path . 'templates/email/' . $slug . '.php', $email_source );
            }
        }
    }

    public function custom_profile_is_updated_email_backend( $user_id, $old_data, $user_data ) {

        if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'update' ) {

            if ( is_admin()) {

                remove_action( 'profile_update', array( $this, 'custom_profile_is_updated_email_backend' ), 999, 3 );
                $this->backend_form_id = sanitize_text_field( UM()->options()->get( 'profile_is_updated_email_backend_form' ));

                if ( ! empty( $this->backend_form_id )) {
                    $this->custom_profile_is_updated_email( $user_data, $user_id );
                }
            }
        }
    }

    public function custom_profile_is_updated_email( $to_update, $user_id, $args = array() ) {

        global $current_user;

        if ( ! empty( $user_id ) && is_numeric( $user_id )) {

            $forms = false;
            if ( isset( $args['form_id'] ) && ! empty( $args['form_id'] ) && is_numeric( $args['form_id'] )) {

                $forms = UM()->options()->get( 'profile_is_updated_email_custom_forms' );

                if ( ! empty( $forms ) && is_array( $forms )) {
                    $forms = array_map( 'sanitize_text_field', $forms );
                }
            }

            if ( empty( $forms ) || ( is_array( $forms ) && in_array( $args['form_id'], $forms ))) {

                um_fetch_user( $user_id );
                $submitted = um_user( 'submitted' );

                if ( empty( $submitted )) {
                    $submitted = array();
                    $submitted['form_id'] = '';
                }

                if ( is_array( $to_update )) {
                    foreach( $to_update as $key => $value ) {
                        $submitted[$key] = $value;
                    }
                }

                $registration_form_id = $submitted['form_id'];
                $registration_timestamp = um_user( 'timestamp' );

                $submitted['form_id'] = ( empty( $this->backend_form_id )) ? $args['form_id'] : $this->backend_form_id;
                $submitted['form_id'] = ( empty( $submitted['form_id'] ))  ? $registration_form_id : $submitted['form_id'];

                if ( ! empty( $submitted['form_id'] ) && is_numeric( $submitted['form_id'] )) {

                    update_user_meta( $user_id, 'submitted', $submitted );
                    update_user_meta( $user_id, 'timestamp', current_time( 'timestamp' ) );

                    UM()->user()->remove_cache( $user_id );
                    um_fetch_user( $user_id );

                    $time_format = get_option( 'date_format', 'F j, Y' ) . ' ' . get_option( 'time_format', 'g:i a' );

                    if ( UM()->options()->get( 'profile_is_updated_email_profile_cc' ) == 1 ) {
                        $this->profile_email = um_user( 'user_email' );
                    }

                    $admin_email = sanitize_text_field( UM()->options()->get( 'profile_is_updated_email_admin_email' ));

                    switch( $admin_email ) {
                        case 'um_admin_email':  $admin_email = um_admin_email(); break; 
                        case 'wp_admin_email':  $admin_email = get_bloginfo( 'admin_email' ); break;
                        default:                $admin_email = um_admin_email(); break;
                    }

                    $args['tags'] = array(  '{profile_url}',
                                            '{current_date}',
                                            '{updating_user}',
                                        );

                    $args['tags_replace'] = array(  um_user_profile_url( $user_id ), 
                                                    date_i18n( $time_format, current_time( 'timestamp' )),
                                                    $current_user->user_login,
                                                );

                    UM()->mail()->send( $admin_email, 'profile_is_updated_email', $args );

                    if ( ! empty( $registration_form_id )) {
                        $submitted['form_id'] = $registration_form_id;
                    }

                    update_user_meta( $user_id, 'submitted', $submitted );
                    update_user_meta( $user_id, 'timestamp', $registration_timestamp );

                    UM()->user()->remove_cache( $user_id );
                    um_fetch_user( $user_id );

                } else {

                    if ( UM()->options()->get( 'profile_is_updated_email_debug_log' ) == 1 ) {
                        $trace = date_i18n( 'Y-m-d H:i:s ', current_time( 'timestamp' )) . 'Admin Email Profile Update error trace for user_id ' . $user_id;
                        $trace .= ' current_user->ID: ' . $current_user->ID;
                        $trace .= ' Forms args: ' . $args['form_id'];
                        $trace .= ' backend: ' . $this->backend_form_id;
                        $trace .= ' registration: ' . $registration_form_id;
                        $trace .= ' submitted: ' . $submitted['form_id'];

                        if ( is_array( $forms )) {
                            $trace .= ' custom: ' . implode( ', ', $forms );
                        } else {
                            $trace .= ' custom: none';
                        }

                        file_put_contents( WP_CONTENT_DIR . '/debug.log', $trace . chr(13), FILE_APPEND  );
                    }
                }
            }
        }
    }

    public function get_form_ids_profile() {

        $um_form_ids = array( '' );
        $um_forms = get_posts( array( 'post_type' => 'um_form', 'numberposts' => -1, 'post_status' => array( 'publish' )));

        if ( ! empty( $um_forms )) {
            foreach ( $um_forms as $um_form ) {

                $um_form_meta = get_post_meta( $um_form->ID );
                if ( isset( $um_form_meta['_um_mode'][0] ) && $um_form_meta['_um_mode'][0] == 'profile' ) {

                    $um_form_ids[$um_form->ID] = $um_form->post_title;
                }
            }
        }
        return $um_form_ids;
    }
}

new UM_Admin_Email_Profile_Update();
