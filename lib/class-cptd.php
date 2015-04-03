<?php
class CPTD{

	static $classes = array('cptd-options', 'cptd-pt', 'cptd-tax', 'cptd-view', 'cptd-search-widget');

	/* 
	* Admin Routines
	*/
	function admin_enqueue(){
		# CSS
		wp_enqueue_style("cptdir-admin-css", cptdir_url("css/cptdir-admin.css"));
	
		$screen = get_current_screen();

		# JS
		if($screen->id == 'cpt-directory_page_cptdir-fields'){
			wp_enqueue_script('cptdir-fields-js', cptdir_url('js/cptdir-fields.js'), array('jquery'));
		}	
		elseif($screen->id == 'cpt-directory_page_cptdir-cleanup'){
			wp_enqueue_script('cptdir-cleanup-js', cptdir_url('js/cptdir-cleanup.js'), array('jquery'));
		}
	}
	function admin_menu() {
		add_menu_page('CPT Directory Settings', 'CPT Directory', 'administrator', 'cptdir-settings-page', array('CPTD_Options', 'settings_page'));
		add_submenu_page( 'cptdir-settings-page', 'CPT Directory Settings', 'Settings', 'administrator', 'cptdir-settings-page', array('CPTD_Options', 'settings_page'));
		add_submenu_page( 'cptdir-settings-page', 'Edit Fields | CPT Directory', 'Fields', 'administrator', 'cptdir-fields', array('CPTD_Options','fields_page' ));
		add_submenu_page( 'cptdir-settings-page', 'Clean Up | CPT Directory', 'Clean Up', 'administrator', 'cptdir-cleanup', array('CPTD_Options','cleanup_page'));	
		add_submenu_page("cptdir-settings-page", "Import | CPT Directory", "Import", "administrator", "cptdir-import", array('CPTD_Options','import_page'));
	}
	/*
	* Front End Routines
	*/
	function enqueue(){
		# CSS
		wp_enqueue_style("cptdir-css", cptdir_url("css/cptdir.css"));
	}

	/*
	* Helper Functions
	*/
	
	# require a file, checking first if it exists
	static function req_file($path){ if(file_exists($path)) require_once $path; }
	
	# Sanitize form input
	public static function san($in){
		return trim(preg_replace("/\s+/", " ", strip_tags($in)));
	}
	# Return slug-formatted string for given input
	public static function clean_str_for_url( $sIn ){
		/*****
		Uncomment Lines In Between Commands To Troubleshoot at each step.
		*****/
		if( $sIn != "" && is_string( $sIn ) ) { 
			// Lowercase and Initial Whitespace Trim	
			$sOut = trim( strtolower( $sIn ) );
			$sOut = preg_replace( "/\s\s+/" , " " , $sOut );					
			//echo "<u>Lowercase and Initial Whitespace Trim:</u><br />'". convert_space_to_nbsp( $sOut ) ."'<br /><br />";

			// Alpha-Numeric, Spaces, and Dashes Only
			$sOut = preg_replace( "/[^a-zA-Z0-9 -]/" , "",$sOut );
			//echo "<u>Alpha-Numeric, Spaces, and Dashes Only:</u><br />'". convert_space_to_nbsp( $sOut ) ."'<br /><br />";

			// No Multiple Dashes
			$sOut = preg_replace( "/--+/" , "-",$sOut );
			//echo "<u>No Multiple Dashes:</u><br />'". convert_space_to_nbsp( $sOut ) ."'<br /><br />";

			// No Spaces Around Dashes
			$sOut = preg_replace( "/ +- +/" , "-",$sOut );
			//echo "<u>No Spaces Around Dashes:</u><br />'". convert_space_to_nbsp( $sOut ) ."'<br /><br />";	

			//Remove any Double Spaces
			$sOut = preg_replace( "/\s\s+/" , " " , $sOut );
			//echo "<u>Remove Double Spaces:</u><br />'". convert_space_to_nbsp( $sOut ) ."'<br /><br />";

			// 	Replace Remaining Spaces With Dash
			$sOut = preg_replace( "/\s/" , "-" , $sOut );
			//echo "<u>Replace Remaining Spaces With Dash:</u><br />'". convert_space_to_nbsp( $sOut ) ."'<br /><br />";

			// 	One Last Remove Multiple Dashes
			$sOut = preg_replace( "/--+/" , "-" , $sOut );
			//echo "<u>One Last Remove Multiple Dashes:</u><br />" . "'{$sDirty}'<br /><br />";		

			// Remove trailing dash
			$nWord_length = strlen( $sOut );
			if( $sOut[ $nWord_length - 1 ] == "-" ) { $sOut = substr( $sOut , 0 , $nWord_length - 1 ); } 
			return $sOut;
		}
		else{ return false;}
	}	
	# Return field_formatted string for given input
	public static function str_to_field_name( $sIn  ){
		/*****
		Uncomment echo statements to see results at each step.
		*****/
		if( $sIn != "" && is_string( $sIn ) ) { 
			// Lowercase and Initial Whitespace Trim	
			$sOut = trim( strtolower( $sIn ) );
			$sOut = preg_replace( "/\s\s+/" , " " , $sOut );					
			//echo "<u>Lowercase and Initial Whitespace Trim:</u><br />'". convert_space_to_nbsp( $sOut ) ."'<br /><br />";

			// Alpha-Numeric, Spaces, and Underscores Only
			$sOut = preg_replace( "/[^a-zA-Z0-9 _]/" , "_",$sOut );
			//echo "<u>Alpha-Numeric, Spaces, and Underscores Only:</u><br />'". convert_space_to_nbsp( $sOut ) ."'<br /><br />";

			// No Multiple Underscores
			$sOut = preg_replace( "/__+/" , "_",$sOut );
			//echo "<u>No Multiple Underscores:</u><br />'". convert_space_to_nbsp( $sOut ) ."'<br /><br />";

			// No Spaces Around Underscores
			$sOut = preg_replace( "/ +_ +/" , "_",$sOut );
			//echo "<u>No Spaces Around Underscores:</u><br />'". convert_space_to_nbsp( $sOut ) ."'<br /><br />";	

			// Remove any Double Spaces
			$sOut = preg_replace( "/\s\s+/" , " " , $sOut );
			//echo "<u>Remove Double Spaces:</u><br />'". convert_space_to_nbsp( $sOut ) ."'<br /><br />";

			// Replace Remaining Spaces With Underscore
			$sOut = preg_replace( "/\s/" , "_" , $sOut );
			//echo "<u>Replace Remaining Spaces With Underscore:</u><br />'". convert_space_to_nbsp( $sOut ) ."'<br /><br />";

			// One Last Remove Multiple Underscores
			$sOut = preg_replace( "/__+/" , "_" , $sOut );
			//echo "<u>One Last Remove Multiple Underscores:</u><br />'" . convert_space_to_nbsp( $sOut ) . "'<br /><br />";		

			// Remove trailing Underscore
			$nWord_length = strlen( $sOut );
			if( $sOut[ $nWord_length - 1 ] == "_" ) { $sOut = substr( $sOut , 0 , $nWord_length - 1 ); } 
			return $sOut;
		}
		else{ return false;}
	}
	function convert_space_to_nbsp( $sIn ){
		return preg_replace( '/\s/' , '&nbsp;' , $sIn );	
	}
	# get an array of IDs for all post type objects (default is published only, passing false returns all)
	public static function get_all_cpt_ids($bPub = true){
		# get all post objects
		$aPosts = self::get_all_cpt_posts($bPub);
		$aIDs = array();
		if($aPosts) foreach($aPosts as $post){
			$aIDs[] = $post->ID;
		}
		return $aIDs;
	}
	# get an array of post objects for our PT (default is published only, passing false returns all)
	public static function get_all_cpt_posts($bPub = true){
		$aOut = array();
		$pt = cptdir_get_pt();
		$slug = $pt->name;
		$args=array(
		  'post_type' => $slug,
		  'posts_per_page' => -1,
		);
		if($bPub) $args["post_status"] = "publish";
		$cpt_query = new WP_Query($args);
		if( $cpt_query->have_posts() ) {
		  while ($cpt_query->have_posts()) : $cpt_query->the_post();
			$aOut[] = $cpt_query->post;
		  endwhile;
		}
		wp_reset_query();
		return $aOut;
	}
	# filter out WP extraneous post meta
	function filter_post_meta($a){
		global $view;
		$out = array();
		if(!$a) return;
		foreach($a as $k => $v){
			# if value is an array, take the first item
			if(is_array($v)){
				if($v[0]) $v = $v[0];
			}
			# do nothing if value is empty
			if("" == $v) continue;
			# check if this is an ACF field
			$bACF = self::is_acf($v);
			if($bACF){
				$view->acf_fields[$k] = $v;
			}
			# Filter out any fields that start with an underscore or that are empty
			## save any ACF fields
			if(
				!in_array($v, $out) 
					&& ( (strpos($k, "_") !== 0 || strpos($k, "_") === false)
						&& !$bACF
					)
			){
				#echo "$k: $v"; echo "<br /><br />";
				$out[$k] = $v;
			}
		}
		return $out;
	}
	# Return list of all fields for single listing
	public static function get_fields_for_listing($id){
		$fields = self::filter_post_meta(get_post_meta($id));
		return $fields;
	}
	# Return list of all fields for custom post type
	public static function get_all_custom_fields($bActive = false){
		# array of custom fields we'll return
		$aCF = array();
	
		# Go through posts and scour field names
		do{
			# Quit if we don't have a post type to work with
			if(!($obj = cptdir_get_pt())) break;
			# Quit if we don't have a slug
			if(!is_object($obj) || !property_exists($obj, "name")) break;
			$slug = $obj->name;
			if(!$slug) break;
			# Get ID's of posts for our CPT
			global $wpdb;
			$aPosts = $wpdb->get_results( "SELECT DISTINCT ID FROM " . $wpdb->prefix . "posts WHERE post_type='$slug'" );
			# Loop through posts ID's for CPT and get list of custom fields
			if(!$aPosts) break;
			foreach($aPosts as $post){
				# Grab all custom fields for post
				$aPM = get_post_custom_keys($post->ID);
				if(!$aPM) continue;

				foreach($aPM as $field){
					# Filter any fields that have already been found and ones that start with the underscore character
					# If a field passes this filter, we'll add it to the $aCF array and show it in the table
					if(!in_array($field, $aCF) && (strpos($field, "_") !== 0 || strpos($field, "_") === false)){
						# This will check if the field has any posts that actually use it
						if($bActive){
							if((!in_array($field, $aCF)) && ("" != get_post_meta($post->ID, $field, true))) $aCF[] = $field;
						}
						else{
							if(!in_array($field, $aCF)) $aCF[] = $field; 
						}
					}
				}
			}
		} while(0); #end: scour posts for field values
	
		# Get Advanced Custom Fields fields if we're not filtering out inactive fields
		if(!$bActive){
			$aACF_fields = self::get_acf_fields();
			foreach($aACF_fields as $a){ if(!in_array($a['name'], $aCF)) $aCF[] = $a['name']; }
		}
		return $aCF;
	}
	# Check if a field is an ACF field
	public static function is_acf($field){
		return preg_match("/field_[\dA-z]+/", $field);
	}
	# Get advanced custom fields
	public static function get_acf_fields(){
		global $wpdb;
		$out = array();
		$args=array(
		  'post_type' => "acf",
		  'post_status' => 'publish',
		  'posts_per_page' => -1,
		);
		$acf_query = new WP_Query($args);
		if( $acf_query->have_posts() ) {
		  while ($acf_query->have_posts()) : $acf_query->the_post();
			$post = $acf_query->post;
			$r = $wpdb->get_results("SELECT meta_value FROM ". $wpdb->prefix . "postmeta WHERE post_id = " . $post->ID . " AND meta_key LIKE \"%field_%\"");
			foreach($r as $field){
				$aField = unserialize($field->meta_value);
				$out[] = $aField;
			}
		  endwhile;
		}
		wp_reset_query();
		return $out;
	}
	# Get all meta values for a certain key
	public static function get_meta_values( $key = '', $type = "", $status = 'publish' ) {
		if( empty( $key ) ) return;
		$pt = cptdir_get_pt();
		if(!$type){
			if(!$pt) return;
			$type = $pt->name;
		}
		global $wpdb;
		$r = $wpdb->get_col( 
			$wpdb->prepare(
				"SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm
				LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
				WHERE pm.meta_key = '%s' 
				AND p.post_status = '%s' 
				AND p.post_type = '%s'", 
				$key, $status, $type 
			)
		);
		return $r;
	}

	###
	# Front end views
	###

	# Search results
	public static function do_search_results(){
		# Make an array of filters from post
		$aFilters = array();
		# Loop through post and sanitize data to store in array
		foreach($_POST as $k => $v){
			# don't include blank fields
			if(!$v) continue;	
			# make sure we only use our own search keys
			$key = sanitize_key($k);
			$matches = array();
			# check first for dropdown options
			if(!preg_match("/cptdir-(.*)?-select/", $key, $matches)) 
				# then check if we're dealing with the widget ID we passed
				if( $k != "cptdir-search-widget-id") continue;
			# if we have matched a dropdown select, store the trimmed-down key and sanitized value
			if(array() != $matches)
				$aFilters[$matches[1]] = sanitize_text_field($v);
			# otherwise see if we have a widget ID and store it for later
			elseif(preg_match("/cptdir_search_widget-(\d)+?/", $v, $matches))
				$widget_id = $matches[1];
		}
		# Make a WP_Query object from the search filters
		$pt = cptdir_get_pt();
		$args = array(
			"post_type" => $pt->name,
			"posts_per_page" => -1,
		);
		# Add taxonomy query if necessary
		$args['tax_query'] = array();	
		# Check if we have the category tax set
		if(array_key_exists("ctax", $aFilters)){
			$ctax = cptdir_get_cat_tax();
			$ctax_args = array(
				"taxonomy" => $ctax->name,
				"terms" => intval($aFilters["ctax"]),
			);
			# push this array into main tax_query array
			$args['tax_query'][] = $ctax_args;
		}
		# Check if we have the tag tax set
		if(array_key_exists("ttax", $aFilters)){
			$ttax = cptdir_get_tag_tax();
			$ttax_args = array(
				"taxonomy" => $ttax->name,
				"terms" => intval($aFilters["ttax"]),
			);
			# push this array into main tax_query array
			$args['tax_query'][] = $ttax_args;	
		}
		# Check for custom fields
		$args["meta_query"] = array();
		foreach($aFilters as $k => $v){
			if($k == "ctax" || $k == "ttax") continue;
			$meta_args = array(
				"key" => $k,
				"value" => $v,
			);
			$args["meta_query"][] = $meta_args;
		}
		# Make the QP_Query object and loop through results
		$s_query = new WP_Query($args);
		# If you wish to make your own search results layout, 
		# create a function named cptdir_search_results()
		# you will be passed the wp_query object and an array containing the post type and taxonomies
		if(function_exists("cptdir_search_results")){ cptdir_search_results($s_query, $widget_id); return; }

		# Copying the block of code below may be a good start.	
		### BEGIN SEARCH RESULTS ###
		if($s_query->have_posts()){
		?>
			<p class="cptdir-post-count">We found <?php echo $s_query->post_count . " " . $pt->labels['name'] ;?> that matched your query.</p>
			<div id="cptdir-search-results">
		<?php
			# Search Results Loop
			do{
				$s_query->the_post();
				$post = $s_query->post;
		?>
				<div class="cptdir-search-result-item">
				<?php
					if(has_post_thumbnail()) the_post_thumbnail("thumbnail", array("class" => "cptdir-archive-thumb alignleft"));
				?>
					<h3 class="cptdir-archive-header"><a href="<?php echo get_permalink($post->ID); ?>"><?php echo $post->post_title; ?></a></h3>			
					<div style="clear: left;"></div>
				</div><?php # .cptdir-search-result-item ?>
			<?php
			} while($s_query->have_posts());
			# end: search results loop
			?>
			</div><?php # #cptdir-search-results ?>
			<?php
		}
		# endif: have_posts
		else{
		?>
			<p>Sorry, we didn't find any results. Please narrow your search parameters</p>
		<?php
			# Display the widget that originally sent us here.
			$widget_options = get_option("widget_cptdir_search_widget");
			if($widget_options){
				if($widget_options[$widget_id]) the_widget("CPTD_search_widget", $widget_options[$widget_id]);
			}
		}
		### END SEARCH RESULTS ###
	}
} #end class
# require dependencies
foreach(CPTD::$classes as $class){
	CPTD::req_file(cptdir_dir("lib/class-{$class}.php"));
}
?>