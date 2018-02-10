<?php 
global $options;
global $PolicyKeyErrors, $PolicyKeyWarnings;
?>
<table class="wpcsp-form-table">
    <tbody data-group="Expect-CT">
		<tr><th colspan='2'><h2><?php _e( "Expect-CT", 'wpcsp' ); ?></h2></th></tr>
		<tr><td colspan='2'><p>Instructs user agents (browsers) to expect valid Signed
						   Certificate Timestamps (SCTs) to be served.  When configured in enforcement mode, user agents (UAs) will
						   remember that hosts expect SCTs and will refuse connections that do
						   not conform to the UA's Certificate Transparency policy.  When
						   configured in report-only mode, UAs will report the lack of valid
						   SCTs but will allow the connection.</p>
						   <p>By turning on Expect-CT, web host operators can discover
						   misconfigurations in their Certificate Transparency deployments and
						   ensure that misissued certificates accepted by UAs are discoverable
						   in Certificate Transparency logs.</p>
			</td>
		</tr>
		<tr class='wpcsp_option_row'>
			<th scope="row"><label for="<?php echo WP_CSP::SETTINGS_OPTIONS_EXPECTCT_OPTIONS; ?>"><?php _e( "Mode", 'wpcsp' ); ?></label></th>
			<td class='wpcsp_option_cell'>
				<select name="<?php echo WP_CSP::SETTINGS_OPTIONS_ALLOPTIONS;?>[<?php echo WP_CSP::SETTINGS_OPTIONS_EXPECTCT_OPTIONS; ?>]" id="<?php echo WP_CSP::SETTINGS_OPTIONS_EXPECTCT_OPTIONS; ?>">
					<?php $selected = !empty( $options[ WP_CSP::SETTINGS_OPTIONS_EXPECTCT_OPTIONS ] ) ? $options[ WP_CSP::SETTINGS_OPTIONS_EXPECTCT_OPTIONS ] : '' ; ?>
					<option value="0" <?php selected( $selected, 0 ); ?> >Not in use</option>
					<option value="1" <?php selected( $selected, 1 ); ?> >Report only - do not enforce Expect CT</option>
					<option value="2" <?php selected( $selected, 2 ); ?> >Enforce Expect CT</option>
				</select>
				<div class='wpcsp_option_description'><?php _e( 'Enforce the Expect CT policy or treat it as report only.', 'wpcsp' ); ?></div>
				<?php if ( !empty( $PolicyKeyErrors[ WP_CSP::SETTINGS_OPTIONS_EXPECTCT_OPTIONS] )):?><div class='wpcsp_option_errors'><?php echo $PolicyKeyErrors[ WP_CSP::SETTINGS_OPTIONS_EXPECTCT_OPTIONS];?></div><?php endif; ?>
				<?php if ( !empty( $PolicyKeyWarnings[ WP_CSP::SETTINGS_OPTIONS_EXPECTCT_OPTIONS] )):?><div class='wpcsp_option_warnings'><?php echo $PolicyKeyWarnings[ WP_CSP::SETTINGS_OPTIONS_EXPECTCT_OPTIONS];?></div><?php endif; ?>
			</td>
		</tr>
		<tr class='wpcsp_option_row'>
			<th scope="row"><label for="<?php echo WP_CSP::SETTINGS_OPTIONS_CSP_MODE; ?>"><?php _e( "Maximum Age", 'wpcsp' ); ?></label></th>
			<td class='wpcsp_option_cell'>
				<select name="<?php echo WP_CSP::SETTINGS_OPTIONS_ALLOPTIONS;?>[<?php echo WP_CSP::SETTINGS_OPTIONS_EXPECTCT_MAXAGE; ?>]" id="<?php echo WP_CSP::SETTINGS_OPTIONS_EXPECTCT_MAXAGE; ?>">
				<?php $selected = !empty( $options[ WP_CSP::SETTINGS_OPTIONS_EXPECTCT_MAXAGE] ) ? $options[ WP_CSP::SETTINGS_OPTIONS_EXPECTCT_MAXAGE] : '' ; ?>
				<option value="0" <?php selected( $selected, 0 ); ?> >0</option>
				<option value="<?php echo HOUR_IN_SECONDS;?>" <?php selected( $selected, HOUR_IN_SECONDS); ?> >One Hour (<?php echo HOUR_IN_SECONDS. " seconds";?>)</option>
				<option value="<?php echo DAY_IN_SECONDS;?>" <?php selected( $selected, DAY_IN_SECONDS); ?> >One Day (<?php echo DAY_IN_SECONDS . " seconds";?>)</option>
				<option value="<?php echo WEEK_IN_SECONDS;?>" <?php selected( $selected, WEEK_IN_SECONDS); ?> >One Week (<?php echo WEEK_IN_SECONDS. " seconds";?>)</option>
				<option value="<?php echo MONTH_IN_SECONDS;?>" <?php selected( $selected, MONTH_IN_SECONDS); ?> >One Month (<?php echo MONTH_IN_SECONDS. " seconds";?>)</option>
				<option value="<?php echo YEAR_IN_SECONDS;?>" <?php selected( $selected, YEAR_IN_SECONDS); ?> >One Year (<?php echo YEAR_IN_SECONDS. " seconds";?>)</option>
				</select>
				<div class='wpcsp_option_description'><?php _e( 'Specifies the number of seconds that the browser should cache and apply the Expect CT policy for.', 'wpcsp' ); ?></div>
				<?php if ( !empty( $PolicyKeyErrors[ WP_CSP::SETTINGS_OPTIONS_EXPECTCT_MAXAGE] )):?><div class='wpcsp_option_errors'><?php echo $PolicyKeyErrors[ WP_CSP::SETTINGS_OPTIONS_EXPECTCT_MAXAGE];?></div><?php endif; ?>
				<?php if ( !empty( $PolicyKeyWarnings[ WP_CSP::SETTINGS_OPTIONS_EXPECTCT_MAXAGE] )):?><div class='wpcsp_option_warnings'><?php echo $PolicyKeyWarnings[ WP_CSP::SETTINGS_OPTIONS_EXPECTCT_MAXAGE];?></div><?php endif; ?>
			</td>
		</tr>
	</tbody>
	
    <tbody data-group="Strict-Transport-Security">
		<tr><th colspan='2'><h2><?php _e( "Strict Transport Security", 'wpcsp' ); ?></h2></th></tr>
		<tr><td colspan='2'><p>The HTTP Strict-Transport-Security response header (HSTS)  lets a web site tell browsers that it should only be accessed using HTTPS, instead of using HTTP.</p>
				<p>Options:</p>
				<ul>
					<li><strong>enable - no options</strong> - enable Strict-Transport-Security</li>
					<li><strong>enable - includeSubDomains</strong> - If this optional parameter is specified, this rule applies to all of the site's subdomains as well.</li>
					<li><strong>enable - preload</strong> - Not part of the specification. - Google maintains an HSTS preload service. By following the guidelines and successfully submitting your domain, browsers will never connect to your domain using an insecure connection.</li>
					<li><strong>enable - includeSubDomains; preload</strong> - Note - you can turn on "preload" and "includeSubDomains" at the same time, this could put you on the HSTS preload list which is a list of sites that are hardcoded into Chrome as being <strong>HTTPS only</strong>.
					If you want to be on this list make sure your HSTS max age setting is one year (31536000 seconds) (the option below).
					<strong>Make sure you expect this and will not go back to non-SSL version of your site.</strong> See <a href='https://hstspreload.org/' rel='external nofollow'>HSTS Preload website for more details.</a>
					</li>
				</ul> 
			</td>
		</tr>
		<tr class='wpcsp_option_row'>
			<th scope="row"><label for="<?php echo WP_CSP::SETTINGS_OPTIONS_STS_OPTIONS; ?>"><?php _e( "Mode", 'wpcsp' ); ?></label></th>
			<td class='wpcsp_option_cell'>
				<select name="<?php echo WP_CSP::SETTINGS_OPTIONS_ALLOPTIONS;?>[<?php echo WP_CSP::SETTINGS_OPTIONS_STS_OPTIONS; ?>]" id="<?php echo WP_CSP::SETTINGS_OPTIONS_STS_OPTIONS; ?>">
					<?php $selected = !empty( $options[ WP_CSP::SETTINGS_OPTIONS_STS_OPTIONS] ) ? $options[ WP_CSP::SETTINGS_OPTIONS_STS_OPTIONS] : '' ; ?>
					<option value="<?php echo WP_CSP::HSTS_NOT_IN_USE  ;?>" <?php selected( $selected, WP_CSP::HSTS_NOT_IN_USE); ?> >Not in use</option>
					<option value="<?php echo WP_CSP::HSTS_USE_NO_OPTIONS ;?>" <?php selected( $selected, WP_CSP::HSTS_USE_NO_OPTIONS); ?> >Use with no options</option>
					<option value="<?php echo WP_CSP::HSTS_SUBDOMAINS ;?>" <?php selected( $selected, WP_CSP::HSTS_SUBDOMAINS); ?> >Include Sub Domains</option>
					<option value="<?php echo WP_CSP::HSTS_PRELOAD ;?>" <?php selected( $selected, WP_CSP::HSTS_PRELOAD); ?> >Preload</option>
					<option value="<?php echo WP_CSP::HSTS_SUBDOMAINS_AND_PRELOAD ;?>" <?php selected( $selected, WP_CSP::HSTS_SUBDOMAINS_AND_PRELOAD); ?> >Include Sub Domains and Preload</option>
				</select>
				<?php if ( !empty( $PolicyKeyErrors[ WP_CSP::SETTINGS_OPTIONS_STS_OPTIONS] )):?><div class='wpcsp_option_errors'><?php echo $PolicyKeyErrors[ WP_CSP::SETTINGS_OPTIONS_STS_OPTIONS];?></div><?php endif; ?>
				<?php if ( !empty( $PolicyKeyWarnings[ WP_CSP::SETTINGS_OPTIONS_STS_OPTIONS] )):?><div class='wpcsp_option_warnings'><?php echo $PolicyKeyWarnings[ WP_CSP::SETTINGS_OPTIONS_STS_OPTIONS];?></div><?php endif; ?>
			</td>
		</tr>
		<tr class='wpcsp_option_row'>
			<th scope="row"><label for="<?php echo WP_CSP::SETTINGS_OPTIONS_STS_MAXAGE; ?>"><?php _e( "HSTS Maximum Age", 'wpcsp' ); ?></label></th>
			<td class='wpcsp_option_cell'>
				<select name="<?php echo WP_CSP::SETTINGS_OPTIONS_ALLOPTIONS;?>[<?php echo WP_CSP::SETTINGS_OPTIONS_STS_MAXAGE; ?>]" id="<?php echo WP_CSP::SETTINGS_OPTIONS_STS_MAXAGE; ?>">
					<?php $selected = !empty( $options[ WP_CSP::SETTINGS_OPTIONS_STS_MAXAGE] ) ? $options[ WP_CSP::SETTINGS_OPTIONS_STS_MAXAGE] : '' ; ?>
					<option value="0" <?php selected( $selected, 0 ); ?> >0 (Remove existing policy)</option>
					<option value="<?php echo HOUR_IN_SECONDS;?>" <?php selected( $selected, HOUR_IN_SECONDS); ?> >One Hour (<?php echo HOUR_IN_SECONDS. " seconds";?>)</option>
					<option value="<?php echo DAY_IN_SECONDS;?>" <?php selected( $selected, DAY_IN_SECONDS); ?> >One Day (<?php echo DAY_IN_SECONDS . " seconds";?>)</option>
					<option value="<?php echo WEEK_IN_SECONDS;?>" <?php selected( $selected, WEEK_IN_SECONDS); ?> >One Week (<?php echo WEEK_IN_SECONDS. " seconds";?>)</option>
					<option value="<?php echo MONTH_IN_SECONDS;?>" <?php selected( $selected, MONTH_IN_SECONDS); ?> >One Month (<?php echo MONTH_IN_SECONDS. " seconds";?>) - Recommended</option>
					<option value="<?php echo YEAR_IN_SECONDS;?>" <?php selected( $selected, YEAR_IN_SECONDS); ?> >One Year (<?php echo YEAR_IN_SECONDS. " seconds";?>)</option>
				</select>
				<div class='wpcsp_option_description'><?php _e( 'Specifies the number of seconds that the browser should cache and apply the Strict Transport Security policy for.', 'wpcsp' ); ?></div>
				<?php if ( !empty( $PolicyKeyErrors[ WP_CSP::SETTINGS_OPTIONS_STS_MAXAGE] )):?><div class='wpcsp_option_errors'><?php echo $PolicyKeyErrors[ WP_CSP::SETTINGS_OPTIONS_STS_MAXAGE];?></div><?php endif; ?>
				<?php if ( !empty( $PolicyKeyWarnings[ WP_CSP::SETTINGS_OPTIONS_STS_MAXAGE] )):?><div class='wpcsp_option_warnings'><?php echo $PolicyKeyWarnings[ WP_CSP::SETTINGS_OPTIONS_STS_MAXAGE];?></div><?php endif; ?>
			</td>
		</tr>
	</tbody>
	
	
    <tbody data-group="X-Frame-Options">
		<tr><th colspan='2'><h2><?php _e( "X-Frame-Options", 'wpcsp' ); ?></h2></th></tr>
		<tr><td colspan='2'>
				<p>The X-Frame-Options HTTP response header can be used to indicate whether or not a browser should be allowed to render a page in a &lt;frame&gt;, &lt;iframe&gt; or &lt;object&gt; . Sites can use this to avoid clickjacking attacks, by ensuring that their content is not embedded into other sites.</p>
				<p>Options:</p>
				<ul>
					<li><strong>DENY</strong> - The page cannot be displayed in a frame, regardless of the site attempting to do so.</li>
					<li><strong>SAMEORIGIN</strong> - The page can only be displayed in a frame on the same origin as the page itself. The spec leaves it up to browser vendors to decide whether this option applies to the top level, the parent, or the whole chain.</li>
					<li><strong>ALLOW-FROM</strong> - The page can only be displayed in a frame on the specified origin.</li>
				</ul> 
			</td>
		</tr>
		<tr class='wpcsp_option_row'>
			<th scope="row"><label for="<?php echo WP_CSP::SETTINGS_OPTIONS_FRAME_OPTIONS; ?>"><?php _e( "Mode", 'wpcsp' ); ?></label></th>
			<td class='wpcsp_option_cell'>
				<select name="<?php echo WP_CSP::SETTINGS_OPTIONS_ALLOPTIONS;?>[<?php echo WP_CSP::SETTINGS_OPTIONS_FRAME_OPTIONS; ?>]" id="<?php echo WP_CSP::SETTINGS_OPTIONS_FRAME_OPTIONS; ?>">
					<?php $selected = !empty( $options[ WP_CSP::SETTINGS_OPTIONS_FRAME_OPTIONS] ) ? $options[ WP_CSP::SETTINGS_OPTIONS_FRAME_OPTIONS] : '' ; ?>
					<option value="0" <?php selected( $selected, 0 ); ?> >Not in use</option>
					<option value="1" <?php selected( $selected, 1 ); ?> >DENY</option>
					<option value="2" <?php selected( $selected, 2 ); ?> >SAMEORIGIN</option>
					<option value="3" <?php selected( $selected, 3 ); ?> >ALLOW-FROM</option>
				</select>
				<?php if ( !empty( $PolicyKeyErrors[ WP_CSP::SETTINGS_OPTIONS_FRAME_OPTIONS] )):?><div class='wpcsp_option_errors'><?php echo $PolicyKeyErrors[ WP_CSP::SETTINGS_OPTIONS_FRAME_OPTIONS];?></div><?php endif; ?>
				<?php if ( !empty( $PolicyKeyWarnings[ WP_CSP::SETTINGS_OPTIONS_FRAME_OPTIONS] )):?><div class='wpcsp_option_warnings'><?php echo $PolicyKeyWarnings[ WP_CSP::SETTINGS_OPTIONS_FRAME_OPTIONS];?></div><?php endif; ?>
			</td>
		</tr>
		<tr class='wpcsp_option_row'>
			<th scope="row"><label for="<?php echo WP_CSP::SETTINGS_OPTIONS_FRAME_OPTIONS_ALLOW_FROM; ?>"><?php _e( "Allow From URL", 'wpcsp' ); ?></label></th>
			<td class='wpcsp_option_cell'>
				<?php $selected = !empty( $options[ WP_CSP::SETTINGS_OPTIONS_FRAME_OPTIONS_ALLOW_FROM] ) ? $options[ WP_CSP::SETTINGS_OPTIONS_FRAME_OPTIONS_ALLOW_FROM] : '' ; ?>
				<input name="<?php echo WP_CSP::SETTINGS_OPTIONS_ALLOPTIONS;?>[<?php echo WP_CSP::SETTINGS_OPTIONS_FRAME_OPTIONS_ALLOW_FROM; ?>]" id="<?php echo WP_CSP::SETTINGS_OPTIONS_FRAME_OPTIONS_ALLOW_FROM; ?>"
												type='text' value='<?php echo esc_attr($selected);?>' size='40' maxlength='255' />
				<div class='wpcsp_option_description'><?php _e( 'Only valid if "ALLOW-FROM" selected in the "Mode" option above.', 'wpcsp' ); ?></div>
				<?php if ( !empty( $PolicyKeyErrors[ WP_CSP::SETTINGS_OPTIONS_FRAME_OPTIONS_ALLOW_FROM ] )):?><div class='wpcsp_option_errors'><?php echo $PolicyKeyErrors[ WP_CSP::SETTINGS_OPTIONS_FRAME_OPTIONS_ALLOW_FROM ];?></div><?php endif; ?>
				<?php if ( !empty( $PolicyKeyWarnings[ WP_CSP::SETTINGS_OPTIONS_FRAME_OPTIONS_ALLOW_FROM ] )):?><div class='wpcsp_option_warnings'><?php echo $PolicyKeyWarnings[ WP_CSP::SETTINGS_OPTIONS_FRAME_OPTIONS_ALLOW_FROM ];?></div><?php endif; ?>
			</td>
		</tr>
	</tbody>
	
	
	<tbody data-group='X-XSS-Protection'>
		<tr><th colspan='2'><h2><?php _e( "X-XSS-Protection", 'wpcsp' ); ?></h2></th></tr>
		<tr><td colspan='2'>
				<p>The HTTP X-XSS-Protection response header is a feature of Internet Explorer, Chrome and Safari that stops pages from loading when they detect reflected cross-site scripting (XSS) attacks. Although these protections are largely unnecessary in modern browsers when sites implement a strong Content-Security-Policy that disables the use of inline JavaScript ('unsafe-inline'), they can still provide protections for users of older web browsers that don't yet support CSP.</p>
				<p>Options:</p><ul>
					<li><strong>0</strong> - Disable filtering.</li>
					<li><strong>1</strong> - Enables XSS filtering</li>
					<li><strong>1; mode=block</strong> - Enables XSS filtering. Rather than sanitizing the page, the browser will prevent rendering of the page if an attack is detected.</li>
					<li><strong>1; report=&lt;report-uri&gt;</strong> - Enables XSS filtering. If a cross-site scripting attack is detected, the browser will sanitize the page and report the violation. This uses the functionality of the CSP report-uri directive to send a report.</li>
					</ul> 
			</td>
		</tr>
		<tr class='wpcsp_option_row'>
			<th scope="row"><label for="<?php echo WP_CSP::SETTINGS_OPTIONS_XSS_PROTECTION; ?>"><?php _e( "Mode", 'wpcsp' ); ?></label></th>
			<td class='wpcsp_option_cell'>
				<select name="<?php echo WP_CSP::SETTINGS_OPTIONS_ALLOPTIONS;?>[<?php echo WP_CSP::SETTINGS_OPTIONS_XSS_PROTECTION; ?>]" id="<?php echo WP_CSP::SETTINGS_OPTIONS_XSS_PROTECTION; ?>">
					<?php $selected = !empty( $options[ WP_CSP::SETTINGS_OPTIONS_XSS_PROTECTION] ) ? $options[ WP_CSP::SETTINGS_OPTIONS_XSS_PROTECTION] : '' ; ?>
					<option value="0" <?php selected( $selected, 0 ); ?> >Not in use</option>
					<option value="1" <?php selected( $selected, 1 ); ?> >0 - Disable Filtering</option>
					<option value="2" <?php selected( $selected, 2 ); ?> >1 - Enable Filtering</option>
					<option value="3" <?php selected( $selected, 3 ); ?> >1; mode=block - Enable Filtering, block invalid requests</option>
					<option value="4" <?php selected( $selected, 4 ); ?> >1; report- Enable Filtering, block and report invalid requests</option>
				</select>
				<?php if ( !empty( $PolicyKeyErrors[ WP_CSP::SETTINGS_OPTIONS_XSS_PROTECTION] )):?><div class='wpcsp_option_errors'><?php echo $PolicyKeyErrors[ WP_CSP::SETTINGS_OPTIONS_XSS_PROTECTION];?></div><?php endif; ?>
				<?php if ( !empty( $PolicyKeyWarnings[ WP_CSP::SETTINGS_OPTIONS_XSS_PROTECTION] )):?><div class='wpcsp_option_warnings'><?php echo $PolicyKeyWarnings[ WP_CSP::SETTINGS_OPTIONS_XSS_PROTECTION];?></div><?php endif; ?>
			</td>
		</tr>
	</tbody>
	
	
	<tbody data-group='X-Content-Type-Options'>
		<tr><th colspan='2'><h2><?php _e( "X-Content-Type-Options", 'wpcsp' ); ?></h2></th></tr>
		<tr><td colspan='2'><p>The X-Content-Type-Options response HTTP header is a marker used by the server to indicate that the MIME types advertised in the Content-Type headers should not be changed and be followed. This allows to opt-out of MIME type sniffing, or, in other words, it is a way to say that the webmasters knew what they were doing.</p>
				<p>Options:</p>
				<ul>
				<li><strong>NOSNIFF</strong> - Blocks a request if the requested type is
											"style" and the MIME type is not "text/css", or
											"script" and the MIME type is not a JavaScript MIME type.</li>
					</ul>
			</td>
		</tr>
		<tr class='wpcsp_option_row'>
			<th scope="row"><label for="<?php echo WP_CSP::SETTINGS_OPTIONS_CONTENT_TYPE_OPTIONS; ?>"><?php _e( "Mode", 'wpcsp' ); ?></label></th>
			<td class='wpcsp_option_cell'>
				<select name="<?php echo WP_CSP::SETTINGS_OPTIONS_ALLOPTIONS;?>[<?php echo WP_CSP::SETTINGS_OPTIONS_CONTENT_TYPE_OPTIONS; ?>]" id="<?php echo WP_CSP::SETTINGS_OPTIONS_CONTENT_TYPE_OPTIONS; ?>">
					<?php $selected = !empty( $options[ WP_CSP::SETTINGS_OPTIONS_CONTENT_TYPE_OPTIONS] ) ? $options[ WP_CSP::SETTINGS_OPTIONS_CONTENT_TYPE_OPTIONS] : '' ; ?>
					<option value="0" <?php selected( $selected, 0 ); ?> >Not in use</option>
					<option value="1" <?php selected( $selected, 1 ); ?> >nosniff</option>
				</select>
				<?php if ( !empty( $PolicyKeyErrors[ WP_CSP::SETTINGS_OPTIONS_CONTENT_TYPE_OPTIONS] )):?><div class='wpcsp_option_errors'><?php echo $PolicyKeyErrors[ WP_CSP::SETTINGS_OPTIONS_CONTENT_TYPE_OPTIONS];?></div><?php endif; ?>
				<?php if ( !empty( $PolicyKeyWarnings[ WP_CSP::SETTINGS_OPTIONS_CONTENT_TYPE_OPTIONS] )):?><div class='wpcsp_option_warnings'><?php echo $PolicyKeyWarnings[ WP_CSP::SETTINGS_OPTIONS_CONTENT_TYPE_OPTIONS];?></div><?php endif; ?>
			</td>
		</tr>
	</tbody>
	
	
	
	<tbody data-group='Referrer-Policy'>
		<tr><th colspan='2'><h2><?php _e( "Referrer-Policy", 'wpcsp' ); ?></h2></th></tr>
		<tr><td colspan='2'>
				<p>The Referrer-Policy HTTP header governs which referrer information, sent in the Referer header, should be included with requests made.</p>
				<p>Options:</p>
				<ul>
					<li><strong>no-referrer</strong> - The Referer header will be omitted entirely. No referrer information is sent along with requests..</li>
					<li><strong>no-referrer-when-downgrade (default)</strong> - This is the user agent's default behavior if no policy is specified. The origin is sent as referrer to a-priori as-much-secure destination (HTTPS->HTTPS), but isn't sent to a less secure destination (HTTPS->HTTP).</li>
					<li><strong>origin</strong> - Only send the origin of the document as the referrer in all cases.
	The document https://example.com/page.html will send the referrer https://example.com/.</li>
					<li><strong>origin-when-cross-origin</strong> - Send a full URL when performing a same-origin request, but only send the origin of the document for other cases.</li>
					<li><strong>same-origin</strong> - A referrer will be sent for same-site origins, but cross-origin requests will contain no referrer information.</li>
					<li><strong>strict-origin</strong> - Only send the origin of the document as the referrer to a-priori as-much-secure destination (HTTPS->HTTPS), but don't send it to a less secure destination (HTTPS->HTTP).</li>
					<li><strong>strict-origin-when-cross-origin</strong> - Send a full URL when performing a same-origin request, only send the origin of the document to a-priori as-much-secure destination (HTTPS->HTTPS), and send no header to a less secure destination (HTTPS->HTTP).</li>
					<li><strong>unsafe-url</strong> - Send a full URL when performing a same-origin or cross-origin request.
	This policy will leak origins and paths from TLS-protected resources to insecure origins. Carefully consider the impact of this setting.</li>
					</ul> 
			</td>
		</tr>
		<tr class='wpcsp_option_row'>
			<th scope="row"><label for="<?php echo WP_CSP::SETTINGS_OPTIONS_REFERRER_POLICY_OPTIONS; ?>"><?php _e( "Mode", 'wpcsp' ); ?></label></th>
			<td class='wpcsp_option_cell'>
				<select name="<?php echo WP_CSP::SETTINGS_OPTIONS_ALLOPTIONS;?>[<?php echo WP_CSP::SETTINGS_OPTIONS_REFERRER_POLICY_OPTIONS; ?>]" id="<?php echo WP_CSP::SETTINGS_OPTIONS_REFERRER_POLICY_OPTIONS; ?>">
					<?php $selected = !empty( $options[ WP_CSP::SETTINGS_OPTIONS_REFERRER_POLICY_OPTIONS] ) ? $options[ WP_CSP::SETTINGS_OPTIONS_REFERRER_POLICY_OPTIONS] : '' ; ?>
					<option value="0" <?php selected( $selected, 0 ); ?> >Not in use</option>
					<option value="1" <?php selected( $selected, 1 ); ?> >no-referrer</option>
					<option value="2" <?php selected( $selected, 2 ); ?> >no-referrer-when-downgrade</option>
					<option value="3" <?php selected( $selected, 3 ); ?> >origin</option>
					<option value="4" <?php selected( $selected, 4 ); ?> >origin-when-cross-origin</option>
					<option value="5" <?php selected( $selected, 5 ); ?> >same-origingin</option>
					<option value="6" <?php selected( $selected, 6 ); ?> >strict-origin</option>
					<option value="7" <?php selected( $selected, 7 ); ?> >strict-origin-when-cross-origin</option>
					<option value="8" <?php selected( $selected, 8 ); ?> >unsafe-url (not recommended)</option>
				</select>
				<?php if ( !empty( $PolicyKeyErrors[ WP_CSP::SETTINGS_OPTIONS_REFERRER_POLICY_OPTIONS] )):?><div class='wpcsp_option_errors'><?php echo $PolicyKeyErrors[ WP_CSP::SETTINGS_OPTIONS_REFERRER_POLICY_OPTIONS];?></div><?php endif; ?>
				<?php if ( !empty( $PolicyKeyWarnings[ WP_CSP::SETTINGS_OPTIONS_REFERRER_POLICY_OPTIONS] )):?><div class='wpcsp_option_warnings'><?php echo $PolicyKeyWarnings[ WP_CSP::SETTINGS_OPTIONS_REFERRER_POLICY_OPTIONS];?></div><?php endif; ?>
			</td>
		</tr>
	</tbody>
</table>
<p>Most of these descriptions are taken from the Mozilla page <a href='https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers'>https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers</a></p>