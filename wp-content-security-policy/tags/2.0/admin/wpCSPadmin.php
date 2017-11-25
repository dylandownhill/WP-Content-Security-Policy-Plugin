<?php
if(!empty($_SERVER['SCRIPT_FILENAME']) && basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])){
	die('You can not access this page directly!');
}

class wpCSPAdmin extends WP_REST_Controller{
	
	/**
	 * Nearly all the constants are in the wpCSPclass class, because that class needs a bunch of the settings, but not access to wpCSPAdmin
	 */
	const wpCSPDBVersionOptionName = 'wpcsp-dbVersion';
	const wpCSPDBVersion = '1.1';
	const wpCSPDBCronJobName = 'wpcsp-DBDailyMaintenance';
	
	/**
	 * Check the user can access the function.
	 *
	 * @param  WP_REST_Request  $request The current request object.
	 * @return WP_Error|boolean
	 */
	public static function permissions_check_edit_posts(  $request  ) {
		return current_user_can( 'edit_posts' );
	}
	
	
	/**
	 * Sanitize a request argument based on details registered to the route.
	 *
	 * @param  mixed            $value   Value of the &#039;filter&#039; argument.
	 * @param  WP_REST_Request  $request The current request object.
	 * @param  string           $param   Key of the parameter. In this case it is &#039;filter&#039;.
	 * @return WP_Error|boolean
	 */
	public static function data_arg_sanitize_string_callback( $value, $request, $param ) {
		// It is as simple as returning the sanitized value.
		return sanitize_text_field( $value );
	}
	
	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {
		register_rest_route( wpCSPclass::ROUTE_NAMESPACE , '/' . wpCSPclass::ROUTE_BASE. '/RestAdmin',
				array(
						'methods'         => WP_REST_Server::CREATABLE,
						'callback'        => array( __CLASS__, 'RestAdmin' ),
						'permission_callback' => array( __CLASS__, 'permissions_check_edit_posts' ),
						'args'            => array(
								'subaction' => array(
										'required' => true,
										'type' => 'string',
										"enum" => array( 'getdata', 'addSafeDomain', 'addIgnoreDomain', 'clearLogFile', 'TestURLChecker' ),
										'description' => '',
								),
								'violateddirective' => array(
										'required' => false,
										'type' => 'string',
										"sanitize_callback" => array( __CLASS__, 'data_arg_sanitize_string_callback' ),
										'description' => '',
								),
								'blockeduri' => array(
										'required' => false,
										'type' => 'string',
										"sanitize_callback" => array( __CLASS__, 'data_arg_sanitize_string_callback' ),
										'description' => '',
								),
								'scheme' => array(
										'required' => false,
										'type' => 'string',
										"sanitize_callback" => array( __CLASS__, 'data_arg_sanitize_string_callback' ),
										'description' => '',
								),
								'domain' => array(
										'required' => false,
										'type' => 'string',
										"sanitize_callback" => array( __CLASS__, 'data_arg_sanitize_string_callback' ),
										'description' => '',
								),
								'path' => array(
										'required' => false,
										'type' => 'string',
										"sanitize_callback" => array( __CLASS__, 'data_arg_sanitize_string_callback' ),
										'description' => '',
								),
								'file' => array(
										'required' => false,
										'type' => 'string',
										"sanitize_callback" => array( __CLASS__, 'data_arg_sanitize_string_callback' ),
										'description' => '',
								),
						),
				) );
	}
	
	/**
	 * register the hooks and other initialization routines.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_options_submenu_page'));
		add_action( 'admin_init', array( __CLASS__, 'register_settings'));
		add_action( 'admin_enqueue_scripts', array(__CLASS__,'add_styles_and_scripts')  );
		
		add_action( 'plugins_loaded', array(__CLASS__,'update_database') );
		add_action( 'wpCSPAdmin_daily_event',  array(__CLASS__,'daily_maintenance')  );
		
		wp_enqueue_script('jquery-ui-core', array( 'jquery' ));
		wp_enqueue_script('jquery-ui-tabs', array( 'jquery-ui-core' ));
		wp_enqueue_style( 'jquery-ui', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.1/themes/base/jquery-ui.css');
		
		register_uninstall_hook(__FILE__, array(__CLASS__,"plugin_uninstall") );
	}
	
	
	public static function add_styles_and_scripts() {
		wp_register_script( 'wpcspadmin', plugins_url( '../js/wpCSPadmin.js', __FILE__ ), array( 'jquery' ),false,true );
		wp_enqueue_style('wpcspadmin', plugins_url( '../css/wpCSPadmin.css', __FILE__ ) );
		
		wp_enqueue_script( 'wpcspadmin' );
		
		$Data = array(
				'restAdminURL' => "/wp-json/" . wpCSPclass::ROUTE_NAMESPACE . "/" . wpCSPclass::ROUTE_BASE . "/RestAdmin" ,
				'restAdminNonce' => wp_create_nonce( "wp_rest" ),
		) ;
		
		wp_localize_script( 'wpcspadmin', 'WPCSP', $Data );
	}
	
	/**
	 * Add an admin submenu link under Settings
	 */
	public static function add_options_submenu_page() {
		add_submenu_page(
				'options-general.php',          // admin page slug - under settings entry
				__( 'WP Content Security Policy', 'wpcsp' ), // page title
				__( 'Content Security Policy Options', 'wpcsp' ), // menu title
				'manage_options',               // capability required to see the page
				'wpcsp_options',                // admin page slug, e.g. options-general.php?page=wporg_options
				array( __CLASS__, 'options_page')            // callback function to display the options page
				);
		add_submenu_page(
				'options-general.php',          // admin page slug - under settings entry
				__( 'WP Content Security Log', 'wpcsp' ), // page title
				__( 'Content Security Policy Log', 'wpcsp' ), // menu title
				'manage_options',               // capability required to see the page
				'wpcsp_log',                // admin page slug, e.g. options-general.php?page=wporg_options
				array( __CLASS__, 'log_page')            // callback function to display the options page
				);
	}
	
	
	
	
	/**
	 * Build the options page
	 */
	public static function options_page() {
		// Make sure the database table exists.
		self::update_database() ;
		global $options;
	    $options = get_option( wpCSPclass::SETTINGS_OPTIONS_ALLOPTIONS );
	    
	    // Go through the options looking for errors.
	    $PolicyKeyErrors = array() ;
	    $ErrorOutput = array() ;
		foreach( wpCSPclass::$CSP_Policies as $PolicyKey => $CSPPolicy) :
			$selected = !empty( $options[ $PolicyKey ] ) ? $options[ $PolicyKey ] : '' ;
			$CSPOptions = wpCSPclass::CleanPolicyOptionText( $selected ) ;
			$Errors = self::FindCSPErrors( $PolicyKey, $CSPOptions );
			if ( !empty( $Errors )) {
				$PolicyKeyErrors[ $PolicyKey ] = "<ul><li>". implode("</li><li>",$Errors) . "</li></ul>";
				$ErrorOutput[] = "<tr><td><a href='#anchor". $PolicyKey ."'>".$PolicyKey."</a></td><td>".$PolicyKeyErrors[ $PolicyKey ]."</td></tr>" ;
			}
		endforeach;
		
		$selected = !empty( $options[ wpCSPclass::SETTINGS_OPTIONS_VIOLATIONSTOIGNORE ] ) ? $options[ wpCSPclass::SETTINGS_OPTIONS_VIOLATIONSTOIGNORE ] : '';
		$CSPOptions = wpCSPclass::CleanPolicyOptionText( $selected ) ;
		$Errors = self::FindCSPErrors( 'URLSToIgnore', $CSPOptions );
		if ( !empty( $Errors )) {
			$PolicyKeyErrors[ 'URLSToIgnore'] = "<ul><li>". implode("</li><li>",$Errors) . "</li></ul>";
			$ErrorOutput[] = "<tr><td><a href='#anchor". $PolicyKey ."'>".$PolicyKey."</a></td><td>".$PolicyKeyErrors[ 'URLSToIgnore' ]."</td></tr>" ;
		}
		
		if ( !empty( $options[ wpCSPclass::SETTINGS_OPTIONS_REPORT_URI_REPORTONLY] ) ) {
			$ReportURI = $options[ wpCSPclass::SETTINGS_OPTIONS_REPORT_URI_REPORTONLY] ;
			if ( !filter_var($ReportURI, FILTER_VALIDATE_URL)) {
				$PolicyKeyErrors[ wpCSPclass::SETTINGS_OPTIONS_REPORT_URI_REPORTONLY] = "REPORT-URI url invalid: $ReportURI";
				$ErrorOutput[] = "<tr><td>REPORT-URI - Report Only</td><td>".$PolicyKeyErrors[ wpCSPclass::SETTINGS_OPTIONS_REPORT_URI_REPORTONLY ]."</td></tr>" ;
			}
		}
		if ( !empty( $options[ wpCSPclass::SETTINGS_OPTIONS_REPORT_URI_ENFORCE] ) ) {
			$ReportURI = $options[ wpCSPclass::SETTINGS_OPTIONS_REPORT_URI_ENFORCE] ;
			if ( !filter_var($ReportURI, FILTER_VALIDATE_URL)) {
				$PolicyKeyErrors[ wpCSPclass::SETTINGS_OPTIONS_REPORT_URI_ENFORCE] = "REPORT-URI url invalid: $ReportURI";
				$ErrorOutput[] = "<tr><td>REPORT-URI - Enforce</td><td>".$PolicyKeyErrors[ wpCSPclass::SETTINGS_OPTIONS_REPORT_URI_ENFORCE]."</td></tr>" ;
			}
		}
		
		if ( isset( $options[ wpCSPclass::SETTINGS_OPTIONS_FRAME_OPTIONS] ) && 3 == $options[ wpCSPclass::SETTINGS_OPTIONS_FRAME_OPTIONS] ) {
			$AllowFromURL = $options[ wpCSPclass::SETTINGS_OPTIONS_FRAME_OPTIONS_ALLOW_FROM ] ;
			if ( !filter_var($AllowFromURL, FILTER_VALIDATE_URL)) {
				$PolicyKeyErrors[ wpCSPclass::SETTINGS_OPTIONS_FRAME_OPTIONS_ALLOW_FROM ] = "ALLOW-FROM url invalid: $AllowFromURL";
				$ErrorOutput[] = "<tr><td>X-Frame-Options</td><td>".$PolicyKeyErrors[ wpCSPclass::SETTINGS_OPTIONS_FRAME_OPTIONS_ALLOW_FROM ]."</td></tr>" ;
			}
		}
		$selected = isset( $options[ wpCSPclass::SETTINGS_OPTIONS_CSP_MODE ] ) ? $options[ wpCSPclass::SETTINGS_OPTIONS_CSP_MODE ] : '' ;
		if ( $selected == '' || $selected == -1 ){
			
			$PolicyKeyErrors[ wpCSPclass::SETTINGS_OPTIONS_CSP_MODE ] = "CSP is currently turned off";
			$ErrorOutput[] = "<tr><td>CSP Mode</td><td>".$PolicyKeyErrors[ wpCSPclass::SETTINGS_OPTIONS_CSP_MODE]."</td></tr>" ;
		}
		?>
		<div class="wrap">
			<div class="wpcsp-wpcspadmin wpcsp-optionsadmin">
	          
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	      
			<?php if ( !empty( $_REQUEST['settings-updated'] ) ) : ?>
				<div class="updated fade"><p><strong><?php _e( 'Content Security Policy Options saved!', 'wpcsp' ); ?></strong></p></div>
			<?php endif; ?>
			
			<?php if ( !empty( $ErrorOutput )): ?>
				<div class="updated fade">
					<table class='wpcsp_option_errors'>
						<thead><tr><td colspan='2'>Errors found in configuration:</td></tr></thead>
						<tbody><?php echo implode("",$ErrorOutput);?></tbody>
					</table>
				</div>
			<?php endif; ?>
          
			<form method="post" action="options.php">
				<?php settings_fields( wpCSPclass::SETTINGS_OPTIONS_SECTION ); // Outputs nonces and other necessary items?>
	                                       
				<div id="wpcsp_tabsAdmin" class='wpcsp_tabsAdmin'>
					<ul>
						<li><a href="#wpcsp_tabsAdmin_Control">CSP Control</a></li>
						<li><a href="#wpcsp_tabsAdmin_CSP">Content Security Policies</a></li>
						<li><a href="#wpcsp_tabsAdmin_Headers">Headers</a></li>
						<li><a href="#wpcsp_tabsAdmin_Test">Test</a></li>
					</ul>
					<div id='wpcsp_tabsAdmin_Control'>
						<?php include('part-cspcontrol.php'); ?>
						<?php include('part-cspsavechanges.php'); ?>
					</div>
					<div id='wpcsp_tabsAdmin_CSP'>
						<?php include('part-cspoptions.php'); ?>
						<?php include('part-cspsavechanges.php'); ?>
					</div>
					<div id='wpcsp_tabsAdmin_Headers'>
						<?php include('part-cspheaders.php'); ?>
						<?php include('part-cspsavechanges.php'); ?>
					</div>
					<div id='wpcsp_tabsAdmin_Test'>
						<?php include('part-csptest.php'); ?>
					</div>
	          </div>
			</form>
			</div>
     	</div> <?php // end of class=wrap ?>
     <?php 
	}
	

	/**
	 * Build the Log page
	 */
	public static function log_page() {
		global $wpdb;
		// Make sure the database table exists.
		self::update_database() ;
		
		// Options as entered by the site admin.
		$CSPOptions = get_option( wpCSPclass::SETTINGS_OPTIONS_ALLOPTIONS );
		
		// Get some display information for the user.
		$LogTableName = wpCSPclass::LogTableName();
		$SinceDate = $wpdb->get_var( "select min( CreatedOn ) from " . $LogTableName );
		$rows = $wpdb->get_results( "select violated_directive, blocked_uri, count( * ) as numerrors from ".$LogTableName." WHERE 1 group by violated_directive,blocked_uri order by numerrors DESC limit 100" );
		$Counter = 0 ;
		?>
	     <div class="wrap">
	     	<div class="wpcsp-wpcspadmin wpcsp-logadmin">
	 
		          <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		           <?php $Target = "WPCSPTargetRow" . $Counter++ ;?>
		          <p data-target='#<?php echo $Target ;?>'>Errors received since <?php echo $SinceDate; ?>. <input type="button" class="button-primary btnWPCSPClearLogFile" value="<?php _e('Clear Log File','wpcsp') ?>" /></p>
		          <p class='pWPCSPViewErrors WPCSPHiddenEntry' id='<?php echo $Target;?>'></p>
		          <table class='wpcsp-logoferrors'>
	          <thead>
	          	<tr><td class='tdWPCSPViolatedDirective'>Violated Directive</td>
	          		<td class='tdWPCSPBlockedURL'>Blocked URL</td>
	          		<td class='tdWPCSPNumErrors'>Count</td>
	          		<td class='tdWPCSPActionButtons'>Action</td></tr>
	          </thead>
	          <tbody>
	          <?php 
				foreach ($rows as $obj) :
				// Check if we have ignored or allowed the URL since the violation was logged.
					$IsURIIgnored = wpCSPclass::IsURIInOptionString( $obj->blocked_uri , $CSPOptions[ wpCSPclass::SETTINGS_OPTIONS_VIOLATIONSTOIGNORE ] ) ;
					if ( !empty( $CSPOptions[ $obj->violated_directive ])){
						$IsURIAllowed = wpCSPclass::IsURIInOptionString( $obj->blocked_uri , $CSPOptions[ $obj->violated_directive ] ) ;
					}
					else {
						$IsURIAllowed = false ;
					}
					// Don't display entries that we have already ignored or allowed.
					if ( $IsURIIgnored || $IsURIAllowed ) {
						continue;
					}
					
					$Counter++ ;
					$TargetRow1 = "WPCSPTargetRow1" . $Counter ;
					$TargetRow2 = "WPCSPTargetRow2" . $Counter ;
					?>
						<tr class='trWPCSPViewErrorSummary' data-violateddirective='<?php echo $obj->violated_directive ;?>' data-blockeduri='<?php echo $obj->blocked_uri ;?>' data-target='#<?php echo $TargetRow1 ;?>'>
							<td class='tdWPCSPViolatedDirective'><?php echo $obj->violated_directive ;?></td>
							<td class='tdWPCSPBlockedURL'><?php echo $obj->blocked_uri ;?></td>
							<td class='tdWPCSPNumErrors'><?php echo $obj->numerrors ; ?></td>
							<td class='tdWPCSPActionButtons'><input type="button" class="button-primary btnWPCSPViewErrors" value="<?php _e('View Errors','wpcsp') ?>" />
								<input type="button" class="button-primary btnWPCSPHideErrors WPCSPHiddenEntry" value="<?php _e('Hide Errors','wpcsp') ?>" />
							</td>
						</tr>
						<tr class='trWPCSPViewErrors WPCSPHiddenEntry' id='<?php echo $TargetRow1;?>'><td colspan='4'></td></tr>
						<?php 
						$URIParts = parse_url( $obj->blocked_uri  ) ;
						if ( $URIParts !== false && !empty( $URIParts['host'])):
							$URIHostnameWildcard = '*' . substr( $URIParts['host'] , strpos($URIParts['host'],"." )) ;
							if ( !empty( $URIParts['path'] )  ) {
								if ( substr( $URIParts['path'] , -1 ) == '/'){
									$URLPathDirectory = $URIParts['path'] ;
									$URLPathFile = '';
								}
								else {
									$URLPathDirectory = substr( $URIParts['path'] , 0, strrpos($URIParts['path'],"/" ) +1) ;
									$URLPathFile = substr( $URIParts['path'] , strrpos($URIParts['path'],"/" )+1) ;
								}
							}
							else {
								$URLPathDirectory = '' ;
								$URLPathFile = '' ;
							}
							?>
							<tr data-violateddirective='<?php echo $obj->violated_directive ;?>' data-target='#<?php echo $TargetRow2 ;?>'>
								<td class='tdWPCSPBlockedURLParts' colspan='3'>
									<table><tr>
									<td><select class='WPCSPBlockedURLScheme'>
			                            <option value="http" <?php selected( $URIParts['scheme'], 'http' ); ?>>http://</option>
			                            <option value="https" <?php selected( $URIParts['scheme'], "https"); ?>>https://</option>
			                            <option value=""  >Any</option>
									</select></td>
									<td><select class='WPCSPBlockedURLDomain'>
			                            <option value="<?php echo $URIParts['host']; ?>" selected='selected' ><?php echo $URIParts['host']; ?></option>
			                            <option value="<?php echo $URIHostnameWildcard; ?>" ><?php echo $URIHostnameWildcard; ?></option>
									</select></td>
									<td>
									<?php if ( empty( $URLPathDirectory )) : ?>
										<input type='hidden'  class='WPCSPBlockedURLPath' value='' />No Path
									<?php else :?><select class='WPCSPBlockedURLPath'>
			                            <option value="<?php echo $URLPathDirectory; ?>"><?php echo $URLPathDirectory; ?></option>
			                            <option value="" selected='selected'>Any Path</option>
									</select><div class='WPCSPBlockedURLPathError WPCSPHiddenEntry'></div>
									<?php endif;?></td>
									<td>
									<?php if ( empty( $URLPathFile )) : ?>
										<input type='hidden'  class='WPCSPBlockedURLFile' value='' />No Filename
									<?php else :?><select class='WPCSPBlockedURLFile'>
			                            <option value="<?php echo $URLPathFile; ?>"><?php echo $URLPathFile; ?></option>
			                            <option value="" selected='selected' >Any Filename</option>
									</select><div class='WPCSPBlockedURLFileError WPCSPHiddenEntry'></div>
									<?php endif;?></td>
									</tr></table>
								</td>
								<td class='tdWPCSPActionButtons'>
									<?php if ( isset( wpCSPclass::$CSP_Policies[ $obj->violated_directive ] )) : ?>
										<input type="button" class="button-primary btnWPCSPAddSafeDomain" value="<?php _e('Allow ' . strtoupper( $obj->violated_directive ) . ' Access' ,'wpcsp') ?>" />
									<?php else:?>
										<div class='wpscp_Cannot_Allow'>No allow Option</div>
									<?php endif; ?>
									<input type="button" class="button-primary btnWPCSPIgnoreDomain" value="<?php _e('Ignore Domain Violations','wpcsp') ?>" />
									<div class='WPCSPInfoBox' style='display:none;'></div>
								</td>
							</tr>
						<tr class='trWPCSPViewErrors WPCSPHiddenEntry' id='<?php echo $TargetRow2;?>'><td colspan='4'></td></tr>
						<?php 
						elseif( in_array( $URIParts['path'], array('data','inline','eval','blob','mediastream','filesystem') )) : 
							switch( $URIParts['path'] ) {
								case 'data':
									$BlockRule = "data:" ;
									break ;
								case 'blob':
									$BlockRule = "blob:" ;
									break ;
								case 'mediastream':
									$BlockRule = "mediastream:" ;
									break ;
								case 'filesystem':
									$BlockRule = "filesystem:" ;
									break ;
								case 'inline':
									$BlockRule = "'unsafe-inline'" ;
									break ;
								case 'eval':
									$BlockRule = "'unsafe-eval'" ;
									break ;
								default:
									$BlockRule = "";
									break;
							}
							?>
							<tr data-violateddirective='<?php echo $obj->violated_directive ;?>' data-target='#<?php echo $TargetRow2 ;?>'>
								<td class='tdWPCSPBlockedURLParts' colspan='3'>
									<table><tr>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>
										<input type='text'  class='WPCSPBlockedURLPath' value='<?php echo esc_attr( $BlockRule ) ; ?>' readonly='readonly' />
			                            <div class='WPCSPBlockedURLPathError WPCSPHiddenEntry'></div>
									<td>&nbsp;</td>
									</tr></table>
								</td>
								<td class='tdWPCSPActionButtons'>
									<input type="button" class="button-primary btnWPCSPAddSafeDomain" value="<?php _e('Allow ' . strtoupper( $obj->violated_directive ) . ' Access' ,'wpcsp') ?>" />
									<input type="button" class="button-primary btnWPCSPIgnoreDomain" value="<?php _e('Ignore Domain Violations','wpcsp') ?>" />
									<div class='WPCSPInfoBox' style='display:none;'></div>
								</td>
							</tr>
						<tr class='trWPCSPViewErrors WPCSPHiddenEntry' id='<?php echo $TargetRow2;?>'><td colspan='4'></td></tr>
						<?php 
						else : ?>
							<tr data-violateddirective='<?php echo $obj->violated_directive ;?>'>
								<td class='tdWPCSPBlockedURLParts' colspan='4'>
									<p>No host name set - you need to add this entry manually.</p>
								</td>
							</tr>
						<?php 
						endif;
						?>
				<?php 
				endforeach ;
				?>
				</tbody>
				</table>
	          </div> <!-- end wpcsp-logadmin -->
	     </div><!-- end wrap -->
	     <?php 
	}
	
		
	/**
	 * create or update the database 
	 */
	public static function update_database() {
		global $wpdb;
		$LogTableName = wpCSPclass::LogTableName();
		
		// Check if the table exists - if not force it to be created.
		if( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s",$LogTableName ) ) != $LogTableName) {
			$installed_ver = false ;
		}
		// Otherwise check the table version is latest version.
		else {
			$installed_ver = get_option( self::wpCSPDBVersionOptionName );
		}
		
		if ( $installed_ver != self::wpCSPDBVersion ) {
				
			$charset_collate = $wpdb->get_charset_collate();
			
			$sql = "CREATE TABLE ".$LogTableName." (
										id mediumint(9) NOT NULL AUTO_INCREMENT,
										violated_directive varchar(50) NOT NULL default '',
										blocked_uri varchar(1024) NOT NULL default '',
										document_uri varchar(1024) NOT NULL default '',
										useragent varchar(1024) NOT NULL default '',
										remoteaddress varchar(1024) NOT NULL default '',
										information text NOT NULL default '',
										createdon timestamp DEFAULT CURRENT_TIMESTAMP,
										PRIMARY KEY  id (id),
										KEY  violated_directive (violated_directive, blocked_uri(191) ),
										KEY  createdon (createdon)
								) " . $charset_collate . ";" ;
			
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			$return = dbDelta( $sql );
			
			// Store the table version in the database so we know whether it needs updating in the future.
			update_option( self::wpCSPDBVersionOptionName, self::wpCSPDBVersion );
		}
		
	}
	
	
	/**
	 * Daily maintenance to perform for this plugin.
	 */
	public static function daily_maintenance() {
		global $wpdb;
		// Stop the log getting out of control.
		$wpdb->query( 'DELETE  FROM '.wpCSPclass::LogTableName().' where createdon < NOW() - INTERVAL 1 WEEK' );
	}

	/**
	 * Cldar log file.
	 */
	private static function ClearLogFile() {
		global $wpdb;
		// Stop the log getting out of control.
		$wpdb->query( 'DELETE  FROM '. wpCSPclass::LogTableName() );
	}
	

	/**
	 * Register the settings
	 */
	public static function register_settings() {
		register_setting( wpCSPclass::SETTINGS_OPTIONS_SECTION,  // settings section
							wpCSPclass::SETTINGS_OPTIONS_ALLOPTIONS // setting name
		);
	}

	/**
	 * Handle the admin ajax calls for data and setting options.
	 */
	public function RestAdmin( WP_REST_Request $request ) {

		global $wpdb;

		@ob_end_clean();
		
		$ReturnStatus = true ;
		$Data = array() ;
		$HTML = "Unknown error" ;
		$AdditionalReturn = array();
		
		$SubAction = $request->get_param('subaction') ;
		$ViolatedDirective = $request->get_param('violateddirective') ;
		$BlockedURI = $request->get_param('blockeduri') ;
		$Scheme = $request->get_param('scheme') ;
		$Domain = $request->get_param('domain') ;
		$Path = $request->get_param('path') ;
		$File = $request->get_param('file') ;

		switch( $SubAction ) {
			case 'getdata':
				$sql = $wpdb->prepare("SELECT document_uri, useragent, count(*) as numerrors ".
									" FROM " . wpCSPclass::LogTableName() .
									" WHERE violated_directive = %s" .
									" AND blocked_uri = %s " .
									" GROUP BY document_uri, useragent",
									$ViolatedDirective,
									$BlockedURI );
				$rows = $wpdb->get_results( $sql );
				foreach ($rows as $obj) :
					$Data[] = array( 'document_uri' => !empty( $obj->document_uri ) ? $obj->document_uri : '(not set)',
							'useragent' => !empty( $obj->useragent ) ? $obj->useragent : '(not set)',
							'numerrors' => $obj->numerrors , ) ;
				endforeach;
				$HTML = '';
				break ;
				
			case 'addSafeDomain':
				if( !empty( $Scheme) && empty( $Domain )) {
					$BlockedURI = $Scheme . ':' ;
				}
				else {
					if ( !empty( $Scheme) && !empty( $Domain )) {
						$BlockedURI = $Scheme . "://" . $Domain ;
					}
					else {
						$BlockedURI = $Domain ;
					}
					if ( !empty( $Path )) {
						$BlockedURI .= $Path ;
						if ( !empty( $File )) {
							$BlockedURI .= $File ;
						}
					}
				}
				$BlockedURI = str_replace("\'","'",$BlockedURI);
				$options = get_option( wpCSPclass::SETTINGS_OPTIONS_ALLOPTIONS );
				$selected = !empty( $options[ $ViolatedDirective ] ) ? $options[ $ViolatedDirective ] : '' ;
				$selected .= " " . $BlockedURI ;
                $options[ $ViolatedDirective ] = implode(" ", wpCSPclass::CleanPolicyOptionText( $selected ) ) ;

				$options = update_option( wpCSPclass::SETTINGS_OPTIONS_ALLOPTIONS , $options );
				$HTML = 'Successfully added <strong>'.esc_html( $BlockedURI ) .'</strong> to the <strong>' . strtoupper($ViolatedDirective) . '</strong> domains list';
				break ;
			case 'addIgnoreDomain':
				if( !empty( $Scheme) && empty( $Domain )) {
					$BlockedURI = $Scheme . ':' ;
				}
				else {
					if ( !empty( $Scheme) && !empty( $Domain )) {
						$BlockedURI = $Scheme . "://" . $Domain ;
					}
					else {
						$BlockedURI = $Domain ;
					}
					if ( !empty( $Path )) {
						$BlockedURI .= $Path ;
						if ( !empty( $File )) {
							$BlockedURI .= $File ;
						}
					}
				}
				$options = get_option( wpCSPclass::SETTINGS_OPTIONS_ALLOPTIONS );
				$selected = !empty( $options[ wpCSPclass::SETTINGS_OPTIONS_VIOLATIONSTOIGNORE ] ) ? $options[ wpCSPclass::SETTINGS_OPTIONS_VIOLATIONSTOIGNORE ] : '' ;
				$selected .= " " . $BlockedURI ;
                $options[ wpCSPclass::SETTINGS_OPTIONS_VIOLATIONSTOIGNORE ] = implode(" ", wpCSPclass::CleanPolicyOptionText( $selected ) ) ;

				$options = update_option( wpCSPclass::SETTINGS_OPTIONS_ALLOPTIONS , $options );
				$HTML = 'Successfully added <strong>'.$BlockedURI.'</strong> to the <strong>IGNORED</strong> domains list';
				break ;
			case 'clearLogFile':
				self::ClearLogFile() ;
				$HTML = 'Successfully cleared the log file. Refresh screen to see';
				$ReturnStatus = true ;
				break ;
			case 'TestURLChecker':
				$HTML = self::TestURLChecker();
				$ReturnStatus = true ;
				break ;
			default:
				$HTML = 'Unknown action';
				$ReturnStatus = false;
				break ;
		}
		// response output
		$return = array('success'=>$ReturnStatus, 'html' => $HTML, 'data' => $Data ) ;
	
		return new WP_REST_Response( $return, 200 );
	}
	/*
	 * What we do when the plugin is activated - create/update table.
	 */
	public static function plugin_activation() {
		self::update_database() ;
		wp_schedule_event( time(), 'daily', self::wpCSPDBCronJobName );
	}

	/*
	 * What we do when the plugin is deactivated 
	 */
	public static function plugin_deactivation() {
		wp_clear_scheduled_hook( self::wpCSPDBCronJobName );
	}
	/*
	 * What we do when the plugin is uninstalled - remove table, unregister options, remove cron
	 */
	public static function plugin_uninstall() {
		global $wpdb; 
		$wpdb->query( "DROP TABLE IF EXISTS " . wpCSPclass::LogTableName( ) );
		
		unregister_setting( wpCSPclass::SETTINGS_OPTIONS_SECTION,  // settings section
							wpCSPclass::SETTINGS_OPTIONS_ALLOPTIONS // setting name
		);
		delete_option( self::wpCSPDBVersionOptionName  );
		delete_option( wpCSPclass::SETTINGS_OPTIONS_ALLOPTIONS  );
		
		wp_clear_scheduled_hook( self::wpCSPDBCronJobName );
	}
	
	/**
	 * Check for common errors and warn the user so they can fix them.
	 * @param array $Policies	- pre-parsed array of URL policies
	 * @return array 			- Array of errors
	 */
	static private function FindCSPErrors( $PolicyKey, $Policies ) {
		$return = array() ;
		$SchemeTags = array( 'data', 'blob','mediastream','filesystem','http','https',);
		if( is_array( $Policies)){
			foreach( $Policies as $Policy ) {
				$StrippedPolicy = preg_replace("/[^a-zA-Z0-9\s]/", "", $Policy);
				if ( $StrippedPolicy == 'self' && $Policy != "'self'") {
					$return[] = "Entry for <strong>self</strong> should read <strong>'self'</strong> (with single quotes) - Policy: " .$Policy ; 
				}
				if ( $StrippedPolicy == 'unsafeinline' && $Policy != "'unsafe-inline'") {
					$return[] = "Entry for <strong>unsafe-inline</strong> should read <strong>'unsafe-inline'</strong> (with single quotes) - Policy: " .$Policy ; 
				}
				if ( $StrippedPolicy == 'unsafeeval' && $Policy != "'unsafe-eval'") {
					$return[] = "Entry for <strong>unsafe-eval</strong> should read <strong>'unsafe-eval'</strong> (with single quotes) - Policy: " .$Policy ; 
				}
				if ( $StrippedPolicy == 'none' && $Policy != "'none'") {
					$return[] = "Entry for <strong>none</strong> should read <strong>'none'</strong> (with single quotes) - Policy: " .$Policy ; 
				}
				if ( $StrippedPolicy == 'strict-dynamic' && $Policy != "'strict-dynamic'") {
					$return[] = "Entry for <strong>strict-dynamic</strong> should read <strong>'strict-dynamic'</strong> (with single quotes) - Policy: " .$Policy ;
				}
				foreach( $SchemeTags as $SchemeTag ) {
					if ( $StrippedPolicy == $SchemeTag && $Policy != $SchemeTag . ":") {
						$return[] = "Entry for <strong>".$SchemeTag.":</strong> should read <strong>".$SchemeTag.":</strong> (with ending colon) - Policy: " .$Policy ; 
					}
				}
				if ( substr( $Policy,0,1) == '/'){
					$return[] = "Entry should not start with a '/' - entry: " .$Policy ;
				}
				if ( strlen( $Policy ) > 2 && substr( $Policy,0,1) == '*' &&  substr( $Policy,1,1) != '.'){
					$return[] = "Allow all subdomain entry should start '*.domain.com' - entry: " .$Policy ;
				}
				if ( $StrippedPolicy == 'data' && $PolicyKey == 'script-src' ) {
					$return[] = "Avoid using 'data:' for script-src: " .$Policy ;
				}
					
			}
		}
		return $return ;
	}
	
	
	/**
	 * checks the URL checker, see if its reading the ignored URLs correctly.
	 */
	private static function TestURLChecker() {
		
		$return = array() ;
		
		// Testing various ways of checking for errors in option arrays
		// array( BlockedURI,  OptionString, ExpectedTestResult )
		// where BlockedURI is emulating the issue we received from the browser.
		// and OptionString is emulating the options entered by the user.
		// ExpectedTestResult is what we expect to receive back from the routine.
		// True indicates the routine should find a match, and false not a match.
		
		$TestArray = array(
				array( 'data:', 'data:', true),
				array( 'http:', 'http:', true),
				array( 'https:', 'https:', true),
				array( 'data:', 'http:', false),
				array( 'data:', 'https:', false),
				array( 'http:', 'data:', false),
				array( 'https:', 'data:', false),
		
				array( 'data:urlencoded 64 dsdsdsddsd', 'data:', true),
				array( 'http://www.example.com', 'http:', true),
				array( 'https://www.example.com', 'https:', true),
		
				array( 'data:urlencoded 64 dsdsdsddsd', 'http:', false),
				array( 'http://www.example.com', 'https:', false),
				array( 'https://www.example.com', 'data:', false),
		
				array( site_url(), "'self'", true),
				array( site_url(), "data:", false),
				array( site_url(), "http://www.example.com", false),
				array( site_url(), "https://www.example.com", false),
				array( site_url(), "www.example.com", false),
				array( site_url(), "*.example.com", false),
		
				array( 'http://www.example.com', "http://www.example.com", true),
				array( 'http://www.example.com', "https://www.example.com", false),
				array( 'www.example.com', "https://www.example.com", false),
				array( 'www.example.com', "http://www.example.com", false),
				array( 'www.example.com', "www.example.com", true),
		
				array( 'http://www.example.com/test/url', "http://www.example.com", true),
				array( 'http://www.example.com/test/url', "https://www.example.com", false),
				array( 'http://www.example.com/test/url', "www.example.com", true),
				array( 'www.example.com/test/url', "https://www.example.com", false),
				array( 'www.example.com/test/url', "http://www.example.com", false),
				array( 'www.example.com/test/url', "www.example.com", true),
		
				array( 'http://www.example.com', "www.example.com", true),
				array( 'http://www.example.com', "*.example.com", true),
				array( 'https://www.example.com', "www.example.com", true),
				array( 'https://www.example.com', "*.example.com", true),
				array( 'ssss://www.example.com', "www.example.com", true),
				array( 'ssss://www.example.com', "*.example.com", true),
				array( 'http://www.example.com', "*example.com", false),
				array( 'https://www.example.com', "*example.com", false),
				array( 'ssss://www.example.com', "*example.com", false),
				array( 'http://www.example.com', ".example.com", false),
				array( 'https://www.example.com', ".example.com", false),
				array( 'ssss://www.example.com', ".example.com", false),
				array( 'http://www.example.com', "example.com", false),
				array( 'https://www.example.com', "example.com", false),
				array( 'ssss://www.example.com', "example.com", false),
		
		
				array( 'http://www.example.com/test/url', "www.example.com", true),
				array( 'http://www.example.com/test/url', "*.example.com", true),
				array( 'https://www.example.com/test/url', "www.example.com", true),
				array( 'https://www.example.com/test/url', "*.example.com", true),
				array( 'ssss://www.example.com/test/url', "www.example.com", true),
				array( 'ssss://www.example.com/test/url', "*.example.com", true),
				array( 'http://www.example.com/test/url', "*example.com", false),
				array( 'https://www.example.com/test/url', "*example.com", false),
				array( 'ssss://www.example.com/test/url', "*example.com", false),
				array( 'http://www.example.com/test/url', ".example.com", false),
				array( 'https://www.example.com/test/url', ".example.com", false),
				array( 'ssss://www.example.com/test/url', ".example.com", false),
				array( 'http://www.example.com/test/url', "example.com", false),
				array( 'https://www.example.com/test/url', "example.com", false),
				array( 'ssss://www.example.com/test/url', "example.com", false),
		
				array( 'http://www.example.com', "www.notexample.com", false),
				array( 'http://www.example.com', "*.notexample.com", false),
				array( 'https://www.example.com', "www.notexample.com", false),
				array( 'https://www.example.com', "*.notexample.com", false),
				array( 'ssss://www.example.com', "www.notexample.com", false),
				array( 'ssss://www.example.com', "*.notexample.com", false),
		
		
				array( 'http://www.example.com/path/to/file/', "*.notexample.com", false),
				array( 'https://www.example.com/path/to/file/', "www.notexample.com", false),
				array( 'http://www.example.com/path/to/file/', "*.example.com", true),
				array( 'https://www.example.com/path/to/file/', "www.example.com", true),
				array( 'http://www.example.com/path/to/file/', "*.example.com/path/", false),
				array( 'https://www.example.com/path/to/file/', "www.example.com/path/", false),
				array( 'http://www.example.com/path/to/file/', "*.example.com/path/to", false),
				array( 'https://www.example.com/path/to/file/', "www.example.com/path/to", false),
				array( 'http://www.example.com/path/to/file/', "*.example.com/path/to/file/", true),
				array( 'https://www.example.com/path/to/file/', "www.example.com/path/to/file/", true),
		
				array( 'http://www.example.com/path/to/file/thefile.php', "*.notexample.com", false),
				array( 'https://www.example.com/path/to/file/thefile.php', "www.notexample.com", false),
				array( 'http://www.example.com/path/to/file/thefile.php', "*.example.com", true),
				array( 'https://www.example.com/path/to/file/thefile.php', "www.example.com", true),
				array( 'https://www.example.com/path/to/file/thefile.php', "http://www.example.com", false),
				array( 'https://www.example.com/path/to/file/thefile.php', "https://www.example.com", true),
				array( 'http://www.example.com/path/to/file/thefile.php', "*.example.com/path/", false),
				array( 'https://www.example.com/path/to/file/thefile.php', "www.example.com/path/", false),
				array( 'https://www.example.com/path/to/file/thefile.php', "http://www.example.com/path/", false),
				array( 'https://www.example.com/path/to/file/thefile.php', "https://www.example.com/path/", false),
				array( 'http://www.example.com/path/to/file/thefile.php', "*.example.com/path/to", false),
				array( 'https://www.example.com/path/to/file/thefile.php', "www.example.com/path/to", false),
				array( 'https://www.example.com/path/to/file/thefile.php', "http://www.example.com/path/to", false),
				array( 'http://www.example.com/path/to/file/thefile.php', "*.example.com/path/to/file/", true),
				array( 'https://www.example.com/path/to/file/thefile.php', "www.example.com/path/to/file/", true),
				array( 'https://www.example.com/path/to/file/thefile.php', "http://www.example.com/path/to/file/", false),
				array( 'https://www.example.com/path/to/file/thefile.php', "https://www.example.com/path/to/file/", true),
				array( 'http://www.example.com/path/to/file/thefile.php', "*.example.com/path/to/file/thefile.php", true),
				array( 'https://www.example.com/path/to/file/thefile.php', "www.example.com/path/to/file/thefile.php", true),
				array( 'https://www.example.com/path/to/file/thefile.php', "http://www.example.com/path/to/file/thefile.php", false),
				array( 'https://www.example.com/path/to/file/thefile.php', "https://www.example.com/path/to/file/thefile.php", true),
		
				array( '', "*.notexample.com", false),
				array( '', "", false),
				array( 'http://www.example.com', "", false),
				array( 'http://www.example.com', "none", false),
				array( 'http://www.example.com', "'none'", false),
				array( "'none'", "'none'", false),
		
				array( 'data:urlencoded 64 dsdsdsddsd', '*', false), // see http://www.w3.org/TR/CSP2/#match-source-expression
				array( 'http://www.example.com', '*', true),
				array( 'https://www.example.com', '*', true),
		
		
		);
		
		foreach( $TestArray as $Test ) {
			$return[] =  "------------ Starting test:" . print_r( $Test,true) ;
			$ret = wpCSPclass::IsURIInOptionString( $Test[0], $Test[1] ) ;
			if ( $ret !== $Test[2] ) {
				$return[] =  "****** failed test:" . print_r( $Test,true);
				$return[] =  "returned:" . print_r( $ret , true );
				break ;
			}
		}
		
		
		// Test end to end including logging.
		$CSPViolation = array( 'csp-report' => array( 'effective-directive' => 'img-src' ,
				'blocked-uri' => 'http://b.wallyworld.zzzz' ) ) ;
		if ( wpCSPclass::ProcessPolicyViolation( $CSPViolation ) === false ) {
			$return[] =  "Should be logging b.wallyworld.zzzz as it is not blocked by ignored urls<br>\n ;" ;
		}
		
		$return[] =  "Finished tests with no issues.<br>\n";
		
		return "<li>" . implode("</li><li>", $return ) . "</li>";
	}
}

add_action('init',array("wpCSPAdmin","init"));
// If action "rest_api_init" hasn't run yet then use that, otherwise we have the route server in place, just register route
if ( did_action('rest_api_init') == 0 ){
	add_action('rest_api_init',array("wpCSPAdmin","register_routes"));
}
else {
	wpCSPAdmin::register_routes();
}