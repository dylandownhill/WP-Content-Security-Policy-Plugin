=== Plugin Name ===
Contributors: dyland
Donate link: None
Tags: content security policy, csp
Requires at least: 4.2.2
Tested up to: 4.6
Stable tag: 1.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Block XSS vulnerabilities by adding a Content Security Policy header, plugin receives violations to easily maintain the security policy.

== Description ==

Content Security Policy (CSP) is a W3C guideline to prevent cross-site scripting (XSS) and related attacks. XSS allows other people to run scripts on your site, making it no
longer your application running on your site, and opens your whole domain to attack due to "Same-Origin Policy" - XSS anywhere on your domain is XSS everywhere on your domain. (see https://www.youtube.com/watch?v=WljJ5guzcLs)

CSP tell your browser to push least-privilege environment on your application, allowing the client to only use resources from trusted domains and block all resources from anywhere else.

Adding CSP to your site will protect your visitors from

* Cross-site scripting (XSS) attacks
* Adware and Spyware while on your site

= Directives =

CSP allows you to control where your visitors' browser is allowed to run code from. The W3C specification allows for 9 directives.

* **default-src**<br>
The default-src is the default policy for loading content. If another setting is blank then this setting will be used.

* **script-src**<br>
Defines valid sources of JavaScript.

* **style-src**<br>
Defines valid sources of stylesheets.

* **img-src**<br>
Defines valid sources of images. 

* **connect-src**<br>
Applies to XMLHttpRequest (AJAX), WebSocket or EventSource.

* **font-src**<br>
Defines valid sources of fonts.

* **object-src**<br>
Defines valid sources of plugins.  Stops your site becoming the source of drive-by attacks. 

* **media-src**<br>
Defines valid sources of audio and video.

* **frame-src**<br>
Defines valid sources for loading frames.

* **sandbox**<br>
Enables a sandbox for the requested resource similar to the iframe sandbox attribute. 

* **form-action**<br>
The form-action restricts which URLs can be used as the action of HTML form elements. 

* **frame-ancestors**<br>
Whether to allow embedding the resource using a frame, iframe, object, embed, etc. in non-HTML resources.

* **plugin-types**<br>
Restricts the set of plugins that can be invoked by limiting the types of resources that can be embedded.

* **report-uri**<br>
URL to post information on violations of the policies you set.

Each directive can take one or more of the following values:

* **\***<br>
Allows loading resources from any source.

* **'none'**<br>
Blocks loading resources from all sources.

* **'self'**<br>
Refers to your own host.

* **'unsafe-inline'**<br>
Allows inline elements, such as functions in script tags, onclicks, etc.

* **'unsafe-eval'**<br>
Allows unsafe dynamic code evaluation such as JavaScript eval()

* **data:**<br>
Allow loading resources from data scheme - usually inline images.

* **https:**<br>
Only allows loading resources from HTTPS: on any domain

* **www.example.com**<br>
Allow loading resources from this domain, using any scheme (http/https)

* **\*.example.com**<br>
Allow loading resourcs from any subdomain under example.com, using any scheme (http/https)

* **http://www.example.com**<br>
Allows loading resources from this domain using this scheme.

* **<domain w or w/o scheme>/path/to/file/**<br>
Allows loading any file from this path on this domain.

* **<domain w or w/o scheme>/path/to/file/thefile**<br>
Allows loading this one file on this domain.

This plugin will help you set your CSP settings, and will add them to the page the visitor requested. Polivy violations will be logged in a database table.
An admin page is provided that supplies all the violations, along with counts. Buttons easily allow you to add the sites to your headers.

This plugin also allows you to ignore sites that repeatedly violate your policies. For example, some tracking images will show as violating your 
policies but you still don't want them to run, therefore you can block the site from showing up in your logs.

== Installation ==

Follow the standard Wordpress plugin installation procedures.

e.g.

1. Upload `plugin-name.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Visit the settings under 'Settings->Content Security Policy Options'. I recommend you run this plugin in 'report only' mode for a little while to help you set your CSP settings correctly.

== Frequently Asked Questions ==

= What is the best way to set a content security policy =

When you first turn on CSP, put into report-only mode and build the basic rules for your site. After about a week, turn off report-only and go to enforce rules.

One good way of building a policy for a site would be to begin with a default-src of 'self', and to build up a policy from there that contains only those resource types 
which are actually in use for the page you'd like to protect. If you don't use webfonts, for instance, there's no reason to specify a source list for font-src; 
specifying only those resource types a page uses ensures that the possible attack surface for that page remains as small as possible.

= Should I set 'self' in all options =

Usually you will trust your own site for all directives; however, I usually only add 'self' when it shows up as a violation. 
None of these directives is inherited, except some directives will default to 'default-src' if not set explicitly.

= Can I have a different policy for each page? =

The W3C specification allows for a different policy for each page, this plugin was not written with page-level security capability.

= Should I set '*' in all options? =

Usually you would want to keep security as strict as possible while still allowing your application to run. Therefore, '*' should be avoided.

== Changelog ==


= 1.4 =
* Update 'tested up to' to Wordpress 4.6
* Fixed wrong label on 'frame ancestors' admin field.
* Added ability to limit load on server by only logging errors for a percentage of page loads.
* Auto creates log table if it's missing.

= 1.3 =
* Some class and admin functions were not static but needed to be.
* Fixed undefined variables.

= 1.2 =
* Added entry verification for admin screen and added information for user.
* Using the automatic add to policy/ignore is now cleaner and simpler.
* Added nonce in callback to prevent attacks.

= 1.1 =
* Bug fix.

= 1.0 =
* Initial release.


== Written By ==
This plugin was written by Dylan Downhill, CIO of [Elixir Interactive](http://www.elixirinteractive.com/) .