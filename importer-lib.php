<?php

class wp_importer {
	/**
	 * Version number
	 *
	 * @var string
	 */
	private $version = '1.1';
	private $posttype = null;
	private $metakey = null;
	private $debugmode = 0;
	private $debugprefix = " ### ";
	private $post_object = array();
	private $meta_prefix = 'himp_';
	private $default_name = '';

	public function __construct( $name = 'Imported' ) {
		$this->default_name = $name;
	}


	public function epi_to_wp_date( $epidate ) {
		date_default_timezone_set( 'Europe/Stockholm' );
		$parsed = date_parse( $epidate );
		return date( 'Y-m-d H:i:s', mktime( $parsed['hour'], $parsed['minute'], $parsed['second'], $parsed['month'], $parsed['day'], $parsed['year'] ) );
	}

	public function create_insert_user( $name = 'Scouterna.se', $email = null, $role = 'subscriber' ) {
		$userid = null;
		if ( $name == '' )
			$name = $this->default_name;

		$user_username = sanitize_user( $name );
		$user_username = preg_replace( '|[^a-z0-9_.\-]|i', '', $user_username );
		$user_displayname = $name;
		$user_email = $email ?: "$user_username@importeduser.se";

		if ( $data = get_user_by( 'login', $user_username ) ) {
			echo "got the id ($data->ID) for username '$user_username'!\n";
			$userid = $data->ID;
		}
		elseif ( $data = get_user_by( 'email', $user_email ) ) {
			echo "got the id ($data->ID) for email '$user_email'!\n";
			$userid = $data->ID;
		}
		else {
			$userdata['user_pass'] = wp_generate_password( $length=12, $include_standard_special_chars=false );
			$userdata['user_login'] = $user_username;
			$userdata['user_nicename'] = $name;
			$userdata['user_email'] = $user_email;
			$userdata['display_name'] = $name;
			$userdata['role'] = $role;
			print_r( $userdata );
			$userid = wp_insert_user( $userdata );
			if ( is_wp_error( $userid ) )
				echo $userid->get_error_message();
			echo "inserted user '$user_username' with email '$user_email' with user ID '$userid'\n";
		}

		return $userid;
	}


	public function wipePostObjects() {
		global $wpdb;
		$query =  "SELECT * FROM $wpdb->posts WHERE post_type = '".$this->posttype. "'";

		$fivesdrafts = $wpdb->get_results( $query );
		if ( $fivesdrafts ) {
			$counter = 0;
			foreach ( $fivesdrafts as $post ) {
				$counter++;
				$this->printDebugMsg( "Deleting " . $post->ID ." : $counter" );
				wp_delete_post( $post->ID, true );
			}
		}
	}


	private $custom_fields = array(
		array( "key"   => "representative", "value" => 'assa' ),
	);


	public function addCustomField( $key, $value, $prefix = true ) {
		if ( $prefix )
			$extraprefix = $meta_prefix;
		else
			$extraprefix = '';
		$this->custom_fields[]=array( "key" => "$extraprefix$key", "value"=>$value );
		$this->printDebugMsg( "Setting up meta keypair: '$key':'$value'" );
		print_r( $this->custom_fields );

	}

	public function resetCustomFields() {
		$this->custom_fields = array();
	}

	public function resetPostObject() {
		$this->post_object = array();
	}

	public function printDebugMsg( $debugmsg ) {
		echo $this->debugprefix . "$debugmsg\n";
	}

	public function getAsset( $value ) {
		if ( !$this->posttype ) die( "Posttype not defined\n" );
		if ( !$this->metakey ) die( "Posttype not defined\n" );

		if ( $retval = $this->find_asset( $value, $this->posttype, $this->metakey ) )
			$this->printDebugMsg( "Found asset for '$value'" );
		else
			$this->printDebugMsg( "Did NOT find asset for '$value'" );

		return $retval;
	}

	private function addCustomFields( $id ) {

		for ( $i = 0;$i<count( $this->custom_fields );$i++ ) {
			add_post_meta( $id, $this->custom_fields[$i]["key"], $this->custom_fields[$i]["value"], true );
			$this->printDebugMsg( "added meta keys to $id : " . $this->custom_fields[$i]["key"] . ': ' .$this->custom_fields[$i]["value"] );

		}
	}


	public function setAssetFields( $array ) {
		$this->post_object = $array;
		$this->post_object['post_type'] = $this->posttype;
	}

	public function insertAsset() {
		$inserted_as = wp_insert_post( $this->post_object, true );

		if ( is_wp_error( $inserted_as ) ) {
			echo $inserted_as->get_error_message();
			die;
		}
		$this->addCustomFields( $inserted_as );
		$this->resetCustomFields();
		$this->resetPostObject();
		return $inserted_as;
	}


	public function find_asset( $value, $posttype, $metakey ) {
		$retArr = array();
		$args = array(
			'numberposts'     => 100,
			'post_type'       => $posttype,
			'post_status'     => 'publish,draft',
			'meta_key' => $this->meta_prefix . $metakey,
			'meta_value' => $value
		);

		print_r( $args );

		$posts_array = get_posts( $args );
		$retval = null;
		if ( count( $posts_array )>0 ) {
			$retval = $posts_array[0];

		}

		wp_reset_query();
		return $retval;
	}

	/**
	 * Set the default username for objects beeing inserted
	 *
	 * @param string  $default_name 'Imported username'
	 */
	public function setDefaultName( $custom_default_name ) { $this->default_name = $custom_default_name; }
	/**
	 * Get the default username for objects beeing inserted
	 *
	 * @return string 'Imported username'
	 */
	public function getDefaultName() {  return $this->default_name;      }

	/**
	 * Set the default post type for objects beeing inserted
	 *
	 * @param string  $posttype Posttype, eg. 'post', 'page' etc
	 */
	public function setPostType( $posttype ) { $this->posttype = $posttype; }
	/**
	 * Get the default post set for objects beeing inserted
	 *
	 * @return string Posttype, eg. 'post', 'page' etc
	 */
	public function getPostType() { return $this->posttype;      }

	public function setMetaKey( $metakey ) { $this->metakey = $metakey;    }
	public function getMetaKey() { return $this->metakey;        }

	public function setDebugMode( $debugmode ) { $this->debugmode = $debugmode;    }
	public function getDebugMode() { return $this->debugmode;        }


}
