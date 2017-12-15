<?php

use \OpenCloud\Rackspace;
 
/**
********************************************************************************************
* Cloudfiles
*
* Provides a basic file management abstraction on top of the PHP-Opencloud SDK.
* 
* This model requires PHP-OpenCloud the official PHP SDK for Rackspace.
* https://developer.rackspace.com/sdks/php/
* https://github.com/rackspace/php-opencloud
* 
* @package Cloudfiles
* @version 0.0.2 
* @author  David Simmons <dsimmons.me>
* @license LGPLv3
********************************************************************************************
*/
class Cloudfiles extends Model{

	public static $rackspace_api_username 	= null;
	public static $rackspace_api_key 	  	= null;
	public static $rackspace_api_region 	= null;
	public static $rackspace_api_container 	= null;
	public static $rackspace_api_client  	= null;
	public static $rackspace_media_types 	= null;

	/**
	*****************************************************************************************************
	* Connects to and authenticates against the Rackspace API
	*
	* @access 	public
	* @return 	void
	*****************************************************************************************************
	*/
	public static function cfConnect(){

		$rackspace_credentials = array(
			'username' 	=> self::$rackspace_api_username, 
			'apiKey' 	=> self::$rackspace_api_key
		);

		self::$rackspace_api_client = new Rackspace(RACKSPACE_US, $rackspace_credentials);

	}

	/**
	*****************************************************************************************************
	* Fetches the symbolic "folders" from all objects in Cloud Files
	* By default, 10,000 objects are returned as a maximum
	*
	* @access 	public
	* @param   	$invisible	array Array of folder names to ignore when fetching folders
	* @return 	array
	* @todo 	This is a low performance method to extract symbolic folders. Needs improvement, 
	* 			however at this time the API does not expose methods for dealing with the concept
	*           of "folders" as they are only symbolic by nature - the cloud filesystem is linear.
	*****************************************************************************************************
	*/
	public static function cfGetFolders($invisible = array()){

		self::cfConnect();

        $service 	= self::$rackspace_api_client->objectStoreService(null, self::$rackspace_api_region);
		$container 	= $service->getContainer(self::$rackspace_api_container);
		$objects 	= $container->objectList();
		$folders 	= array();
		foreach($objects as $object){
			$object_name = $object->getName();
			$object_type = $object->getContentType();
			if($object_type == 'application/directory' && !in_array($object_name, $invisible)){
				$folders[] = array(
					'object_name' 	=> $object_name,
					'object_type' 	=> $object_type
					);
			}
		}

		return $folders;

	}

	/**
	*****************************************************************************************************
	* Fetches the Public URLs for all objects in Cloud Files
	* By default, 10,000 objects are returned as a maximum
	*
	* @param 	$folder 		string 		Optional folder name to filter objects by.
	* @access 	public
	* @return 	array
	* @todo 	This is a low performance method to filter by symbolic folders. Needs improvement, 
	* 			however at this time the API does not expose methods for dealing with the concept
	*           of "folders" as they are only symbolic by nature - the cloud filesystem is linear.
	*****************************************************************************************************
	*/
	public static function cfGetObjects($folder = ''){

		self::cfConnect();

        $service 	= self::$rackspace_api_client->objectStoreService(null, self::$rackspace_api_region);
		$container 	= $service->getContainer(self::$rackspace_api_container);
		$objects 	= $container->objectList();
		$files 		= array();
		foreach($objects as $object){
			$object_name 	= $object->getName();
			$object_size 	= $object->getContentLength();
			$object_type 	= $object->getContentType();
			$object_folder 	= dirname($object_name);
			if($object_type != 'application/directory'){
				if($folder != ''){
					if($folder == $object_folder){
						$files[] = array(
							'object_url' 	=> (string) $object->getPublicUrl(),
							'object_name' 	=> $object_name,
							'object_size' 	=> $object_size,
							'object_type' 	=> $object_type,
							'object_folder' => $object_folder
							);
					}
				} else {
					$files[] = array(
						'object_url' 	=> (string) $object->getPublicUrl(),
						'object_name' 	=> $object_name,
						'object_size' 	=> $object_size,
						'object_type' 	=> $object_type,
						'object_folder' => $object_folder
						);
				}
			}
		}

		return $files;

	}

	/**
	*****************************************************************************************************
	* Returns true or false if the requested object exists in Cloud Files
	*
	* @param 	$objPath 		string 		Full path and filename of the object within the container.
	* @access 	public
	* @return 	boolean
	*****************************************************************************************************
	*/
	public static function cfIsObject($objPath){

		self::cfConnect();

        $service 	= self::$rackspace_api_client->objectStoreService(null, self::$rackspace_api_region);
        $container 	= $service->getContainer(self::$rackspace_api_container);
       	$exists 	= $container->objectExists($objPath);

        return ($exists) ? TRUE : FALSE;

	}

	/**
	*****************************************************************************************************
	* Fetches the Public URL for a given object in Cloud Files
	*
	* @param 	$objPath 		string 		Full path and filename of the object within the container.
	* @access 	public
	* @return 	string
	*****************************************************************************************************
	*/
	public static function cfGetObject($objPath){

		self::cfConnect();

        $service 		= self::$rackspace_api_client->objectStoreService(null, self::$rackspace_api_region);
        $container 		= $service->getContainer(self::$rackspace_api_container);
        $object 		= $container->getObject($objPath);
		$object_name 	= $object->getName();
		$object_size 	= $object->getContentLength();
		$object_type 	= $object->getContentType();
		$object_folder 	= dirname($object_name);
        return array(
			'object_url' 	=> (string) $object->getPublicUrl(),
			'object_name' 	=> $object_name,
			'object_size' 	=> $object_size,
			'object_type' 	=> $object_type,
			'object_folder' => $object_folder
			);

	}

	/**
	*****************************************************************************************************
	* Fetches the temporary Public URL for a given object in Cloud Files
	*
	* @param 	$objPath 		string 		Full path and filename of the object within the container.
	* @param 	$expires 		integer 	URL TTL in seconds (default = 3800  which is 1 hour).
	* @access 	public
	* @return 	string
	*****************************************************************************************************
	*/
	public static function cfGetTempObject($objPath, $expires = 3600){

		self::cfConnect();

        $service 	= self::$rackspace_api_client->objectStoreService(null, self::$rackspace_api_region);
        $container 	= $service->getContainer(self::$rackspace_api_container);
        $object 	= $container->getPartialObject($objPath);
        $publicUrl 	= $object->getTemporaryUrl($expires, 'GET');

        return $publicUrl;

	}

	/**
	*****************************************************************************************************
	* Uploads binary file data into an object in Cloud Files
	*
	* @param 	$objPath 		string 		Full path and filename of the object within the container.
	* @param 	$binaryData 	string 		Binary file data (read from fopen or file_get_contents) OR a file handle.
	* @access 	public
	* @return 	boolean
	*****************************************************************************************************
	*/
	public static function cfSetObject($objPath, $binaryData){

		self::cfConnect();

        $service 	= self::$rackspace_api_client->objectStoreService(null, self::$rackspace_api_region);
        $container 	= $service->getContainer(self::$rackspace_api_container);
		$container->uploadObject($objPath, $binaryData);

		return TRUE;

	}

	/**
	*****************************************************************************************************
	* Deletes an object in Cloud Files
	*
	* @param 	$objPath 		string 		Full path and filename of the object within the container.
	* @access 	public
	* @return 	boolean
	*****************************************************************************************************
	*/
	public static function cfDelObject($objPath){

		self::cfConnect();

        $service 	= self::$rackspace_api_client->objectStoreService(null, self::$rackspace_api_region);
        $container 	= $service->getContainer(self::$rackspace_api_container);
        try{
        	$object = $container->getObject($objPath);
        	$object->delete();
        } catch(\Exception $e){
	    	return FALSE;
	    }

        return TRUE;
	}

}

?>