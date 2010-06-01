<?php
/**
 * AgilePHP Framework :: The Rapid "for developers" PHP5 framework
 * Copyright (C) 2009-2010 Make A Byte, inc
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
 * AgilePHP core framework Class
 * 
 * @author Jeremy Hahn
 * @copyright Make A Byte, inc
 * @package com.makeabyte.agilephp
 * @static
 */
class AgilePHP {

	  private static $instance;
	  private $displayPhpErrors = true;
	  private $webroot;						// The full system path to the web application
	  private $frameworkRoot;				// The full system path to the location of the AgilePHP framework
	  private $documentRoot;				// The relative path to the web app from the server's document root.
	  private $requestBase;					// The base request URL (used to communicate with MVC component)
	  private $debugMode = false;			// Whether or not this component is running in debug mode
	  private $xml;							// AgilePHP configuration - agilephp.xml
	  private $appName;						// Name of the AgilePHP application
	  private $interceptions = array();		// An array of interceptions which have occurred during __autoload
	  private $startTime;					// Used with startClock and stopClock methods

	  /**
	   * Initalize the AgilePHP framework. Sets the following defaults \n
	   * $webroot = current working directory (of the script that instantiated the framework)
	   * $requestBase = name of the script that instantiated the framework
	   * $frameworkRoot = $webroot/AgilePHP
	   * $appName = The HTTP HOST header value
	   * 
	   * @return void
	   */
	  private function __construct( $agilephpDotXml ) {

	  	      $this->webroot = getcwd();
	  	      $this->requestBase = $_SERVER['SCRIPT_NAME'];
	  	      $this->frameworkRoot = $this->webroot . DIRECTORY_SEPARATOR . 'AgilePHP';
	  	      $this->appName = (isset( $_SERVER['HTTP_HOST'] )) ? $_SERVER['HTTP_HOST'] : 'localhost';

	  	      // Parse and set documentRoot
	  	      $pieces = explode( '.php', $_SERVER['SCRIPT_NAME'] );
	  	      array_pop( $pieces );
	  	      $docRootPieces = array();
	  	      $newPieces = explode( '/', implode( '/', $pieces ) );
	  	      for( $i=0; $i<(count( $newPieces ) - 1); $i++ )
	  	      	   $docRootPieces[$i] = $newPieces[$i];
	  	      $this->documentRoot = implode( '/', $docRootPieces );

	  	      $this->parseXml( $agilephpDotXml );
	  }

	  private function __clone() { }

	  /**
	   * Factory method which is used to instantiate a singleton
	   * instance of the AgilePHP framework.
	   * 
	   * @param string $agilephpDotXml Optional file path to agilephp.xml configuration file
	   * @return AgilePHP A Singleton instance of the AgilePHP Framework
	   * @static
	   */
	  public static function getFramework( $agilephpDotXml = null ) {

	  	     if( self::$instance == null )
	  	         self::$instance = new self( $agilephpDotXml );

	  	     return self::$instance;
	  }

	  /**
	   * Accessor method for Model-View-Control component. The MVC component this method returns is
	   * created as follows:
	   * 
	   * 1) If an agilephp.xml file is present in the web app root and contains a valid 'mvc'
	   * 	configuration, the MVC component is created using the specified values when AgilePHP
	   * 	framework is instantiated by a call to 'getFramework'.
	   * 
	   * 2) No agilephp.xml file is present. This method will return a singleton instance.
	   */
	  public function getMVC() {

	  		 return MVC::getInstance();
	  }

	  /**
	   * Sets the fully qualified path to the base directory of
	   * the web application.
	   * 
	   * @param $path The fully qualified path to the web application
	   */
	  public function setWebRoot( $path ) {

	  	     $this->webroot = $path;
	  }

	  /**
	   * Returns the fully qualified path to the base directory of
	   * the web application.
	   * 
	   * @return The fully qualified path to the web application
	   */
	  public function getWebRoot() {

	  	     return $this->webroot;
	  }

	  /**
	   * Sets the full system path to the location of the AgilePHP framework. The given path is
	   * appended to the current php.ini include_path configuration.
	   * 
	   * @param $path The full system path to the location where AgilePHP framework resides.
   	   * @return void 
	   */
	  public function setFrameworkRoot( $path ) {

	  	     $this->frameworkRoot = $path;

	  	     $include_path = ini_get( 'include_path' );

	  	     if( strpos( $include_path, ':' . $path ) === false )
	  	     	 ini_set( 'include_path', $include_path . PATH_SEPARATOR . ':' . $path );

	  	     Log::debug( 'Initalizing framework with php include_path: ' . ini_get( 'include_path' ) );
	  }

	  /**
	   * Gets the full system path to the location where AgilePHP resides.
	   * 
	   * @return The full system path to the location of the AgilePHP framework.
	   */
	  public function getFrameworkRoot() {

	  		 return $this->frameworkRoot;
	  }

	  /**
	   * Sets the relative path to the web application from the server's document root.
	   * 
	   * @param String $path The document root path
	   * @return void
	   */
	  public function setDocumentRoot( $path ) {

	  		 $this->documentRoot = $path;
	  }

	  /**
	   * Returns the relative path to the web application from the server's document root.
	   * 
	   * @return The web applications relative path from the server's document root
	   */
	  public function getDocumentRoot() {

	  		 return $this->documentRoot;
	  }

	  /**
	   * Returns the base action url used to communicate with the
	   * AgilePHP MVC component. Defaults to the name of the script
	   * which initalizes the framework.
	   *
	   * @return The base action url used to communicate with the AgilePHP MVC component.
	   */
	  public function getRequestBase() {

	  		 return $this->requestBase;
	  }

	  /**
	   * Sets the base action url used to communicate with the AgilePHP MVC component.
	   * 
	   * @param $url The base url to be used to communicate with the AgilePHP MVC component.
	   */
	  public function setRequestBase( $url ) {

	  		 $this->requestBase = $url;
	  }

	  /**
	   * Sets the name of the AgilePHP web application.
	   * 
	   * @param String $name The name of the AgilePHP application
	   * @return void
	   */
	  public function setAppName( $name ) {
	  	
	  		 $this->appName = $name;
	  }

	  /**
	   * Returns the name of the AgilePHP web application.
	   * 
	   * @return AgilePHP web application name
	   */
	  public function getAppName() {

	  		 if( !$this->appName )
	  		 	  $this->appName = (isset( $_SERVER['REMOTE_ADDR'] )) ? $_SERVER['REMOTE_ADDR'] : 'localhost';

	  		 return $this->appName;
	  }

	  /**
	   * Loads a class from the web application 'classes' or 'components' directory using a
	   * package dot type notation. First the classes directory is searched, then components.
	   * 
	   * @param String $classpath The dot notation classpath (my.package.ClassName)
	   * @return void
	   * @throws AgilePHP_Exception If an error occurred loading the specified classpath
	   */
	  public static function import( $classpath ) {

	  		 Log::debug( 'AgilePHP::import ' . $classpath );

	  		 $file = preg_replace( '/\./', DIRECTORY_SEPARATOR, $classpath );
	  		 if( file_exists( 'classes' . DIRECTORY_SEPARATOR . $file . '.php' ) )
	  		 	 require_once( 'classes' . DIRECTORY_SEPARATOR . $file . '.php' );

	  		 else if( file_exists( 'components' . DIRECTORY_SEPARATOR . $file . '.php' ) )
	  		 	 require_once( 'components' . DIRECTORY_SEPARATOR . $file . '.php' );

	  		 else
  		 	 	throw new AgilePHP_Exception( 'Failed to import source from \'' . $classpath . '\'.' );
	  }

	  /**
	   * By default PHP hides errors on production servers. Setting this to true enables PHP
	   * 'display_errors', sets 'error_reporting' to 'E_ALL'.
	   * 
	   * @param bool $bool True to turn on error reporting on (E_ALL)
	   * @return void
	   */
	  public function setDisplayPhpErrors( $bool ) {

	  	     $this->displayPhpErrors = ($bool == true);

	  	     if( $this->displayPhpErrors ) {

	  	      	 ini_set( 'display_errors', '1' );
	  	      	 error_reporting( E_ALL );
	  	     }
	  }

	  /**
	   * Enables or disables AgilePHP framework debug mode.
	   * 
	   * @param bool $boolean True for debug mode, false for production mode. Default is production.
	   */
	  public function setDebugMode( $boolean ) {

	  		 $this->debugMode = ($boolean === true) ? true : false;
	  		 if( $this->debugMode ) $this->setDisplayPhpErrors( true );
	  }

	  /**
	   * Whether or not AgilePHP framework is running in debug mode.
	   * 
	   * @return void
	   */
	  public function isInDebugMode() {

	  		 return $this->debugMode;
	  }

	  /**
	   * Calls PHP date_default_timezone_set function to set the current timezone.
	   * 
	   * @param String $timezone The timezone to use as default.
	   * @return void
	   * <code>
	   * $agilephp->setDefaultTimezone( 'America/New_York' );
	   * </code>
	   */
	  public function setDefaultTimezone( $timezone ) {

	  		 date_default_timezone_set( $timezone );
	  }

	  /**
	   * Adds an Interception to the interceptions stack
	   * 
	   * @param Interception $interception The interception instance to add to the stack
	   * @return void
	   */
	  public function addInterception( Interception $interception ) {

	  		 Log::debug( 'AgilePHP::addInterception Adding interception for class \'' . $interception->getClass() . '\'.' );

	  		 array_push( $this->interceptions, $interception );
	  }

	  /**
	   * Returns an array of Interceptions which have been loaded into the framework
	   * 
	   * @return Array of Interception instances
	   */
	  public function getInterceptions() {

	  		 return $this->interceptions;
	  }

	  /**
	   * Returns the agilephp.xml file as a SimpleXMLElement. NOTE: This method reads the xml file
	   * with each call to avoid "Exception: Serialization of 'SimpleXMLElement' is not allowed" in
	   * unit tests. Is this a php bug?
	   * 
	   * @return SimpleXMLElement agilephp.xml configuration
	   */
	  public function getConfiguration() {

	  		 $agilephp_xml = $this->getWebRoot() . DIRECTORY_SEPARATOR . 'agilephp.xml';
  	      	 return simplexml_load_file( $agilephp_xml );
	  }

	  /**
	   * Parses AgilePHP configuration file (agilephp.xml) and initalizes the
	   * framework according to the specified configuration.
	   * 
	   * @param string $agilephpDotXml Optional file path to agilephp.xml configuration file
	   * @return void
	   */
	  private function parseXml( $agilephpDotXml = null ) {

	  	  	  $agilephp_xml = ($agilephpDotXml) ? $agilephpDotXml : $this->getWebRoot() . DIRECTORY_SEPARATOR . 'agilephp.xml';

	  		  if( !file_exists( $agilephp_xml ) )
	  		  	  return;

	  	      $xml = simplexml_load_file( $agilephp_xml );

	  		  $dom = new DOMDocument();
 			  $dom->Load( $agilephp_xml );			 
			  if( !$dom->validate() ) {

			 	  throw new AgilePHP_Exception( "agilephp.xml Document Object Model validation failed. You can validate your configurations with the DTD at AgilePHP/agilephp.dtd." );
			 	  return;
			  }

	  	      if( $xml->mvc ) {

	  	      	  require_once 'MVC.php';

	  	      	  $controller = (string)$xml->mvc->attributes()->controller;
	  	      	  $action = (string)$xml->mvc->attributes()->action;
	  	      	  $renderer = (string)$xml->mvc->attributes()->renderer;
	  	      	  $sanitize = (string)$xml->mvc->attributes()->sanitize;

	  	      	  MVC::getInstance()->setconfig( $controller, $action, $renderer, $sanitize );
  	      	  } 	      	  
	  }

	  /**
	   * Starts a timer. Useful for measuring how long a particular
	   * operation takes.
	   * 
	   * @return void
	   */
	  public function startClock() {

		  	 $mtime = microtime();
		     $mtime = explode( ' ', $mtime );
		     $mtime = $mtime[1] + $mtime[0];
		     $this->startTime = $mtime;
	  }

	  /**
	   * Defines the error handler responsible for handling framework and application wide errors.
	   * 
	   * @param mixed $function A standard PHP function or static method responsible for error handling
	   * @return void
	   */
	  public static function setErrorHandler( $function ) {

	  		 set_error_handler( $function );
	  }

	  /**
	   * Handles PHP E_NOTICE, E_WARNING, E
	   */
	  public static function handleErrors() {

	  		 set_error_handler( 'AgilePHP::ErrorHandler' );
	  }

	  /**
	   * Custom PHP error handling function which writes error to log instead of echoing it out.
	   * 
	   * @param Integer $errno Error number
	   * @param String $errmsg Error message
	   * @param String $errfile The name of the file that caused the error
	   * @param Integer $errline The line number that caused the error
	   * @return false
	   * @throws AgilePHP_Exception
	   */
 	  public static function ErrorHandler( $errno, $errmsg, $errfile, $errline ) {

 	  		 $entry = PHP_EOL . 'Number: ' . $errno . PHP_EOL . 'Message: ' . $errmsg . 
 	  		 		  PHP_EOL . 'File: ' . $errfile . PHP_EOL . 'Line: ' . $errline;

 	  		 switch( $errno ) {

 	  		 	case E_NOTICE:
 	  		 	case E_USER_NOTICE:

 	  		 		Log::info( $entry );
 	  		 		break;

 	  		 	case E_WARNING:
 	  		 	case E_USER_WARNING:
 	  		 		
 	  		 		Log::warn( $entry );
 	  		 		break;

 	  		 	case E_ERROR:
 	  		 	case E_USER_ERROR:
 	  		 	case E_RECOVERABLE_ERROR:

 	  		 		Log::error( $entry );
 	  		 		break;

 	  		 	default:
 	  		 		Log::debug( $entry );
 	  		 }
    	      
	  }

	  /**
	   * Stops the timer and returns the elapsed time between startClock()
	   * and stopClock().
	   * 
	   * @return Time elapsed time between startClock() and endClock()
	   */
	  public function stopClock() {

	  		 $mtime = microtime();
     		 $mtime = explode( ' ', $mtime );
   		     $mtime = $mtime[1] + $mtime[0];
   			 $endtime = $mtime;
   			 $difference = ($endtime - $this->startTime);

   			 return $difference; 
	  }

	  public function __destruct() { }
}

/**
 * Base AgilePHP exception class
 * 
 * @author Jeremy Hahn
 * @copyright Make A Byte, inc
 * @package com.makeabyte.agilephp
 * @version 0.1a
 * @throws AgilePHP_Exception
 */
class AgilePHP_Exception extends Exception {

	  /**
	   * Creates a new instance of AgilePHP_Exception.
	   * 
	   * @param String $message The exception message
	   * @param Integer $code Optional error code.
	   * @param String $file Optional file path to the exception
	   * @param Integer $line The line number the exception / error occurred
	   * @return void
	   */
	  public function __construct( $message, $code = null, $file = null, $line = null ) {

			 $this->message = $message;
	  		 if( $code ) $this->code = $code;
	  		 $this->file = ($file == null) ? __FILE__ : $file;
  		 	 $this->line = ($line == null ) ? __LINE__ : $line;
	  		 $this->trace = debug_backtrace();
	  }
}

/**
 * Exceptions thrown by the Persistence/ORM framework
 * 
 * @author Jeremy Hahn
 * @copyright Make A Byte, inc
 * @package com.makeabyte.agilephp
 * @version 0.1a
 * @throws AgilePHP_PersistenceException
 */
class AgilePHP_PersistenceException extends AgilePHP_Exception { }

/**
 * Thrown when a user is not logged in and requests secure content
 * 
 * @author Jeremy Hahn
 * @copyright Make A Byte, inc
 * @package com.makeabyte.agilephp
 * @version 0.1a
 * @throws AgilePHP_NotLoggedInException
 */
class AgilePHP_NotLoggedInException extends AgilePHP_Exception { }

/**
 * Thrown when a user attempts to access content which they do not have
 * permission to view.
 * 
 * @author Jeremy Hahn
 * @copyright Make A Byte, inc
 * @package com.makeabyte.agilephp
 * @version 0.1a
 * @throws AgilePHP_AccessDeniedException
 */
class AgilePHP_AccessDeniedException extends AgilePHP_Exception { }

/**
 * Exceptions thrown by the Annotation package
 * 
 * @author Jeremy Hahn
 * @copyright Make A Byte, inc
 * @package com.makeabyte.agilephp
 * @version 0.1a
 * @throws AgilePHP_AnnotationException
 */
class AgilePHP_AnnotationException extends AgilePHP_Exception { }

/**
 * Exceptions thrown by the Interception package
 * 
 * @author Jeremy Hahn
 * @copyright Make A Byte, inc
 * @package com.makeabyte.agilephp
 * @version 0.1a
 * @throws AgilePHP_InterceptionException
 */
class AgilePHP_InterceptionException extends AgilePHP_Exception { }

/**
 * Handles all remoting exceptions. Output is returned in JSON format
 * with an application/json HTTP header.
 * 
 * @author Jeremy Hahn
 * @copyright Make A Byte, inc
 * @package com.makeabyte.agilephp
 * @version 0.1a
 * @throws AgilePHP_RemotingException
 */
class AgilePHP_RemotingException extends AgilePHP_Exception { 

	  /*
	   * Public context fields reduce the chance that AJAXRenderer will
	   * use reflection method setAccessible which requires PHP 5.3+
	   */ 
	  public $code;
	  public $message;
	  public $file;
	  public $trace;
	  public $line;
	  public $_class = 'AgilePHP_RemotingException';

	  /**
	   * Deliver remoting exceptions in JSON format and halt execution.
	   * 
	   * @param String $message The exception message
	   * @return void
	   */
	  public function __construct( $message ) {

			 $this->message = $message;
			 $this->trace = parent::getTraceAsString();
	  		 $renderer = MVC::getInstance()->createRenderer( 'AJAXRenderer' );
	  		 $renderer->render( $this );
	  		 exit;
	  }
}

/**
 * Responsible for 'lazy loading' classes.
 * 
 * @param String $class The class being lazy loaded
 * @return void
 */
function __autoload( $class ) {

		 __autoload_interceptions( $class );

		 if( !class_exists( $class, false ) )
  		   	 __autoload_class( $class );
}

require_once 'Annotation.php';
require_once 'Interception.php';
require_once 'Interception.php';
require_once 'interception/AroundInvoke.php';

/**
 * If the class being loaded is annotated with any interceptors, an InterceptorProxy
 * class is created for the requested class. The InterceptorProxy is a template used
 * to create the dynamic proxy for the class being intercepted. 
 * 
 * Note: Anootations are being gotten as arrays here instead of Annoated* objects
 * 		 because in order to create the dynamic proxy with the name of the requested
 * 	 	 class, we need to make sure that the class has not been loaded since there
 * 		 is no easy/elegant way to unload a PHP class once it has been loaded.
 * 
 * @param String $class The name of the class being loaded by __autoload
 * @return void
 */
function __autoload_interceptions( $class ) {

  	     $classAnnotations = Annotation::getClassAsArray( $class );
  	     if( count( $classAnnotations ) ) {

		     foreach( $classAnnotations as $annotation ) {

		  	   		  $annote = new AnnotatedClass( $annotation );
			   	   	  if( $annote->hasAnnotation( 'Interceptor' ) ) {

			   	   	  	  $interceptor = $annote->getName();
			   	   	  	  $interception = new Interception( $class, null, null, $annotation );
			   	   	  	  AgilePHP::getFramework()->addInterception( $interception );
			   	   	  }
			 }
  	     }

		 $annotatedMethods = Annotation::getMethodsAsArray( $class );
	 	 if( count( $annotatedMethods ) ) {

			 foreach( $annotatedMethods as $methodName => $methodAnnotation ) {

			     foreach( $methodAnnotation as $annotation ) {

			  	   		  $annote = new AnnotatedClass( $annotation );
				   	   	  if( $annote->hasAnnotation( 'Interceptor' ) ) {

				   	   	  	  $interceptor = $annote->getName();
				   	   	  	  $interception = new Interception( $class, $methodName, null, $annotation );
				   	   	  	  AgilePHP::getFramework()->addInterception( $interception );
				   	   	  }
				 }
			 }
  	     }

  	     $annotatedProperties = Annotation::getPropertiesAsArray( $class );	  	     
	 	 if( count( $annotatedProperties ) ) {

			 foreach( $annotatedProperties as $fieldName => $fieldAnnotation ) {

			     foreach( $fieldAnnotation as $annotation ) {

			  	   		  $annote = new AnnotatedClass( $annotation );
				   	   	  if( $annote->hasAnnotation( 'Interceptor' ) ) {

				   	   	  	  $interceptor = $annote->getName();
				   	   	  	  $interception = new Interception( $class, null, $fieldName, $annotation );
				   	   	  	  AgilePHP::getFramework()->addInterception( $interception );
				   	   	  }
				 }
			 }
  	     }
}

/**
 * Lazy loads standard framework and web application classes that do not
 * contain interceptor annotations.
 *  
 * @param String $class The name of the class being loaded by __autoload
 * @return void
 */
function __autoload_class( $class ) {

		 // php namespace support
		 $namespace = explode( '\\', $class );
	 	 $class = array_pop( $namespace );
	 	 $namespace = implode( DIRECTORY_SEPARATOR, $namespace ) . DIRECTORY_SEPARATOR;

		 // Load framework classes
		 $path = AgilePHP::getFramework()->getFrameworkRoot() . DIRECTORY_SEPARATOR . $namespace . $class . '.php';
	     if( file_exists( $path ) ) {

	     	 require_once $path;
	     	 return;
	     }
		 $it = new RecursiveDirectoryIterator( AgilePHP::getFramework()->getFrameworkRoot() );
		 foreach( new RecursiveIteratorIterator( $it ) as $file ) {

		   	      if( substr( $file, -1 ) != '.' && substr( $file, -2 ) != '..' ) {

		   	      	  $array = explode( DIRECTORY_SEPARATOR, $file );
			 		  if( array_pop( $array ) == $class . '.php' ) {

		     	 		  require_once $file;
			 		      return;
			 		  }
			      }
		 }

		 // Load web application classes
  	     $path = AgilePHP::getFramework()->getWebRoot() . DIRECTORY_SEPARATOR . 'control' .
  	     		 DIRECTORY_SEPARATOR . $namespace . $class . '.php';
  	     if( file_exists( $path ) ) {

		     require_once $path;
  	         return;
  	     }
  	     $path = AgilePHP::getFramework()->getWebRoot() . DIRECTORY_SEPARATOR . 'model' .
  	     		 DIRECTORY_SEPARATOR . $namespace . $class . '.php';
	     if( file_exists( $path ) ) {

		      require_once $path;
  	          return;
	     }
	     $path = AgilePHP::getFramework()->getWebRoot() . DIRECTORY_SEPARATOR . 'classes' .
	     		 DIRECTORY_SEPARATOR . $namespace . $class . '.php';
  	     if( file_exists( $path ) ) {

		     require_once $path;
  	         return;
  	     }
		 $path = AgilePHP::getFramework()->getWebRoot() . DIRECTORY_SEPARATOR . 'components' .
		 		 DIRECTORY_SEPARATOR . $namespace . $class . '.php';
	     if( file_exists( $path ) ) {

		     require_once $path;
	     	 return;
	     }
  	     // Not found in the usual places - perform deep scan
	  	 $it = new RecursiveDirectoryIterator( AgilePHP::getFramework()->getWebRoot() );
		 foreach( new RecursiveIteratorIterator( $it ) as $file ) {

		   	      if( substr( $file, -1 ) != '.' && substr( $file, -2 ) != '..'  &&
		   	      	  substr( $file, -4 ) != 'view' ) {

		   	      	  $pieces = explode( DIRECTORY_SEPARATOR, $file );
			 		  if( array_pop( $pieces ) == $class . '.php' ) {

		     	 		  require_once $file;
			 		      return;
			 		  }
			      }
		 }

		 throw new AgilePHP_Exception( 'The requested class \'' . $class . '\' could not be loaded.' );
}
?>