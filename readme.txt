=== Trialfire High Value Pages ===
Contributors: yllus
Tags: 
Requires at least: 4.9.8
Tested up to: 4.9.8
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Trialfire High Value Pages adds a sidebar metabox to your Posts and Pages, allowing you to designate them as High Value Pages for follow-up actions.

== Installation ==

1. Enable **Trialfire High Value Pages** within the **Plugins** > **Installed Plugins** interface.

2. Edit a few Posts and/or Pages on your WordPress backend; in the **Trialfire - High Value Page** metabox, check the "Mark As High Value Page?" checkbox; then Publish or Update the post/page.

3. Those posts/pages will now be displayed via the WP REST API:

      https://www.yoursite.com/wp-json/trialfire-high-value-pages/v1/list/

4. On the WordPress side of things, your work is done. You may now consume the list of URLs at the URL above, look for Trialfire event data for matches and action those events as needed.

== Changelog ==

= 1.0.0 =
* Initial release.