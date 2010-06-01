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
 * Model-View-Control (MVC) component
 * 
 * @author Jeremy Hahn
 * @copyright Make A Byte, inc
 * @package com.makeabyte.agilephp
 */
class MVC {

	  private static $instance = null;

	  private $scriptExtension = '.php';
	  private $defaultController = 'IndexController';
	  private $defaultAction = 'index';
	  private $defaultRenderer = 'PHTMLRenderer';
	  private $controller;
	  private $action;
	  private $parameters;
	  private $sanitize = true;

	  private function __construct() {

	  		  require_once 'mvc/BaseController.php';
	  		  require_once 'mvc/BaseRenderer.php';
	  }

	  private function __clone() {}

	  /**
	   * Returns a singleton instance of MVC
	   * 
	   * @return Singleton instance of MVC
	   * @static
	   */
	  public static function getInstance() {

	  	     if( self::$instance == null )
	  	         self::$instance = new self;

	  	      return self::$instance;
	  }

	  /**
	   * Initalizes the MVC component with agilephp.xml configuration.
	   * 
	   * @param SimpleXMLElement $config SimpleXMLElement containing the MVC configuration.
	   * @return void
	   * @static
	   */
	  public function setConfig( $controller, $action, $renderer, $sanitize ) {

	  		 if( $controller ) $this->defaultController = $controller;
	  		 if( $action ) $this->defaultAction = $action;
	  		 if( $renderer ) $this->defaultRenderer = $renderer;
	  		 if( $sanitize ) $this->sanitize = $sanitize;
	  }
  
	  /**
	   * Sets the name of the default controller which is used if one is not
	   * specified in the request URI. Default is 'IndexController'.
	   * 
	   * @param String $name The name of the controller
	   * @return void
	   */
	  public function setDefaultController( $name ) {

	  	     $this->defaultController = $name;
	  }

	  /**
	   * Returns the name of a default controller if one is not specified
	   * in the request URI. Default is 'IndexController'.
	   * 
	   * @return String The name of the default controller
	   */
	  public function getDefaultController() {

	  	     return $this->defaultController;
	  }

	  /**
	   * Sets the name of the default action method if one is not specified
	   * in the request URI. Default is 'index'. 
	   * 
	   * @param String $name The name of the default action method
	   * @return void
	   */
	  public function setDefaultAction( $name ) {

	  	     $this->defaultAction = $name;
	  }

	  /**
	   * Returns the name of a default action method if one is not specified
	   * in the request URI. Default is 'index'.
	   * 
	   * @return String The name of the default action method
	   */
	  public function getDefaultAction() {

	  	     return $this->defaultAction;
	  }

	  /**
	   * Sets the name of the default view renderer. Default is 'PHTMLRenderer'.
	   * 
	   * @param String $renderer The name of a view renderer to use as the default
	   * @return void
	   */
	  public function setDefaultRenderer( $renderer ) {

	  	     $this->defaultRenderer = $renderer;
	  }

	  /**
	   * Returns the name of the default view renderer
	   * 
	   * @return String The default view renderer
	   */
	  public function getDefaultRenderer() {

	  	     return $this->defaultRenderer;
	  }
	  
	  /**
	   * Returns the name of the controller currently in use.
	   * 
	   * @return String The name of the controller in use by the MVC component.
	   */
	  public function getController() {
	  	
	  		 return $this->controller;
	  }

	  /**
	   * Returns the action currently being invoked.
	   * 
	   * @return String The name of the action currently being invoked.
	   */
	  public function getAction() {

	  		 return $this->action;
	  }
	  
	  /**
	   * Returns the action parameters specified in the request
	   * 
	   * @return Array Parameters passed to the invoked action
	   */
	  public function getParameters() {

	  		 return $this->parameters;
	  }

	  /**
	   * Parses the current request URI to obtain the controller, action method, and arguments
	   * present for this request and then performs the invocation. If these parameters ARE NOT
	   * present, the default controller and default action method are used instead.
	   * 
	   * NOTE: The URI requirement to communicate with this MVC system is as follows
	   *       http://domain.com/ScriptName.php/ControllerName/ActionMethod/arg1/arg2/arg3/etc...
	   * 
	   * @return void
	   */
	  public function dispatch() {

	  		 $path = (isset( $_SERVER['PHP_SELF'] )) ? $_SERVER['PHP_SELF'] : '/';

		  	 preg_match( '/^.*?\.php(.*)/si', $path, $matches );
	  	 
	  	     if( count( $matches ) ) {

		  	  	 $this->parameters = explode( '/', $matches[count($matches)-1] );
			  	 array_shift( $this->parameters );

			  	 // Assign controller and action
		  	     $controller = (isset($this->parameters[0]) > 0 && $this->parameters[0] != '') ? $this->parameters[0] : $this->getDefaultController(); 
		  	     $action = (isset($this->parameters[1])) ? $this->parameters[1] : $this->getDefaultAction();

		  	     // Remove controller and action from mvcPieces
		  	     array_shift( $this->parameters );
		  	     array_shift( $this->parameters );

		  	     // Security, Security, Security.... 
		  	     $controller = addslashes( strip_tags( $controller ) );
		  	     $action = addslashes( strip_tags( $action ) );

		  	     $this->controller = $controller;
		  	     $this->action = $action;
	  	     }

		  	 $this->controller = isset( $controller ) ? $controller : $this->getDefaultController();
		  	 $this->action = isset( $action ) ? $action : $this->getDefaultAction();

		     // Make sure controllers are loaded from the web application control directory ONLY.
	  	     if( !in_array( $this->controller, get_declared_classes() ) ) {

	  	     	 // Load front controller style phars first
	  	     	 $phar = AgilePHP::getFramework()->getWebRoot() . DIRECTORY_SEPARATOR . 'control' .
	  		  				DIRECTORY_SEPARATOR . $controller . '.phar';

	  		  	 if( file_exists( $phar ) ) {
	  		  	 	 require_once $phar;
	  		  	 	 return;
	  		  	 }

	  	     	 $this->loadController( $controller );
	  	     }

	  	     $oController = new $this->controller;
	  	     $action = $this->action;

	  	     // This try/catch statement hides the exception stack of the inner call. This makes debugging difficult.
	  	     //try {
		  	     	if( isset($this->parameters[0]) ) {

		  	     		$request = Scope::getRequestScope();

		  	     		if( $this->sanitize )
		  	     			foreach( $this->parameters as $key => $val )
					  	     	 	 $this->parameters[$key] = $request->sanitize( $val );

					  	// If this is a class thats been intercepted, check both the inner class and the interceptor for
					  	// the presence of the requested method/action.
					  	if( method_exists( $this->controller, 'getInterceptedInstance' ) ) {

					  		if( !method_exists( $oController->getInterceptedInstance(), $action ) )
					  			throw new AgilePHP_Exception( 'The specified action \'' . $action . '\' does not exist.' );
					  	}
					  	else {

					  		// This is a standard PHP class that hasnt been intercepted
						  	if( !method_exists( $this->controller, $action ) )
						  		throw new AgilePHP_Exception( 'The specified action \'' . $action . '\' does not exist.' );
					  	} 

					  	Log::debug( 'MVC::processRequest Invoking controller \'' . $this->controller . 
					  	     			'\', action \'' . $this->action . '\', args \'' . implode( ',', $this->parameters  ) . '\'.' );

		  	     		call_user_func_array( array( $oController, $action ), $this->parameters ); 
		  	     	}
		  	     	else {
	
		  	     		Log::debug( 'MVC::processRequest Invoking controller \'' . $this->controller . 
					  	     			'\', action \'' . $this->action . '\'.' );
	
		  	     		$oController->$action();
		  	     	}
	  	     //}
	  	     //catch( Exception $e ) {

	  	     		//throw new AgilePHP_Exception( $e->getMessage(), $e->getCode() );
	  	     //}
	  }

	  /**
	   * Returns a new instance of the default view renderer
	   * 
	   * @return Object An instance of the default renderer
	   */
	  public function createDefaultRenderer() {

	  	     $path = AgilePHP::getFramework()->getFrameworkRoot() . '/mvc/' . $this->getDefaultRenderer() . '.php';

	  	     Log::debug( 'MVC::createDefaultRenderer loading renderer: ' . $this->getDefaultRenderer() );

	  	     if( !file_exists( $path ) )
	  	     	 throw new AgilePHP_Exception( 'Default framework renderer could not be loaded from: ' . $path );

	  	     require_once $path;

	  	     $renderer = $this->getDefaultRenderer();
	  	     return new $renderer();
	  }

	  /**
	   * Returns a new instance of the specified view renderer
	   * 
	   * @return Object An instance of the specified renderer
	   */
	  public function createRenderer( $renderer ) {

	  	     $path = AgilePHP::getFramework()->getFrameworkRoot() . '/mvc/' . $renderer . '.php';

	  	     Log::debug( 'MVC::createRenderer loading renderer: ' . $renderer );

	  		 if( !file_exists( $path ) )
	  	     	 throw new AgilePHP_Exception( 'Framework renderer could not be loaded from: ' . $path );

			 require_once $path; 	  		 
	  		 return new $renderer;
	  }

	  /**
	   * Returns a new instance of the specified renderer. The renderer is loaded from
	   * the web app 'classes' directory.
	   * 
	   * @param $renderer The name of the custom view renderer
	   * @param $classpath A relative child path under the webapp's 'classes' folder where the renderer is located.
	   * @return Object A new instance of the custom renderer
	   */
	  public function createCustomRenderer( $renderer, $classpath='' ) {

	  	     $path = AgilePHP::getFramework()->getWebRoot() . '/classes/' . $classpath . '/' . $renderer . '.php';

	  	     Log::debug( 'MVC::createDefaultRenderer loading custom renderer: ' . $renderer );

	  	     if( !file_exists( $path ) )
	  	     	 throw new AgilePHP_Exception( 'Custom renderer could not be loaded from: ' . $path );

	  	     require_once $path;
	  	     return new $renderer;
	  }

	  /**
	   * Loads a controller class only if it exists in the application controller directory.
	   * 
	   * @param String $controller The name of the controller to load.
	   * @return void
	   * @throws AgilePHP_Exception if the requested controller could not be found.
	   */
	  private function loadController( $controller ) {

	  		  $f = AgilePHP::getFramework()->getWebRoot() . DIRECTORY_SEPARATOR . 'control' .
	  		  		DIRECTORY_SEPARATOR . $controller . '.php';

	  		  if( file_exists( $f ) ) {

	  		  	  __autoload( $controller );
	  		  	  return;
	  		  }

	  		  // Perform deeper scan of control directory
	  		  $f = AgilePHP::getFramework()->getWebRoot() . DIRECTORY_SEPARATOR . 'control';
		  	  $it = new RecursiveDirectoryIterator( $f );
			  foreach( new RecursiveIteratorIterator( $it ) as $file ) {
	
			   	       if( substr( $file, -1 ) != '.' && substr( $file, -2 ) != '..' ) {
	
			   	       	   $pieces = explode( DIRECTORY_SEPARATOR, $file );
			   	      	   $item = array_pop( $pieces ); 

			   	      	   if( $item == $controller . '.php' ) {

				 		   	   __autoload( $controller );
				 		       return;
				 		   }
				       }
			  }

	  		  throw new AgilePHP_Exception( 'The requested controller \'' . $controller . '\' could not be found.' );
	  }
}
?>