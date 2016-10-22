=== No Page Comment ===

Contributors: sethta
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=5WWP2EDSCAJR4
Tags: admin, comments, custom post type, discussion, pages, posts, attachment, attachments, media, settings, tools, trackbacks
Requires at least: 3.1
Tested up to: 4.6.1
Stable tag: trunk

An admin interface to control the default comment and trackback settings on new posts, pages and custom post types.

== Description ==

Up until recently, WordPress gave two options: You could either disable comments and trackbacks by default for all pages and posts, or you could have them active by default. In WordPress version 4.3, this finally changed so comments are always disabled on new pages.

While the new change makes it easier for many of the sites, it make it harder for people who need to get the reverse and enable comments on all pages, or if they need to change the default for a custom post type. This plugin allows you to choose whether comments are enabled or disabled by default on all new posts, pages and custom post types, while still giving the ability to individually enable comments on posts or pages.

Also, this plugin provides a way to quickly disable all comments or pingbacks for a specific custom post type. It directly interacts with your database to modify the status, so it is highly recommended that you backup your database first. There shouldn't be any issues using this feature, but it's always good to play it safe.

[Official No Page Comment Plugin Page](http://sethalling.com/plugins/wordpress/no-page-comment "No Page Comment WordPress Plugin")

[View No Page Comment Development on Github](https://github.com/sethta/no-page-comment "No Page Comment Development on Github")

[Please Report any Issues about No Page Comment on Github](https://github.com/sethta/no-page-comment/issues "Report an Issue about No Page Comment on Github")

[Donate to Support No Page Comment Development](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=5WWP2EDSCAJR4 "Donate to support the No Page Comment Plugin development")

== Installation ==

1. Unzip the `no-page-comment.zip` file and `no-page-comment` folder to your `wp-content/plugins` folder.
1. Alternatively, you can install it from the 'Add New' link in the 'Plugins' menu in WordPress.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Comments and trackbacks will be turned off by default when adding a new page.

= Settings Page =

Click 'No Page Comment' in the settings panel. A screen will display showing the following settings for posts, pages, attachments and any other custom post type installed on your blog:

* Disable comments
* Disable trackbacks

Note: These settings set the default when creating a new page. Once a new post, page, or custom post type is added, comments can be enabled by modifying the Discussion settings for that page.

Also, there is now the option to globally enable/disable comments or pingbacks of a specific post type.

== Frequently Asked Questions ==

= Why aren't comments and trackbacks being disabled? =

There are two possible issues for this. The first is that you are using a version of WordPress earlier than 3.4 and have javascript disabled as it relies on jQuery. WordPress version of 3.4 and later do not require javascript.

The second possible issue is that you are duplicating a post or page. This plugin only works when you are on a new post/page screen, while plugins that duplicate posts, duplicate the post first and then take you to an edit screen. Unfortunately, there is no way to get around this issue, so if you plan on using a duplication plugin, then you will just have to remember to disable your comments.

= Why can't I enable comments of custom post type: "X" =

Depending on your theme or plugin that created custom post type "X", that post type may not have comments set up. If this is the case, this plugin cannot help you and you will have to talk to your theme/plugin author.

= Why is "Comments are closed" or some other text displayed after I disable my comments? =

Many themes will include text to show that comments are not enabled on a post. To remove it, you would need to talk to your theme author.

= How do I modify the comment settings on an individual post or page? =

First, you must make sure you can see the Discussion admin box. Enable this by clicking on the 'Screen Options' tab at the top right and then checking the discussion checkbox. Below the post/page editor, there will be a new admin box allowing you to specifically enable or disable comments and trackbacks for that page or post.

= I want to quickly disable all trackbacks throughout my blog posts. Is this possible? =

Of course, although *it is highly recommended that you backup your blog's database prior to completing this step*. Go to the 'No Page Comment' settings page and scroll to the bottom of the page. There is an area that will allow you to either enable or disable both comments and trackbacks for any post type you have installed on your blog.

= How can I help support No Page Comment? =

[Donations](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=5WWP2EDSCAJR4 "Donate to support the No Page Comment Plugin development") are always accepted, but I am also looking for help from others who want to make this plugin better. Please [fork the plugin on Github](https://github.com/sethta/no-page-comment "Fork No Page Comment on Github") and feel free to [report any issues](https://github.com/sethta/no-page-comment/issues "Report an Issue about No Page Comment on Github"). Also, I am looking for people who are interested in translating No Page Comment into other languages. Please [contact me](http://sethalling.com/contact/ "Contact Seth Alling") me if you are interested.

== Screenshots ==

1. The Settings page on a fresh WordPress 4.3 installation

== Changelog ==

= 1.2 =
* SECURITY: Patch XSS security vulnerability.
* SECURITY: Patch CSRF security vulnerability.
* UPDATE: PHP 7 support.

= 1.1 =
* UPDATE: WordPress 4.3 support.
* UPDATE: Rename settings option in database.
* NEW: Clean up database on uninstall.

= 1.0.7 =
* NEW: Add Dutch language support.
* NEW: Add Italian language support.

= 1.0.6 =
* NEW: Add Swedish language support.

= 1.0.5 =
* FIX: Fix to make mu-plugin friendly. Thanks @swinggraphics.

= 1.0.4 =
* NEW: Add Spanish language support.
* NEW: Add Serbian language support.

= 1.0.3 =
* UPDATE: Complete translation support.
* NEW: Add .pot file for translation.

= 1.0.2 =
* FIX: Stop plugin CSS from loading on other admin pages.

= 1.0.1 =
* FIX: Add missing files from failed SVN commit.

= 1.0 =
* UPDATE: Rewrite plugin to decrease code bloat
* UPDATE: Remove javascript dependency by default for WordPress versions 3.4 and up
* UPDATE: Fix settings so they are not dependent on WordPress's comment settings
* UPDATE: Fix settings page so it displays responsively
* NEW: Add support for attachments
* NEW: Update discussion options page to include link to No Page Comment settings page
* NEW: Prepare plugin for translation into other languages

= 0.3 =
* NEW: Add ability to enable/disable all comments or trackbacks on any specific custom post type. It is highly recommended that you backup your blog's database prior to doing this.

= 0.2 =
* UPDATE: Style Admin Settings Page to match with WordPress
* NEW: Add support for posts
* NEW: Add support for custom post types

= 0.1 =
* NEW: Initial release.

== Upgrade Notice ==

= 1.2 =
Security fix. Supports PHP 7. Adds database cleanup on uninstall. All previous No Page Comment settings will remain intact with upgrade.

= 1.1 =
Supports new core default comment change in WordPress 4.3. Adds database cleanup on uninstall. All previous No Page Comment settings will remain intact with upgrade.

= 1.0 =
Improves plugin performance and adds ability to enable/disable all comments or trackbacks on attachment pages. All previous No Page Comment settings will remain intact with upgrade.

= 0.3 =
Adds the ability to enable/disable all comments or trackbacks on any specific custom post type. All previous No Page Comment settings will remain intact with upgrade.

= 0.2 =
Adds the ability to disable comments on posts, pages, and custom post types. All previous No Page Comment settings will remain intact with upgrade.