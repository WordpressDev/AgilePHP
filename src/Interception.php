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
 * Includes all interception package dependancies
 */
require_once 'interception/InterceptorProxy.php';
require_once 'interception/InvocationContext.php';

/**
 * Performs interceptions by creating a dynamic proxy for intercepted
 * classes. The proxy invokes the intended calls after inspecting (and/or
 * intercepting) it according to the annotations in the intercepted object.
 * 
 * @author Jeremy Hahn
 * @copyright Make A Byte, inc.
 * @package com.makeabyte.agilephp
 * @version 0.2a
 */

class Interception {

	  private $class;
	  private $method;
	  private $property;
	  private $interceptor;

	  /**
	   * Initalizes the Interception
	   * 
	   * @param String $class The target class name
	   * @param String $method The method name
	   * @param Object $interceptor The instance of the interceptor which will intercept calls
	   * @return void
	   */
	  public function __construct( $class, $method, $property, $interceptor ) {

	  		 $this->class = $class;
	  		 $this->method = $method;
	  		 $this->property = $property;
	  		 $this->interceptor = $interceptor;

	  		 $this->createInterceptedTarget();
	  		 $this->createInterceptorProxy();
	  }

	  /**
	   * Returns the name of the target class being intercepted
	   * 
	   * @return The interception target class name
	   */
	  public function getClass() {

	  		 return $this->class;
	  }

	  /**
	   * Returns the name of the target method to intercept
	   */
	  public function getMethod() {

	  		 return $this->method;
	  }

	  /**
	   * Returns the name of the property to intercept
	   */
	  public function getProperty() {
	  	
	  		 return $this->property;
	  }

	  /**
	   * Returns the interceptor instance which handles the intercepted
	   * call.
	   * 
	   * @return void
	   */
	  public function getInterceptor() {

	  		 return $this->interceptor;
	  }

	  /**
	   * Creates a new intercepted target instance. The target is created by modifying
	   * the source code of the class being intercepted to *classname*_Intercepted.
	   * 
	   * @return Object The new intercepted target instance
	   */
	  public function createInterceptedTarget() {

	  		 if( class_exists( $this->class ) ) return;

	  		 $className = $this->class;
	  		 $code = $this->getSourceCode( $this->class );
	  		 $code = preg_replace( '/class\s' . $className . '\s/', 'class ' . $className . '_Intercepted ', $code );

			 $code = $this->clean( $code );
	  		 eval( $code );
	  }

	  /**
	   * Creates and loads a dynamic proxy class which performs interceptions
	   * on the class created by Interception::createInterceptedTarget().
	   * 
	   * @return void
	   */
	  public function createInterceptorProxy() {

	  		 if( class_exists( $this->class ) ) return;

	  	     $className = $this->class;
	  		 $code = $this->getSourceCode( 'InterceptorProxy' );
	  		 $code = preg_replace( '/InterceptorProxy/', $className, $code );
	  		 $code = $this->clean( $code );
	  		 eval( $code );
	  }

	  /**
	   * Strips PHP open/close tags from source code document so it can be
	   * passed to PHP eval().
	   * 
	   * @param $code The PHP code to clean
	   * @return The cleaned code
	   */
	  private function clean( $code ) {

	  		  $code = preg_replace( '/<\?php/', '', $code );
	  		  $code = preg_replace( '/\?>/', '', $code );

	  		  return $code;
	  }

	  /**
	   * Returns the PHP file content to be parsed.
	   * 
	   * @return String PHP code
	   * @throws AgilePHP_InterceptionException if the source could not be loaded
	   */
	  public function getSourceCode( $class ) {

		  	 // Search framework 
		     $it = new RecursiveDirectoryIterator( AgilePHP::getFramework()->getFrameworkRoot() );
			 foreach( new RecursiveIteratorIterator( $it ) as $file ) {
	
			   	      if( substr( $file, -1 ) != '.' && substr( $file, -2 ) != '..' ) {

				 		  if( array_pop( explode( DIRECTORY_SEPARATOR, $file ) ) == $class . '.php' )
			     	 			  return file_get_contents( $file );
				      }
			 }

			 // Search web app
	  	     $it = new RecursiveDirectoryIterator( AgilePHP::getFramework()->getWebRoot() );
			 foreach( new RecursiveIteratorIterator( $it ) as $file ) {
	
			   	      if( substr( $file, -1 ) != '.' && substr( $file, -2 ) != '..'  &&
			   	      	  substr( $file, -4 ) != 'view' ) {
	
				 		  if( array_pop( explode( DIRECTORY_SEPARATOR, $file ) ) == $class . '.php' )
			     	 			  return file_get_contents( $file );
				      }
			 }

	  		 throw new AgilePHP_InterceptionException( 'Failed to load source code for class \'' . $class . '\'.' );
	  }
}

?>