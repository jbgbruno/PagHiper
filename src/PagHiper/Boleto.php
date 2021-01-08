<?php 

namespace App\PagHiper;

class Boleto {

  private $data_post;
  private $headers;
  private $url;
  private $ch;

  public function __construct($data){
    $mediaType = "application/json";
    $charSet = "UTF-8";
    $this->headers[] = "Accept: ".$mediaType;
    $this->headers[] = "Accept-Charset: ".$charSet;
    $this->headers[] = "Accept-Encoding: ".$mediaType;
    $this->headers[] = "Content-Type: ".$mediaType.";charset=".$charSet;
    $this->data_post = json_encode( $data );   
    $this->ch = curl_init();
  }

  public function gerar(){
    $json = $this->execReq("https://api.paghiper.com/transaction/create/");
    $result = json_decode($json, true);
    $httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
    if($httpCode == 201){
      // CÓDIGO 201 SIGNIFICA QUE O BOLETO FOI GERADO COM SUCESSO
      $transaction_id = $result['create_request']['transaction_id'];
      $url_slip = $result['create_request']['bank_slip']['url_slip'];
      $digitable_line = $result['create_request']['bank_slip']['digitable_line'];
      return [
        'transaction_id'=> $transaction_id,
        'url_slip'=> $url_slip,
        'digitable_line'=> $digitable_line
      ];
    }else{
      echo $json;
    }
  }

  public function cancelar(){
    $json = $this->execReq("https://api.paghiper.com/transaction/cancel/");
    $result = json_decode($json, true);
    $httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
    if($httpCode == 201){
      return $result['cancellation_request']['response_message'];
    }else{
      echo $json;
    }
  }

  public function verStatus(){
    $json = $this->execReq("https://api.paghiper.com/transaction/status/");
    $result = json_decode($json, true);
    $httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
    if($httpCode == 201){
      return $result['status_request']['status'];
    }else{
      return $json;
    }
  }
  public function listar(){
    $json = $this->execReq("https://api.paghiper.com/transaction/list/");
    $result = json_decode($json, true);
    $httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
    if($httpCode == 201){
      return $result['transaction_list_request']['transaction_list'];
    }else{
      return $json;
    }
  }

  private function execReq($url){
    curl_setopt($this->ch, CURLOPT_URL, $url);
    curl_setopt($this->ch, CURLOPT_POST, 1);
    curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->data_post);
    curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);
    curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
    return curl_exec($this->ch);
  }
}