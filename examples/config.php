<?PHP

$shell['title1'] = "Simple PHP Proxy";
$shell['link1']  = "http://benalman.com/projects/php-simple-proxy/";

ob_start();

$shell['h3'] = ob_get_contents();
ob_end_clean();

$shell['jquery'] = 'jquery-1.10.2.min.js';

$shell['shBrush'] = array( 'JScript', 'Xml' );

?>
