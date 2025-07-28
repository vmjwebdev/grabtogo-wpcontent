=== Frontend Admin by DynamiApps ===
Contributors: shabti, freemius
Tags: frontend editing, frontend posting, acf frontend, custom fields, custom dashboard
Requires at least: 4.6
Tested up to: 6.4.3
Stable tag: 3.24.8
Donate link: https://paypal.me/KaplanWebDev
Requires PHP: 5.6.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This awesome plugin allows you to easily display frontend forms on your site so your clients can easily edit content by themselves from the frontend.

== Description ==
Add and edit posts, pages, users, terms, ACF fields and more all from the frontend. 

(Previously called ACF Frontend)

This awesome plugin allows you to easily display frontend admin forms on your site so your clients can easily edit content by themselves from the frontend. You can create awesome forms with our form builder to allow users to save custom meta data to pages, posts, users, and more. Then use our Gutenberg block or shortcode to easily display these forms for your users.  

So, what can this plugin do for you?

== FREE Features ==

1. No Coding Required
Give the end user the best content managment experience without having to know code. It’s all ready to go right here. 

2. Display Post Data 
Use [frontend_admin field=field_key] to display any field value effortlessly

3. Edit Posts 
Let your users edit posts from the frontend of their site without having to access the WordPress dashboard. 

4. Add Posts 
Let your users publish new posts from the frontend using the “new post” form

5. Delete Posts 
Let your users delete or trash posts from the frontend using the “trash button” form

6. Edit User Profile
Allow users to edit their user data easily from the frontend.

7. User Registration Form
Allow new users to register to your site with a built in user registration form! You can even hide the WordPress dashboard from these new users.

8. Hide Admin Area 
Pick and chose which users have acess to the WordPress admin area.

9. Configure Permissions
Choose who sees your form based on user role or by specific users.

10. Modal Popup 
Display the form in a modal window that opens when clicking a button so that it won’t take up any space on your pages.


== PRO Features ==

1. Edit Global Options 
If you have global data – like header and footer data – you can create an options page using ACF and let your users edit from the frontend.

2. Limit Submits
Prevent all or specific users from submitting the form more than a number of times.

3. Send Emails 
Set emails to be sent and map the ACF form data to display in the email fields such as the email address, the from address, subject, and message. 

4. Style Tab
Use Elementor to style the form and as well the buttons. 

5. Multi Step Forms 
Make your forms more engaging by adding multiple steps.

6. Stripe and Paypal 
Accept payments through Stripe or Paypal upon form submission. 

7. Woocommerce Intergration 
Easily add Woocomerce products from the frontend.
 

Purchase your copy here at the official website: [Frontend Admin website](https://www.dynamiapps.com/)


== Useful Links ==
Appreciate what we're doing? Want to stay updated with new features? Give us a like and follow us on our facebook page: 
[Frontend Admin Facebook page](https://www.facebook.com/frontendadmin/)

The Pro version has even more cool features. Check it out at the official website:
[DynamiApps website](https://www.dynamiapps.com/)

Check out our other plugin, which let's you dynamically query your posts more easily: 
[Advanced Post Queries for Elementor](https://wordpress.org/plugins/advanced-post-queries/)


== Installation ==

1. Make sure both Advanced Custom Fields is installed and activated. 
2. Upload the plugin files to the `/wp-content/plugins/acf-frontend-form-elements` directory, or install the plugin through the WordPress plugins screen directly.
3. Activate the plugin through the 'Plugins' screen in WordPress
4. Create a form under Frontend Admin > forms.
5. Choose the desired form type. 
6. Configure the fields permisions, display, and other settings as you please.
7. Copy and paste the shortcode on any page. You can also use our Gutenberg block.
8. You should now see a form on the frontend for editing a post, adding a post, editing or adding a user, and more.


== Tutorials ==  

= Paul from WP Tuts shows how to use Frontend Admin to create a frontend dashboard =
https://www.youtube.com/watch?v=FsLSrd-11-g

= Enable Users To Add Content From The Front End Without Logging Into WordPress Using Frontend Admin =
https://www.youtube.com/watch?v=6yT2E2IV-JU

= WordPress Frontend Edits and Updates Using Frontend Admin =
https://www.youtube.com/watch?v=s6FeL77i2iM

= Installating Frontend Admin =
https://www.youtube.com/watch?v=Qio9iHzpMLo

= How to create a form for frontend data submission =
https://www.youtube.com/watch?v=7vrW8hx5jlE



== Frequently Asked Questions ==

= Can I send emails through this form? =

You can use a "action hook" to send emails when this form is registered: <a href="https://www.dynamiapps.com/frontend_admin/save_post/">frontend_admin/save_post</a>

If you purchase our pro version, you will be able to configure this from the form settings without any code. You will be able to send any number of emails upon form submission. 

= Can I let users set post categories through this form? =

Yes. Simply add a taxonomy field and set the taxonomy type to Category




== Changelog ==
= 3.24.8 - 12-11-2024 =
 * Fixed product inventory fields not saving

= 3.24.7 - 5-11-2024 =
 * Fixed "From Email" setting not saving
 * Fixed multi step validation
 * Added dynamic value checks to custom url redirect setting 

= 3.24.6 - 4-11-2024 =
 * Fixed multi step form not proceding to next step

= 3.24.5 - 3-11-2024 =
 * Fixed conflict with ACF update causing multi select fields to show no results
 * Added taxonomy field widget in Elementor

= 3.24.4 - 1-11-2024 =
 * Fixed ACF image fields adding suffix
 * Added "ACF Fields" button in form builder
 * Fixed "count error"
 * Added option to hide modal button if there is no post being edited

= 3.24.1 - 07-10-2024 =
 * Removed ACF dependancy. Still works seamlessly with ACF, but doesn't require it to be activated

= 3.23.9 - 26-09-2024 =
 * Fixed passwrod field not allowing very weak passwords despite the setting
 * Fixed password preview showing even when password field is hidden
 * Fixed Submissions not loading post fields data

= 3.23.8 - 24-09-2024 =
 * Added form_element to form response
 
 = 3.23.7 - 23-09-2024 =
 * Fixed gallery field layout

= 3.23.6 - 22-09-2024 =
 * Added encryption for extra security for inline edit fields
 * Added support for terms in loops

= 3.23.4 - 18-09-2024 =
 * Patched missing function
 
 = 3.23.3 - 18-09-2024 =
 * Added Frontend Admin metabox to enable "visibilty" limitation based on user role
 * Added compatibility for Elementor's element cache feature

= 3.23.2 - 17-09-2024 =
 * Improved ACF fields selections in the Elementor editor
 * Fixed delete button not showing up

= 3.23.1 - 10-09-2024 =
 * Fixed delete button not working within nested form
 * Fixed submissions table error

= 3.23.0 - 10-09-2024 =
 * Added Nested "New Taxonomy Form" and Nested "Edit Taxonomy Form
 * Added support for Elementor taxonomy loops
 * Fixed Image validation issue
 * Fixed delete button not redirecting

= 3.22.4 - 01-09-2024 =
 * Fixed edit permissions issue

= 3.22.3 - 15-08-2024 =
 * Fixed file upload error

= 3.22.2 - 14-08-2024 =
 * Fixed Google Map field not initializing
 * Added option to add password revealer

= 3.22.1 - 09-08-2024 =
 * Fixed field info not saving
 * Added option to add a preview via the 'frontend_admin/form/submission_preview' filter hook

= 3.22.0 - 12-07-2024 =
 * Added support for PHP 8.2
 * Added redirect options to submit buttons
 * Fixed edit term form
 * Fixed media library not working with latest WP version
 * Added proper filename sanitization 

= 3.21.12 - 27-06-2024 =
 * Fixed select2 fields (taxonomy, post author, select) not fetching options
 * Fixed Elementor Manage Product widgets not showing fields
 * Fixed 'special permissions' reference error

= 3.21.10 - 25-06-2024 =
 * Fixed browser autofill triggering form input change
 * Fixed default required message

= 3.21.9 - 23-06-2024 =
 * Fixed Elementor widget permissions
 * Added limit submissions to permissions rule

= 3.21.8 - 18-06-2024 =
 * Fixed "New post terms" option not working
 * Added option Special Permissions to allow users to edit other posts and users if checked

= 3.21.7 - 13-06-2024 =
 * Fixed product delete button not working when together with "acf form" widget
 * Fixed acf nested fields not validating
 * Added ability to add shortcodes to emails and to the message field

= 3.21.6 - 09-06-2024 =
 * Fixed delete button not working

= 3.21.5 - 07-06-2024 =
 * Fixed success message always showing in "fixed" position
 * Fixed form validation not calling acf/validate_save_post hook
 * Fixed email verification feature to allow non logged in users to post content by verifying email address
 * Added fea_emails db table for handling verified emails 

= 3.21.4 - 05-06-2024 =
 * Added option to add any string to the file input allowed attribute

= 3.21.3 - 03-06-2024 =
 * Fixed new posts not being creating upon submission approval
 * Fixed delete button missing dependencies when displayed alone
 * Fixed file field not accepting mp4

= 3.21.1 - 31-05-2024 =
 * Fixed submission approval not working
 * Added extra context to forms so that success messages will only show based on context if displayed in loop
 * Added option to click enter to go to the next field

= 3.21.0 - 28-05-2024 =
 * Added ajax submit feature to free version
 * Improved delete button html
 * Added logic to check if post exists before attempting to render edit post form and delete button 
 * Added logic to check if user exists before attempting to render edit user form and delete button 
 * Added option to customize redirect based on which submit button is triggered

= 3.20.16 - 24-05-2024 =
 * Added SQLlite support

= 3.20.15 - 23-05-2024 =
 * Fixed Post to Edit field loading form as modal within modal
 * Fixed Post to Edit field adding new posts to posts instead of CPT

= 3.20.14 - 22-05-2024 =
 * Optimized file uploads speed
 * Improved uploads progress bar
 * Added warnings when uploads exceed server upload limits
 * Added option to either add validation warning or client-side resize the images
 * Added "local avatar" option directly inside of image fields, simplifying the process of creating a local avatar field

= 3.20.12 - 17-05-2024 =
 * Fixed modal window in form builder
 * Fixed verifiy emails
 * Added email verified success message

= 3.20.11 - 15-05-2024 =
 * Fixed url query not working in Elementor built forms
 * Added duplicate post "copy title" option
 * Added duplicate post "copy date" option
 * Added "new post status" option for duplicate post

= 3.20.9 - 15-05-2024 =
 * Fixed approval forms not working
 * Fixed relationship field within repeater or group not using the correct form or field groups for inner forms
 * Fixed date picker fields display error

= 3.20.8 - 11-05-2024 =
 * Fixed WP library not working
 * Fixed post_to_edit showing form "post" on initial load 

= 3.20.7 - 10-05-2024 =
 * Fixed delete button not working
 * Fixed File field not opening WP media library

= 3.20.5 - 08-05-2024 =
 * Fixed show in modal not working in Admin Forms
 * Fixed field shortcodes not updating data

= 3.20.4 - 07-05-2024 =
 * Fixed missing block editor field

= 3.20.3 - 07-05-2024 =
 * Fixed "Upload Images" field mobile display
 * Fixed ios upload file error
 * Added Elementor "Images Field" widget
 * Fixed Nested forms basic upload not working
 * Added inline edit to fields widgets
 * Fixed date field shortcodes so that they display based on the return format

= 3.20.1 - 03-05-2024 =
 * Fixed Paypal not working
 * Change "form" hidden field from serialzed json to a form id

= 3.19.7 - 22-04-2024 =
 * Fixed post status in Elementor widget
 * Fixed update message in Elemntor widget

= 3.19.6 - 18-04-2024 =
 * Fixed Elementor widget permissions issues with logged out users
 * Fixed Elementor widget modal button showing up if permissions test failed
 * Fixed descrypt and encrypt functions
 * Fixed user custom fields not working in Elementor widgets

= 3.19.4 - 18-04-2024 =
 * Fixed post to edit field not loading post on initial load
 * Fixed iphone image upload issue
 * Fixed modal button icon

= 3.19.3 - 17-04-2024 =
 * Fixed product stock fields not showing
 * Fixed modal button not working in Elementor widgets
 * Added permissions controls to new nested form widgets

= 3.19.2 - 15-04-2024 =
 * Added new Elementor Frontend Form widgets: "Nested New post form" and "Nested Edit post form"
 * Added new Elemntor widgets: "Text Field", "Textarea Field", "Number Field", 
 * Added new Elementor post managment widgets: "Post Title", "Post Excerpt", "Featured Image", and "Post Content
 * Changed loader icon css class from 'acf-loading' to 'fea-loader'
 * Fixed Elementor widgets not loading properly in Elementor panel and search
 * Added helpful tutorials in readme and plugin settings page 

= 3.18.17 - 30-03-2024 =
 * Fixed dynamic tags not showing
 * Fixed dynamic tags not loading value in Edit Forms

= 3.18.16 - 29-03-2024 =
 * Fixed email adress settings not saving
 * Fixed verify email redirection issue

= 3.18.14 - 22-03-2024 =
 * Fixed Product forms not loading and updating data
 * Added success message below modal when form is loaded in modal
 * Fixed missing required setting for WP core fields

= 3.18.13 - 18-03-2024 =
 * Added Related Terms setting that allows to show terms as buttons
 * Added option to customize validation error message and default "required field" message
 * Moved validation to seperate PHP class

= 3.18.12 - 14-03-2024 =
 * Fixed "implode" error on edit post form
 * Added post type check to "post to edit" field

= 3.18.11 - 13-03-2024 =
 * Added default of "any" to new "Limit Post Type" setting 

= 3.18.10 - 13-03-2024 =
 * Fixed Webhooks to send json encoded data
 * Added a filter to webhooks to manipulate the data being sent
 * Added two options to "Post to Edit" setting: "User's First Post" and "User's Latest Post"
 * Added Url Query option to "Post to Edit" field
 * Added "Limit Post Type" setting to edit post form so that the form won't attempt to edit a post that doesn't have the chosen post type 

= 3.18.9 - 07-03-2024 =
 * Fixed current user edit form not loading data

= 3.18.8 - 28-02-2024 =
 * Fixed Upload Files field and made public
 * Reverted submission records to hold all data due to the change ruining code snippets that use the submission record. 
 (In the future, we will add this kind of change as an experiment)

= 3.18.7 - 20-02-2024 =
 * Fixed Submissions not displaying new objects' values

= 3.18.6 - 18-02-2024 =
 * New pro feature: countries and cities select fields
 * Tweeked submission records to only hold updated or new data. 
   If you need to access existing data inside of a code snippet, use WordPress functions like get_post_field
 * Fixed Try Pro message not disappearing when clicked

= 3.18.5 - 09-02-2024 =
 * New feature: indications of edits within submissions
 * New feature: "Post to Edit" field updates browser state when changed

= 3.18.4 - 19-01-2024 =
 * Added validation to file uploads 
 * Fixed frontend block editor field not loading

= 3.18.3 - 06-12-2023 =
 * Fixed emails not being verified when link clicked
 * Fixed submit limits error
 * Added default list of allowed files types to basic uploader 

= 3.18.2 - 20-08-2023 =
 * Fixed uploaded images not showing

= 3.18.0 - 17-08-2023 =
 * Fixed bug with frontend block editor
 * Fixed issue with invisble image uploads when admin approval is required
 * Fixed issue with "Add and Edit Posts" in relationship field

= 3.17.2 - 11-07-2023 =
 * Fixed delete buttons not redirecting properly
 * Fixed Elementor widget "ACF Fields" showing red button

= 3.17.1 - 30-06-2023 =
 * Fixed assets url error

= 3.17.0 - 29-06-2023 =
 * Added PDF generator for submissions
 * Fixed signature not showing

= 3.16.11 - 08-06-2023 =
 * Added pdf logic to core plugin

= 3.16.10 - 02-06-2023 =
 * Fixed payments not submitting form when "submit form" checked

= 3.16.9 - 12-05-2023 = 
 * Fixed issue with payments plugin

= 3.16.7 - 05-05-2023 = 
 * Fixed file name error

= 3.16.6 - 03-05-2023 = 
 * Fixed non sanitized data
 * Fixed attributes field
 * Fixed variations field not showing

= 3.16.5 - 28-04-2023 = 
 * Fixed Signature field error
 * Fixed issue with ACF fields select in Elemntor widgets

= 3.16.3 - 24-04-2023 = 
 * Fixed inability to edit fields when using ACF free version

= 3.16.2 - 23-04-2023 = 
 * Fixed multi steps not showing
 * Fixed ACF fields not updating and rendering values in Elementor Widgets
 * Fixed Delete Buttons

= 3.16.1 - 10-04-2023 = 
 * Added compatibility for ACF version 6.1
 * Fixed errors with fields generated by Elementor widget 

= 3.15.3 - 28-02-2023 =
 * Fixed license key error
 * Added missing block editor field build

= 3.15.2 - 27-02-2023 =
 * Added block editor field type
 * Added block editor appearance type in Post Content and Product Description field types
 * Fixed parse tags issue in Elementor widgets

= 3.15.1 - 27-02-2023 =
 * Fixed File field not showing non-image files
 * Fixed form submissions not showing forms created with Elementor
 * Fixed file based fields not working in repeater
 * Fixed form settings stripping out <[ and ]> 
 * Fixed pdf and zip uploads
 * Fixed custom directories not working if nested
 * Added feature that saves files in fea-submissions folder until form is submitted 
 * Added feature that hides attachments until form is submitted 
 
= 3.14.0 - 09-02-2023 =
 * Added option to save images in custom directories
 * Added option to secure custom directories

= 3.13.13 - 03-02-2023 =
 * Fixed conflict when using multiple "ACF Fields" in forms
 * Added field group shortcode: [frontend_admin group=63c4475ttytr edit=false]

= 3.13.12 - 02-02-2023 =
 * Fixed date picker not showing previous dates

= 3.13.11 - 31-01-2023 =
 * Fixed issue in Woo price field
 * Fixed Elementor deprecated functions
 * Fixed issue with Frontend settings form

= 3.13.10 - 30-01-2023 =
 * Fixed Wysiwyg bug in modal window
 * Fixed relationship field add/edit nested forms
 * Fixed escaped strings in step tabs
 * Fixed bug in import tool

= 3.13.9 - 25-01-2023 =
 * Added filter for form settings tabs
 * Fixed submission data unslashed

= 3.13.8 - 23-01-2023 =
 * Fixed submission view page
 * Fixed unslashed form data 
 * Fixed list items missing label options

= 3.13.7 - 22-01-2023 =
 * Fixed broken layouts in Elementor widgets
 * Fixed missing dynamic data dropdown
 * Fixed missing Display Mode setting in edit field UI
 * Updated Freemius SDK in Freemius version of Frontend Admin to 2.5.3

= 3.13.6 - 17-01-2023  =
 * Fixed email validation issue
 * Fixed file field not uploading with basic upload
 * Fixed tabs to steps feature
 * Added better genrated usernames when no username field is in the form
 * Added setting to allow users to edit their username

= 3.13.7 - 16-01-2023  =
 * Fixed Product to Edit field
 * Fixed missing license tab in pro
 * Fixed user fields not loading values
 * Fixed missing step tabs

= 3.13.3 - 15-01-2023  =
 * Added frontend_admin/form/after_submit hook
 * Fixed simple Date field and simple Time field displays
 * Fixed form showing in modal even in backend submission page
 * Fixed error when submitting forms with no uploads

= 3.13.2 - 12-01-2023  =
 * Fixed error with current_screen function
 * Fixed js error when adding fields to forms 
 * Fixed no permission to access submissions

= 3.13.1 - 11-01-2023  =
 * Adjusted the forms UI to match the new ACF field groups UI
 * Fixed nonce errors
 * Fixed Elementor widgets
 * Fixed clash with WP media library in block editor
 * Fixed forms and submissions blocks not loading form list

= 3.12.3 - 29-12-2022  =
 * Fixed errors
 * Added new php hooks

= 3.12.2 - 29-12-2022  =
 * Fixed missing plugin page

= 3.11.4 - 22-12-2022  =
 * Fixed error in edit button widget

= 3.11.3 - 20-12-2022  =
 * Added action hook after field render
 * Added actions error logging
 * Moved modal window to body

= 3.11.2 - 16-12-2022  =
 * Fixed Featured Image field appearing as File field
 * Added option to view submissions with url query ?submissions=1 ?submission={submission_id}
 * Fixed error with ACF location rules

= 3.11.1 - 14-12-2022  =
 * Added signature field
 * Added js-free time input field
 * Added js-free date input field
 * Added js-free datetime input field
 * Added "add post" option to post object field
 * Fixed submissions shortcode copy button
 * Restyled Submissions list
 * Added resize function to file uploads that automatically resizes large images  
 * Added validations and escapes

= 3.10.3 - 05-12-2022  =
 * Fixed issue with new post action

= 3.10.2 - 04-12-2022  =
 * Fixed issue with Webhooks action
 * Fixed all_fields shortcode in emails 
 * Fixed form using basic uploader on submissions page
 * Fixed missing New Post Form and Edit Post Form widgets in Elementor legacy version
 * Fixed conflict when free version and pro version are both activated
 * Added post argument to form shortcode

= 3.10.0 - 28-11-2022 =
 * Created Freemius-free pro version. If you want to switch, reach out to support

= 3.9.23 - 24-11-2022 =
 * Fixed add item button error

= 3.9.22 - 23-11-2022 =
 * Fixed form builder tabs not working
 
= 3.9.21 - 22-11-2022 =
 * Fixed field type settings not loading
 * Added option to display repeater shortcodes in rows
 * Added no value option for shortcode
 * Fixed Wysiwyg field display issues

= 3.9.20 - 20-11-2022 =
 * Fixed display of "ACF Fields" type in shortcodes
 * Added wrapper attributes to shortcode display
            
= 3.9.19 - 19-11-2022 =
 * Added option to show message when shortcode returns no value.
 * Fixed acf fields not saving when admin approval is Required
 * Made inline edit open fields in modal popup for faster initial loading of page
 * Fixed edit=false not working in new shortcode

 = 3.9.16 - 14-11-2022 =
 * Fixed issue with image based fields using basic uploader

 = 3.9.14 - 13-11-2022 =
 * Fixed nonce error when updating image through editable field shortcode
 * Fixed duplicate image in gallery field when using basic upload
 * Fixed field shortcode showing data twice

 = 3.9.12 - 10-11-2022 =
 * Fixed "add step" and "acf fields" buttons not working
 * Added shortcodes to display any field with option to edit on the fly 

 = 3.9.11 - 06-11-2022 =
 * Fixed relationship field Add and Edit Posts not working
 * Added option to limit editing to post author in relationship field
 * Fixed email dynamic options not disappering when clicking outsude of setting

 = 3.9.10 - 01-11-2022 =
 * Fixed Verify Emails in Submission Requirements
 * Removed "Frontend Admin" from toolbar

 = 3.9.9 - 27-10-2022 =
 * Fixed ACF fields not showing in submissions

 = 3.9.8 - 25-10-2022 =
 * Fixed Product Attributes fields
 * Fixed Product Images field
 * Fixed wp_kses() missing parameter

 = 3.9.7 - 23-10-2022 =
 * Removed Oxygen Element due to conflicts with latest Oxygen updates and lack of dev documentation. Add forms using shortcodes
 * Updated code to meet WordPress PHP standards
 
 = 3.9.6 - 19-10-2022 =
 * Added default value to featured image field
 * Fixed modal window conflict with YITH
 * Fixed nested forms in relationship field

 = 3.9.5 - 03-10-2022 =
 * Fixed featured image not opening media library
 * Added List Items
 * Added support for basic input inside List Item fields

 = 3.9.4 - 02-10-2022 =
 * Fixed custom fields not saving to post

 = 3.9.3 - 30-09-2022 =
 * Fixed file uploads not saving sizes
 * Reduced submissions table "fields" column data intake
 * Added options to save progress button
 * Save progress now button saves current step

 = 3.9.2 - 28-09-2022 =
  * Fixed multi step forms showing all steps
  * Fixed basic upload file limits
 
 = 3.9.1 - 25-09-2022 =
  * Fixed file fields not loading values in submissions when using basic upload

 = 3.9.1 - 23-09-2022 =
  * Added support for ACF 6
  * Updated Gutenberg blocks to use block.json, useSelect, and useDispatch
  * Added ACF Fields button to form
  * Added validation for uploads when using basic upload  
  * Added Save Progress button field

 = 3.9.1 - 13-09-2022 =
  * Fixed "Add Step" button not working
  * Fixed delete button redirect
  * Fixed redirect url properties repeating
  * Fixed recaptcha field not working in free version
  * Added option to disable default submit button in forms

 = 3.8.3 - 08-09-2022 =
  * Added option to make tab fields appear as steps on frontend
  * Added legacy elementor setting under plugin settings
  * Added Custom Choices field type
  * Fixed "No page reload" not reloading form
  * Fixed success message always showing at top of current page instead of above the form
  * Fixed recapthca validation API request

 = 3.8.2 - 30-08-2022 =
  * Added fields exclude feature to ACF Fields field type
  * Removed Freemius dependancy from free version
  * Removed Freemius from Payments addon

 = 3.7.17 - 28-08-2022 = 
  * Added option to remove admin bar node
  * Fixed relationship field rtl css issue
  * Fixed undefined 'fea' js error

 = 3.7.16 - 17-08-2022 =
  * Fixed forms saving submissions even if set not to
  * Fixed conflict with One Click Modal

 = 3.7.14 - 08-08-2022 =
 * Fixed recaptcha not working with instances on one page

 = 3.7.13 - 04-08-2022 =
 * Changed email headers to array
 * Added Mailchimp email, first name, and last name fields for use without user form
 * Allowed custom fields to save to submission only

 = 3.7.12 - 02-08-2022 =
 * Fixed mailchimp 'merge_fields' error
 * Fixed shortcodes conflict with objects
 * Fixed bcc emails sending as seperate emails

 = 3.7.11 - 27-07-2022 =
 * Placed "form type" select directly in form builder page

 = 3.7.10 - 20-07-2022 =
 * Fixed Woo attributes not saving properly
 * Optimized Mailchimp action
 * Fixed Elementor submit button alignment

 = 3.7.9 - 14-07-2022 =  
 * Fixed delete button not redirecting properly
 * Fixed success message not showing at top of page

 = 3.7.8 - 06-07-2022 =  
 * Added Mailchimp Status field to allow user to choose whether or not to sign up to mailing list
 * Fixed post to edit not showing "new post" option if no posts are found in the query
 * Fixed neccesary scripts not enqueued if admin is not logged in 
 * Removed deprecated "documents" folder
 
 = 3.7.7 - 30-06-2022 = 
 * Fixed admin css not loading in min file
 * Fixed Save Custom Fields to option not saving to correctly in legacy Elemntor widgets  

 = 3.7.6 - 28-06-2022 = 
 * Fixed plugin icon svg issue
 * Fixed modal button calling create form modal  

 = 3.7.5 - 27-06-2022 = 
 * Fixed dynamic value shortcodes 
 * Fixed delete button redirect broken

 = 3.7.4 - 26-06-2022 = 
 * Added solution for cache plugin breaking acf js object
 * Changed plugin name from ACF Frontend to Frontend Admin 
 * Added presets to form builder: 
         Delete Post Button
         Post Status Button
         Delete Product Button
         Product Status Button
 * Added Favicon field

 = 3.7.0 - 15-06-2022 = 
 * Added option to choose Frontend Form in Elementor widgets
 * Lite deprecated all Elementor widgets for new users except for Frontend Form (if you are already using these widgets, they will continue to function)
 * Added assets for form builder

 = 3.6.6 - 15-06-2022 = 
 * Fixed issue with multi step product forms 
 * Tweeked multi step forms to allow fields before steps that will show always
 * Fixed step tabs to only show when fields show
 * Fixed issue with multiple "ACF Fields" instances in one form 

 = 3.6.4 - 10-06-2022 = 
 * Moved form builder section tabs to the side of form settings
 * Added User Website field type

 = 3.6.3 - 09-06-2022 = 
 * Added Frontend Submissions Gutenberg block
 * Fixed Gutenberg block not loading form options
 * Fixed dynamic options in url query option not being generated properly
 * Added "Save Form Submissions" on form level

 = 3.6.1 - 07-06-2022 = 
 * Added Mailchimp integration 
 * Deprecated URL parameters setting in actions tab. Parameters can be placed directly in the url
 * Fixed taxonomy field to show "add new" button under field instead of transparent icon

 = 3.5.9 - 01-06-2022 = 
 * Fixed ACF Fields in multi step showing

 = 3.5.8 - 31-05-2022 = 
 * Fixed post author permissions error in modal window
 * Fixed post_to_edit current user filter not working
 * Fixed ACF Fields always showing full width

 = 3.5.7 - 30-05-2022 = 
 * Added "Post to Edit" field type to allow users to easily choose which post to edit with a dropdown
 * Added "Product to Edit" field type to allow users to easily choose which product to edit with a dropdown
 * Added option to change the "frontend-dashboard" slug
 * Fixed url query post not loading field values

 = 3.5.6 - 26-05-2022 = 
 * Fixed User email field not updating when edited
 * Fixed username editing logs user out

 = 3.5.5 - 25-05-2022 = 
 * Fixed submissions shortcode not showing new submissions

 = 3.5.4 - 25-05-2022 = 
 * Fixed edit form links not showing in admin bar in certain themes
 * Fixed shortcodes using form keys not working
 * Added preview button to form edit page

 = 3.5.3 - 24-05-2022 = 
 * Added Frontend Admin edit posts to admin toolbar
 * Added public preview for forms removing the need to create a dedicated page
 * Fixed Elementor modal window styles not working due to recent change to the css classes
 * Added link to form in submissions table
 * Fixed user field values not being loaded when using url query

= 3.5.2 - 18-05-2022 =
 * Fixed hidden modal button
 * Fixed default value not including shortcodes on submit
 * Added Frontend Admin quick access to admin toolbar

= 3.5.1 - 16-05-2022 =
 * Fixed ACF Fields not saving in admin form builder
 * Fixed "confirm password" field not hiding initially in multi step form

= 3.5.0 - 15-05-2022 =
 * Added submissions shortcode to forms: [frontend_admin submissions="{form id}"]
 * Removed bulk add fields option in form builder




== Upgrade Notice ==





