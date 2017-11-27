=== Plugin Name ===
Contributors: dyland
Donate link: None
Tags: content security policy, csp
Requires at least: 4.8
Tested up to: 4.9
Stable tag: 2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Block XSS vulnerabilities by adding a Content Security Policy header, plugin receives violations to easily maintain the security policy.

== Description ==

Content Security Policy (CSP) is a W3C guideline to prevent cross-site scripting (XSS) and related attacks. XSS allows other people to run scripts on your site, making it no
longer your application running on your site, and opens your whole domain to attack due to "Same-Origin Policy" - XSS anywhere on your domain is XSS everywhere on your domain. (see https://www.youtube.com/watch?v=WljJ5guzcLs)

CSP tells your browser to push least-privilege environment on your application, allowing the client to only use resources from trusted domains and block all resources from anywhere else.

Adding CSP to your site will protect your visitors from:

* Cross-site scripting (XSS) attacks
* Adware and Spyware while on your site

This plugin will help you set your CSP settings and will add them to the page the visitor requested. Policy violations will be logged in a database table which can be viewed via an admin page that supplies all the violations, along with counts. Buttons easily allow you to add the sites to your headers or to ignore them.

This plugin also allows you to ignore sites that repeatedly violate your policies. For example, some tracking images will show as violating your policies but you still don't want them to run, therefore you can block the site from showing up in your logs - note, however, that the browser will still call your server and your server will still spend resources processing the call.

= CSP Directives =

CSP allows you to control where your visitors' browser is allowed to run code from. The W3C specification allows for the following directives.

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

* **manifest-src**<br>
Specifies which manifest can be applied to the resource

* **worker-src**<br>
Specifies valid sources for Worker, SharedWorker, or ServiceWorker scripts.

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

= CSP Entry Syntax =

Each directive can take one or more of the following values:

* **\***<br>
Allows loading resources from any source.

* **'none'**<br>
Blocks loading resources from all sources. The single quotes are required.

* **'self'**<br>
Refers to your own host. The single quotes are required.

* **'unsafe-inline'**<br>
Allows inline elements, such as functions in script tags, onclicks, etc. The single quotes are required.

* **'unsafe-eval'**<br>
Allows unsafe dynamic code evaluation such as JavaScript eval(). The single quotes are required.

* **'strict-dynamic'**<br>
The trust explicitly given to a script present in the markup, by accompanying it with a nonce or a hash, shall be propagated to all the scripts loaded by that root script. The single quotes are required. The single quotes are required.

* **data:**<br>
Allow loading resources from data scheme - usually inline images. **This is insecure**; an attacker can also inject arbitrary data: URIs. Use this sparingly and definitely not for scripts.

* **mediastream:**<br>
Allows mediastream: URIs to be used as a content source.

* **filesystem:**<br>
Allow loading resource from file system.
				
* **https:**<br>
Only allows loading resources from HTTPS: on any domain. This can be used to block insecure requests.

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

= Security Headers =

In addition to the CSP headers, there are other security headers supported, including:

* **Expect-CT**<br>
Instructs user agents (browsers) to expect valid Signed Certificate Timestamps (SCTs) to be served.

* **Strict Transport Security**<br>
The HTTP Strict-Transport-Security response header (HSTS)  lets a web site tell browsers that it should only be accessed using HTTPS, instead of using HTTP.

* **X-Frame-Options**<br>
The X-Frame-Options HTTP response header can be used to indicate whether or not a browser should be allowed to render a page in a &lt;frame&gt;, &lt;iframe&gt; or &lt;object&gt; . Sites can use this to avoid clickjacking attacks, by ensuring that their content is not embedded into other sites.

* **X-XSS-Protection**<br>
The HTTP X-XSS-Protection response header is a feature of Internet Explorer, Chrome and Safari that stops pages from loading when they detect reflected cross-site scripting (XSS) attacks. Although these protections are largely unnecessary in modern browsers when sites implement a strong Content-Security-Policy that disables the use of inline JavaScript ('unsafe-inline'), they can still provide protections for users of older web browsers that don't yet support CSP.

* **X-Content-Type-Options**<br>
The X-Content-Type-Options response HTTP header is a marker used by the server to indicate that the MIME types advertised in the Content-Type headers should not be changed and be followed. This allows to opt-out of MIME type sniffing, or, in other words, it is a way to say that the webmasters knew what they were doing.

* **Referrer-Policy**<br>
The Referrer-Policy HTTP header governs which referrer information, sent in the Referer header, should be included with requests made.

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

= No errors are getting logged =

1. First check that your site is producing CSP errors by starting the dev tools in your browser (usually F12) and checking whether anything is mentioned in the console output.
1. If nothing is in the console output then check the page has a CSP header by looking at the page in the 'network' tab of the dev tools. Check the 'response' has a header called 'content-security-policy' or 'content-security-policy-report-only' - if this is misisng then the plugin is not running or CSP is not enabled.
1. If there is a CSP header and nothing is reported in the console then you have no violations and everything is running as it should.
1. If there is a CSP header and errors in the console then the REST route might not be registered properly. Go to <your domain>/wp-json and look for 'wpcsp' (usually CTRL-F for find and type in wpcsp) - if nothing is listed then the REST route is not getting registered.
1. Look in the PHP error logs for an error - post the error, file name and line number in the support forums and I should be able to work out why it's failing.

== Changelog ==

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