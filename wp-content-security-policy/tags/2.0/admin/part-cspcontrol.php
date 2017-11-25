<?php 
global $options;
?>
<table class="wpcsp-form-table">
	<tr class='wpcsp_option_row'>
		<th scope="row"><?php _e( "CSP Mode", 'wpcsp' ); ?></th>
		<td class='wpcsp_option_cell'>
			<select name="<?php echo wpCSPclass::SETTINGS_OPTIONS_ALLOPTIONS;?>[<?php echo wpCSPclass::SETTINGS_OPTIONS_CSP_MODE; ?>]" id="<?php echo wpCSPclass::SETTINGS_OPTIONS_CSP_MODE; ?>">
			<?php $selected = $options[ wpCSPclass::SETTINGS_OPTIONS_CSP_MODE ]; ?>
			<option value="-1" <?php selected( $selected, -1 ); ?> >Not in use</option>
			<option value="0" <?php selected( $selected, 0 ); ?> >Enforce policies</option>
			<option value="1" <?php selected( $selected, 1 ); ?> >Report only - do not enforce policies</option>
			</select>
			<label class="wpcsp_option_description" for="<?php echo wpCSPclass::SETTINGS_OPTIONS_CSP_MODE; ?>"><?php _e( 'Toggles whether or not to run in report only mode or cause the browsers to enforce the security policy.', 'wpcsp' ); ?></label>
			<?php if ( !empty( $PolicyKeyErrors[ wpCSPclass::SETTINGS_OPTIONS_CSP_MODE])) :?><div class='wpcsp_option_errors'><?php echo $PolicyKeyErrors[ wpCSPclass::SETTINGS_OPTIONS_CSP_MODE];?></div><?php endif; ?>
		</td>
	</tr>
	<tr class='wpcsp_option_row'>
		<th scope="row"><?php _e( "Log violations", 'wpcsp' ); ?></th>
		<td class='wpcsp_option_cell'>
			<select name="<?php echo wpCSPclass::SETTINGS_OPTIONS_ALLOPTIONS;?>[<?php echo wpCSPclass::SETTINGS_OPTIONS_LOGVIOLATIONS; ?>]" id="<?php echo wpCSPclass::SETTINGS_OPTIONS_LOGVIOLATIONS; ?>">
				<?php $selected = $options[ wpCSPclass::SETTINGS_OPTIONS_LOGVIOLATIONS]; ?>
				<option value="<?php echo wpCSPclass::LOGVIOLATIONS_IGNORE; ?>" <?php selected( $selected, wpCSPclass::LOGVIOLATIONS_IGNORE ); ?> >No, ignore</option>
				<option value="<?php echo wpCSPclass::LOGVIOLATIONS_LOG_ALL; ?>" <?php selected( $selected, wpCSPclass::LOGVIOLATIONS_LOG_ALL ); ?> >Yes, log all</option>
				<option value="<?php echo wpCSPclass::LOGVIOLATIONS_LOG_10PERC; ?>" <?php selected( $selected, wpCSPclass::LOGVIOLATIONS_LOG_10PERC ); ?> >Yes, log for 10% of page loads</option>
				<option value="<?php echo wpCSPclass::LOGVIOLATIONS_LOG_1PERC; ?>" <?php selected( $selected, wpCSPclass::LOGVIOLATIONS_LOG_1PERC ); ?> >Yes, log for 1% of page loads</option>
				<option value="<?php echo wpCSPclass::LOGVIOLATIONS_LOG_POINT1PERC; ?>" <?php selected( $selected, wpCSPclass::LOGVIOLATIONS_LOG_POINT1PERC ); ?> >Yes, log for 0.1% of page loads</option>
			</select>
			<label class="wpcsp_option_description" for="<?php echo wpCSPclass::SETTINGS_OPTIONS_LOGVIOLATIONS; ?>"><?php _e( 'Whether to store the CSP violations or ignore them. Logging can be a system drain, you can lower the number of log entries by not logging errors on all page loads.', 'wpcsp' ); ?></label>
		</td>
	</tr>
	<tr class='wpcsp_option_row'>
		<th scope="row"><?php _e( "ReportURI - Report Only", 'wpcsp' ); ?></th>
		<td class='wpcsp_option_cell'>
			<?php $selected = $options[ wpCSPclass::SETTINGS_OPTIONS_REPORT_URI_REPORTONLY]; ?>
			<input name="<?php echo wpCSPclass::SETTINGS_OPTIONS_ALLOPTIONS;?>[<?php echo wpCSPclass::SETTINGS_OPTIONS_REPORT_URI_REPORTONLY; ?>]" id="<?php echo wpCSPclass::SETTINGS_OPTIONS_REPORT_URI_REPORTONLY; ?>"
				type='text' value='<?php echo esc_attr($selected);?>' size='80' maxlength='255' /><br />
			<label class="wpcsp_option_description" for="<?php echo wpCSPclass::SETTINGS_OPTIONS_LOGVIOLATIONS; ?>"><?php _e( "Leave blank to report violations to this server of fill in the URL of the server to receive your reports i.e. <a href='https://report-uri.com/'>https://report-uri.com/</a>", 'wpcsp' ); ?></label>
			<?php if ( !empty( $PolicyKeyErrors[ wpCSPclass::SETTINGS_OPTIONS_REPORT_URI_REPORTONLY])) :?><div class='wpcsp_option_errors'><?php echo $PolicyKeyErrors[ wpCSPclass::SETTINGS_OPTIONS_REPORT_URI ];?></div><?php endif; ?>
		</td>
	</tr>
	<tr class='wpcsp_option_row'>
		<th scope="row"><?php _e( "ReportURI - Enforce", 'wpcsp' ); ?></th>
		<td class='wpcsp_option_cell'>
			<?php $selected = $options[ wpCSPclass::SETTINGS_OPTIONS_REPORT_URI_ENFORCE]; ?>
			<input name="<?php echo wpCSPclass::SETTINGS_OPTIONS_ALLOPTIONS;?>[<?php echo wpCSPclass::SETTINGS_OPTIONS_REPORT_URI_ENFORCE; ?>]" id="<?php echo wpCSPclass::SETTINGS_OPTIONS_REPORT_URI_ENFORCE; ?>"
				type='text' value='<?php echo esc_attr($selected);?>' size='80' maxlength='255' /><br />
			<label class="wpcsp_option_description" for="<?php echo wpCSPclass::SETTINGS_OPTIONS_LOGVIOLATIONS; ?>"><?php _e( "Leave blank to report violations to this server of fill in the URL of the server to receive your reports i.e. <a href='https://report-uri.com/'>https://report-uri.com/</a>", 'wpcsp' ); ?></label>
			<?php if ( !empty( $PolicyKeyErrors[ wpCSPclass::SETTINGS_OPTIONS_REPORT_URI_ENFORCE])) :?><div class='wpcsp_option_errors'><?php echo $PolicyKeyErrors[ wpCSPclass::SETTINGS_OPTIONS_REPORT_URI_ENFORCE];?></div><?php endif; ?>
		</td>
	</tr>
</table>