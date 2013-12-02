<?php

 function retToken() {
      //Chamada ao webservice que recupera o token do usuario
      $url = 'http://192.168.2.220/orquestra/webservice/v2.5/authentication.asmx?wsdl';
      //$url = 'http://192.168.4.252/orquestra/webservice/v2.5/authentication.asmx?wsdl';

      $client = new SoapClient($url);
      $param = array(
          'DsUsername' => 'admin',
          'DsPassword' => 'pdca1520'
      );
      $result = $client->Login01($param);
      $xml = simplexml_load_string($result->Login01Result->any);
      if ($xml->returns) {
           return $xml->returns;
      } else {
           throw new Exception($result->Login01Result->any);
      }
 }

 function registraProcesso($token, $xml) {
      $url = 'http://192.168.2.220/orquestra/webservice/v2.5/instance.asmx?wsdl';
      //$url = 'http://192.168.4.252/orquestra/webservice/v2.5/instance.asmx?wsdl';
      $client = new SoapClient($url);
      $param = array(
          'Token' => (string) $token,
          'CodFlow' => 339,
          'CodArea' => (int) 2742,
          'CodPosition' => (int) 801,
          //'Simulation' => TRUE,
            'Simulation' => FALSE,
          'XmlValues' => $xml
      );

      $result = $client->CreateInstance02($param);

      $rxml = simplexml_load_string($result->CreateInstance02Result->any);

      if ($rxml->returns) {
           return $rxml->returns;
      } else {
           return 0;
      }
 }