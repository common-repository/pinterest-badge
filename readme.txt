=== Plugin Name ===
Contributors: Skipser
Donate link: 
Tags: Pinterest, widget, sidebar, pinterest badge, pinterest follow, pinterest follow badge, pinterest button
Requires at least: 3
Tested up to: 4.6
Stable tag: 1.9

Pinterest badge links your blog to your pinterest profile by showing your recent pins. Also shows the follower count and follow button as well.

== Description ==
= Pinterest Badge - =
Pinterest badge is a fully customisable pinterest badge plugin for wordpress.

It adds a widget to your blog that will display a list of your latest pinned images from your pinterest profile or page. It also displays the number of people who have followed you in pinterest along with the follow button.

= Additional Info - =
The plugin uses caching to store your pinterest profile data to eliminate checking pinterest on every page load.
For the caching to work, your web-server needs to be able to write to wp-content. (a lot of plugins require this so it should be fine).

For any issues, please mail to arun@skipser.com

Plugin page : http://www.pinterestbadge.skipser.com

== Installation ==

1. Download pinterest-badge.zip and unzip
2. Upload the unzipped pinterest-badge folder to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to the 'Plugins' menu in Wordpress and add the widget to your sidebar.
5. Go to 'Appearence'->'Widgets' and in the PinterestBadge drop down form, input your pinterest id.

== Frequently Asked Questions ==

= I get the error "Parse error: syntax error, unexpected T_STRING, expecting T_OLD_FUNCTION or T_FUNCTION or T_VAR or ‘}’ in plugins/pinterest-badge/pinterestbadgehelper.php" =

This is a problem with your PHP setup. You are almost certainly running PHP4. The plugin requires PHP5. WordPress requires PHP5 after version 3.2 too. Talk to your host about using PHP5.

= I get the error "Warning: file_get_contents() [function.file-get-contents]: Filename cannot be empty in /*****/*****/public_html/blog/wp-content/plugins/pinterest-badge/pinterestbadgehelper.php" =

You probably have something other than your Pinterest id in the id box. Make sure you just put in the correct pinterest profile id and nothing else.

= I get the error "Warning: file_get_contents(http://pinterest.com/*******… [function.file-get-contents]: failed to open stream: HTTP request failed! HTTP/1.0 403 Forbidden" =

This is a HTTP 403 error from Pinterest, it means they have banned your server’s IP from making requests to their servers. This usually isn’t anything to do with the plugin (it makes very few calls to Pinterest's servers) and it is more likely that you are on shared hosting, and someone else who shares your IP has been scraping Pinterest.

= I get the error "Warning: file_get_contents() [function.file-get-contents]: URL file-access is disabled in the server configuration in /var/www/web1281/html/wordpress/wp-content/plugins/pinterest-badge/pinterestbadgehelper.php". AND/OR I get the error "Warning: file_get_contents(http://pinterest.com/...[function.file-get-contents]: failed to open stream: no suitable wrapper could be found in /var/www/web1281/html/wordpress/wp-content/plugins/pinterest-badge/pinterestbadgehelper.php" =

The plugin requires either CURL or file_get_contents() to be enabled on your server.If your host gives you access to your php.ini then you can change the ‘allow_url_fopen’ setting to ’1′ which will fix your problem. Otherwise speak to your host and ask them to enable CURL or allow_url_fopen for you.

== Screenshots ==

1. pinterestBadge widget in the sidebar.

2. pinterestBadge Configuration panel.

== Changelog ==

= 1.0.0 =
* Release ...

= 1.1.0 =
* Fix for pinterest accounts with less than 9 pins.


= 1.3.0 =
* Fix html syntax issue for follower count.


= 1.5.0 =
* Fixed badge code to work with new pinterest code

= 1.6.0 =
* More debugging code for caching.

= 1.7.0 =
* More debugging code for cache writing failures.

= 1.8.0 =
* Using pinterest api which is more efficient.

= 1.9.0 =
* Update to be compatible with latest wordpress 4.6.1