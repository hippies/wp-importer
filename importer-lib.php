<?php


class wp_importer
{  

	private $version = '1.0';
    private $posttype = null; 
    private $metakey = null; 
	private $debugmode = 0;
	private $debugprefix = " ### "; 
	private $post_object = array();
	private $meta_prefix = 'himp_';



	public function wipePostObjects()
	{                             
		global $wpdb;                    
		$query =  "SELECT * FROM $wpdb->posts WHERE post_type = '".$this->posttype. "'";
		echo "$query \n";

		$fivesdrafts = $wpdb->get_results($query);
		print_r($fivedrafts);
		if ($fivesdrafts) 
        {
                $counter = 0;
                foreach ($fivesdrafts as $post) 
                {
                        $counter++;
                        print $post->ID ." : $counter \n";      
                        wp_delete_post($post->ID,true);
                }
        }
     }


	private $custom_fields = array(
		array("key"   => "representative","value" => 'assa'),
			);


	public function addCustomField($key,$value)
	{
			$this->custom_fields[]=array("key" => "himp_$key", "value"=>$value);			
			$this->printDebugMsg("Setting up meta keypair: '$key':'$value'");
			print_r($this->custom_fields);

	}

	public function resetCustomFields()
	{
			$this->custom_fields = array();
	}

	public function resetPostObject()
	{
			$this->post_object = array();
	}

	public function printDebugMsg($debugmsg)
	{
		echo $this->debugprefix . "$debugmsg\n";
	}

	public function getAsset($value)
	{
		if (!$this->posttype) die("Posttype not defined\n");
		if (!$this->metakey) die("Posttype not defined\n");

		if ($retval = $this->find_asset($value, $this->posttype, $this->metakey))
				$this->printDebugMsg("Found asset for '$value'");
			else
				$this->printDebugMsg("Did NOT find asset for '$value'");

		return $retval;		
	}

	private function addCustomFields($id)
	{

		for($i = 0;$i<count($this->custom_fields);$i++)
		{
			add_post_meta($id, $this->custom_fields[$i]["key"], $this->custom_fields[$i]["value"], true);
			$this->printDebugMsg("added meta keys to $id : " . $this->custom_fields[$i]["key"] . ': ' .$this->custom_fields[$i]["value"]);
						
		}
	}


	public function setAssetFields($array)
	{
			$this->post_object = $array; 
			$this->post_object['post_type'] = $this->posttype; 
	}

	public function insertAsset()

	{			
			$inserted_as = wp_insert_post( $this->post_object, true);

			if ( is_wp_error($inserted_as) )
				{
				   echo $inserted_as->get_error_message();			
				   die; 
				}
			$this->addCustomFields($inserted_as);
			$this->resetCustomFields();
			$this->resetPostObject();
			return $inserted_as; 			
	}


	public function find_asset($value,$posttype,$metakey){
		$retArr = array();
		$args = array(
					'numberposts'     => 100,
					'post_type'       => $posttype,
					'post_status'     => 'publish,draft',
					'meta_key' => $this->meta_prefix . $metakey,
					'meta_value' => $value
					);
		
		print_r($args);

		$posts_array = get_posts( $args );
		$retval = null;
		if(count($posts_array)>0){
			$retval = $posts_array[0];
			
		}

        wp_reset_query();   
		return $retval;		
	}


    public function setPostType($posttype) { $this->posttype = $posttype; }
    public function getPostType() { return $this->posttype; 			  }

    public function setMetaKey($metakey) { $this->metakey = $metakey; 	  }
    public function getMetaKey() { return $this->metakey; 			 	  }

    public function setDebugMode($debugmode) { $this->debugmode = $debugmode; 	  }
    public function getDebugMode() { return $this->debugmode; 			 	  }


}