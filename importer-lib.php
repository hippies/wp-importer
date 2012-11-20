<?php


class wp_importer
{  

	function find_asset($value,$posttype,$metakey){
		$retArr = array();
		$args = array(
					'numberposts'     => 100,
					'post_type'       => 'produkt',
					'post_status'     => 'publish,draft',
					'meta_key' => 'product_id',
					'meta_value' => $value
					);
		
		$posts_array = get_posts( $args );
		$retval = -1;
		if(count($posts_array)>0){
			$retval = $posts_array[0];
			
		}
        wp_reset_query();   
		return $retval;
		
	}

}