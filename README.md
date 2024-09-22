# UM Admin User Profile Update Email
An email template for sending an email to the site admin when an UM User Profile is updated either by the User or a Site Admin both from WP User -> Edit and the UM Profile Form.

Profile fields updated via the UM Account page's existing or optional tabs are not supported by this plugin and will not send an Admin email.

## UM Settings -> Email -> Profile is updated email 
1. Profile is updated email -> Include these UM Profile Forms - Multiple selection of UM Profile Forms for admin email when profile is updated by the User, none selected send emails always.
2. Admin Email Profile Update - Backend UM Profile "Form" - Select UM Profile "Form" for mapping of backend submitted WP fields. No selection disable backend emails.
3. Admin Email Profile Update - CC: email to User - Click for a CC: email to the profile owner address.
4. Admin Email Profile Update - Admin User email - Select UM or WP Admin User email address.
5. Customize the email template "Profile is updated email" if required.
6. Extra Email placeholders: {profile_url}, {current_date}, {updating_user}

## Updates
1. Version 3.0.0 Replaced the code snippet version 2 with a plugin
2. Version 3.1.0 Update which auto installs the email template
3. Version 3.2.0 Improved installation of the email template
4. Version 3.3.0 Bug fix update from Backend
5. Version 3.4.0 Update of editing the template file
6. Version 3.5.0 Update of PHP 8 issue with STYLESHEETPATH
7. Version 4.0.0 Redesign of Backend WP profile updates. You can build an UM Form being used only for mapping of backend submitted WP fields for the email template.
8. Version 4.1.0 Multiple selections of "Include these UM Profile Forms" instead of comma separated. Disable backend emails with no "Form" selected.
9. Version 4.4.0 Fix for empty form ID. Trace to debug.log.
10. Version 4.5.0 Removed email conflict and updated for UM 2.8.3
11. Version 4.6.0 Updated and tested both for UM 2.8.4 and UM 2.8.5
12. Version 4.7.0 Addition of option for CC: email to User.
13. Version 4.7.1 Select UM or WP Admin User email address.
14. Version 4.7.2 Code improvement
15. Version 4.7.3 Code improvements incl GitHub links and Plugin settings link

## Installation
1. Install by downloading the plugin ZIP file ( Green button ) and install as a new Plugin, which you upload in WordPress -> Plugins -> Add New -> Upload Plugin.
2. Activate the Plugin: Ultimate Member - Admin Email Profile Update
3. Email Template autoinstalls when you go to UM Settings Email

