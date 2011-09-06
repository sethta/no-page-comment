=== No Page Comment ===

Contributors: Seth Alling
Tags: admin, comments, javascript, page, pages, plugin, settings, tools, trackbacks
Requires at least: 3.1
Tested up to: 3.2.1
Stable tag: 0.1

Disable comments by default on new pages, while still giving you the ability to individually set them on a page or post basis.

== Description ==

By default, WordPress gives you two options. You can either disable comments and trackbacks by default for all pages and posts, or you can have them active by default. Unfortunately, there is no specific WordPress setting that allows comments and trackbacks to be active by default for posts, while disabling them on pages.

There have been workarounds created by disabling comments sitewide on all pages and/or posts, but what if you may actually want to have comments on a page or two. The difference between this plugin and others is that it will automatically uncheck to discussion settings boxes for you when creating a new page, while still giving you the flexibility to open comments up specifically on individual pages.

== Installation ==

1. Unzip the `no-page-comment.zip` file and `no-page-comment` folder to your `wp-content/plugins` folder.
1. Alternatively, you can install it from the 'Add New' link in the 'Plugins' menu in WordPress.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Comments and trackbacks will be turned off by default when adding a new page.

= Settings Page = 

Click 'FAQs Settings' in the settings panel:

* Disable comments on pages
* Disable trackbacks on pages

Note: These settings set the default when creating a new page. Once a new page has been created, comments can be enabled by modifying the Discussion settings for that page.

== Frequently Asked Questions ==

= Why aren't comments and trackbacks being disabled? =

Javascript probably isn't active on your browser. Enable javascript for the plugin to work correctly.

= Why are comments disabled in posts as well? =

This is most likely due to a setting in WordPress. Go to the Discussion settings page and make sure that comments are enabled on. The plugin will only block comments on pages.

= How do I modify the comment settings on an individual post or page? =

First, you must make sure you can see the Discussion admin box. Enable this by clicking on the 'Screen Options' tab at the top right and then checking the discussion checkbox. Below the post/page editor, there will be a new admin box allowing you to specifically enable or disable comments and trackbacks for that page or post.

== Changelog ==

= 0.1 =
* NEW: Initial release.