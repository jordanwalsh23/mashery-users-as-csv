# mashery-users-as-csv

Simple PHP script to Export Users (based on the sample provided by Mashery).

Allows you to export your Mashery Users from the Mashery Platform into a CSV file that you can use locally.

Bases the filter by a specific key status, e.g. 

Get me all users that have a key of 'Active', or, Get me all users that have a key of 'Waiting'.

Automatically handles the Pagination of API results, and presents back a simple CSV file.

## Usage

Simply get your Site ID, API key and API secret from support.mashery.com and paste them into the appropriate variables.


	// Substitute your site id here.  You can find your site id on the Summary tab of the
	// administractive dashboard.
	$your_site_id = 'xxx';

	// Substitute your site id here.  You can find your site id on the Summary tab of the
	// administractive dashboard.
	$your_apikey = "xxx";
	$your_shared_secret = "xxx";

	//Set the status of the keys you are looking for:
	$status = 'active'; // 'waiting', 'disabled' etc

Update the status to be what is required, then run the script.

## Support

I'm not actively maintaining this script, it's just something I use from time to time.  

Feel free to tweet me @jordwalsh if you want something added.

Cheers.