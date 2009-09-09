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
 * @package com.makeabyte.agilephp.annotation
 */

/**
 * AgilePHP :: AnnotationParser
 * Responsible for parsing and returning annotation details about PHP classes.
 * 
 * @author Jeremy Hahn
 * @copyright Make A Byte, inc.
 * @package com.makeabyte.agilephp.annotation
 * @version 0.1a
 * @static
 */
class AnnotationParser {

	  private static $instance;
	  private $classes = array();
	  private $properties = array();
	  private $methods = array();
	  private $sources = array();

	  private $class;
	  private $filename;

	  private function __construct() { }
	  private function __clone() { }

	  public static function getInstance() {

	  		 if( self::$instance == null )
	  		 	 self::$instance = new self();

	  		 return self::$instance;
	  }

	  /**
	   * Breaks the class file into PHP tokens and extracts all interface,
	   * class, method, and property level annotations.
	   * 
	   * @param $class The name of the class to parse
	   * @return void
	   */
	  public function parse( $class ) {

	  		 $this->class = $class;
	  		 $this->filename = $class . '.php';

		     if( in_array( $this->filename, $this->sources ) )
		         return;

	  		 array_push( $this->sources, $this->filename );

	  		 $comments = array();
	  		 $tokens = token_get_all( $this->getSourceCode() );

	  		 for( $i=0; $i<count( $tokens ); $i++ ) {

	  			  $token = $tokens[$i];
				  if( is_array( $token ) ) {

					  list( $code, $value ) = $token;

					  switch( $code ) {

							  case T_COMMENT:

							 	   array_push( $comments, $value );
								   break;

							  case T_CLASS:

								   if( count( $comments ) ) {

									   $this->classes[$class] = $this->parseAnnotations( implode( "\n", $comments ) );
									   $comments = array();
								   }
								   break;

							  case T_VARIABLE:

								   if( count( $comments ) ) {

								   	   $key = str_replace( '$', '', $token[1] );
									   $this->properties[$class][ $key ] = $this->parseAnnotations( implode( "\n", $comments ) );
									   $comments = array();
								   }
								   break;

							  case T_FUNCTION:

								   if( count( $comments ) ) {

									   for( $j=$i; $j<count( $tokens ); $j++ ) {
										    if( is_array( $tokens[$j] ) ) {
										 	    if( $tokens[$j][0] == T_STRING ) {
										 	 	    $this->methods[$class][$tokens[$j][1]] = $this->parseAnnotations( implode( "\n", $comments ) );
										 	 	    $comments = array();
										 	 	    break;
										 	    }
										    }
									   }
								   }
							 	   break;

							 	   /*
							case T_INTERFACE:

								   if( count( $comments ) ) {

									   $this->annotations['interface'] = $this->parseAnnotations( implode( "\n", $comments ) );
									   $comments = array();
								   }
								   break;
								   */

							case T_DOC_COMMENT;
							case T_WHITESPACE: 
							case T_PUBLIC: 
							case T_PROTECTED: 
							case T_PRIVATE: 
							case T_ABSTRACT: 
							case T_FINAL: 
							case T_VAR: 
								break;

							default:
								$comments = array();
								break;
						}
					}
					else {

						$comments = array();
					}
				}
	  }

	  /**
	   * Returns an array of class level annotations or false if no class
	   * level annotations are present.
	   * 
	   * @return Array of class level annotations or false if no annotations
	   * 		 are present.
	   */
	  public function getClassAnnotations( AnnotatedClass $class ) {

	  		 return isset( $this->classes[$class->getName()] ) ? $this->classes[$class->getName()] : false; 
	  }

	  /**
	   * Returns an array of property level annotations or false if no annotations
	   * are present for the specified property.
	   * 
	   * @param $property The AnnotatedProperty to get the annotations for
	   * @return Array of property level annotations
	   */
	  public function getPropertyAnnotations( AnnotatedProperty $property ) {

  		 	 $class = $property->getDeclaringClass()->getName();
  		 	 
  		 	 if( isset( $this->properties[$class] ) ) {

		  		 foreach( $this->properties[$class] as $name => $value )
		  		  		if( $name == $property->getName() )
		  		 			return $value;
  		 	 }

  		 	 return false;
	  }

	  /**
	   * Returns an array of method level annotations or false if no annotations
	   * are found for the specified method.
	   * 
	   * @param $method The AnnotatedMethod to search
	   * @return Array of method level annotations or false if no annotations are present.
	   */
	  public function getMethodAnnotations( AnnotatedMethod $method ) {

	  	     $class = $method->getDeclaringClass()->getName();
	  	     if( isset( $this->methods[$class] ) ) {
			     
	  	     	 foreach( $this->methods[$class] as $name => $value )
			  		 	if( $name == $method->getName() )
			  		 		return $value;
	  	     }

		  	 return false;
	  }

	  /**
	   * Returns an array of class level annotations
	   * 
	   * @param $class The name of the parsed class
	   * @return Array of class level annotations
	   */
	  public function getClassAnnotationsAsArray( $class ) {

	  		 return $this->classes[$class];
	  }

	  /**
	   * Returns an array of method level annotations for the specified class
	   * 
	   * @param $class The name of the parsed class
	   * @param $method The name of the parsed method
	   * @return Array of method level annotations
	   */
	  public function getMethodAnnotationsAsArray( $class ) {

	  		 return $this->methods[$class];
	  }

	  /**
	   * Returns an array of property level annotations for the specified class
	   * 
	   * @param $class The name of the parsed class
	   * @param $property The name of the parsed property
	   * @return Array of property level annotations
	   */
	  public function getPropertyAnnotationsAsArray( $class ) {

	  		 return $this->properties[$class];
	  }

	  /**
	   * Parses the text string extracted from tokenized PHP file which contains
	   * annotation markup.
	   * 
	   * @param $text The text string containing the annotation to parse
	   * @return void
	   */
	  private function parseAnnotations( $text ) {

	  		  $annotations = array();

			  // Extract the annotation string including the name and property/value declaration
	  		  preg_match_all( '/#?@(.*)/', $text, $annotes );

			  if( !count( $annotes ) )
	  		  	  return;

	  		  foreach( $annotes[1] as $annote ) {

	  		  		   // Extract the annotation name
	  		  		   preg_match( '/\w+/', $annote, $className );

	  		  		   // Create instance of the annotation class or create a new instance of stdClass
	  		  		   // if the annotation class could not be parsed
	  		  		   $oAnnotation = new $className[0]();

	  		  		   // Extract name/value pair portion of the annotation
	  		  		   preg_match_all( '/\((.*=.*\(?\)?)\)/', $annote, $props );

					   // Extract arrays
					   preg_match_all( '/[_a-zA-Z]+[0-9_]?\s?=\s?{+?.*?}+\s?,?/', $props[1][0], $arrays );

					   // Extract other annotations
					   //preg_match_all( '/@(.*)?,?/', $props[1][0], $childAnnotes );
					   //if( isset( $childAnnotes[1] ) ) { }

					   // Add arrays to annotation instance and remove it from the properties
	  		  		   if( count( $arrays ) ) {

	  		  		   	   $result = $this->parseKeyArrayValuePairs( $oAnnotation, $arrays[0], $props[1][0] );
	  		  		   	   $oAnnotation = $result->annotation;
	  		  		   	   $props[1][0] = $result->properties;
					   }

					   // Add strings and PHP literals to annotation instance
					   $oAnnotation = $this->parseKeyValuePairs( $oAnnotation, $props[1][0] );

					   // Push the annotation instance onto the stack
	  		  		   array_push( $annotations, $oAnnotation );
	  		  }

	  		  return $annotations;
	  }
	  
	  /**
	   * Parses an annotations property assignments which contain one or more array values. The
	   * array is added to the annotation instance according to its property name and the array
	   * is removed from the properties string.
	   * 
	   * @param $oAnnotation An instance of the annotation object
	   * @param $arrays The string value containing each of the property assignments
	   * @param $properties The annotations properties as they were parsed from the code
	   * @return stdClass instance containing the annotation instance and truncated properties string
	   */
	  private function parseKeyArrayValuePairs( $oAnnotation, $arrays, $properties ) {

	  		 foreach( $arrays as $array ) {

		  		 	// Remove arrays from the parsed annotation property/value assignments
					$properties = preg_replace( '/' . $array . '/', '', $properties ) . "\n";

			   		// Split the array into key/value
			   		preg_match( '/(.*)={1}\s?\{(.*)\},?/', $array, $matches );
			   		$property = trim( $matches[1] );
			   		$elements = explode( ',', trim( $matches[2] ) );

			   		// Place each of the annotations array elements into a PHP array
			 		$value = array();
			   		foreach( $elements as $element ) {
	
			   				$pos = strpos( $element, '=' );
			   				if( $pos !== false ) {
	
			   					// Associative array element
			   					$pieces = explode( '=', $element );
			   					//$value[ trim( $pieces[0] ) ] = trim( $pieces[1] );
			   					$value[ trim( $pieces[0] ) ] = $this->getQuotedStringValue( $pieces[1] );
			   				}
			   				else

			   					// Indexed array element
			   					array_push( $value, $this->getQuotedStringValue( $element ) );
			   		}

			   		// Set the annotation instance property with the PHP array
			   		$oAnnotation->$property = $value;
			   }

			   $stdClass = new stdClass();
			   $stdClass->annotation = $oAnnotation;
			   $stdClass->properties = $properties;

			   return $stdClass;
	  }

	  /**
	   * Parses quoted strings from annotations property VALUE definitons.
	   * 
	   * @param $value The value to parse
	   * @return void
	   */
	  private function getQuotedStringValue( $value ) {

	  		  // Single quoted value
  	  		  $pos = strpos( $value, '\'' );
  	  		  if( $pos !== false ) {

  	  		      preg_match( '/\s?\'+(.*)\'+/', $value, $matches );
  	  		   	  if( count( $matches ) == 2 )
  	  		   	      return $matches[1];
  	  		   }

  	  		   // Double quoted value
			   $pos = strpos( $value, '"' );
  	  		   if( $pos !== false ) {

  	  		       preg_match( '/\s?"+(.*)"+/', $value, $matches );
  	  		   	   if( count( $matches ) == 2 )
  	  		   	 	   return $matches[1];
  	  		   }

  	  		   // Treat unquoted values as objects
			   $o = str_replace( ' ', '', $value );
  	  		   return new $o;
	  }

	  /**
	   * Parses strings and PHP literals from annotation property definition(s).
	   * 
	   * @param $oAnnotation An instance of the annotation object
	   * @param $properties String representation of the annotations property definition(s).
	   * @return The annotation instance populated according to its definition(s).
	   */
	  private function parseKeyValuePairs( $oAnnotation, $properties ) {

  		  	 $keyValuePairItems = explode( ',', $properties );

  	  		 foreach( $keyValuePairItems as $kv ) {

  	  		   		  $pieces = explode( '=', $kv );

  	  		   		  preg_match( '/(.*)=(.*)/', $kv, $pieces );
  	  		   		  $property = trim( $pieces[1] );
  	  		   		  $value = trim( $pieces[2] );

  	  		   		  // Single quoted value
  	  		   		  $pos = strpos( $value, '\'' );
  	  		   		  if( $pos !== false ) {

  	  		   			  preg_match( '/^\'(.*)\'/', $value, $matches );
  	  		   			  if( count( $matches ) == 2 ) {

  	  		   				  $oAnnotation->$property = $matches[1];
  	  		   				  continue;
  	  		   			  }
  	  		   		  }

  	  		   		  // Double quoted value
					  $pos = strpos( $value, '"' );
  	  		   		  if( $pos !== false ) {

  	  		   			  preg_match( '/^"(.*)"/', $value, $matches );
  	  		   			  if( count( $matches ) == 2 ) {

  	  		   				  $oAnnotation->$property = $matches[1];
  	  		   				  continue;
  	  		   			  }
  	  		   		  }

  	  		   		  // Treat values which are not quoted as PHP literals
  	  		   		  if( $property && $value )
  	  		   			  $oAnnotation->$property = eval( 'return ' . $value . ';' );
  	  		   }

  	  		   return $oAnnotation;
	  }

	  /**
	   * Returns the PHP file content to be parsed.
	   * 
	   * @return PHP code
	   */
	  public function getSourceCode() {

	  		 if( $code = $this->search( AgilePHP::getFramework()->getFrameworkRoot() ) )
	  		     return $code;

	  		 if( $code = $this->search( AgilePHP::getFramework()->getWebRoot() ) )
	  		     return $code;

	  		 throw new AgilePHP_AnnotationException( 'Failed to load source code for class \'' . $this->class . '\'.' );
	  }

	  /**
	   * Recursively scan the specified directory in an effort to find $this->class to load its
	   * source code.
	   * 
	   * @param $directory The directory to scan. 
	   * @return File contents for $this->class or null if the file contents could not be located
	   */
	  private function search( $directory ) {

	  	 $it = new RecursiveDirectoryIterator( $directory );
		 foreach( new RecursiveIteratorIterator( $it ) as $file ) {

		   	      if( substr( $file, -1 ) != '.' && substr( $file, -2 ) != '..'  &&
		   	      	  substr( $file, -4 ) != 'view' ) {

			 		  if( array_pop( explode( '/', $file ) ) == $this->filename )
		     	 			  return file_get_contents( $file );
			      }
		 }
	  }
}
?>