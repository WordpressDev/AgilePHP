<?php
/**
 * AgilePHP Framework :: The Rapid "for developers" PHP5 framework
 * Copyright (C) 2009 Make A Byte, inc
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package com.makeabyte.agilephp
 */

/**
 * AgilePHP :: Crypto 
 * Provides one way hashing and encryption
 * 
 * @author Jeremy Hahn
 * @copyright Make A Byte, inc.
 * @package com.makeabyte.agilephp
 * @version 0.1a
 */
class Crypto {

	  private static $instance;
	  private $algorithm;

	  /**
	   * Initalizes the Crypto component with the hasing algorithm defined in agilephp.xml
	   * 
	   * @return void
	   */
	  public function __construct() {

	  		 $xml = AgilePHP::getFramework()->getXmlConfiguration();

	  	     if( $xml->crypto )
	  		  	 $this->setAlgorithm( (string)$xml->crypto->attributes()->algorithm );
	  }

	  /**
	   * Returns a singleton instance of Crypto
	   * 
	   * @return Singleton instance of Crypto
	   */
	  public static function getInstance() {

	  	     if( self::$instance == null )
	  	         self::$instance = new self;

	  	     return self::$instance;
	  }

	  /**
	   * Sets the algorithm for the Crypto component for use with getDigest.
	   * 
	   * @param $algorithm The algorithm to perform the hashing operation with.
	   * 				   NOTE: getSupportedHashAlgorithms() will return a list of
	   * 						 algorithms available on the server.
	   * @return void
	   * @throws AgilePHP_Exception If passed a hashing name not available in
	   * 							getSupportedHashAlgorithms().
	   */
	  public function setAlgorithm( $algorithm ) {

	  		 if( in_array( $algorithm, $this->getSupportedHashAlgorithms() ) )
	  		 	 $this->algorithm = $algorithm;

	  		 if( !$this->algorithm )
	  		 	 throw new AgilePHP_Exception( 'Unsupported hashing algorithm \'' . $algorithm . '\'.' );
	  }

	  /**
	   * Returns the algorithm the Crypto component is configured to perform
	   * a hashing operation with using the 'getDigest()' method.
	   * 
	   * @return The name of the hashing algorithm
	   */
	  public function getAlgorithm() {
	  	
	  		 return $this->algorithm;
	  }

	  /**
	   * Returns the hashed $data. This operation requires either a valid configuration
	   * in agilephp.xml for the Crypto component or you must manually set the algorithm
	   * with a call to 'setAlgorithm()'.
	   * 
	   * @param $data The data to hash
	   * @return The hashed string
	   */
	  public function getDigest( $data ) {

	  		 return $this->hash( $this->getAlgorithm(), $data );
	  }

	  /**
	   * Returns a hashed MD5 string.
	   * 
	   * @param $data The data to hash
	   * @return The hashed MD5 string
	   */
	  public function md5( $data ) {

	  		 return md5( $data );
	  }

	  /**
	   * Returns an SHA1 hashed string.
	   * 
	   * @param $data The data to hash
	   * @return The hashed SHA1 string
	   */
	  public function sha1( $data ) {

	  		 return hash( 'sha1', $data );
	  }

	  /**
	   * Returns an SHA256 hashed string.
	   * 
	   * @param $data The data to hash
	   * @return The hashed SHA256 string
	   */
	  public function sha256( $data ) {

	  		 return hash( 'sha256', $data );
	  }

	  /**
	   * Returns an SHA384 hashed string.
	   * 
	   * @param $data The data to hash
	   * @return The hashed SHA384 string
	   */
	  public function sha384( $data ) {

	  		 return hash( 'sha384', $data );
	  }

	  /**
	   * Returns an SHA512 hashed string.
	   * 
	   * @param $data The data to hash
	   * @return The hashed SHA512 string
	   */
	  public function sha512( $data ) {

	  		 return hash( 'sha512', $data );
	  }

	  /**
	   * Returns an CRC32 hashed string.
	   * 
	   * @param $data The data to hash
	   * @return The hashed CRC32 string
	   */
	  public function crc32( $data ) {

	  		 return hash( 'crc32', $data );
	  }

	  /**
	   * Returns the hashed $data parameter according to the defined $algorithm
	   * parameter.
	   * 
	   * @param The algorithm to hash the defined data with. NOTE: You can get
	   * 		a list of supported algorithms on the server with a call to
	   * 	    getSupportedHashAlgorithms().
	   * @param $data The data to hash
	   * @return The hashed SHA1 string
	   */
	  public function hash( $algorithm, $data ) {

	  		 return hash( $algorithm, $data );
	  }

	  /**
	   * Returns an array of supported hashing algorithms on the current
	   * PHP enabled web server.
	   * 
	   * @return An array of supported hashing algorithms in the current
	   * 		 instance of PHP.
	   */
	  public function getSupportedHashAlgorithms() {

	  		 return hash_algos();
	  }

	  /* Cryptography */

	  public function createIV() {

	  		 return mcrypt_create_iv( mcrypt_get_block_size( MCRYPT_TripleDES, MCRYPT_MODE_CBC ), MCRYPT_DEV_RANDOM );
	  }

	  /**
	   * Encrypts the specified data using Triple DES.
	   * 
	   * @param $iv The IV/salt
	   * @param $key The secret key used to encrypt the data
	   * @param $data The data to encrypt
	   * @return Triple DES encrypted string
	   */
	  public function encrypt_3des( $iv, $key, $data ) {

	  		 return mcrypt_cbc( MCRYPT_TripleDES, $key, $data, MCRYPT_ENCRYPT, $iv );
	  }
	  
	  /**
	   * Decrypts Triple DES data 
	   * @param $iv The IV/salt
	   * @param $key The secret key used to encrypt the data
	   * @param $data The data to encrypt
	   * @return Plain text, decrypted data if a proper key was supplied
	   */
	  public function decrypt_3des( $iv, $key, $data ) {

	  		 return trim( mcrypt_cbc( MCRYPT_TripleDES, $key, $data, MCRYPT_DECRYPT, $iv ) );
	  }

	  /**
	   * Encrypts the specified data using Blowfish
	   * 
	   * @param $iv The IV/salt
	   * @param $key The secret key used to encrypt the data
	   * @param $data The data to encrypt
	   * @return Blowfish encrypted string
	   */
	  public function encrypt_blowfish( $iv, $key, $data ) {

	  		 return mcrypt_cbc( MCRYPT_BLOWFISH, $key, $data, MCRYPT_ENCRYPT, $iv );
	  }

	  /**
	   * Decrypts a string previously encrypted with encrypt_blowfish
	   * 
	   * @param $iv The IV/salt
	   * @param $key The secret key used to encrypt the data
	   * @param $data The data to encrypt
	   * @return Plain text, decrypted data if a proper key was supplied
	   */
	  public function decrypt_blowfish( $iv, $key, $data ) {

	  		 return trim( mcrypt_cbc( MCRYPT_BLOWFISH, $key, $data, MCRYPT_DECRYPT, $iv ) );
	  }

	  /**
	   * Encrypts the specified data using AES 256 encryption
	   * 
	   * @param $key The secret key used to encrypt the data
	   * @param $data The data to encrypt
	   * @return AES 256 encrypted data
	   */
	  public function encrypt_aes256( $iv, $key, $data ) {

	  		 return mcrypt_cbc( MCRYPT_RIJNDAEL_256, $key, $data, MCRYPT_ENCRYPT, $iv );
	  }

	  /**
	   * Decrypts the specified data which was previously encrypted using AES 256
	   * 
	   * @param $key The secret key used to encrypt the data
	   * @param $data The data to encrypt
	   * @return AES 256 decrypted data if a proper key was supplied
	   */
	  public function decrypt_aes256( $iv, $key, $data ) {

	  		 return trim( mcrypt_cbc( MCRYPT_RIJNDAEL_256, $key, $data, MCRYPT_DECRYPT, $iv ) );
	  }
}
?>