<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SendSMS extends Controller
{
    public static function send($phone,$message)
    {
		    	//Your authentication key
		$authKey = "274119AWWVwpDgv3Y5cc3205c";

		//Multiple mobiles numbers separated by comma
		$mobileNumber = $phone;

		//Sender ID,While using route4 sender id should be 6 characters long.
		$senderId = "Soni";

		//Your message to send, Add URL encoding here.
		$message = $message/*urlencode($message)*/;

		//Define route 
		$route = "default";
		//Prepare you post parameters
		$postData = array(
		    'authkey' => $authKey,
		    'mobiles' => $mobileNumber,
		    'message' => $message,
		    'sender' => $senderId,
		    'route' => $route
		);
		print_r($postData);die;
		//API URL
		$url="http://api.msg91.com/api/sendhttp.php";

		// init the resource
		$ch = curl_init();
		curl_setopt_array($ch, array(
		    CURLOPT_URL => $url,
		    CURLOPT_RETURNTRANSFER => true,
		    CURLOPT_POST => true,
		    CURLOPT_POSTFIELDS => $postData
		    //,CURLOPT_FOLLOWLOCATION => true
		));


		//Ignore SSL certificate verification
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);


		//get response
		$output = curl_exec($ch);

		//Print error if any
		if(curl_errno($ch))
		{
		    echo 'error:' . curl_error($ch);
		}

		curl_close($ch);

		//echo $output;

    }
}
