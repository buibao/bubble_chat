<?php
defined('BASEPATH') OR exit('No direct script access allowed');

 class ChatApi extends CI_Controller {

    function __construct() {
        parent::__construct();
       $this->load->helper('url'); 
	   $this->load->library('curl');
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
		$this->load->view('index_node');
	}
	// GET
	public function createChat(){
			if(   isset($_GET['author'])
			   && isset($_GET['title'])
			   && isset($_GET['tags'])
			   && isset($_GET['name'])
			   && is_numeric($_GET['ccxqueuetag'])
			   && isset($_GET['feedRefUrl'])
			   && isset($_GET['urlChat']))
			{
				    $author = $_GET['author'];
			        $title = $_GET['title'];
			        $tags = $_GET['tags'];
			        $name = $_GET['name'];
					$ccxqueuetag = $_GET['ccxqueuetag'];
			        $feedRefUrl = $_GET['feedRefUrl'];
			        $urlChat = $_GET['urlChat'];
					$dataXMl = "<SocialContact>
					<feedRefURL>". $feedRefUrl ."</feedRefURL>
					<author>".$author."</author>
					<title>".$title."</title>
					<tags>".$tags."</tags>
					<extensionFields>
					   <extensionField>
						  <name>ccxqueuetag</name>
						  <value>" . $ccxqueuetag  . "</value>
					   </extensionField>
					   <extensionField>
						  <name>h_Name</name>
						  <value>".$name."</value>
					   </extensionField>
					</extensionFields>
				 </SocialContact>";
			// echo $dataXMl . $urlChat ;
			$curl = curl_init();

			  curl_setopt_array($curl, array(
			  CURLOPT_URL =>  $urlChat,

			  CURLOPT_SSL_VERIFYPEER => 0,
			  CURLOPT_SSL_VERIFYHOST => 0,
		      CURLOPT_CAINFO => "https://i3international.com/i3internationalcom.crt",
			  CURLOPT_TIMEOUT => 500,
			  CURLOPT_CONNECTTIMEOUT => 500,

			  CURLOPT_HEADER => TRUE,
			  CURLOPT_COOKIESESSION => TRUE,
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_POSTFIELDS =>	$dataXMl,
			  CURLOPT_HTTPHEADER => array(
			    "Content-Type: application/xml",
			    // isset($_COOKIE["JSESSIONID"]) ? "Cookie: JSESSIONID=" . $_COOKIE["JSESSIONID"] .","
			    "Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept",
				"Access-Control-Allow-Methods: PUT, POST, GET, DELETE, OPTIONS",
				"Access-Control-Allow-Origin: *"
			  ),
			));
			$response = curl_exec($curl);
			$header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
			$header = substr($response, 0, $header_size);
			$body = substr($response, $header_size);
			// echo 	$response; //"Data: " . "Body: " .$body . "Header: " . $header;
			preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $response, $matches);
			$cookies = array();
			foreach($matches[1] as $item) {
			    parse_str($item, $cookie);
			    $cookies = array_merge($cookies, $cookie);
			}
			 if(isset($cookies['JSESSIONID'])){
				setcookie("JSESSIONID", $cookies['JSESSIONID']);
				echo $response;
			 }else{
			 	echo "Fail: " . "Body: " . $body . " Header: " . $header;
			 }
			// $_SESSION["JSESSIONID"] = $cookies['JSESSIONID'];
			
		}else{
				echo "Fail";
	    }
	}
	public function polling(){
		if(isset($_COOKIE["JSESSIONID"]) && isset($_GET['urlChat']) && is_numeric($_GET['eventID'])){
			$urlChat   =     $_GET['urlChat'];
			$eventID   =     $_GET['eventID'];
		/*	echo $urlChat . "?eventid=" .$eventID  ;*/
			$curl = curl_init();
			curl_setopt_array($curl, array(
    			  CURLOPT_URL => $urlChat . "?eventid=" .$eventID ,

	    	      CURLOPT_SSL_VERIFYPEER => 0,
				  CURLOPT_SSL_VERIFYHOST => 0,
		          CURLOPT_CAINFO => "https://i3international.com/i3internationalcom.crt",
				  CURLOPT_TIMEOUT => 500,
				  CURLOPT_CONNECTTIMEOUT => 500,

				  CURLOPT_HEADER => TRUE,
				  CURLOPT_COOKIESESSION => TRUE,
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_ENCODING => "",
				  CURLOPT_MAXREDIRS => 10,
				  CURLOPT_FOLLOWLOCATION => true,
				  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				  CURLOPT_CUSTOMREQUEST => "GET",
			      CURLOPT_HTTPHEADER => array(

				"Content-Type: application/xml",
				"Cookie: JSESSIONID=" . $_COOKIE["JSESSIONID"],
				"Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept",
				"Access-Control-Allow-Methods: PUT, POST, GET, DELETE, OPTIONS",
				"Access-Control-Allow-Origin: *"

			  ),
			));
        	$response = curl_exec($curl);
			$header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
			$header = substr($response, 0, $header_size);
			$body = substr($response, $header_size);
			echo $body;
	}
			
    }
    public function putChatMessage()
    {
    	if(isset($_COOKIE["JSESSIONID"])   && isset($_GET['message'])   && isset($_GET['urlChat'])){
    		$message = $_GET['message'];
    		$urlChat = $_GET['urlChat'];
			$curl = curl_init();
			$url = 	$urlChat ;
			curl_setopt_array($curl, array(
			      CURLOPT_URL => $url,

			      CURLOPT_SSL_VERIFYPEER => 0,
				  CURLOPT_SSL_VERIFYHOST => 0,
				  CURLOPT_CAINFO => "https://i3international.com/i3internationalcom.crt",
				  CURLOPT_TIMEOUT => 500,
				  CURLOPT_CONNECTTIMEOUT => 500,

				  CURLOPT_HEADER => TRUE,
				  CURLOPT_COOKIESESSION => TRUE,
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_ENCODING => "",
				  CURLOPT_MAXREDIRS => 10,
				  CURLOPT_FOLLOWLOCATION => true,
				  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				  CURLOPT_CUSTOMREQUEST => "PUT",
				  CURLOPT_POSTFIELDS =>"<Message><body>".$message. "</body></Message>",
			      CURLOPT_HTTPHEADER => array(
				"Content-Type: application/xml",
				"Cookie: JSESSIONID=" . $_COOKIE["JSESSIONID"],
				"Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept",
				"Access-Control-Allow-Methods: PUT, POST, GET, DELETE, OPTIONS",
				"Access-Control-Allow-Origin: *"
			  ),
			));
			$response = curl_exec($curl);
			$header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
			$header = substr($response, 0, $header_size);
			$body = substr($response, $header_size);
			echo $body;
			}
    }
    public function getTranscript()
    {
    	if(isset($_COOKIE["JSESSIONID"])  && isset($_GET['urlChat'])){
    		$urlChat = $_GET['urlChat'];
			$curl = curl_init();
			$url = 	$urlChat ;
			curl_setopt_array($curl, array(
			      CURLOPT_URL => $url,

			      CURLOPT_SSL_VERIFYPEER => 0,
				  CURLOPT_SSL_VERIFYHOST => 0,
				  CURLOPT_CAINFO => "https://i3international.com/i3internationalcom.crt",
				  CURLOPT_TIMEOUT => 500,
				  CURLOPT_CONNECTTIMEOUT => 500,

				  CURLOPT_HEADER => TRUE,
				  CURLOPT_COOKIESESSION => TRUE,
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_ENCODING => "",
				  CURLOPT_MAXREDIRS => 10,
				  CURLOPT_FOLLOWLOCATION => true,
				  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				  CURLOPT_CUSTOMREQUEST => "GET",
				  // CURLOPT_POSTFIELDS =>"<Message><body>".$message. "</body></Message>",
			      CURLOPT_HTTPHEADER => array(
				"Content-Type: application/xml",
				"Cookie: JSESSIONID=" . $_COOKIE["JSESSIONID"],
				"Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept",
				"Access-Control-Allow-Methods: PUT, POST, GET, DELETE, OPTIONS",
				"Access-Control-Allow-Origin: *"
			  ),
			));
			$response = curl_exec($curl);
			$header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
			$header = substr($response, 0, $header_size);
			$body = substr($response, $header_size);
			echo $body;
			}
	}
	public function deleteChat()
    {
    	if(isset($_COOKIE["JSESSIONID"])  && isset($_GET['urlChat'])){
    		$urlChat = $_GET['urlChat'];
			$curl = curl_init();
			$url = 	$urlChat ;
			curl_setopt_array($curl, array(
			      CURLOPT_URL => $url,

			      CURLOPT_SSL_VERIFYPEER => 0,
				  CURLOPT_SSL_VERIFYHOST => 0,
				  CURLOPT_CAINFO => "https://i3international.com/i3internationalcom.crt",
				  CURLOPT_TIMEOUT => 500,
				  CURLOPT_CONNECTTIMEOUT => 500,

				  CURLOPT_HEADER => TRUE,
				  CURLOPT_COOKIESESSION => TRUE,
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_ENCODING => "",
				  CURLOPT_MAXREDIRS => 10,
				  CURLOPT_FOLLOWLOCATION => true,
				  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				  CURLOPT_CUSTOMREQUEST => "DELETE",
				  // CURLOPT_POSTFIELDS =>"<Message><body>".$message. "</body></Message>",
			      CURLOPT_HTTPHEADER => array(
				"Content-Type: application/xml",
				"Cookie: JSESSIONID=" . $_COOKIE["JSESSIONID"],
				"Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept",
				"Access-Control-Allow-Methods: PUT, POST, GET, DELETE, OPTIONS",
				"Access-Control-Allow-Origin: *"
			  ),
			));
			$response = curl_exec($curl);
			$header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
			$header = substr($response, 0, $header_size);
			$body = substr($response, $header_size);
			echo $body;
			}
	}
	public function leaveChat()
    {
    	if(isset($_COOKIE["JSESSIONID"])  && isset($_GET['urlChat'])){
    		$urlChat = $_GET['urlChat'];
			$curl = curl_init();
			$url = 	$urlChat ;
			curl_setopt_array($curl, array(
			      CURLOPT_URL => $url,


			      CURLOPT_SSL_VERIFYPEER => 0,
				  CURLOPT_SSL_VERIFYHOST => 0,
				  CURLOPT_CAINFO => "https://i3international.com/i3internationalcom.crt",
				  CURLOPT_TIMEOUT => 500,
				  CURLOPT_CONNECTTIMEOUT => 500,
				  
				  CURLOPT_HEADER => TRUE,
				  CURLOPT_COOKIESESSION => TRUE,
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_ENCODING => "",
				  CURLOPT_MAXREDIRS => 10,
				  CURLOPT_FOLLOWLOCATION => true,
				  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				  CURLOPT_CUSTOMREQUEST => "PUT",
				  // CURLOPT_POSTFIELDS =>"<Message><body>".$message. "</body></Message>",
			      CURLOPT_HTTPHEADER => array(
				"Content-Type: application/xml",
				"Cookie: JSESSIONID=" . $_COOKIE["JSESSIONID"],
				"Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept",
				"Access-Control-Allow-Methods: PUT, POST, GET, DELETE, OPTIONS",
				"Access-Control-Allow-Origin: *"
			  ),
			));
			$response = curl_exec($curl);
			$header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
			$header = substr($response, 0, $header_size);
			$body = substr($response, $header_size);
			echo $body;
	    }
    }

}
