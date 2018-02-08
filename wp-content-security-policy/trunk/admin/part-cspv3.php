<table>
	<tr class='wpcsp_v3_row'>
		<td class="">
<h3>CSP V3 Introduction</h3>

<p>With the advent of Content Security Policy version 3 the workings for CSP changed (note: this is the W3C Content Security Policy version 3, not the plugin's version).</p>
<ul>
<li>In CSP version 1 and 2 you have to declare each host name you trust individually, this works great for most sites; however, it can become an issue on sites that have a lot of advertising or other content and you can end up with dozens of sites with permissions.</li>
<li>In CSP version 3 you declare the scripts and styles that you trust using a 'nonce' (random string of characters, different to Wordpress Nonces), they then pass on the trust to whatever they do. Nonces change on 
<strong>every single page refresh</strong>.</li>
</ul>
<p>Ideally you would use CSP version 3; however, a lot of scripts do not work well with CSP version 3, so you might have to revert to using version 2 syntax for now.
Scripts that don't work with CSP version 3:</p>
<ol>
<li>Revolution Slider</li>
</ol>
<p>Let me know of any more with issues and I'll note them here.</p>

<h3>CSP Version 3</h3>

<p>Version 3 uses 'nonce's to indicate which scripts and styles you trust to run on your site. When you set 'strict-dynamic' as your policy the plugin will:</p>
<ol>
<li>Automatically generate a valid nonce for use by the plugin and by your code.</li>
<li>Automatically add the correct nonce to your CSP policy header.</li>
<li>Allow manual tagging of scripts/styles through additional functionality.</li>
</ol>
<h3>CSP v3 Additional Nonce Tagging</h3>

<p>There are four additional ways to add the nonce to your code:</p>
<ol>
<li>Add your included script or stylesheet to the header or footer and the code will be tagged automatically (use wp_enqueue_scripts/wp_enqueue_style). </li>
<li>
If you use get_template_part() you can tag these through add_action too:<br>
&lt;?php <br>
add_action('wp_footer',function() {<br>
	get_template_part( 'track/part', 'trackfooter' );<br>
	get_template_part( 'track/part', 'anothertracker' );<br>
	get_template_part( 'track/part', 'paidads' );<br>
});<br>
wp_footer(); ?&gt;<br>
</li>
<li>Have WP_CSP add the tagging automatically through output buffer capturing. i.e.<br>
WP_CSP::ob_start();<br>
My scripts and styles<br>
WP_CSP::ob_end_flush();<br>
</li>
<li>Send the string through the WP_CSP auto tagging function:<br>
$content = do_shortcode('[rev_slider alias="homepage"]');<br>
echo WP_CSP::tag_string($content);<br>
</li>

<li>Add the nonce by hand:<br>
&lt;script async defer data-pin-hover="true" data-pin-round="true" data-pin-save="false" src="//assets.pinterest.com/js/pinit.js" <strong>&lt;?php if ( class_exists ('WP_CSP') ) { echo "nonce='".WP_CSP::getNonce() . "' "; } ?&gt;</strong>&gt;&lt;/script&gt;
</ol>
<h3>CSP v3 Inline Scripts/Styles and Untaggable Code</h3>

<p>Inline scripts and styles can be dangerous, you do not know which scripts wrote them and probably don't want them run if you can avoid it. 
When you use 'script-dynamic', the "unsafe-eval" and "unsafe-inline stop working and the browser will say in the console (your browser's developer tools console) "Note that 'unsafe-inline' is ignored if either a hash or nonce value is present in the source list."</p>

<p>To fix this either:</p>
<ol>
<li>Put all the scripts and/or style code into files and include the files. The include statements can be tagged.</li>
<li>If the browser returns "Either the 'unsafe-inline' keyword, a hash (**'sha256-h3SEZNZpOYg4jp6TCkoWN7Z477Qt3q1owH0SPbz+a4M='**), or a nonce ('nonce-...') is required to enable inline execution." - you can take the SHA number (including single quotes) and put that in the policy line.</li>
</ol>
<p>At the time of writing this section (2018/2/5) browsers do not report the SHA code in their error report to the server so you will have to add this by hand.</p>
		
<h3>How to Convert</h3>
<p>Note - the following is from the W3C website. If you are converting then this plugin will add the nonce code for you; therefore, your CSP setting for both "script-src" or "default-src" should be (keep single quotes): 
'unsafe-inline' https: 'nonce-abcdefg' 'strict-dynamic'</p>
<p>The first change allows you to deploy "'strict-dynamic' in a backwards compatible way, without requiring user-agent sniffing: the policy 'unsafe-inline' https: 'nonce-abcdefg' 'strict-dynamic' will act like 'unsafe-inline' https: in browsers that support CSP1, https: 'nonce-abcdefg' in browsers that support CSP2, and 'nonce-abcdefg' 'strict-dynamic' in browsers that support CSP3.</p>
<p>The second allows scripts which are given access to the page via nonces or hashes to bring in their dependencies without adding them explicitly to the page's policy.</p>
		</td>
	</tr>
	
	<tr class='wpcsp_v3_row'>
		<td>
		<input type="button"  class="btnWPCSP btnWPCSPConvertToV3" value="Convert script-src to CSP V3"  data-target='#btnWPCSPConvertToV3Output' />
		<div id='btnWPCSPConvertToV3Output'></div>
		</td>
	</tr>
</table>