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
 * AgilePHP :: MVC XSLTRenderer
 * Provides base implementation for XSLT transformations
 * 
 * @author Jeremy Hahn
 * @copyright Make A Byte, inc
 * @package com.makeabyte.agilephp.mvc
 * @version 0.1a
 */
class XSLTRenderer extends BaseRenderer {

      /**
	   * Renders a view by performing an XSLT transformation. The XSL document
	   * is passed in as a string. The XML document is passed in as a string.
	   * 
	   * @param $xsl XSL document as a string type
	   * @param $xml XML document as a string type
	   * @return void
       */
      public function render( $xsl, $xml = '' ) {
 
      	 	 $dom = new DomDocument();
			 $dom->loadXML( $xsl );

			 $xp = new XSLTProcessor();
			 $xsl = $xp->importStylesheet( $dom );

			 $doc = new DomDocument();
			 $doc->loadXML( $xml );

			 $xslt = $xp->transformToXml( $doc );

			 Logger::getInstance()->debug( 'XSLTRenderer::render executed with parameters xsl = ' . $xsl . ', xml = ' . $xml );
			 Logger::getInstance()->debug( 'XSLTRenderer::render performed transformation with result ' . $xslt );

			 print $xslt;
	  }

	  /**
	   * Performs an XSLT transformation and returns the rendered HTML.
	   * 
	   * @param $xsl XSL document as a string type
	   * @param $xml XML document as a string type
	   * 
	   * @return The rendered HTML from the XSLT transformation
       */
      public function transform( $xsl, $xml = '' ) {

    	     set_error_handler( 'XSLTRenderer::ErrorHandler' );

      	 	 $dom = new DomDocument();
	 		 $dom->loadXML( $xsl );

			 $xp = new XSLTProcessor();
			 $xsl = $xp->importStylesheet( $dom );

			 $doc = new DomDocument();
			 try {
			 		$doc->loadXML( $xml );
			 }
			 catch( AgilePHP_Exception $e ) {
			 	
			 	    $doc->loadXML( addslashes( $xml ) );
			 }

			 $xslt = $xp->transformToXml( $doc );

			 restore_error_handler();

			 Logger::getInstance()->debug( 'XSLTRenderer::transform executed with parameters xsl = ' . $xsl . ', xml = ' . $xml );
			 Logger::getInstance()->debug( 'XSLTRenderer::transform performed transformation with result ' . $xslt );

			 return $xslt;
	  }

	  /**
	   * Renders a view by performing an XSLT transformation. The XSL document
	   * is read in from the specified xsl view. The XML document is passed in as a string.
	   * 
	   * @param $xsl XSL document located in the web app view directory
	   * @param $xml XML document as a string type
	   * @return void
       */
      public function renderXslFile( $xsl, $xml='' ) {

      	 	 $dom = new DomDocument();
			 $dom->load( AgilePHP::getFramework()->getWebRoot() . '/view/' . $xsl . '.xsl' );

			 $xp = new XSLTProcessor();
			 $xsl = $xp->importStylesheet( $dom );

			 $doc = new DomDocument();
			 $doc->loadXML( $xml );

			 $xslt = $xp->transformToXml( $doc );
			 
			 Logger::getInstance()->debug( 'XSLTRenderer::renderXslFile executed with parameters xsl = ' . $xsl . ', xml = ' . $xml );
			 Logger::getInstance()->debug( 'XSLTRenderer::renderXslFile performed transformation with result ' . $xslt );

			 print $xslt;
	  }

	  /**
	   * Performs an XSLT tranformation using a specified XSL view document and returns the HTML result.
	   * The XSL document is read in from the web application 'view' directory. The XML document is
	   * passed in as a string.
	   * 
	   * @param $xsl XSL document located in the web app view directory
	   * @param $xml XML document as a string type
	   * @return void
       */
      public function getRenderedXslFile( $xsl, $xml='' ) {

      	 	 $dom = new DomDocument();
			 $dom->load( AgilePHP::getFramework()->getWebRoot() . '/view/' . $xsl . '.xsl' );

			 $xp = new XSLTProcessor();
			 $xsl = $xp->importStylesheet( $dom );

			 $doc = new DomDocument();
			 $doc->loadXML( $xml );

			 $xslt = $xp->transformToXml( $doc );

			 Logger::getInstance()->debug( 'XSLTRenderer::renderXslFile executed with parameters xsl = ' . $xsl . ', xml = ' . $xml );
			 Logger::getInstance()->debug( 'XSLTRenderer::renderXslFile performed transformation with result ' . $xslt );

			 return $xslt;
	  }

	  /**
	   * loadXml reports an error instead of throwing an exception when the xml is not well formed. This
	   * is a custom PHP error handling function which throws an AgilePHP_Exception instead of reporting
	   * a PHP error. 
	   */
 	  public static function ErrorHandler( $errno, $errmsg, $errfile, $errline ) {

 	  	     if( $errno==E_WARNING && (substr_count( $errmsg, "DOMDocument::loadXML()" ) > 0 ) )
	    	     throw new AgilePHP_Exception( $errmsg );
	    	 else
	             return false;
	  }
}
?>