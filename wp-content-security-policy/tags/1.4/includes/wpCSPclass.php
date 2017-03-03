<?php
if(!empty($_SERVER['SCRIPT_FILENAME']) && basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME']))
	die('You can not access this page directly!');

class wpCSPclass{

	const LOG_TABLE_NAME = 'wpcsplog';
	
	
	const SETTINGS_OPTIONS_SECTION = 'wpcsp_options' ;
	const SETTINGS_OPTIONS_ALLOPTIONS = 'wpcsp_all_options' ;
	
 	const SETTINGS_OPTIONS_REPORTONLY = 'wpcsp_reportonly' ;
 	const SETTINGS_OPTIONS_LOGVIOLATIONS = 'wpcsp_logviolations' ;
 	const SETTINGS_OPTIONS_VIOLATIONSTOIGNORE = 'wpcsp_ViolationsToIgnore' ;
 	const SETTINGS_OPTIONS_SANDBOX = 'wpcsp_Sandbox' ;
 	const SETTINGS_OPTIONS_SANDBOX_BLANKENTRY = 'blankentry' ;
 	const SETTINGS_OPTIONS_SANDBOX_NOTSET = '' ;
 	
 	const PLUGIN_TRIGGER = 'wpcspReceiveCSPviol';
 	const RESPONSE_NONCE_ACTION = 'wpCSPclass' ;
 	const RESPONSE_NONCE_QUERYFIELD = 'wpCSPNonce' ;
 	
 	const OPTIONS_DELIMITER = "||";


 	const LOGVIOLATIONS_IGNORE = 0 ;
 	const LOGVIOLATIONS_LOG_ALL = 1 ;
 	const LOGVIOLATIONS_LOG_10PERC = 10 ;
 	const LOGVIOLATIONS_LOG_1PERC = 11 ;
 	const LOGVIOLATIONS_LOG_POINT1PERC = 12;
 	
 	
 	const HEADERTAG_ENFORCPOLICY = 'Content-Security-Policy' ;
 	const HEADERTAG_REPORTONLY = 'Content-Security-Policy-Report-Only';

	static  $CSP_Policies = array( 'default-src' => array( 'label' => 'Default SRC' ,
															'description' => "The default-src is the default policy for loading content such as JavaScript, Images, CSS, Font's, AJAX requests, Frames, HTML5 Media." ,
															),
								'script-src' => array( 'label' => 'Script SRC' ,
															'description' => 'Defines valid sources of JavaScript.' ,
															),
								'style-src' => array( 'label' => 'Style SRC' ,
															'description' => 'Defines valid sources of stylesheets.' ,
															),
								'img-src' => array( 'label' => 'Image SRC' ,
															'description' => 'Defines valid sources of images.' ,
															),
								'font-src' => array( 'label' => 'Font SRC' ,
															'description' => 'Defines valid sources of fonts.' ,
															),
								'frame-src' => array( 'label' => 'Frame SRC' ,
															'description' => 'Defines valid sources for loading frames.' ,
															),
								'object-src' => array( 'label' => 'Object SRC' ,
															'description' => 'Defines valid sources of plugins, eg &lt;object&gt;, &lt;embed&gt; or &lt;applet&gt;.' ,
															),
								'connect-src' => array( 'label' => 'Connect SRC' ,
															'description' => 'Applies to XMLHttpRequest (AJAX), WebSocket or EventSource. If not allowed the browser emulates a 400 HTTP status code.' ,
															), 
								'media-src' => array( 'label' => 'Media SRC' ,
															'description' => 'Defines valid sources of audio and video, eg HTML5 &lt;audio&gt;, &lt;video&gt; elements.' ,
															), 
								'form-action' => array( 'label' => 'Form Action' ,
															'description' => 'The form-action restricts which URLs can be used as the action of HTML form elements.' ,
															), 
								'frame-ancestors' => array( 'label' => 'Frame Ancestors' ,
															'description' => 'The frame-ancestors directive indicates whether the user agent should allow embedding the resource using a frame, iframe, object, embed or applet element, or equivalent functionality in non-HTML resources.' ,
															), 
								'plugin-types' => array( 'label' => 'Plugin Types' ,
															'description' => 'The plugin-types directive restricts the set of plugins that can be invoked by the protected resource by limiting the types of resources that can be embedded.' ,
															), 
	) ;
							
	public static function init() {
		add_action('get_header', array(__CLASS__,"add_header"));
		add_filter('query_vars', array(__CLASS__,'plugin_add_trigger' ) );
		add_action('template_redirect', array(__CLASS__,'plugin_trigger_check' ) );
	}
	
	/**
	 * Add trigger to Wordpress so we can have the violation call back to the site.
	 * @param array $vars
	 * @return array
	 */
	public static function plugin_add_trigger($vars) {
		$vars[] = self::PLUGIN_TRIGGER ;
		return $vars;
	}
	
	/**
	 * Check if the call back trigger is set - if so do the work.
	 */
	public static function plugin_trigger_check() {
		if(intval(get_query_var( self::PLUGIN_TRIGGER )) == 1) {

			// Check the nonce is valid, try to stop people attacking the site.
			if ( !check_admin_referer( self::RESPONSE_NONCE_ACTION, self::RESPONSE_NONCE_QUERYFIELD ) ) {
				exit();
			}
			
			// Send `204 No Content` status code
// 			http_response_code(204) ;
			
			// Get the raw POST data
			$data = file_get_contents('php://input');
			
			// Receives:
			// {"csp-report":
			// 		{"blocked-uri":"http://www.localhost.com/wp-content/uploads/2014/10/testimonial-3.png",
			// 		"document-uri":"http://www.localhost.com/",
			// 		"original-policy":"default-src 'none'; script-src 'none'; style-src 'none'; img-src 'none'; font-src 'none'; frame-src 'none'; object-src 'none'; connect-src 'none'; report-uri http://www.localhost.com/wp-content/plugins/wp-content-security-policy/includes/receiveCSPviol.php",
			// 		"referrer":"",
			// 		"violated-directive":"img-src 'none'"}
			// }
			
			
			// Only continue if it’s valid JSON that is not just `null`, `0`, `false` or an
			// empty string, i.e. if it could be a CSP violation report.
			if ($CSPViolation = json_decode($data,true)) {
				wpCSPclass::LogPolicyViolation($CSPViolation);
			}
		    exit;
	    }
	}
		
	
	/**
	 * Add the header to each page.
	 */
	public static function add_header() {
		
		$CSPOutput = "" ;
		$options = get_option( self::SETTINGS_OPTIONS_ALLOPTIONS );
		foreach( wpCSPclass::$CSP_Policies as $PolicyKey => $CSPPolicy) {
			$CSPOption = self::CleanPolicyOptionText( $options[$PolicyKey] );
			if ( in_array( "'self'", $CSPOption)) {
				$CSPOption[] = site_url();
			}
			$CSPOptionOptionString = implode(" ", $CSPOption ) ;
			
			// if the option sting is not empty then output it. If it is empty it will default to default-src.
			if ( !empty( $CSPOptionOptionString ) ) {
				$CSPOutput .= $PolicyKey . " " . $CSPOptionOptionString . "; ";
				// Legacy setting, some browsers still need it.
				if ( $PolicyKey == 'frame-src' ) {
					$CSPOutput .=  "child-src " . $CSPOptionOptionString . "; ";
				}
			}
		}
		
		// Sandbox - If its blank its not set, if its not blank then display something.
		if ( !empty( $options[ self::SETTINGS_OPTIONS_SANDBOX]) && is_array( $options[ self::SETTINGS_OPTIONS_SANDBOX] )) {
			// If the first entry is blank then nothing should be output.
			if ( !empty( $options[ self::SETTINGS_OPTIONS_SANDBOX][0])) {
				// A true blank entry is the most restrictive type of entry
				if ( in_array( wpCSPclass::SETTINGS_OPTIONS_SANDBOX_BLANKENTRY , $options[ self::SETTINGS_OPTIONS_SANDBOX] )) {
					$SandboxOptions = "" ;
				}
				else {
					$SandboxOptions = implode(" ", $options[ self::SETTINGS_OPTIONS_SANDBOX]  );
				}
				$CSPOutput .=  "sandbox " . $SandboxOptions . "; ";
			}
		}
		$ReportOnly = $options[ self::SETTINGS_OPTIONS_REPORTONLY];
		$CSPHeaderTag = !empty( $ReportOnly ) ? self::HEADERTAG_REPORTONLY : self::HEADERTAG_ENFORCPOLICY;
		
		// Do we want the browser to log the violations with the server, or only block without logging?
 		$LogViolations = false ;
 		switch( $options[ self::SETTINGS_OPTIONS_LOGVIOLATIONS ] ) {
 			case wpCSPclass::LOGVIOLATIONS_LOG_ALL:
 				$LogViolations = true ;
 				break ;
			case wpCSPclass::LOGVIOLATIONS_LOG_10PERC:
 				if ( mt_rand(0,10) == 1){
 					$LogViolations = true ;
 				}
 				break ;
			case wpCSPclass::LOGVIOLATIONS_LOG_1PERC:
 				if ( mt_rand(0,100) == 1){
 					$LogViolations = true ;
 				}
 				break ;
			case wpCSPclass::LOGVIOLATIONS_LOG_POINT1PERC:
 				if ( mt_rand(0,1000) == 1){
 					$LogViolations = true ;
 				}
 				break ;
			case wpCSPclass::LOGVIOLATIONS_IGNORE:
			default:
 				break ;
 		}
 		// We want to log violations - set the correct URL to log the errors.
 		if ( $LogViolations === true ) {
 			$Nonce = wp_create_nonce( self::RESPONSE_NONCE_ACTION );
 			$URLParams = array( self::PLUGIN_TRIGGER => '1', self::RESPONSE_NONCE_QUERYFIELD => $Nonce );
 			$ReportURI = add_query_arg( $URLParams, site_url( ) ) ;
			$CSPOutput .= "report-uri " . $ReportURI  ;
 		}
 		// Output the CSP header
 		header($CSPHeaderTag . ": " . $CSPOutput);
	}
	
	/**
	 * Takes the option text in the database, cleans it up, removes blanks and duplicates, and returns an array of host entries
	 * @param string $option
	 * @return array of strings
	 */
	public static function CleanPolicyOptionText( $option ) {
		$return = array();
		if ( !empty( $option )) {
			// Later versions should remove the " " option - that's an old option
			$option = str_replace(array("\n","\r"," "), self::OPTIONS_DELIMITER , $option);
			$option = preg_replace('/\s+/', self::OPTIONS_DELIMITER ,$option);
			$return = array_filter( array_unique( explode( self::OPTIONS_DELIMITER, $option ) ) ) ;
			usort($return, array( __CLASS__, "SortByHostname" ) ) ;
		}
		return $return;
	}
	

	/**
	 * Returns the table name of the log table.
	 * @return string
	 */
	public static function LogTableName() {
		global $wpdb;
		$table_name = $wpdb->prefix . self::LOG_TABLE_NAME ;
		return $table_name ;
	}
	
	/**
	 * Put an entry into the log table so the admin can figure out what to do wwith the violation.
	 * @param array $CSPViolation
	 */
	public static function LogPolicyViolation( $CSPViolation ) {

		// Two bits of information we need to be able to log the violation.
		$ViolatedDirective = '' ;
		$DocumentURI = '' ;
		$BlockedURI = '' ;
		$UserAgent = '' ;
		$RemoteAddress = '' ;
		$LogViolation = false;
		
		// Options as entered by the site admin.
		$CSPOptions = get_option( wpCSPclass::SETTINGS_OPTIONS_ALLOPTIONS );

		//Figure out the policy that was violated.
		if ( isset( $CSPViolation['csp-report']['effective-directive'] )) {
			$ViolatedDirective = $CSPViolation['csp-report']['effective-directive'] ;
		}
		elseif ( isset( $CSPViolation['csp-report']['violated-directive'] )) {
			$parts = explode(" ", $CSPViolation['csp-report']['violated-directive'],2 );
			$ViolatedDirective = $parts[0];
		}

		// Find out which URL was blocked.
		if (  isset( $CSPViolation['csp-report']['document-uri'] ) ){
			$DocumentURI = $CSPViolation['csp-report']['document-uri'] ;
		}
		elseif (  isset( $_SERVER['HTTP_REFERER'] ) ){
			$DocumentURI = $_SERVER['HTTP_REFERER'] ;
		}
		
		// Find out which URL was blocked.
		$BlockedURI = isset( $CSPViolation['csp-report']['blocked-uri'] )  ? $CSPViolation['csp-report']['blocked-uri'] : '' ;
		
		// Find out browser information.
		$UserAgent = isset( $_SERVER['HTTP_USER_AGENT'] )  ? $_SERVER['HTTP_USER_AGENT'] : '' ;

		// Find out source of problem.
		$RemoteAddress = isset( $CSPViolation['REMOTE_ADDR']['REMOTE_ADDR'] ) ? '' : '' ;
		
		// Do we have enough information to do anything with?
		if ( !empty( $ViolatedDirective ) && !empty( $BlockedURI ) ) {
			
			$LogViolation = true; 
			
			// Let's see if we are set to ignore this host - Not reporting ignored URLs to stop clogging up the database
			if ( !empty( $CSPOptions[ wpCSPclass::SETTINGS_OPTIONS_VIOLATIONSTOIGNORE ] ) && self::IsURIInOptionString( $BlockedURI , $CSPOptions[ wpCSPclass::SETTINGS_OPTIONS_VIOLATIONSTOIGNORE ] ) ) {
				$LogViolation = false  ;
			}
			
			// Sometimes some browsers seem to cache old directives - see if the host is now OK.
			elseif ( !empty( $CSPOptions[ $ViolatedDirective ]) && self::IsURIInOptionString( $BlockedURI, $CSPOptions[ $ViolatedDirective ] ) ) {
				$LogViolation = false  ;
			}
			
			// Did the user want us to log the violations?
			elseif ( $CSPOptions[ wpCSPclass::SETTINGS_OPTIONS_LOGVIOLATIONS ] == wpCSPclass::LOGVIOLATIONS_IGNORE ) {
				$LogViolation = false ;
			}
			
			// Do we still want to log the violation?
			if ( $LogViolation === true ) {
			
				// This is the extra information to help track down weird violations.
				$PrettyData = "Violated Directive: " . $ViolatedDirective . " <br>\n" . 
							"Blocked Host: " . $BlockedURI . " <br>\n"  ;
				// Not sure we can handle blocking individual ports....
				if ( isset( $URLParts['port'])) {
					$PrettyData .= "Port Blocked: " . $URLParts['port'] . " <br>\n" ;
				}
				$PrettyData .= print_r( $CSPViolation , true ) ;
				
				// Insert the violation into the custom table.
				global $wpdb;
				$InsertReturn = $wpdb->insert(
											wpCSPclass::LogTableName(),
											array(
													'violated_directive' => $ViolatedDirective,
													'blocked_uri' => $BlockedURI ,
													'document_uri' => $DocumentURI ,
													'useragent' => $UserAgent ,
													'remoteaddress' => $RemoteAddress ,
													'information' => $PrettyData ,
											),
											array(
													'%s',
													'%s',
													'%s',
													'%s',
													'%s',
													'%s'
											)
									);
				// Insert into table failed - need to indicate there was an issue.
				if ( $InsertReturn === false ) {
					echo "Could not store error.";
				}
			}
		}
		return  $LogViolation;
	}
	
	/**
	 * 
	 * @param string $URI				URI we're trying to match
	 * @param string $OptionString		String we're trying to match in.
	 * @param array $args				Future use.
	 * @return false - no match, otherwise match URI.
	 */
	public static function IsURIInOptionString( $URI, $OptionString, $args = array() ) {
		$return = -1;

		$OptionURLArray = wpCSPclass::CleanPolicyOptionText( $OptionString ) ;

		// No URI to check therefore no match possible.
		if ( empty( $URI ) || empty( $OptionURLArray )) {
			$return = false ;
		}
		
		// Does the URL include a scheme?
		$URI = trim( $URI ) ;
		if ( strpos( $URI,":") === false ) {
			$URLPathDirectory = "" ;
			if ( strpos( $URI , "/" ) !== false ) {
				$URLPathDirectory = substr( $URI , strpos( $URI , "/" ) );
				$URI = substr( $URI , 0 , strpos( $URI , "/" ) );
			}
			$URIParts = array( 'scheme' => '' ,
								'host' => $URI ,
								'path' => $URLPathDirectory ) ;
		}
		else {
			$URIParts = parse_url( $URI );
			if ( $URIParts === false ) {
				return false ;
			}
		}
		
		// For matching against anything with a wildcard - remove the subdomain.
		$URIHostnameWildcard = substr( $URIParts['host'] , strpos($URIParts['host'],"." )) ;
		
		// Split the path into path and file.
		if ( empty( $URIParts['path'] )){
			$URLPathDirectory = '' ;
			$URLPathFile = '';
		}
		elseif ( substr( $URIParts['path'] , -1 ) == '/'){
			$URLPathDirectory = $URIParts['path'] ;
			$URLPathFile = '';
		}
		else {
			$URLPathDirectory = substr( $URIParts['path'] , 0, strrpos($URIParts['path'],"/" ) +1) ;
			$URLPathFile = substr( $URIParts['path'] , strrpos($URIParts['path'],"/" )+1) ;
		}
		
		// Quick search for special options!
		if ( $return === -1 ) {
			foreach( $OptionURLArray as $key => $OptionURL ) {
				
				// Empty option - ignore.
				if ( empty(  $OptionURL )) {
					continue ;
				}
				
				// Find out the options parts.
				$OptionURL = trim( $OptionURL ) ;
				if ( $OptionURL == "'self'" ) {
					$OptionURL = site_url() ;
				}
				
				if ( strpos( $OptionURL,":") === false ) {
					$OptionURLPathDirectory = "" ;
					if ( strpos( $OptionURL , "/" ) !== false ) {
						$OptionURLPathDirectory = substr( $OptionURL , strpos( $OptionURL , "/" ) );
						$OptionURL = substr( $OptionURL , 0 , strpos( $OptionURL , "/" ) );
					}
					$OptionURLParts = array( 'scheme' => '' ,
							'host' => $OptionURL ,
							'path' => $OptionURLPathDirectory ) ;
				}
				else {
					$OptionURLParts = parse_url( $OptionURL );
					if ( $OptionURLParts === false ) {
						continue ;
					}
				}
				
				if ( empty( $OptionURLParts['path'] )){
					$OptionURLPathDirectory = '';
					$OptionURLPathFile = '';
				}
				elseif ( substr( $OptionURLParts['path'] , -1 ) == '/'){
					$OptionURLPathDirectory = $OptionURLParts['path'] ;
					$OptionURLPathFile = '';
				}
				else {
					$OptionURLPathDirectory = substr( $OptionURLParts['path'] , 0, strrpos($OptionURLParts['path'],"/" ) +1) ;
					$OptionURLPathFile = substr( $OptionURLParts['path'] , strrpos($OptionURLParts['path'],"/" )+1) ;
				}
				
				// * matched everything!
				if ( $OptionURL == "*" ) {
					if ( $URIParts['scheme'] != 'blob' && $URIParts['scheme'] != 'data' && $URIParts['scheme'] != 'filesystem' ){
						$return = true ;
					}
				}
				// If it ends in ':' then it just matches schema i.e. http: and data:
				elseif ( substr( $OptionURL, -1,1) == ':'){
					if ( substr( $OptionURL, 0, -1 ) == $URIParts['scheme']) {
						$return = true ;
					}
				}
				// Special options - not sure what to check
				elseif ( $OptionURL == "'unsafe-inline'"){
				}
				// Special options - not sure what to check
				elseif ( $OptionURL == "'unsafe-eval'"){
				}
				// system set to not allow any connections therefore everything fails matching.
				elseif ( $OptionURL == "'none'"){
					$return = false ;
				}
				// If this option doesn't contain a : then it is just matching the host name
				else {
					// Does the option  have a scheme to check?
					if ( strpos( $OptionURL , ":" ) !== false ){
						
						// If the host name starts with a '*' then remvoe subdomain and check against the other url minus the subdomain
						if ( substr( $OptionURL ,0,1) == '*') {
							if ( substr( $OptionURLParts['host'] , strpos($OptionURLParts['host'],"." ) ) != $URIHostnameWildcard ) {
								continue;
							}
						}
						elseif( $OptionURLParts['host'] != $URIParts['host'] || $OptionURLParts['scheme'] != $URIParts['scheme']  ) {
							continue;
						}
					}
					// If the host name starts with a '*' then wildcard the  match
					elseif ( substr( $OptionURL ,0,1) == '*') {
						if ( substr( $OptionURLParts['host'] , strpos($OptionURLParts['host'],"." ) ) != $URIHostnameWildcard ) {
							continue;
						}
					}
					elseif( $OptionURL != $URIParts['host'] ) {
							continue;
					}

					if ( !empty( $OptionURLPathDirectory )) {
						if ( $OptionURLPathDirectory !== $URLPathDirectory ) {
							continue ;
						}
						if ( !empty( $OptionURLPathFile )) {
							if ( $OptionURLPathFile !== $URLPathFile) {
								continue ;
							}
						}
					}
					$return = true ;
				}
				// Did something set something this time around? Then stop checking options.
				if ( $return !== -1 ){
					break ;
				}
			}
		}
		if ( $return === -1 ){
			$return = false ;
		}
		return $return ;
	}

	/**
	 * Sort the passed in strings into a logically understandable order for the user.
	 * @param string $a
	 * @param string $b
	 * @return -1,0,1
	 */
	public static function SortByHostname( $a, $b ) {
		$a = trim( $a ) ;
		$b = trim( $b ) ;
		if ( $a == $b ) {
			return 0 ;
		}
		$ahost = parse_url ( $a ,  PHP_URL_HOST ) ;
		$bhost = parse_url ( $b ,  PHP_URL_HOST ) ;
		if ( ( empty( $ahost ) && empty( $bhost ) ) || $ahost == $bhost ) {
			return strcasecmp( $a, $b ) ;
		}
		elseif ( empty( $ahost )) {
			return -1;
		}
		elseif ( empty( $bhost )) {
			return 1;
		}
		return strcasecmp( $ahost, $bhost );
	}
}
wpCSPclass::init();