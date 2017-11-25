<?php 
global $options;
?>
<table class="wpcsp-form-table">
	<tr class='wpcsp_option_row'>
		<th scope="row"><?php _e( "Mixed Content", 'wpcsp' ); ?></th>
		<td class='wpcsp_option_cell'>
			<select name="<?php echo wpCSPclass::SETTINGS_OPTIONS_ALLOPTIONS;?>[<?php echo wpCSPclass::SETTINGS_OPTIONS_MIXED_CONTENT; ?>]" id="<?php echo wpCSPclass::SETTINGS_OPTIONS_MIXED_CONTENT; ?>">
				<?php $selected = $options[ wpCSPclass::SETTINGS_OPTIONS_MIXED_CONTENT]; ?>
				<option value="" <?php selected( $selected, ""); ?> >None</option>
				<option value="<?php echo wpCSPclass::BLOCK_ALL_MIXED_CONTENT; ?>" <?php selected( $selected, wpCSPclass::BLOCK_ALL_MIXED_CONTENT); ?> >Block Mixed Content</option>
				<option value="<?php echo wpCSPclass::UPGRADE_INSECURE_REQUESTS; ?>" <?php selected( $selected, wpCSPclass::UPGRADE_INSECURE_REQUESTS); ?> >Upgrade Insecure Requests</option>
			</select>
			<label class="wpcsp_option_description" for="<?php echo wpCSPclass::SETTINGS_OPTIONS_LOGVIOLATIONS; ?>"><?php _e( "Block Mixed Content - All mixed content resource requests are blocked, including both active and passive mixed content. This also applies to &lt;iframe&gt; documents, ensuring the entire page is mixed content free.<br>upgrade-insecure-requests directive instructs user agents to treat all of a site's insecure URLs (those served over HTTP) as though they have been replaced with secure URLs (those served over HTTPS).", 'wpcsp' ); ?></label>
		</td>
	</tr>
	
	<tr class='wpcsp_option_row'>
		<th scope="row"><?php _e( "Policy Entries", 'wpcsp' ); ?></th>
		<td><p>Content Security Policy allows the following entries - one per line:</p>
			<table>
				<tr><td>*</td><td>Allow Anything (try to avoid)</td></tr>
				<tr><td>'none'</td><td>Allow nothing. The single quotes are required.</td></tr>
				<tr><td>'self'</td><td>Allow from the same domain (scheme and host) only. The single quotes are required.</td></tr>
				<tr><td>'unsafe-inline'</td><td>Allow use of inline source elements - scripts, fonts, etc. The single quotes are required.</td></tr>
				<tr><td>'unsafe-eval'</td><td>Allow unsafe execution of evaluated javascript code. The single quotes are required.</td></tr>
				<tr><td>'strict-dynamic'</td><td>The trust explicitly given to a script present in the markup, by accompanying it with a nonce or a hash, shall be propagated to all the scripts loaded by that root script. The single quotes are required.</td></tr>
				<tr><td>data:</td><td>Allow loading resource from data scheme. <strong>This is insecure</strong>; an attacker can also inject arbitrary data: URIs. Use this sparingly and definitely not for scripts.</td></tr>
				<tr><td>mediastream:</td><td>Allows mediastream: URIs to be used as a content source.</td></tr>
				<tr><td>filesystem:</td><td>Allow loading resource from file system</td></tr>
				<tr><td>https:</td><td>Allow loading resource over a secure connection from any domain (block insecure content)</td></tr>
				<tr><td>domain.example.com</td><td>Allow loading resource from this specific domain, any scheme</td></tr>
				<tr><td>*.example.com</td><td>Allow loading resource from any subdomain of the specified domain</td></tr>
				<tr><td>http://domain.example.com</td><td>Allow loading resource from this specific domain and this scheme</td></tr>
			</table>
		</td>
	</tr>
	<?php 
	foreach( wpCSPclass::$CSP_Policies as $PolicyKey => $CSPPolicy) :
		$selected = !empty( $options[ $PolicyKey ] ) ? $options[ $PolicyKey ] : '' ;
		$CSPOptions = wpCSPclass::CleanPolicyOptionText( $selected ) ;
		$selected = implode( PHP_EOL, array_unique( $CSPOptions ) ) ;
		$RowsToDisplay = count( array_unique( $CSPOptions ) ) + 1 ;
		if ( $RowsToDisplay < 3 ) {
			$RowsToDisplay = 3 ;
		}
		?>
		<tr class='wpcsp_option_row'>
			<th scope="row"><?php _e( $CSPPolicy['label'], 'wpcsp' ); ?></th>
			<td class='wpcsp_option_cell'><a name='anchor<?php echo $PolicyKey;?>'></a>
				<textarea name="<?php echo wpCSPclass::SETTINGS_OPTIONS_ALLOPTIONS;?>[<?php echo $PolicyKey;?>]" id="<?php echo $PolicyKey;?>" rows="<?php echo intval( $RowsToDisplay ) ;?>"><?php echo $selected;?></textarea><br />
				<label class="wpcsp_option_description" for="<?php echo $PolicyKey;?>" name='label<?php echo $PolicyKey; ?>'><?php esc_html( _e( $CSPPolicy['description'], 'wpcsp' ) ) ; ?></label>
				<?php if ( !empty( $PolicyKeyErrors[ $PolicyKey ])) :?><div class='wpcsp_option_errors'><?php echo $PolicyKeyErrors[ $PolicyKey ];?></div><?php endif; ?>
			</td>
		</tr>
		<?php 
	endforeach; ?>
	<tr class='wpcsp_option_row'>
		<th scope="row"><?php _e( 'URLs to Ignore', 'wpcsp' ); ?></th>
		<td class='wpcsp_option_cell'>
			<?php 
			$selected = !empty( $options[ wpCSPclass::SETTINGS_OPTIONS_VIOLATIONSTOIGNORE ] ) ? $options[ wpCSPclass::SETTINGS_OPTIONS_VIOLATIONSTOIGNORE ] : '';
			$CSPOptions = wpCSPclass::CleanPolicyOptionText( $selected ) ;
			$selected = implode( PHP_EOL, array_unique( $CSPOptions ) ) ;
			?>
			<textarea name="<?php echo wpCSPclass::SETTINGS_OPTIONS_ALLOPTIONS;?>[<?php echo wpCSPclass::SETTINGS_OPTIONS_VIOLATIONSTOIGNORE;?>]" id="<?php echo wpCSPclass::SETTINGS_OPTIONS_VIOLATIONSTOIGNORE;?>"><?php echo $selected;?></textarea><br />
			<label class="wpcsp_option_description" for="<?php echo wpCSPclass::SETTINGS_OPTIONS_VIOLATIONSTOIGNORE;?>"><?php _e( 'Ignore violations from these URLs', 'wpcsp' ); ?></label>
			<?php if ( !empty( $PolicyKeyErrors[ 'URLSToIgnore'])) :?><div class='wpcsp_option_errors'><?php echo $PolicyKeyErrors[ 'URLSToIgnore'];?></li></ul></div><?php endif; ?>
		</td>
	</tr>
	
	<tr class='wpcsp_option_row'>
		<th scope="row"><?php _e( 'Sandbox', 'wpcsp' ); ?></th>
		<td class='wpcsp_option_cell'>
			<?php 
			$SandboxOptions = array( 
									wpCSPclass::SETTINGS_OPTIONS_SANDBOX_NOTSET => 'Not Set' ,
									wpCSPclass::SETTINGS_OPTIONS_SANDBOX_BLANKENTRY => 'Most Restrictive Sandbox' , // pseudo element I made up.
									"allow-forms" => 'allow-forms' ,
									"allow-pointer-lock" => 'allow-pointer-lock' ,
									"allow-popups" => 'allow-popups' ,
									"allow-same-origin" => 'allow-same-origin' ,
									"allow-scripts" => 'allow-scripts' ,
									"allow-top-navigation" => 'allow-top-navigation' , ) ;
			?>
			<select name="<?php echo wpCSPclass::SETTINGS_OPTIONS_ALLOPTIONS;?>[<?php echo wpCSPclass::SETTINGS_OPTIONS_SANDBOX; ?>][]" 
			 		id="<?php echo wpCSPclass::SETTINGS_OPTIONS_SANDBOX; ?>" class='wpcsp-selectpolicysandbox'multiple="multiple" size="7">
			<?php 
			$CurrentOptions = !empty( $options[ wpCSPclass::SETTINGS_OPTIONS_SANDBOX ] ) ? $options[ wpCSPclass::SETTINGS_OPTIONS_SANDBOX ] : '';
			foreach( $SandboxOptions as $key => $option ) :
				if ( is_array( $CurrentOptions )) {
					$selected = in_array( $key, $CurrentOptions ) ? ' selected="selected" ' : '' ;
				}
				else{
					$selected = $key == '' ? ' selected="selected" ' : '' ;
				}?>
				<option value="<?php echo $key; ?>" <?php echo $selected; ?> ><?php echo $option; ?></option>
			<?php endforeach; ?>
			</select> 
			<label class="wpcsp_option_description" for="<?php echo wpCSPclass::SETTINGS_OPTIONS_SANDBOX;?>"><?php _e( "HTML5 defines a sandbox attribute for iframe elements, intended to allow web authors to reduce the risk of including potentially untrusted content by imposing restrictions on that content's abilities. When the attribute is set, the content is forced into a unique origin, prevented from submitting forms, running script, creating or navigating other browsing contexts, and prevented from running plugins. These restrictions can be loosened by setting certain flags as the attribute's value.", 'wpcsp' ); ?></label>
		</td>
	</tr>
</table>