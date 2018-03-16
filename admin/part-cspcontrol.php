<?php 
global $options;
?>
<table class="wpcsp-form-table">
	<tr class='wpcsp_option_row'>
		<th scope="row"><label for="<?php echo WP_CSP::SETTINGS_OPTIONS_CSP_MODE; ?>"><?php _e( "CSP Mode", 'wpcsp' ); ?></label></th>
		<td class='wpcsp_option_cell'>
			<select name="<?php echo WP_CSP::SETTINGS_OPTIONS_ALLOPTIONS;?>[<?php echo WP_CSP::SETTINGS_OPTIONS_CSP_MODE; ?>]" id="<?php echo WP_CSP::SETTINGS_OPTIONS_CSP_MODE; ?>">
			<?php $selected = isset( $options[ WP_CSP::SETTINGS_OPTIONS_CSP_MODE ] ) ? $options[ WP_CSP::SETTINGS_OPTIONS_CSP_MODE ]  : WP_CSP::CSP_MODE_DEFAULT; ?>
			<option value="<?php echo WP_CSP::CSP_NOTINUSE ; ?>" <?php selected( $selected, WP_CSP::CSP_NOTINUSE); ?> >Not in use</option>
			<option value="<?php echo WP_CSP::CSP_ENABLED_ENFORCE; ?>" <?php selected( $selected, WP_CSP::CSP_ENABLED_ENFORCE); ?> >Enforce policies</option>
			<option value="<?php echo WP_CSP::CSP_ENABLED_REPORTONLY; ?>" <?php selected( $selected, WP_CSP::CSP_ENABLED_REPORTONLY ); ?> >Report only - do not enforce policies</option>
			</select>
			<div class='wpcsp_option_description'><?php _e( 'Toggles whether or not to run in report only mode or cause the browsers to enforce the security policy.', 'wpcsp' ); ?></div>
			<?php if ( !empty( $PolicyKeyErrors[ WP_CSP::SETTINGS_OPTIONS_CSP_MODE])) :?><div class='wpcsp_option_errors'><?php echo $PolicyKeyErrors[ WP_CSP::SETTINGS_OPTIONS_CSP_MODE];?></div><?php endif; ?>
		</td>
	</tr>
	<tr class='wpcsp_option_row'>
		<th scope="row"><label for="<?php echo WP_CSP::SETTINGS_OPTIONS_LOGVIOLATIONS; ?>"><?php _e( "Log violations", 'wpcsp' ); ?></label></th>
		<td class='wpcsp_option_cell'>
			<select name="<?php echo WP_CSP::SETTINGS_OPTIONS_ALLOPTIONS;?>[<?php echo WP_CSP::SETTINGS_OPTIONS_LOGVIOLATIONS; ?>]" id="<?php echo WP_CSP::SETTINGS_OPTIONS_LOGVIOLATIONS; ?>">
				<?php $selected = !empty( $options[ WP_CSP::SETTINGS_OPTIONS_LOGVIOLATIONS] ) ? $options[ WP_CSP::SETTINGS_OPTIONS_LOGVIOLATIONS] : '' ; ?>
				<option value="<?php echo WP_CSP::LOGVIOLATIONS_IGNORE; ?>" <?php selected( $selected, WP_CSP::LOGVIOLATIONS_IGNORE ); ?> >No, ignore</option>
				<option value="<?php echo WP_CSP::LOGVIOLATIONS_LOG_ALL; ?>" <?php selected( $selected, WP_CSP::LOGVIOLATIONS_LOG_ALL ); ?> >Yes, log all</option>
				<option value="<?php echo WP_CSP::LOGVIOLATIONS_LOG_10PERC; ?>" <?php selected( $selected, WP_CSP::LOGVIOLATIONS_LOG_10PERC ); ?> >Yes, log for 10% of page loads</option>
				<option value="<?php echo WP_CSP::LOGVIOLATIONS_LOG_1PERC; ?>" <?php selected( $selected, WP_CSP::LOGVIOLATIONS_LOG_1PERC ); ?> >Yes, log for 1% of page loads</option>
				<option value="<?php echo WP_CSP::LOGVIOLATIONS_LOG_POINT1PERC; ?>" <?php selected( $selected, WP_CSP::LOGVIOLATIONS_LOG_POINT1PERC ); ?> >Yes, log for 0.1% of page loads</option>
			</select>
			<div class='wpcsp_option_description'><?php _e( 'Whether to store the CSP violations or ignore them. Logging can be a system drain, you can lower the number of log entries by not logging errors on all page loads.', 'wpcsp' ); ?></div>
		</td>
	</tr>
	<tr class='wpcsp_option_row'>
		<th scope="row"><label for="<?php echo WP_CSP::SETTINGS_OPTIONS_LOGVIOLATIONS; ?>"><?php _e( "ReportURI - Report Only", 'wpcsp' ); ?></label></th>
		<td class='wpcsp_option_cell'>
			<?php $selected = !empty( $options[ WP_CSP::SETTINGS_OPTIONS_REPORT_URI_REPORTONLY] ) ? $options[ WP_CSP::SETTINGS_OPTIONS_REPORT_URI_REPORTONLY] : '' ; ?>
			<input name="<?php echo WP_CSP::SETTINGS_OPTIONS_ALLOPTIONS;?>[<?php echo WP_CSP::SETTINGS_OPTIONS_REPORT_URI_REPORTONLY; ?>]" id="<?php echo WP_CSP::SETTINGS_OPTIONS_REPORT_URI_REPORTONLY; ?>"
				type='text' value='<?php echo esc_attr($selected);?>' size='80' maxlength='255' />
			<div class='wpcsp_option_description'><?php _e( "Leave blank to report violations to this server of fill in the URL of the server to receive your reports i.e. <a href='https://report-uri.com/'>https://report-uri.com/</a>", 'wpcsp' ); ?></div>
			<?php if ( !empty( $PolicyKeyErrors[ WP_CSP::SETTINGS_OPTIONS_REPORT_URI_REPORTONLY])) :?><div class='wpcsp_option_errors'><?php echo $PolicyKeyErrors[ WP_CSP::SETTINGS_OPTIONS_REPORT_URI ];?></div><?php endif; ?>
		</td>
	</tr>
	<tr class='wpcsp_option_row'>
		<th scope="row"><label for="<?php echo WP_CSP::SETTINGS_OPTIONS_LOGVIOLATIONS; ?>"><?php _e( "ReportURI - Enforce", 'wpcsp' ); ?></label></th>
		<td class='wpcsp_option_cell'>
			<?php $selected = !empty( $options[ WP_CSP::SETTINGS_OPTIONS_REPORT_URI_ENFORCE] ) ? $options[ WP_CSP::SETTINGS_OPTIONS_REPORT_URI_ENFORCE] : '' ; ?>
			<input name="<?php echo WP_CSP::SETTINGS_OPTIONS_ALLOPTIONS;?>[<?php echo WP_CSP::SETTINGS_OPTIONS_REPORT_URI_ENFORCE; ?>]" id="<?php echo WP_CSP::SETTINGS_OPTIONS_REPORT_URI_ENFORCE; ?>"
				type='text' value='<?php echo esc_attr($selected);?>' size='80' maxlength='255' />
			<div class='wpcsp_option_description'><?php _e( "Leave blank to report violations to this server of fill in the URL of the server to receive your reports i.e. <a href='https://report-uri.com/'>https://report-uri.com/</a>", 'wpcsp' ); ?></div>
			<?php if ( !empty( $PolicyKeyErrors[ WP_CSP::SETTINGS_OPTIONS_REPORT_URI_ENFORCE])) :?><div class='wpcsp_option_errors'><?php echo $PolicyKeyErrors[ WP_CSP::SETTINGS_OPTIONS_REPORT_URI_ENFORCE];?></div><?php endif; ?>
		</td>
	</tr>
</table>