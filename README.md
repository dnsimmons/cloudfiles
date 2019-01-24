# cloudfiles
Provides a basic file management abstraction on top of the PHP-Opencloud SDK

**PHP-OpenCloud** (*the official PHP SDK for Rackspace*)

- [https://developer.rackspace.com/sdks/php/](https://developer.rackspace.com/sdks/php/)
- [https://github.com/rackspace/php-opencloud](https://github.com/rackspace/php-opencloud)

### Configure

The following variables must be set with your Rackspace credentials:

	public static $rackspace_api_username 	= null;
	public static $rackspace_api_key 	  	= null;
	public static $rackspace_api_region 	= null;
	public static $rackspace_api_container 	= null;

### Static Methods

The abstraction provides **static** methods allowing the creation, retrieval, and deletion of objects in the configured Rackspace Cloudfiles instance.

- **cfConnect** 	Connects to and authenticates against the Rackspace API
- **cfGetFolders** 	Fetches the symbolic "folders" from all objects
- **cfGetObjects** 	Fetches the Public URLs for all objects (10,000 max.)
- **cfIsObject** 	Returns true or false if the requested object exists
- **cfGetObject** 	Fetches the Public URL for a object
- **cfGetTempObject** Fetches the temporary Public URL for a object
- **cfSetObject** 	Uploads binary data as a object
- **cfDelObject** 	Deletes an object

### Basic Examples

Fetch all objects in the billing folder.

	$list = Cloudfiles::cfGetObjects('billing');
	print_r($list);

Add a spreadsheet called `invoices.xls` to the `billing` folder from a local file pointer.

	$fp = fopen('localfile.xls', 'r');
	Cloudfiles::cfSetObject('billing/invoices.xls', $fp);
	fclose($fp);

Fetch a public URL to the spreadsheet.

	$publicUrl = Cloudfiles::cfGetObject('billing/invoices.xls');
	echo $publicUrl;

Fetch a temporary URL to the spreadsheet and have it expire in 1 hour (3600 seconds).

	$tempUrl = CloudFiles::cfGetTempObject('billing/invoices.xls', 3600);
	echo $tempUrl;

Delete the spreadsheet.

	Cloudfiles::cfDelObject('billing/invoices.xls'));
