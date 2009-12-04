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
 * @package com.makeabyte.agilephp.mvc
 */

/**
 * AgilePHP :: MVC BaseController
 * Provides basic renderer implementations and defines 'index'
 * abstract method.
 * 
 * @author Jeremy Hahn
 * @copyright Make A Byte, inc
 * @package com.makeabyte.agilephp.mvc
 * @version 0.1a
 * @static
 * @abstract
 */
abstract class BaseController {

	     private $renderer = null;

	     /**
	      * Creates a new instance of default renderer
	      * 
	      * @return void
	      */
	     protected function __construct() {

	  	           $this->renderer = AgilePHP::getFramework()->getMVC()->createDefaultRenderer();
	     }

	     /**
	      * Returns the controllers view renderer.
	      * 
	      * @return void
	      */
	     protected function getRenderer() {

	     		   return $this->renderer;
	     }

	     /**
		  * Creates an instance of the specified renderer the controller will use to render views.
		  * This renderer is loaded from the AgilePHP framework.
		  * 
		  * @param $renderer The framework renderer the controller will use to render views
		  * @return void
	      */
	     protected function createRenderer( $renderer ) {

	     	       AgilePHP::getInstance()->getMVC()->createRenderer( $renderer ); 
	     }

	     /**
		  * Creates an instance of the specified custom renderer the controller will use to render views.
		  * This renderer is loaded from the application 'classes' directory.
		  * 
		  * @param $renderer The custom renderer the controller will use to render views
		  * @return void
	      */
	     protected function createCustomRenderer( $renderer ) {

	     	       AgilePHP::getInstance()->getMVC()->createCustomRenderer( $renderer ); 
	     }

	     /**
	      * Returns the raw JavaScript contents of the AgilePHP.js file and pre-configures the library
	      * with a default AgilePHP.debug, AgilePHP.MVC.controller, and AgilePHP.MVC.action value.
	      * 
	      * @param $debug True to enable client side AgilePHP debugging.
	      * @return void
	      */
	     public function getBaseJS( $debug = false ) {

	  		    $js = file_get_contents( AgilePHP::getFramework()->getFrameworkRoot() . '/AgilePHP.js' );

	  		    if( $debug ) $js .= "\nAgilePHP.setDebug( true );";

	  		    $js .= "\nAgilePHP.setRequestBase( '" . AgilePHP::getFramework()->getRequestBase() . "' );";
	  		    $js .= "\nAgilePHP.MVC.setController( '" . MVC::getInstance()->getController() . "' );";
	  		    $js .= "\nAgilePHP.MVC.setAction( '" . MVC::getInstance()->getAction() . "' );";

	  		    header( 'content-type: application/json' );
	  		    print $js;
	     }

	     /**
	      * Default controller action method.
	      * 
	      * @return void
	      */
	     abstract public function index();
}