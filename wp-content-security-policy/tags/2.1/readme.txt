=== WP Content Security Plugin ===
Contributors: dyland
Donate link: None
Tags: content security policy, csp
Requires WP: 4.8
Tested up to: 4.9
Requires PHP: 5.3
Stable tag: 2.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
GitHub Plugin URI: https://github.com/dylandownhill/WP-Content-Security-Policy-Plugin

Block XSS vulnerabilities by adding a Content Security Policy header, plugin receives violations to easily maintain the security policy.

== Description ==

Content Security Policy (CSP) is a W3C guideline to prevent cross-site scripting (XSS) and related attacks. XSS allows other people to run scripts on your site, making it no
longer your application running on your site, and opens your whole domain to attack due to "Same-Origin Policy" - XSS anywhere on your domain is XSS everywhere on your domain. (see https://www.youtube.com/watch?v=WljJ5guzcLs)

CSP tells your browser to push least-privilege environment on your application, allowing the client to only use resources from trusted domains and block all resources from anywhere else.

Adding CSP to your site will protect your visitors from:

* Cross-site scripting (XSS) attacks
* Adware and Spyware while on your site

This plugin will help you set your CSP settings and will add them to the page the visitor requested. Policy violations will be logged in a database table which can be viewed via an admin page that supplies all the violations, along with counts. Buttons easily allow you to add the sites to your headers or to ignore them.

This plugin also allows you to ignore sites that repeatedly violate your policies. For example, some tracking images will show as violating your policies, but you still don't want them to run, therefore you can block the site from showing up in your logs - note, however, that the browser will still call your server and your server will still spend resources processing the call.

= CSP Directives =

CSP allows you to control where your visitors' browser can run code from. 

The W3C specification allows for the following directives:

* **default-src**
The default-src is the default policy for loading content. If another setting is blank then this setting will be used.

* **script-src**
Defines valid sources of JavaScript.

* **style-src**
Defines valid sources of stylesheets.

* **img-src**
Defines valid sources of images. 

* **connect-src**
Applies to XMLHttpRequest (AJAX), WebSocket or EventSource.

* **manifest-src**
Specifies which manifest can be applied to the resource

* **worker-src**
Specifies valid sources for Worker, SharedWorker, or ServiceWorker scripts.

* **font-src**
Defines valid sources of fonts.

* **object-src**
Defines valid sources of plugins.  Stops your site becoming the source of drive-by attacks. 

* **media-src**
Defines valid sources of audio and video.

* **base-uri**<br>
Limit the values that can be used in the <base> entry.

* **frame-src**
Defines valid sources for loading frames.

* **sandbox**
Enables a sandbox for the requested resource similar to the iframe sandbox attribute. 

* **form-action**
The form-action restricts which URLs can be used as the action of HTML form elements. 

* **frame-ancestors**
Whether to allow embedding the resource using a frame, iframe, object, embed, etc. in non-HTML resources.

* **plugin-types**
Restricts the set of plugins that can be invoked by limiting the types of resources that can be embedded.

* **report-uri**
URL to post information on violations of the policies you set.

* **require-sri-for**<br>
Require integrity check for scripts and/or styles.

= CSP Entry Syntax =

**Note** - with version 3 of the CSP specification there has been a move to 'strict-dynamic' - see the **Upgrade Notice** section for more information.

Each directive can take one or more of the following values:

* **\***
Allows loading resources from any source.

* **'none'**
Blocks loading resources from all sources. The single quotes are required.

* **'self'**
Refers to your own host. The single quotes are required.

* **'unsafe-inline'**
Allows inline elements, such as functions in script tags, onclicks, etc. The single quotes are required.

* **'unsafe-eval'**
Allows unsafe dynamic code evaluation such as JavaScript eval(). The single quotes are required.

* **'strict-dynamic'**
The trust explicitly given to a script present in the markup, by accompanying it with a nonce or a hash, shall be propagated to all the scripts loaded by that root script. The single quotes are required.

* **'sha-AAAAAAAAA'**
For scripts and styles that can't take a nonce the browser will tell you a 'sha-' value you can use. The single quotes are required. 

* **'nonce-AAAAAAAAA'**
The trust nonce value - this value is automatically generated per page refresh and should not be entered by the user. The single quotes are required. 

* **data:**
Allow loading resources from data scheme - usually inline images. **This is insecure**; an attacker can also inject arbitrary data: URIs. Use this sparingly and definitely **not for scripts**.

* **mediastream:**
Allows mediastream: URIs to be used as a content source.

* **filesystem:**
Allow loading resource from file system.
				
* **https:**
Only allows loading resources from HTTPS: on any domain. This can be used to block insecure requests.

* **www.example.com**
Allow loading resources from this domain, using any scheme (http/https)

* **\*.example.com**
Allow loading resourcs from any subdomain under example.com, using any scheme (http/https)

* **http://www.example.com**
Allows loading resources from this domain using this scheme.

* **<domain w or w/o scheme>/path/to/file/**
Allows loading any file from this path on this domain.

* **<domain w or w/o scheme>/path/to/file/thefile**
Allows loading this one file on this domain.

= Security Headers =

In addition to the CSP headers, there are other security headers supported, including:

* **Expect-CT**
Instructs user agents (browsers) to expect valid Signed Certificate Timestamps (SCTs) to be served.

* **Strict Transport Security**
The HTTP Strict-Transport-Security response header (HSTS)  lets a web site tell browsers that it should only be accessed using HTTPS, instead of using HTTP.

* **X-Frame-Options**
The X-Frame-Options HTTP response header can be used to indicate whether or not a browser should be allowed to render a page in a &lt;frame&gt;, &lt;iframe&gt; or &lt;object&gt; . Sites can use this to avoid clickjacking attacks, by ensuring that their content is not embedded into other sites.

* **X-XSS-Protection**
The HTTP X-XSS-Protection response header is a feature of Internet Explorer, Chrome and Safari that stops pages from loading when they detect reflected cross-site scripting (XSS) attacks. Although these protections are largely unnecessary in modern browsers when sites implement a strong Content-Security-Policy that disables the use of inline JavaScript ('unsafe-inline'), they can still provide protections for users of older web browsers that don't yet support CSP.

* **X-Content-Type-Options**
The X-Content-Type-Options response HTTP header is a marker used by the server to indicate that the MIME types advertised in the Content-Type headers should not be changed and be followed. This allows to opt-out of MIME type sniffing, or, in other words, it is a way to say that the webmasters knew what they were doing.

* **Referrer-Policy**
The Referrer-Policy HTTP header governs which referrer information, sent in the Referer header, should be included with requests made.

== Installation ==

= Before You Start =

I recommend you *move all styles and scripts into include files* - this will allow WP_CSP to approve the included file and will mean you can stop the browser running scripts that have been added to the page from an unknown source.
Read the ** Upgrade notice** for information on CSP version 3.

= To Install =

Follow the standard Wordpress plugin installation procedures.

e.g.

1. Upload `plugin-name.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Visit the settings under 'Settings->Content Security Policy Options'. I recommend you run this plugin in 'report only' mode for a little while to help you set your CSP settings correctly.

== Upgrade Notice ==

= Before You Start =

I recommend you **move all styles and scripts into include files** - this will allow WP_CSP to approve the included file and will mean you can stop the browser running scripts that have been added to the page from an unknown source.

With the advent of Content Security Policy version 3 the workings for CSP changed (note: this is the W3C CSP version 3, not the WP_CSP version). 
* In CSP version 1 and 2 you have to declare each host name you trust individually, this works great for most sites; however, it can become an issue on sites that have a lot of advertizing or other content and you can end up with dozens of sites with permissions.
* In CSP version 3 you declare the scripts and styles that you trust using a 'nonce' (random string of characters, different to Wordpress Nonces), they then pass on the trust to whatever they do. Nonces change on **every single page** refresh.

Ideally you would use CSP version 3; however, a lot of scripts do not work well with CSP version 3, so you might have to revert to using version 2 syntax for now.
Scripts that don't work with CSP version 3 includes "Revolution Slider" - let me know of any more with issues and I'll note them here.

= CSP Version 3 =

Version 3 uses 'nonce's to indicate which scripts and styles you trust to run on your site. When you set 'strict-dynamic' as your policy the plugin will:
* Automatically generate a valid nonce for use by the plugin and by your code.
* Automatically add the correct nonce to your CSP policy header.
* Automatically tag all styles and scripts in your header and footer with the correct nonce value (wp_head() and wp_footer()).
* Allow manual tagging of scripts/styles through additional functionality.

= CSP v3 Additional Nonce Tagging =

There are four additional ways to add the nonce to your code:
1. Add your included script or stylesheet to the header or footer and the code will be tagged automatically (use wp_enqueue_scripts/wp_enqueue_style). If you use get_template_part() you can tag these through add_action too i.e.
`<?php 
add_action('wp_footer',function() {
	get_template_part( 'track/part', 'trackfooter' );
	get_template_part( 'track/part', 'anothertracker' );
	get_template_part( 'track/part', 'paidads' );
});?>
<?php wp_footer(); ?>`

1. have WP_CSP add the tagging automatically through output buffer capturing. i.e.
`WP_CSP::ob_start();
My scripts and styles
WP_CSP::ob_end_flush()`

1. Send the string through the WP_CSP auto tagging function
`$content = do_shortcode('[rev_slider alias="homepage"]');
echo WP_CSP::tag_string($content);`

1. Add the nonce by hand:
`<script async defer data-pin-hover="true" data-pin-round="true" data-pin-save="false" src="//assets.pinterest.com/js/pinit.js" **<?php if ( class_exists ('WP_CSP') ) { echo "nonce='".WP_CSP::getNonce() . "' "; } ?>**></script>`

= CSP v3 Inline Scripts/Styles and Untaggable Code =

Inline scripts and styles can be dangerous, you do not know which scripts wrote them and probably don't want them run if you can avoid it. 
When you use 'script-dynamic', the "unsafe-eval" anh "unsafe-inline stop working and the browser will say in the console (your browser's developer tools console) "Note that 'unsafe-inline' is ignored if either a hash or nonce value is present in the source list."

To fix this either:
* Put all the scripts and/or style code into files and include the files. The include statements can be tagged.
* If the browser returns "Either the 'unsafe-inline' keyword, a hash (**'sha256-h3SEZNZpOYg4jp6TCkoWN7Z477Qt3q1owH0SPbz+a4M='**), or a nonce ('nonce-...') is required to enable inline execution." - you can take the SHA number (including single quotes) and put that in the policy line.

As of writing browsers do not report the SHA code in their error report to the server so you will have to add this by hand.


== Frequently Asked Questions ==

= What is the best way to set a content security policy =

When you first turn on CSP, put into report-only mode and build the basic rules for your site. After about a week, turn off report-only and go to enforce rules.

If you want to implement the latest W3C version of CSP - version 3 [Google recommends](https://csp.withgoogle.com/docs/strict-csp.html) - set the following for default-src, script-src, and style-src:
    'unsafe-inline' 'unsafe-eval' 'strict-dynamic' https: http:
(single quotes are required) This will allow modern browsers to run the latest version of CSP with nonces, etc. and older browsers to just work without restrictions.

If you're going to run CSP v2, one good way of building a policy for a site would be to begin with a default-src of 'self', and to build up a policy from there that contains only those resource types which are actually in use for the page you'd like to protect. If you don't use webfonts, for instance, there's no reason to specify a source list for font-src; specifying only those resource types a page uses ensures that the possible attack surface for that page remains as small as possible.

= Should I set 'self' in all options =

Usually you will trust your own site for all directives; however, I usually only add 'self' when it shows up as a violation. None of these directives is inherited, except some directives will default to 'default-src' if not set explicitly.

= Should I set '*' in all options? =

Usually you would want to keep security as strict as possible while still allowing your application to run. Therefore, '*' should be avoided.

= Can I have a different policy for each page? =

The W3C specification allows for a different policy for each page, this plugin was not written with page-level security capability.

= Can I have some options enforced and some report-only? =

The W3C specification allows for this functionality; this plugin does not support this capability.

= No errors are getting logged =

1. First check that your site is producing CSP errors by starting the developer tools in your browser (usually F12) and checking for messages in the console output.
1. If nothing is in the console output then check the page has a CSP header by looking at the page in the 'network' tab of the dev tools. Check the 'response' has a header called 'content-security-policy' or 'content-security-policy-report-only' - if this is misisng then the plugin is not running or CSP is not enabled.
1. If there is a CSP header and nothing is reported in the console then you have no violations and everything is running as it should. Yippee!
1. If there is a CSP header and errors in the console then the REST route might not be registered properly. Go to <your domain>/wp-json and look for 'wpcsp' (usually CTRL-F for find and type in wpcsp) - if nothing is listed then the REST route is not getting registered.
1. Look in the PHP error logs for an error - post the error, file name and line number in the support forums and I should be able to work out why it's failing.

= CSP v3 Inline Scripts/Styles =

Inline scripts and styles can be dangerous, you do not know which scripts wrote them and probably don't want them run if you can avoid it. When you use 'script-dynamic', the "unsafe-eval" and "unsafe-inline stop working and the browser will say in the console "Note that 'unsafe-inline' is ignored if either a hash or nonce value is present in the source list." 

To fix this either:
* Put all the scripts and/or style code into files and include the files.
* If the browser returns "Either the 'unsafe-inline' keyword, a hash (**'sha256-h3SEZNZpOYg4jp6TCkoWN7Z477Qt3q1owH0SPbz+a4M='**), or a nonce ('nonce-...') is required to enable inline execution." - you can take the SHA number (including single quotes) and put that in the policy line.

= How Big Does The Database Get =

This is different for all sites. The plugin will automatically delete records older than one week to keep the size managable. Also, if too many records are found the system will only report on the worse errors to avoid locking your browser.

= Handling the Violation Reports/Errors Is A Big Resouce Drain =

Every error output by your browser is likely to result in a call to the server to log the error - if a page has 20 errors that's 20 calls to your server - this can be a lot of processing power. To avoid this change the "Log Violations" option from "Yes, All" to "Yes - 10%", "Yes - 1%', or "Yes - 0.1%" - in each case the plugin will randomly allow only a set fraction of your visitors to report errors back to the server, they're still enfored at the browser but no report will come back to your site.

== Changelog ==

= 2.1 =
* Added full support for CSP version 3 - nonces, auto-tagging scripts and style tags, etc. See section **CSP v3 Additional Nonce Tagging**
* Added 'base-uri' and 'require-sri-for'
* Changed to use get_rest_url()

= 2.0 =
* Added support for various other security related header options
* Added ability to change URL violations are reported to
* Added validation of URLs entered
* Added ability to turn off CSP headers while keeping other security related headers
* Fixed issue when run on PHP below 5.6 (tested on PHP 5.3)

= 1.9 =
* Add support for block-mixed-content and upgrade-insecure-requests
* Add support for worker-src and manifest-src
* Add support for mediastream: and filesystem:
* Add display of configuration errors at the top of the save page.
* Fix register_route issues
* Change size of configuration entry boxes to match their contents.

= 1.8 =
* Fixed issues running on WP 4.8
* Limit logged violations to top 100.

= 1.7 =
* Bug in Rest route registering
* Create table limits the size of the secondary key to avoid table creation issues
* Updated requires to 4.4 due to Rest integration

= 1.6 =
* Bug in Rest route 

= 1.5 =
* Update 'tested up to' to Wordpress 4.9
* Moved to REST calls

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
This plugin was written by Dylan Downhill, CDO of [Elixir Interactive](https://www.elixirinteractive.com/) .
