<?php
if(!empty($_SERVER['SCRIPT_FILENAME']) && basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])){
	die('You can not access this page directly!');
}

class WP_CSP extends WP_REST_Controller{
	
	const LOG_TABLE_NAME = 'wpcsplog';
	
	
	const SETTINGS_OPTIONS_SECTION = 'wpcsp_options' ;
	const SETTINGS_OPTIONS_ALLOPTIONS = 'wpcsp_all_options' ;
	
	const SETTINGS_OPTIONS_CSP_MODE = 'wpcsp_reportonly' ;
	const SETTINGS_OPTIONS_LOGVIOLATIONS = 'wpcsp_logviolations' ;
	const SETTINGS_OPTIONS_VIOLATIONSTOIGNORE = 'wpcsp_ViolationsToIgnore' ;
	const SETTINGS_OPTIONS_SANDBOX = 'wpcsp_Sandbox' ;
	const SETTINGS_OPTIONS_SANDBOX_BLANKENTRY = 'blankentry' ;
	const SETTINGS_OPTIONS_SANDBOX_NOTSET = '' ;
	const SETTINGS_OPTIONS_MIXED_CONTENT = 'wpcsp_MixedContent';
	const SETTINGS_OPTIONS_EXPECTCT_OPTIONS = 'wpcsp_expectct_enforce' ;
	const SETTINGS_OPTIONS_EXPECTCT_MAXAGE = 'wpcsp_expectct_maxage' ;
	const SETTINGS_OPTIONS_STS_OPTIONS = 'wpcsp_sts_options' ;
	const SETTINGS_OPTIONS_STS_MAXAGE = 'wpcsp_sts_maxage' ;
	const SETTINGS_OPTIONS_FRAME_OPTIONS = 'wpcsp_frame_options' ;
	const SETTINGS_OPTIONS_FRAME_OPTIONS_ALLOW_FROM = 'wpcsp_frame_options_allow_from' ;
	const SETTINGS_OPTIONS_XSS_PROTECTION = 'wpcsp_xss_protection' ;
	const SETTINGS_OPTIONS_CONTENT_TYPE_OPTIONS = 'wpcsp_content_type_options' ;
	const SETTINGS_OPTIONS_REFERRER_POLICY_OPTIONS = 'wpcsp_referrer_policy_options' ;
	const SETTINGS_OPTIONS_REPORT_URI_REPORTONLY = 'wpcsp_report_uri+reportonly' ;
	const SETTINGS_OPTIONS_REPORT_URI_ENFORCE = 'wpcsp_report_uri_enforce' ;
	const SETTINGS_OPTIONS_REQUIRE_SRI = 'wpcsp_require_sri_options' ;
	
	const PLUGIN_TRIGGER = 'wpcspReceiveCSPviol';
	
	const OPTIONS_DELIMITER = "||";
	
	
	const LOGVIOLATIONS_IGNORE = 0 ;
	const LOGVIOLATIONS_LOG_ALL = 1 ;
	const LOGVIOLATIONS_LOG_10PERC = 10 ;
	const LOGVIOLATIONS_LOG_1PERC = 11 ;
	const LOGVIOLATIONS_LOG_POINT1PERC = 12;
	
	const BLOCK_ALL_MIXED_CONTENT = 21 ;
	const UPGRADE_INSECURE_REQUESTS = 22 ;
	
	
	const ROUTE_BASE = 'route';
	const ROUTE_NAMESPACE = 'wpcsp/v1' ;
	
	
	static private $CSPHeaderNonce = false ;
	const LengthOfNonce = 128 ;
	
	static  $CSP_Policies = array( 
			'default-src' => array( 'label' => 'Default SRC' ,
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
			'base-uri' => array( 'label' => 'Base URI' ,
					'description' => "base-uri directive restricts the URLs which can be used in a document's <base> element. If this value is absent, then any URI is allowed. If this directive is absent, the user agent will use the value in the <base> element." ,
			),
			'manifest-src' => array( 'label' => 'Manifest SRC' ,
					'description' => 'manifest-src directive specifies which manifest can be applied to the resource.' ,
			),
			'worker-src' => array( 'label' => 'Worker SRC' ,
					'description' => 'worker-src directive specifies valid sources for Worker, SharedWorker, or ServiceWorker scripts.' ,
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
	
	
	/**
	 * Work out where to add the nonce tagging.
	 */
	public static function init() {
		// Add's the CSP header for the request.
		add_action('get_header', array(__CLASS__,"add_header"));
		
		// Find the user set options from the database
		$options = get_option( self::SETTINGS_OPTIONS_ALLOPTIONS );
		$AddNonces = self::DoIAddNoncesToAllOptions($options);
		
		if ( $AddNonces ){
			$HighestPriority = -1000; //PHP_INT_MIN +1 ;
			$LowestPriority = PHP_INT_MAX -1 ;
			// Need to output buffer the contents as we can't filter the localize scripts code.
			add_action('wp_head', array( __CLASS__,"ob_start"),$HighestPriority);
			add_action('wp_head', array( __CLASS__,"ob_end_flush"),$LowestPriority);
			add_action('wp_footer', array( __CLASS__,"ob_start"), $HighestPriority);
			add_action('wp_footer', array( __CLASS__,"ob_end_flush"),$LowestPriority);
			add_action('wp_before_admin_bar_render', array( __CLASS__,"ob_start"), $HighestPriority );
			add_action('wp_after_admin_bar_render', array( __CLASS__,"ob_end_flush"), $LowestPriority);
			add_action('shutdown', array( __CLASS__,"ob_start"), $HighestPriority );
			add_action('shutdown', array( __CLASS__,"ob_end_flush"),$LowestPriority -1);
			
			// This routine stops our ob_end_flush() routine working properly.
			remove_action( 'shutdown',  'wp_ob_end_flush_all',  1    );
			add_action( 'shutdown',    'wp_ob_end_flush_all',  $LowestPriority);
		}
	}
	
	/**
	 * Start buffering the output ready for tagging.
	 */
	public static function ob_start() {
		ob_start( ) ;
	}
	/**
	 * Gets the contents from the buffer and tags it. Buffer stays running.
	 * @return string
	 */
	public static function ob_get_clean() {
		$content = ob_get_contents();
		ob_clean();
		$content = self::tag_string( $content );
		return $content;
	}
	/**
	 * Gets the contents from the buffer and tags it. Buffer is closed.
	 * @return string
	 */
	public static function ob_end_flush() {
		$content = ob_get_contents();
		ob_end_clean();
		$content = self::tag_string( $content );
		//echo "Content: ". esc_html( $content ). "<br>\n";
		echo $content;
	}
	
	/**
	 * Tag an arbitary string with nonces are possible
	 * @param string $html
	 * @return string
	 */
	public static function tag_string( $html){
		$html = str_replace("<script","<script nonce=".self::getNonce() , $html );
		$html = str_replace("<link","<link nonce=".self::getNonce() , $html );
		$html = str_replace("<style","<style nonce=".self::getNonce() , $html );
// 		$html = str_replace(" style="," nonce='".self::getNonce()."' style=", $html );
		// Remove any double nonces added by accident.
		$html = str_replace(" nonce=".self::getNonce()." nonce=".self::getNonce(), " nonce=".self::getNonce() ,$html);
		return $html;
	}
	
	/**
	 * Returns the CSP nonce used in the header.
	 * @return string - nonce
	 */
	public static function getNonce(){
		if ( empty( self::$CSPHeaderNonce)){
			self::$CSPHeaderNonce= self::str_rand( self::LengthOfNonce ) ;
		}
		return self::$CSPHeaderNonce;
	}
	
	/**
	 * Random string generation.
	 * @param number $largura
	 * @return string
	 */
	private static function str_rand($largura = 32){
		$chars = str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz');
		// separar a string acima com uma virgula após cada letra ou número;
		$chars = preg_replace("/([a-z0-9])/i", "$1,", $chars);
		$chars = explode(',', $chars);
		
		$string_generate = array();
		for($i = 0; $i < $largura; $i++){
			// $chars[random_int(0, 61) = largura da array $chars
			array_push($string_generate, $chars[random_int(0, 61)]);
		}
		$string_ready = str_shuffle(implode($string_generate));
		
		for($i = 0; $i < random_int(256,512); $i++){
			$random_string = str_shuffle($string_ready);
		}
		// se a largura for um número par o numero de caracteres da string for maior ou igual a 4
		if($largura % 2 === 0 && strlen($random_string) >= 4){
			$random_string_start = str_shuffle(substr($random_string, 0, $largura / 2));
			$random_string_end = str_shuffle(substr($random_string, $largura / 2, $largura));
			$new_random_string = str_shuffle($random_string_start . $random_string_end);
			return str_shuffle($new_random_string);
		}
		else {
			return str_shuffle($random_string);
		}
	}
	
	
	
	/**
	 * Add the header to each page.
	 */
	public static function add_header() {
		// Find the user set options from the database
		$options = get_option( self::SETTINGS_OPTIONS_ALLOPTIONS );
		
		$WP_Rest_Nonce =  wp_create_nonce( "wp_rest" );
		//$ReportURI_ReportOnlyBase = site_url( "/wp-json/" . WP_CSP::ROUTE_NAMESPACE . "/" . WP_CSP::ROUTE_BASE . "/LogPolicyViolation" ) ;
		$ReportURI_ReportOnlyBase = get_rest_url( null, WP_CSP::ROUTE_NAMESPACE . "/" . WP_CSP::ROUTE_BASE . "/LogPolicyViolation" );
		$ReportURI_ThisServer = add_query_arg( array("_wpnonce" => $WP_Rest_Nonce), $ReportURI_ReportOnlyBase) ;

		// Work out the report URI - used a few times in the settings.
		if ( empty( $options[ self::SETTINGS_OPTIONS_REPORT_URI_REPORTONLY ])){
			$ReportURI_ReportOnly = $ReportURI_ThisServer ;
		}
		else {
			$ReportURI_ReportOnly = $options[ self::SETTINGS_OPTIONS_REPORT_URI_REPORTONLY] ;
		}
		
		if ( empty( $options[ self::SETTINGS_OPTIONS_REPORT_URI_ENFORCE ])){
			$ReportURI_Enforce = $ReportURI_ThisServer ;
		}
		else {
			$ReportURI_Enforce = $options[ self::SETTINGS_OPTIONS_REPORT_URI_ENFORCE ] ;
		}
		
		// Work out the content security policy settings.
		$CSPOutput = array() ;
		foreach( WP_CSP::$CSP_Policies as $PolicyKey => $CSPPolicy) {
			$CSPOptions = self::CleanPolicyOptionText( $options[$PolicyKey] );
			// If self is listed, add the current site name to the CSP too as some browsers need it.
			if ( in_array( "'self'", $CSPOptions)) {
				$CSPOptions[] = site_url();
			}
			// If we have a strict dynamic setting then add a nonce 
			if ( self::DoIAddNoncesToCSPPolicy($CSPOptions)) {
				$CSPOptions[] = "'nonce-".self::getNonce()."'";
			}
			$CSPOptions = array_filter( array_unique( $CSPOptions ));
			$CSPOptionsOptionString = implode(" ", $CSPOptions ) ;
			
			// if the option sting is not empty then output it. If it is empty it will default to default-src.
			if ( !empty( $CSPOptionsOptionString ) ) {
				$CSPOutput[] = $PolicyKey . " " . $CSPOptionsOptionString ;
				// Legacy setting, some browsers still need it.
				if ( $PolicyKey == 'frame-src' ) {
					$CSPOutput[] =  "child-src " . $CSPOptionsOptionString ;
				}
			}
		}
		
		// Sandbox - If its blank its not set, if its not blank then display something.
		if ( !empty( $options[ self::SETTINGS_OPTIONS_SANDBOX]) && is_array( $options[ self::SETTINGS_OPTIONS_SANDBOX] )) {
			// If the first entry is blank then nothing should be output.
			if ( !empty( $options[ self::SETTINGS_OPTIONS_SANDBOX][0])) {
				// A true blank entry is the most restrictive type of entry
				if ( in_array( WP_CSP::SETTINGS_OPTIONS_SANDBOX_BLANKENTRY , $options[ self::SETTINGS_OPTIONS_SANDBOX] )) {
					$SandboxOptions = "" ;
				}
				else {
					$SandboxOptions = implode(" ", $options[ self::SETTINGS_OPTIONS_SANDBOX]  );
				}
				$CSPOutput[] =  "sandbox " . $SandboxOptions ;
			}
		}

		// Mixed Content - if its blank its not set, if its not blank then something needs outputting..
		if ( !empty( $options[ self::SETTINGS_OPTIONS_MIXED_CONTENT]) ) {
			switch( $options[ self::SETTINGS_OPTIONS_MIXED_CONTENT] ) {
				case self::BLOCK_ALL_MIXED_CONTENT:
					$CSPOutput[] = "block-all-mixed-content";
					break ;
					
				case self::UPGRADE_INSECURE_REQUESTS:
					$CSPOutput[] = "upgrade-insecure-requests";
					break ;
				default:
					break;
			}
		}
		
		// Require SRI - if its blank its not set, if its not blank then something needs outputting..
		if ( !empty( $options[ self::SETTINGS_OPTIONS_REQUIRE_SRI]) ) {
			$CSPOutput[] = "require-sri-for " . $options[ self::SETTINGS_OPTIONS_REQUIRE_SRI] ;
		}
		
		// Do we want the browser to log the violations with the server, or only block without logging?
		$LogViolations = false ;
		switch( $options[ self::SETTINGS_OPTIONS_LOGVIOLATIONS ] ) {
			case WP_CSP::LOGVIOLATIONS_LOG_ALL:
				$LogViolations = true ;
				break ;
			case WP_CSP::LOGVIOLATIONS_LOG_10PERC:
				if ( mt_rand(0,10) == 1){
					$LogViolations = true ;
				}
				break ;
			case WP_CSP::LOGVIOLATIONS_LOG_1PERC:
				if ( mt_rand(0,100) == 1){
					$LogViolations = true ;
				}
				break ;
			case WP_CSP::LOGVIOLATIONS_LOG_POINT1PERC:
				if ( mt_rand(0,1000) == 1){
					$LogViolations = true ;
				}
				break ;
			case WP_CSP::LOGVIOLATIONS_IGNORE:
			default:
				break ;
		}
		
		// Output the CSP header
		$ReportOnly = isset( $options[ self::SETTINGS_OPTIONS_CSP_MODE] ) ? $options[ self::SETTINGS_OPTIONS_CSP_MODE ] : 0;
		switch( $ReportOnly ) {
			case "":
			case -1: // Not In use - -1 because this was added after 0/1 were allocated.
				break ;
			case 0:
				// We want to log violations - set the correct URL to log the errors.
				if ( $LogViolations === true ) {
					$CSPOutput[] = "report-uri " . $ReportURI_Enforce  ;
				}
				header("Content-Security-Policy: " . implode( "; ", $CSPOutput ));
				break ;
			case 1:
				if ( $LogViolations === true ) {
					$CSPOutput[] = "report-uri " . $ReportURI_ReportOnly  ;
				}
				header("Content-Security-Policy-Report-Only: " . implode( "; ", $CSPOutput ));
				break ;
		}
		
		// Ensure all the header options are set before continuing.
		$HeaderOptions = array( WP_CSP::SETTINGS_OPTIONS_EXPECTCT_OPTIONS, WP_CSP::SETTINGS_OPTIONS_STS_OPTIONS, WP_CSP::SETTINGS_OPTIONS_FRAME_OPTIONS, 
				WP_CSP::SETTINGS_OPTIONS_XSS_PROTECTION, WP_CSP::SETTINGS_OPTIONS_CONTENT_TYPE_OPTIONS, WP_CSP::SETTINGS_OPTIONS_REFERRER_POLICY_OPTIONS ,
				WP_CSP::SETTINGS_OPTIONS_EXPECTCT_MAXAGE, WP_CSP::SETTINGS_OPTIONS_STS_MAXAGE, 
		) ;
		foreach( $HeaderOptions as $HeaderOption ) {
			if ( !isset( $options[ $HeaderOption ] ) || !is_numeric( $options[ $HeaderOption ] )){
				$options[ $HeaderOption ] = 0 ;
			}
		}
		
		// Find the other header options.
		$ExpectCTMaxAge = intval( $options[ WP_CSP::SETTINGS_OPTIONS_EXPECTCT_MAXAGE] ) ;
		$STSMaxAge = intval( $options[ WP_CSP::SETTINGS_OPTIONS_STS_MAXAGE] ) ;
		switch($options[ WP_CSP::SETTINGS_OPTIONS_EXPECTCT_OPTIONS ] ) {
			case "":
			case 0:
				break ;
			case 1: // Report only - do not enforce Expect CT
				header( "Expect-CT: max-age=$ExpectCTMaxAge,report-uri=$ReportURI_ReportOnly" );
				break ;
			case 2: // Enforce Expect CT
				header( "Expect-CT: enforce,max-age=$ExpectCTMaxAge,report-uri=$ReportURI_ReportOnly" );
				break ;
		}
		switch($options[ WP_CSP::SETTINGS_OPTIONS_STS_OPTIONS] ) {
			case "":
			case 0:
				break ;
			case 1: // Use with no options
				header( "Strict-Transport-Security: max-age=$STSMaxAge" );
				break ;
			case 2: // Include Sub Domains
				header( "Strict-Transport-Security: max-age=$STSMaxAge; includeSubDomains" );
				break ;
			case 3: // Preload
				header( "Strict-Transport-Security: max-age=$STSMaxAge; preload" );
				break ;
		}
		switch( $options[ WP_CSP::SETTINGS_OPTIONS_FRAME_OPTIONS] ) {
			case "":
			case 0:
				break ;
			case 1: // DENY
				header( "X-Frame-Options: DENY" );
				break ;
			case 2: // SAMEORIGIN
				header( "X-Frame-Options: SAMEORIGIN" );
				break ;
			case 3: // ALLOW-FROM
				$AllowFromURL = $options[ WP_CSP::SETTINGS_OPTIONS_FRAME_OPTIONS_ALLOW_FROM ] ;
				header( "X-Frame-Options: ALLOW-FROM $AllowFromURL" );
				break ;
		}
		switch($options[ WP_CSP::SETTINGS_OPTIONS_XSS_PROTECTION]  ) {
			case "":
			case 0:
				break ;				
			case 1: // 0 - Disable Filtering
				header( "X-XSS-Protection: 0" );
				break ;
			case 2: // 1 - Enable Filtering
				header( "X-XSS-Protection: 1" );
				break ;
			case 3: // 1; mode=block
				header( "X-XSS-Protection: 1; mode=block" );
				break ;
			case 4: // 1; report
				header( "X-XSS-Protection: 1; report=$ReportURI_Enforce" );
				break ;
		}
		switch($options[ WP_CSP::SETTINGS_OPTIONS_CONTENT_TYPE_OPTIONS] ) {
			case "":
			case 0:
				break ;
			case 1: //NOSNIFF
				header( "X-Content-Type-Options: nosniff");
				break ;
		}
		switch( $options[ WP_CSP::SETTINGS_OPTIONS_REFERRER_POLICY_OPTIONS] ) {
			case "":
			case 0:
				break ;
			case 1: // no-referrer
				header( "Referrer-Policy: no-referrer" );
				break ;
			case 2: // no-referrer-when-downgrade
				header( "Referrer-Policy: no-referrer-when-downgrade" );
				break ;
			case 3: // origin
				header( "Referrer-Policy: origin" );
				break ;
			case 4: // origin-when-cross-origin
				header( "Referrer-Policy: origin-when-cross-origin" );
				break ;
			case 5: // same-origingin
				header( "Referrer-Policy: same-origin" );
				break ;
			case 6: // strict-origin
				header( "Referrer-Policy: strict-origin" );
				break ;
			case 7: // strict-origin-when-cross-origin
				header( "Referrer-Policy: strict-origin-when-cross-origin" );
				break ;
			case 8: // unsafe-url (not recommended)
				header( "Referrer-Policy: unsafe-url" );
				break ;
		}
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
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {
		register_rest_route( WP_CSP::ROUTE_NAMESPACE , '/' . WP_CSP::ROUTE_BASE. '/LogPolicyViolation',
				array(
						'methods'         => WP_REST_Server::CREATABLE,
						'callback'        => array( __CLASS__, 'LogPolicyViolation' ),
						//'permission_callback' => array( $this, 'permissions_check_edit_posts' ),
						'args'            => array(
						),
				) );
	}
	
	
	/**
	 * Check if the call back trigger is set - if so do the work.
	 */
	public static function LogPolicyViolation() {
		
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
			WP_CSP::ProcessPolicyViolation($CSPViolation);
		}
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
	 * Put an entry into the log table so the admin can figure out what to do wwith the violation.
	 * @param array $CSPViolation
	 */
	private static function ProcessPolicyViolation( $CSPViolation ) {
		
		/*
		 * csp-report: {document-uri: "https://staging.performanceplustire.com/packages/",…}}
			csp-report
				{document-uri			: "https://staging.performanceplustire.com/packages/",…}
				blocked-uri				:	"eval"
				column-number			:	266
				disposition				:	"enforce"
				document-uri			:	"https://staging.performanceplustire.com/packages/"
				effective-directive		:	"script-src"
				line-number				:	51
				original-policy			:	"default-src 'self' https://staging.performanceplustire.com; script-src 'strict-dynamic' 'nonce-E4mbZyubjEoqQb6oiGOXomeVNLbMD5dGuux6BvGIe9BwDb09TDTiQfj1098m11itE5lfgGhprqZyElVWPdgKu57V2VSpXybtGqqAvNy9DtwWo1adQy5dlB2z01H2GgYN'; style-src 'unsafe-inline' https://ajax.googleapis.com https://staging.performanceplustire.com; img-src data: https://cdnstaging.performanceplustire.com https://insight.adsrvr.org https://secure.gravatar.com https://staging.performanceplustire.com https://stats.g.doubleclick.net https://www.google-analytics.com; font-src data: https://staging.performanceplustire.com; frame-src https://e1.fanplayr.com; child-src https://e1.fanplayr.com; connect-src https://api.rollbar.com https://staging.performanceplustire.com https://tracker.affirm.com https://www.google-analytics.com; block-all-mixed-content; report-uri https://staging.performanceplustire.com/wp-json/wpcsp/v1/route/LogPolicyViolation?_wpnonce=bfe10f966d"
				referrer				:	"https://staging.performanceplustire.com/policies/"
				script-sample			:	""
				source-file				:	"https://s.btstatic.com"
				status-code				:	0
				violated-directive		:	"script-src"
		 */
		// Two bits of information we need to be able to log the violation.
		$ViolatedDirective = '' ;
		$DocumentURI = '' ;
		$BlockedURI = '' ;
		$ColumnNumber = '' ;
		$Disposition = '';
		$LineNumber = '' ;
		$Referrer = '' ;
		$ScriptSample = '' ;
		$SourceFile = '' ;
		$StatusCode = '' ;
		$UserAgent = '' ;
		$RemoteAddress = '' ;
		$LogViolation = false;
		
		
		// Options as entered by the site admin.
		$CSPOptions = get_option( WP_CSP::SETTINGS_OPTIONS_ALLOPTIONS );
		
		//Figure out the policy that was violated.
		if ( isset( $CSPViolation['csp-report']['effective-directive'] )) {
			$ViolatedDirective = $CSPViolation['csp-report']['effective-directive'] ;
		}
		elseif ( isset( $CSPViolation['csp-report']['violated-directive'] )) {
			$parts = explode(" ", $CSPViolation['csp-report']['violated-directive'],2 );
			$ViolatedDirective = $parts[0];
		}
		
		
		$DocumentURI = isset( $CSPViolation['csp-report']['document-uri'] ) ? $CSPViolation['csp-report']['document-uri'] : '' ;
		$BlockedURI = isset( $CSPViolation['csp-report']['blocked-uri'] )  ? $CSPViolation['csp-report']['blocked-uri'] : '' ;
		$ColumnNumber = isset( $CSPViolation['csp-report']['column-number'] )  ? $CSPViolation['csp-report']['column-number'] : '' ;
		$Disposition = isset( $CSPViolation['csp-report']['disposition'] )  ? $CSPViolation['csp-report']['disposition'] : '' ; // enforce
		$LineNumber = isset( $CSPViolation['csp-report']['line-number'] )  ? $CSPViolation['csp-report']['line-number'] : '' ;
		$Referrer = isset( $CSPViolation['csp-report']['referrer'] )  ? $CSPViolation['csp-report']['referrer'] : '' ;
		$ScriptSample = isset( $CSPViolation['csp-report']['script-sample'] )  ? $CSPViolation['csp-report']['script-sample'] : '' ;
		$SourceFile = isset( $CSPViolation['csp-report']['source-file'] )  ? $CSPViolation['csp-report']['source-file'] : '' ;
		$StatusCode = isset( $CSPViolation['csp-report']['status-code'] )  ? $CSPViolation['csp-report']['status-code'] : '' ;
		
		// Find out which URL was blocked.
		if (  empty( $DocumentURI ) && isset( $_SERVER['HTTP_REFERER'] ) ){
			$DocumentURI = $_SERVER['HTTP_REFERER'] ;
		}
		
		// Find out browser information.
		$UserAgent = isset( $_SERVER['HTTP_USER_AGENT'] )  ? $_SERVER['HTTP_USER_AGENT'] : '' ;
		
		// Find out source of problem.
		$RemoteAddress = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '' ;
		
		
		// Do we have enough information to do anything with?
		if ( !empty( $ViolatedDirective ) && !empty( $BlockedURI ) ) {
			
			$LogViolation = true;
			
			// Let's see if we are set to ignore this host - Not reporting ignored URLs to stop clogging up the database
			if ( !empty( $CSPOptions[ WP_CSP::SETTINGS_OPTIONS_VIOLATIONSTOIGNORE ] ) && self::IsURIInOptionString( $BlockedURI , $CSPOptions[ WP_CSP::SETTINGS_OPTIONS_VIOLATIONSTOIGNORE ] ) ) {
				$LogViolation = false  ;
			}
			
			// Sometimes some browsers seem to cache old directives - see if the host is now OK.
			elseif ( !empty( $CSPOptions[ $ViolatedDirective ]) && self::IsURIInOptionString( $BlockedURI, $CSPOptions[ $ViolatedDirective ] ) ) {
				$LogViolation = false  ;
			}
			
			// Did the user want us to log the violations?
			elseif ( $CSPOptions[ WP_CSP::SETTINGS_OPTIONS_LOGVIOLATIONS ] == WP_CSP::LOGVIOLATIONS_IGNORE ) {
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
						WP_CSP::LogTableName(),
						array(
								'violated_directive' => $ViolatedDirective,
								'document_uri' => $DocumentURI ,
								'source_file' => $SourceFile ,
								'linenumber' => $LineNumber,
								'disposition' => $Disposition,
								'blocked_uri' => $BlockedURI ,
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
		
		$OptionURLArray = WP_CSP::CleanPolicyOptionText( $OptionString ) ;
		
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
	 * Figures out whether I need to add nonces to the output
	 * @param array string $options
	 * @return boolean
	 */
	private static function DoIAddNoncesToAllOptions( $options ) {
		
		// Let's see if we want to auto-add nonces to the calls
		$AddNonces = false ;
		foreach( WP_CSP::$CSP_Policies as $PolicyKey => $CSPPolicy) {
			if ( !isset( $options[$PolicyKey] )){
				$options[$PolicyKey] = '' ;
			}
			$CSPOptions = self::CleanPolicyOptionText( $options[$PolicyKey] );
			$AddNonces = self::DoIAddNoncesToCSPPolicy( $CSPOptions );
			if ( $AddNonces === true ) {
				break ;
			}
		}
		return $AddNonces;
	}
	/**
	 * Figures out whether I need to add nonces to the individual policy
	 * @param array string $CSPOptions
	 * @return boolean
	 */
	private static function DoIAddNoncesToCSPPolicy( $CSPOptions ) {
		
		$AddNonces = false ;
		// If we have a strict dynamic setting then add a nonce
		if ( in_array( "'strict-dynamic'", $CSPOptions)) {
			$AddNonces = true;
		}
		else {
			foreach( $CSPOptions as $CSPOption ){
				if ( substr( $CSPOption , 0 , 4) == "'sha-" ) {
					$AddNonces = true;
					break ;
				}
			}
		}
		return $AddNonces;
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

add_action('init',array("WP_CSP","init"));
// If action "rest_api_init" hasn't run yet then use that, otherwise we have the route server in place, just register route
if ( did_action('rest_api_init') == 0 ){
	add_action('rest_api_init',array("WP_CSP","register_routes"));
}
else {
	WP_CSP::register_routes();
}