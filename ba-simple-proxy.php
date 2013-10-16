<?PHP
// Changelog for fork by ets : see <https://github.com/ets/php-simple-proxy> for applicable commits & dates
//	
//  * Default to FolderGrid API <https://secure.foldergrid.com> if no URL parameter is passed
//  * Default to sending cookies. Disabled by setting send_cookies=0
//  * Default to native mode enabled. Disabled only through a change to this script.
//  * Default valid_url_regex to match FolderGrid API calls only
//  * Added support for properly separating content from headers after a redirect
//  * Added support for auto-converting a FolderGrid authentication cookie into a header

// ======================= Begin unrevised Original Documentation =========================
// Script: Simple PHP Proxy: Get external HTML, JSON and more!
//
// *Version: 1.6, Last updated: 1/24/2009*
// 
// Project Home - http://benalman.com/projects/php-simple-proxy/
// GitHub       - http://github.com/cowboy/php-simple-proxy/
// Source       - http://github.com/cowboy/php-simple-proxy/raw/master/ba-simple-proxy.php
// 
// About: License
// 
// Copyright (c) 2010 "Cowboy" Ben Alman,
// Dual licensed under the MIT and GPL licenses.
// http://benalman.com/about/license/
// 
// About: Examples
// 
// This working example, complete with fully commented code, illustrates one way
// in which this PHP script can be used.
// 
// Simple - http://benalman.com/code/projects/php-simple-proxy/examples/simple/
// 
// About: Release History
//
// 1.6 - (1/24/2009) Now defaults to JSON mode, which can now be changed to
//       native mode by specifying ?mode=native. Native and JSONP modes are
//       disabled by default because of possible XSS vulnerability issues, but
//       are configurable in the PHP script along with a url validation regex.
// 1.5 - (12/27/2009) Initial release
// ======================= End unrevised Original Documentation =========================


// Topic: GET Parameters
// 
// Certain GET (query string) parameters may be passed into ba-simple-proxy.php
// to control its behavior, this is a list of these parameters. 
// 
//   url - The remote URL resource to fetch. Any GET parameters to be passed
//     through to the remote URL resource must be urlencoded in this parameter.
//   callback - If specified, the response JSON will be wrapped in this named
//     function call. This parameter and <JSONP requests> are disabled by
//     default, see <Configuration Options> for more information.
//   user_agent - This value will be sent to the remote URL request as the
//     `User-Agent:` HTTP request header. If omitted, the browser user agent
//     will be passed through.
//   send_cookies - If send_cookies=0, cookies will NOT be forwarded through to
//     the remote URL request.
//   send_session - If send_session=1 and send_cookies=1, the SID cookie will be
//     forwarded through to the remote URL request.
//   full_headers - If a JSON request and full_headers=1, the JSON response will
//     contain detailed header information.
//   full_status - If a JSON request and full_status=1, the JSON response will
//     contain detailed cURL status information, otherwise it will just contain
//     the `http_code` property.
// 
// Topic: POST Parameters
// 
// All POST parameters are automatically passed through to the remote URL
// request.
// 
// Topic: JSON "mode" requests
// 
// This request will return the contents of the specified url in JSON format.
// 
// Request:
// 
// > ba-simple-proxy.php?url=http://example.com/
// 
// Response:
// 
// > { "contents": "<html>...</html>", "headers": {...}, "status": {...} }
// 
// JSON object properties:
// 
//   contents - (String) The contents of the remote URL resource.
//   headers - (Object) A hash of HTTP headers returned by the remote URL
//     resource.
//   status - (Object) A hash of status codes returned by cURL.
// 
// Topic: JSONP "mode" requests
// 
// This request will return the contents of the specified url in JSONP format
// (but only if $enable_jsonp is enabled in the PHP script).
// 
// Request:
// 
// > ba-simple-proxy.php?url=http://example.com/&callback=foo
// 
// Response:
// 
// > foo({ "contents": "<html>...</html>", "headers": {...}, "status": {...} })
// 
// JSON object properties:
// 
//   contents - (String) The contents of the remote URL resource.
//   headers - (Object) A hash of HTTP headers returned by the remote URL
//     resource.
//   status - (Object) A hash of status codes returned by cURL.
// 
// Topic: Native "mode" requests
// 
// This request will return the contents of the specified url in the format it
// was received in, including the same content-type and other headers (but only
// if $enable_native is enabled in the PHP script).
// 
// Request:
// 
// > ba-simple-proxy.php?url=http://example.com/&send_session=1
// 
// Response:
// 
// > <html>...</html>
// 
// Topic: Notes
// 
// * Assumes magic_quotes_gpc = Off in php.ini
// 
// Topic: Configuration Options
// 
// These variables can be manually edited in the PHP file if necessary.
// 
//   $enable_jsonp - Only enable <JSONP requests> if you really need to. If you
//     install this script on the same server as the page you're calling it
//     from, plain JSON will work. Defaults to false.
//   $enable_native - You can enable <Native requests>, but you should only do
//     this if you also whitelist specific URLs using $valid_url_regex, to avoid
//     possible XSS vulnerabilities. Defaults to false.
//   $valid_host_regex - This regex is matched against the host specified by the 
//     url parameter to ensure that it is valid. This setting only needs to be used
//     if either $enable_jsonp or $enable_native are enabled. Defaults to '/.*/' which
//     validates all URLs.
// 
// ############################################################################

// Change these configuration options if needed, see above descriptions for info.
$enable_jsonp    = false;
$enable_native   = true;
$valid_host_regex = '/.*\.foldergrid\.com/';

// ############################################################################

$url = $_GET['url'];
if ( !$url ) {
	$url = 'https://secure.foldergrid.com';
}	
$parsed = parse_url($url);

if ( !preg_match( $valid_host_regex, $parsed['host'] ) ) {
  
  // Passed url doesn't match $valid_url_regex.
  $contents = 'ERROR: invalid url';
  $status = array( 'http_code' => 'ERROR' );
  
} else {

  $ch = curl_init( $url );
  curl_setopt($ch, CURLOPT_VERBOSE, 0);
  curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, $_SERVER['REQUEST_METHOD'] );

  if ( strtolower($_SERVER['REQUEST_METHOD']) == 'post' ) {
    curl_setopt( $ch, CURLOPT_POST, true );
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $_POST );
  }else if (strtolower($_SERVER['REQUEST_METHOD']) == 'put'){
    curl_setopt( $ch, CURLOPT_POST, true );
	parse_str(file_get_contents("php://input"),$put_vars);
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $put_vars );	
  }
  
  if ( $_GET['send_cookies'] != '0' ) {
    $cookie = array();
    foreach ( $_COOKIE as $key => $value ) {
      $cookie[] = $key . '=' . $value;
    }
    if ( $_GET['send_session'] ) {
      $cookie[] = SID;
    }
    $cookie = implode( '; ', $cookie );
    
    curl_setopt( $ch, CURLOPT_COOKIE, $cookie );
  }
  
  curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
  curl_setopt( $ch, CURLOPT_HEADER, true );
  curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
  
  curl_setopt( $ch, CURLOPT_USERAGENT, $_GET['user_agent'] ? $_GET['user_agent'] : $_SERVER['HTTP_USER_AGENT'] );
  
  $response = curl_exec( $ch );
  $parts = preg_split( '/([\r\n][\r\n])\\1/', $response, -1 );
  $contents = end($parts);
  $header = prev($parts);

  $status = curl_getinfo( $ch );
  
  curl_close( $ch );
}

// Split header text into an array.
$header_text = preg_split( '/[\r\n]+/', $header );

if ( $enable_native ) {
  
  // Propagate headers to response.
  foreach ( $header_text as $header ) {
    if ( preg_match( '/^(?:Content-Type|Content-Language|Set-Cookie):/i', $header ) ) {
      header( $header );
    }
    if ( preg_match( '/Set-Cookie: GRID_SESSION/', $header ) ) {
      header( 'Authentication-Token: '.substr($header,25) );
    }
  }
  
  print $contents;
  
} else {
  
  // $data will be serialized into JSON data.
  $data = array();
  
  // Propagate all HTTP headers into the JSON data object.
  if ( $_GET['full_headers'] ) {
    $data['headers'] = array();
    
    foreach ( $header_text as $header ) {
      preg_match( '/^(.+?):\s+(.*)$/', $header, $matches );
      if ( $matches ) {
        $data['headers'][ $matches[1] ] = $matches[2];
      }
    }
  }
  
  // Propagate all cURL request / response info to the JSON data object.
  if ( $_GET['full_status'] ) {
    $data['status'] = $status;
  } else {
    $data['status'] = array();
    $data['status']['http_code'] = $status['http_code'];
  }
  
  // Set the JSON data object contents, decoding it from JSON if possible.
  $decoded_json = json_decode( $contents );
  $data['contents'] = $decoded_json ? $decoded_json : $contents;
  
  // Generate appropriate content-type header.
  $is_xhr = strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
  header( 'Content-type: application/' . ( $is_xhr ? 'json' : 'x-javascript' ) );
  
  // Get JSONP callback.
  $jsonp_callback = $enable_jsonp && isset($_GET['callback']) ? $_GET['callback'] : null;
  
  // Generate JSON/JSONP string
  $json = json_encode( $data );
  
  print $jsonp_callback ? "$jsonp_callback($json)" : $json;
  
}

?>
