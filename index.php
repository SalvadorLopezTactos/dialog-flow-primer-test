<?php
$metodo=$_SERVER['REQUEST_METHOD'];

if($metodo == 'POST'){


    $requesBody=file_get_contents('php://input');
    $json=json_decode($requesBody);

    $instance_url = "http://localhost/unifinRediseno/unifinUpgrade/rest/v11_1";
    $auth_url = $instance_url . "/oauth2/token";

        $username = "carmen.velasco";
        $password = "unifin";


        $oauth2_token_arguments = array(
            "grant_type" => "password",
            //client id - default is sugar. 
            //It is recommended to create your own in Admin > OAuth Keys
            "client_id" => "sugar", 
            "client_secret" => "",
            "username" => $username,
            "password" => $password,
            "platform" => "api" 
        );

        //Petición para obtener token
        $response_token=call($auth_url,'','POST',$oauth2_token_arguments,true,false);
        
        $oauth_token = $response_token->access_token;

        //Petición para obtener cuenta filtrada
        $url_cuentas = $instance_url . "/Contacts/filter";
        
        $filtro=array(
            "filter"=>array(
                array("first_name"=>"Salvador"),
                array("last_name"=>"Lopez"),
                array("email1"=>"salvador.lopez@tactos.com.mx"),
            ),
            "fields"=>'id,name'
        );


//$filter_response = call($url_cuentas, $oauth_token, 'POST',$filtro);
        $filter_request = curl_init($url_cuentas);
        curl_setopt($filter_request, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($filter_request, CURLOPT_HEADER, false);
        curl_setopt($filter_request, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($filter_request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($filter_request, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($filter_request, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "oauth-token: {$oauth_token}"
        ));

//convert arguments to json
$json_arguments = json_encode($filtro);
curl_setopt($filter_request, CURLOPT_POSTFIELDS, $json_arguments);

//execute request
$filter_response = curl_exec($filter_request);

//decode json
$filter_response_obj = json_decode($filter_response);

        $response = array('fulfillmentText' => 'HOLAAA' , "source"=>"webhook" );
        echo json_encode($response);


}else{
    echo "Metodo no reconocido";
}

function call($url, $oauthtoken = '', $type = 'GET', $arguments = array(), $encodeData = true, $returnHeaders = false) {
        $type = strtoupper($type);
        
        if ($type == 'GET') {
            $url .= "?" . http_build_query($arguments);
        }
        
        //$url .= "?" . http_build_query('access_token=bbd6aea9-c264-4b45-b4d3-c7941f2af9e');
        $curl_request = curl_init($url);
        if ($type == 'POST') {
            curl_setopt($curl_request, CURLOPT_CUSTOMREQUEST, 'POST');
        } elseif ($type == 'PUT') {
            curl_setopt($curl_request, CURLOPT_CUSTOMREQUEST, "PUT");
        } elseif ($type == 'DELETE') {
            curl_setopt($curl_request, CURLOPT_CUSTOMREQUEST, "DELETE");
        }
        curl_setopt($curl_request, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($curl_request, CURLOPT_HEADER, $returnHeaders);
        curl_setopt($curl_request, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl_request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_request, CURLOPT_FOLLOWLOCATION, 0);
        if (!empty($oauthtoken)) {
            $token = array("oauth-token: {$oauthtoken}");
            curl_setopt($curl_request, CURLOPT_HTTPHEADER, $token);
        }
        if (!empty($arguments) && $type !== 'GET') {
            if ($encodeData) {
                //encode the arguments as JSON
                $arguments = json_encode($arguments);
            }
            curl_setopt($curl_request, CURLOPT_POSTFIELDS, $arguments);
            curl_setopt($curl_request, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($arguments))
            );
        }
        $result = curl_exec($curl_request);
        if ($returnHeaders) {
            //set headers from response
            list($headers, $content) = explode("\r\n\r\n", $result, 2);
            foreach (explode("\r\n", $headers) as $header) {
                header($header);
            }
            //return the nonheader data
            return trim($content);
        }
        curl_close($curl_request);
        //decode the response from JSON
        $response = json_decode($result);
        return $response;
    }//fin funcion call


?>
