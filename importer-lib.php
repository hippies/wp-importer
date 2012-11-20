<?php


class wp_importer
{  

	private $version = '1.0';
    private $posttype = null; 
    private $metakey = null; 
	private $debugmode = 0;
	private $debugprefix = " ### "; 

	private $custom_fields = array(
		array("key"   => "representative","value" => 'assa'),
			);


	public function addCustomField($key,$value)
	{
			$custom_fields[]=array("key" => "himp_$key", "value"=>$value);			
			$this->printDebugMsg("Setting up meta keypair: '$key':'$value'");
	}

	public function resetCustomFields()
	{
			$this->custom_fields = array();
	}

	private function printDebugMsg($debugmsg)
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

	public function find_asset($value,$posttype,$metakey){
		$retArr = array();
		$args = array(
					'numberposts'     => 100,
					'post_type'       => 'produkt',
					'post_status'     => 'publish,draft',
					'meta_key' => 'product_id',
					'meta_value' => $value
					);
		
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