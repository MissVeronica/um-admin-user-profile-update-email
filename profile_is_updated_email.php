<?php
/**
 * Template for the "Admin Email Profile Update".
 * Send the Admin an email when a User updated their profile.
 *
 * @version 2.6.8
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div style="max-width: 560px;padding: 20px;background: #ffffff;border-radius: 5px;margin: 40px auto;font-family: Open Sans,Helvetica,Arial;font-size: 15px;color: #666">

    <div style="color: #444444;font-weight: normal">

        <div style="text-align: center;font-weight: 600;font-size: 26px;padding: 10px 0;border-bottom: solid 3px #eeeeee">{site_name}</div>
        <div style="clear: both"> </div>

    </div>

    <div style="padding: 0 30px 30px 30px;border-bottom: 3px solid #eeeeee">

        <div style="padding: 30px 0;font-size: 24px;text-align: center;line-height: 40px">The profile text for {username} <br />has been updated.</div>
        <div style="padding: 0 0 15px 0">

            <div style="background: #eee;color: #444;padding: 12px 15px;border-radius: 3px;font-weight: bold;font-size: 16px">Account Information</div>

            <div style="padding: 10px 15px 0 15px;color: #333"><span style="color: #999">Account e-mail:</span> <span style="font-weight: bold">{email}</span></div>
            <div style="padding: 10px 15px 0 15px;color: #333"><span style="color: #999">Account username:</span> <span style="font-weight: bold"><span style="font-weight: bold">{display_name}</span></span></div>
            <div style="padding: 10px 15px 0 15px;color: #333"><span style="color: #999">User profile </span><a href="{profile_url}" target="_blank" rel="noopener">link</a></div>
            <div style="padding: 10px 15px 0 15px;color: #333"><span style="color: #999">Updated by: </span><span style="font-weight: bold">{updating_user}</span></div>

        </div>

        <div style="padding: 0 0 15px 0">
            <div style="background: #eee;color: #444;padding: 12px 15px;border-radius: 3px;font-weight: bold;font-size: 16px">The updated profile form {current_date}:<br /><br />{submitted_registration}</div>
        </div>

    </div>

    <div style="color: #999;padding: 20px 30px">

        <div>Thank you</div>
        <div>The <a style="color: #3ba1da;text-decoration: none" href="{site_url}">{site_name}</a> Team</div>

    </div>

</div>
