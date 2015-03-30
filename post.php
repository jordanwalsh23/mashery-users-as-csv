<?php
/**
 * Author:  Jordan Walsh - Whispir
 * Date:    30 March 2015
 * Purpose: Script to download the API users based on key status. 
 * Returns a CSV file when executed from the browser.
 * 
 * Based on the example provided on the Mashery support portal
 */



// Substitute your site id here.  You can find your site id on the Summary tab of the
// administractive dashboard.
$your_site_id = 'xxx';

// Substitute your site id here.  You can find your site id on the Summary tab of the
// administractive dashboard.
$your_apikey = "xxx";
$your_shared_secret = "xxx";

//Set the status of the keys you are looking for:
$status = 'active'; // 'waiting', 'disabled' etc

//Start
$results = buildMasheryData();

if(sizeof($results) > 0) {

    //Set the Headers for a CSV file
    header("Content-type: text/csv");
    header("Content-Disposition: attachment; filename=mashery-keys-$status.csv");
    header("Pragma: no-cache");
    header("Expires: 0");
    
    //Print the headers
    echo "username,display_name,email,created_date,key.created_date\n";

    //Print the results
    $i = 0;
    foreach ($results as $key => $value) {
        
        foreach($value as $v) {
            echo $v->{'username'}.",";
            echo $v->{'display_name'}.",";
            echo $v->{'email'}.",";
            echo $v->{'created'}.",";
            echo $v->{'keys'}[0]->{'created'};
            echo "\n";
        }
        
    }
} else {
    echo "No records found, or authentication failed.";
}


//--------------------------------------------------------------------
function buildMasheryData() {

    global $status;

    $api = getMasheryApi();

    //Setup the results array
    $results = array();

    // Perform a test call
    $result = $api->call('object.query', array('select created, username, email, display_name, keys.created from members require related keys with status = \''.$status.'\''));

    if(is_object($result->{'error'})) {
        return array();
    }

    array_push($results, $result->{'result'}->{'items'});

    $pages = $result->{'result'}->{'total_pages'};

    for($i = 2; $i <= $pages; $i++) {
        $result = $api->call('object.query', array('select created, username, email, display_name, keys.created from members require related keys with status = \''.$status.'\' PAGE '.$i));
        array_push($results, $result->{'result'}->{'items'});
    }

    return $results;
}


function getMasheryApi() {

    global $your_site_id, $your_apikey, $your_shared_secret;

    // Create an instance of an object to make API calls with
    return new MasheryApi(
        MasheryApi::PRODUCTION_ENDPOINT, 
        $your_site_id, 
        $your_apikey, 
        $your_shared_secret);
}


/**
 * A class to call the Mashery API using the curl extension.
 */
class MasheryApi {

    const SANDBOX_ENDPOINT = 'api.sandbox.mashery.com';
    
    const PRODUCTION_ENDPOINT = 'api.mashery.com';

    protected $host;
    
    protected $apikey;
    
    protected $secret;
    
    protected $site_id;
    
    /**
     * Record necessary configuration values
     */
    function __construct($host, $site_id, $apikey, $secret)
    {
        $this->host = $host;
        $this->site_id = $site_id;
        $this->apikey = $apikey;
        $this->secret = $secret;
    }
    
    /**
     * Return the url of the API endpoint to call
     */
    function getEndpointUrl() 
    {
        // The path identifies the site
        $path = 'v2/json-rpc/' . $this->site_id;

        // Authentication and signing are handled in query parameters
        $auth = array();
        $auth['apikey'] = $this->apikey;
        $auth['sig'] = md5($this->apikey . $this->secret . gmdate('U'));
        $query = http_build_query($auth);
        
        // The host identifies which environment to call
        $host = $this->host;
        
        return 'http://' . $host . '/' . $path . '?' . $query;
    }

    /**
     * Perform an HTTP POST
     */
    function httpPost($url, $post_body) 
    {
        $ch = curl_init();
        
        // CUSTOMREQUEST used to bypass automatic 
        // application/x-www-form-urlencoded content type.
        $opts = array(
            CURLOPT_URL => $url,        
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_USERAGENT => 'mashery_api_call/1.0',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($post_body) . "\r\n",
                $post_body
            )
        );
        curl_setopt_array($ch, $opts);
        $response = curl_exec($ch);
        $headers = curl_getinfo($ch);
        curl_close($ch);
        
        return $response;
    }
    
    /**
     * Perform the API Call
     */
    function call($method, $parameters) 
    {

        // We can use a constant for id because we are not making
        // asyncronous calls
        $id = 1;

        // Build a PHP representation of the json-rpc request
        $call = array(
            'method' => $method,
            'params' => $parameters,
            'id' => $id, 
            );

        // Convert the php values to a json string
        $request = json_encode($call);

        // Make the api call
        $response = $this->httpPost($this->getEndpointUrl(), $request);
        
        // Decode the result
        return json_decode($response);
    }

}

?>