<?php namespace bbdn\core;
require_once './bbdn/vendor/autoload.php';
/**
*  AuthToken
*
*  AuthToken is a class that allows ease of getting, settings and monitoring
*  the token retrieved by the BbRestAPI oauth request token API route.
*
*  @author Michael Bechtel
*/
class AuthToken {

   public function __construct($key, $secret, $tokenRoutes, $verbose) {
     $this->key = $key;
     $this->secret = $secret;
     $this->tokenRoutes = $tokenRoutes;
     $this->verbose = $verbose;
     $this->payload = null;
   }

   /**
   * Get Key
   *
   * Retrieve the Currently stored key and return it back to the caller
   *
   * @return string
   */
   public function getKey() {
   }

   /**
   * Set Key
   *
   * Set the key and return it back to the caller
   *
   * @param
   *
   * @return string
   */
   public function setKey($key) {
     return $key;
   }

   /**
   * Get Secret
   *
   * Retrieve the Currently stored secret and return it back to the caller
   *
   * @return string
   */
   public function getSecret() {

   }

   /**
   * Set Secret
   *
   * Set the secret and return it back to the caller
   *
   * @param $secret Sets the secret to the current store
   *
   * @return string
   */
   public function setSecret($secret) {
     return $secret;
   }

   /**
   * Get Token
   *
   * Retrieve the Currently stored token and return it back to the caller
   *
   * @return string
   */
   public function getToken() {

   }

   /**
   * Set Token
   *
   * Sets a new/refreshed token to the current store and then returns is back to
   * the caller
   *
   * @return string
   */
   public function setToken() {
     if ($this->verbose) {
       echo $this->tokenRoutes['set_token'] . PHP_EOL;
       echo PHP_EOL;
     }

      $curl = curl_init($this->tokenRoutes['set_token'] . '?grant_type=client_credentials');

      if ($this->verbose) {
        print_r($curl);
        echo PHP_EOL;
        echo PHP_EOL;
      }

      $header = array();
      $header[] = 'Content-type: application/x-www-form-urlencoded';
      $header[] = 'Authorization: Basic ' . base64_encode($this->key . ':' . $this->secret);

      if ($this->verbose) {
        print_r($header);
        echo PHP_EOL;
        echo PHP_EOL;
      }

      curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
      curl_setopt($curl, CURLOPT_POST, true);
      // curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_VERBOSE, 1);
      $resp = curl_exec($curl);

      if ($this->verbose) {
        print_r($resp);
        echo PHP_EOL;
        echo PHP_EOL;
      }

      $this->payload = json_decode($resp);
   }

   /**
   * Revoke Token
   *
   * Revoke the token and return the results of the task.
   *
   * @return string
   */
   public function revokeToken() {

   }

   /**
   * Date Handler
   *
   * Checks the correct date format for the expiration of the token.
   *
   * @return boolean
   */
   public function dateHandler() {
     return true;
   }

   /**
   * Is Expired
   *
   * Checks for the validity of the current token.
   *
   * @return boolean
   */
   public function isExpired() {
     return true;
   }


}
