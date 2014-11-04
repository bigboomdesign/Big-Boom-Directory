<?php
class CPTD_view{
	var $ID = 0;
	var $type = "";
	
	var $fields = array();
	var $acf_fields = array();
	
	function __construct($args = array()){
		# Loop through arguments and set object variables
		foreach($args as $arg => $val){
			if(property_exists($this, $arg)) $this->$arg = $val;
		}
	} # end: __construct()
	# single field display
	public static function do_single_field($field){
		# for image fields
		if($field["type"] == "image"){
			$src = "";
			switch($field['save_format']){
				case "object":
				case "url":
					# if set to return an object, we'll have an array as the value
					# otherwise we'll have the URL string
					$src = is_array($field['value']) ? $field['value']['url'] : $field['value'];
				break;
				case "id";
					$src = wp_get_attachment_url($field['value']);
				break;
			}
			# show image if we have a src
			if($src){?>
				<img class="cptdir-image <?php echo $field['name' ]; ?>" 
						src="<?php echo $src; ?>" />	
			<?php 
			}
			# go to next field after showing the image
			return;
		} # endif: image field
		elseif($field["type"] == "gallery"){
			if($field["value"]){
			?><div class="cptdir-gallery <?php echo $field['name']; ?>"><?php
					$i = 0;
					foreach($field["value"] as $img){
						$i++;
					?>
						<img class="cptdir-image <?php echo $field['name'] . " " . $i; ?>" src="<?php echo $img['url']; ?>" />
					<?php
					} #end foreach: gallery image
			?></div>
			<?php
			}#endif: value exists

			# go to next field after showing the gallery
			return;
		}
		
		# field wrapper for text-based fields
		?><div class="cptdir-field <?php echo $field['type'] . " " . $field['name']; ?>">
			<label><?php echo $field['label'];?>: &nbsp; </label><?php echo $field["value"]; ?>
		</div>
	<?php
	}
	# display fields for listing
	function do_fields($callback = ""){
		if(!$this-ID) return;
		
		# if ACF is activated
		if(function_exists("get_fields")){
			$fields = get_fields($this->ID);
			$fields = CPTDirectory::filter_post_meta($fields);

			$ordered_fields = array();
			
			# order the fields
			foreach($fields as $field => $value){
				$aField = get_field_object($field);
				if($aField['order_no'] >= 0) $ordered_fields[$aField['order_no']] = $aField;
			}
			ksort($ordered_fields);

			# If callback is set, filter out fields using user-specified conditions
			if(function_exists($callback)){$ordered_fields = array_filter($ordered_fields, $callback);}
						
			# loop through fields and display label & value
			if($ordered_fields){
				?><div class="cptdir-fields-wrap"><?php
					foreach($ordered_fields as $field){
						$this->do_single_field($field, $callback);
					} # end foreach: fields
				?></div><?php
			} #end if: fields exist
		}
		# work in progress: plugin behavior without ACF
		else $fields = CPTDirectory::get_fields_for_listing($this->ID);
	}
} # end class: CPTD_view