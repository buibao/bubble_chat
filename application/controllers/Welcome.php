<?php
defined('BASEPATH') OR exit('No direct script access allowed');

 class Welcome extends CI_Controller {

    function __construct() {
        parent::__construct();
       $this->load->helper('url'); 
	   $this->load->library('curl');
	   $hostChat = "http://hq-socialminer.abc.inc/ccp/chat"; // "https://i3-socialminer-1.i3international.com";
    }
	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
		$this->load->view('chat');
	}
	// GET
	public function createChat(){

		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => "http://hq-socialminer.abc.inc/ccp/chat",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_COOKIESESSION, TRUE,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS =>"<SocialContact><feedRefURL>http://hq-socialminer.abc.inc/ccp-webapp/ccp/feed/100040</feedRefURL>\n<author>sdfsd</author>\n<title>New visitor on web page</title>\n<tags>i3_chat</tags>\n<extensionFields><extensionField>\n<name>ccxqueuetag</name>\n<value>0</value>\n</extensionField>\n<extensionField>\n<name>h_Name</name>\n<value>sdfsd</value></extensionField>\n<extensionField><name>Email</name><value>sdfdsf</value></extensionField>\n<extensionField><name>Phone</name><value>dsf</value></extensionField></extensionFields>\n</SocialContact>",
		  CURLOPT_HTTPHEADER => array(
			"Content-Type: application/xml",
			"Cookie: JSESSIONID=B70F35A9833F9E3834ED582DE80AC948"
		  ),
		));
		
		$response = curl_exec($curl);
		
		curl_close($curl);
		echo $response;
		
	}
	// POST
	 public function initiate(){

		$author = $_GET['author'];
        $title = $_GET['title'];
        $tags = $_GET['tags'];
        $name = $_GET['name'];
		$ccxqueuetag = $_GET['ccxqueuetag'];
        $feedRefUrl = "http://hq-socialminer.abc.inc/ccp-webapp/ccp/feed/100040"; //$_GET['feedRefUrl'];
		
		$dataXMl = "<SocialContact>
		<feedRefURL>". $feedRefUrl ."</feedRefURL>
		<author>".$author."</author>
		<title>".$title."</title>
		<tags>".$tags."</tags>
		<extensionFields>
		   <extensionField>
			  <name>ccxqueuetag</name>
			  <value>".$ccxqueuetag."</value>
		   </extensionField>
		   <extensionField>
			  <name>h_Name</name>
			  <value>".$name."</value>
		   </extensionField>
		</extensionFields>
	 </SocialContact>";
		
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => "http://hq-socialminer.abc.inc/ccp/chat",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_COOKIESESSION, TRUE,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => $dataXMl,
		  CURLOPT_HTTPHEADER => array(
			"Content-Type: application/xml",
			"Cookie: JSESSIONID=B70F35A9833F9E3834ED582DE80AC948",
			"Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept",
			"Access-Control-Allow-Methods: PUT, POST, GET, DELETE, OPTIONS",
			"Access-Control-Allow-Origin: *"
		  ),
		));
		$response = curl_exec($curl);
		curl_close($curl);

		// echo $response ;

		// get cookie
// multi-cookie variant contributed by @Combuster in comments
// preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $response, $matches);
// $cookies = array();
// foreach($matches[1] as $item) {
//     parse_str($item, $cookie);
//     $cookies = array_merge($cookies, $cookie);
// }

echo var_dump(	$response );


		// print_r($data);
	 }
	 public function cok(){
	 
echo 'Hello ' . htmlspecialchars($_COOKIE['JSESSIONID']) . '!';

	 }
	 public function polling(){
		$lastEventId = $_GET['lastEventId'];
		$curl = curl_init();
		$url = "http://hq-socialminer.abc.inc/ccp/chat" . "?eventid=" . $lastEventId;
		curl_setopt_array($curl, array(
		  CURLOPT_URL => $url,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_CUSTOMREQUEST => "GET",
		//   CURLOPT_POSTFIELDS => $dataXMl,
		  CURLOPT_HTTPHEADER => array(
			"Content-Type: application/xml",
			"Cookie: JSESSIONID=B70F35A9833F9E3834ED582DE80AC948",
			"Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept",
			"Access-Control-Allow-Methods: PUT, POST, GET, DELETE, OPTIONS",
			"Access-Control-Allow-Origin: *"
		  ),
		));
		$response = curl_exec($curl);
		curl_close($curl);
		echo $response ;
	 }

}
// "
// 			<SocialContact>
// 			<feedRefURL>https://i3-socialminer-1.i3international.com/ccp-webapp/ccp/feed/100022</feedRefURL>
// 			<author>sdfsd</author>
// 			<title>New visitor on web page</title>
// 			<tags>i3_chat</tags>
// 			<extensionFields>
// 				<extensionField>
// 					<name>ccxqueuetag</name>
// 					<value>0</value>
// 				</extensionField>
// 				<extensionField>
// 					<name>h_Name</name>
// 					<value>sdfsd</value>
// 				</extensionField>
// 				<extensionField>
// 					<name>Email</name>
// 					<value>sdfdsf</value>
// 				</extensionField>
// 				<extensionField>
// 					<name>Phone</name>
// 					<value>dsf</value>
// 				</extensionField>
// 			</extensionFields>
// 		</SocialContact>
