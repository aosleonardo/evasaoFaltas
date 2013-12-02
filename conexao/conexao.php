<?php

 function conectar($banco) {
      $servidor = '192.168.2.239';
      //$servidor = '192.168.3.253';
      $usuario = '';
      $senha = '';
      $con = mssql_connect($servidor, $usuario, $senha);
      mssql_select_db($banco, $con);
 }

 function debug($array){
     echo '<pre>';
     print_r($array);
     echo '</pre>';
}
