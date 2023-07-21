# UM Admin User Profile Update Email
An email template for sending an email to the site admin when an UM User Profile is updated either by the User or a Site Admin both from WP User -> Edit and the UM Profile Form.

## UM Settings
1. UM Settings -> Email -> Profile is updated email -> Include these UM Profile Forms for sending emails - Comma separated UM Profile Form IDs, empty send emails always.
2. Customize the email template if required.
3. Email placeholders: {profile_url}, {current_date}, {updating_user}

## Installation
1. Install by downloading the plugin ZIP file and install as a new Plugin, which you upload in WordPress -> Plugins -> Add New -> Upload Plugin.
2. Activate the Plugin: Ultimate Member - Admin Email Profile Update
3. Upload the email template file profile_is_updated_email.php to your theme or child-theme directory: .../ultimate-member/email/profile_is_updated_email.php
