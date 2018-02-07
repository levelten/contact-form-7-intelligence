=== Contact Form 7 Google Analytics Intelligence ===
Contributors: tomdude
Donate link: getlevelten.com/blog/tom
Tags: analytics, contact, contact form, form, google analytics, marketing, metrics, stats, tracking, web form
Requires at least: 4.5
Tested up to: 4.9.2
Stable tag: 1.0.8
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Automates Contact Form 7 submission tracking in Google Analytics.

== Description ==

### Contact Form 7 Google Analytics Goal Tracking Plugin

Google Analytics goal tracking for Contact Form 7 made easy.

#### Core Features

* Create and manage Google Analytics goals directly in WordPress
* Set a default goal that triggers on all Contact Form 7 submissions
* Customize goals per form
* Customize goal values per form
* No coding required
* No advanced Google Analytics skills needed
* No Google Tag Manager setup needed
* 5 minute installation

https://www.youtube.com/watch?v=6vFl4hbPkfQ&rel=0

#### Enhanced Google Analytics

The plugin integrates with the Intelligence API to automate Google Analytics goal management in WordPress. Intelligence is a framework for enhancing Google Analytics.

To learn more about Intelligence for WordPress visit [intelligencewp.com](https://intelligencewp.com)

== Installation ==

=== Install Files Within WordPress ===

1. Visit 'Plugins > Add New'
1. Search for 'Contact Form 7 Intelligence'
1. Activate Contact Form 7 Google Analytics Intelligence from your Plugins page.
1. Go to "plugin setup" below.

=== Install Files Manually ===

1. Download the [Contact Form 7 Google Analytics Intelligence plugin](https://wordpress.org/plugins/cf7-intelligence) and the [Intelligence plugin](https://wordpress.org/plugins/intelligence)
1. Add `cf7-intelligence` and `intelligence` folders to the `/wp-content/plugins/` directory
1. Activate the Contact Form 7 Google Analytics Intelligence plugin through the 'Plugins' menu in WordPress
1. Go to "plugin setup" below.

=== Plugin Setup ===

1. You should see a notice at the top of the Plugins page. Click "Setup plugin" to launch the setup wizard. You can also launch the wizard from the Contact Form 7 settings, 'Contact > Intelligence'.
1. Go through the setup wizard and set up the plugin for your site.
1. You're done!

=== Popular options ===
Track and manage Intelligence goals and events in existing Google Analytics tracking ID:

1. Go to "Intelligence > Settings"
1. Under "Tracking settings" fieldset, open the "Base Google Analytics profile" fieldset
1. If base profile is not set, click "Set profile" link to set your existing tracking ID
1. Check "Track Intelligence events & goals in base profile"
1. Check "Sync Intelligence goals configuration to base profile"
1. Click "Save settings" button at bottom of page

Embed Google Analytics tracking code if site does not already embed tracking code through some other method.

1. Go to "Intelligence > Settings"
1. Under "Tracking settings" fieldset, open the "Advanced" fieldset.
1. For "Include Google Analytics tracking code" select the "Analytics" option
1. Click "Save settings" button at bottom of page

== Screen Shots ==

1. Select a goal and goal value to track on different form submissions
2. Easily add goals to your in Google Analytics
3. Manage Google Analytics goals without leaving WordPress
4. Automatically trigger goals on form submission
5. Set a default goal to make sure no form submissions are missed

== Changelog ==

= 1.0.0 =
* Initial version

== Upgrade Notice ==

= 1.0.0 =
No notices

= 1.0.3 =
* Support for non rendered forms

= 1.0.4 =
* Setup wizard
* CF7 settings page

= 1.0.5 =
* Added video demo
* Added more detailed installation instructions

= 1.0.6 =
* Support for intel_system API
* Support for intel_form_type API