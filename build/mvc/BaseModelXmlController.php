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
 * AgilePHP :: MVC BaseModelXmlController
 * Provides base implementation for model xml controllers.
 * 
 * @author Jeremy Hahn
 * @copyright Make A Byte, inc
 * @package com.makeabyte.agilephp.mvc
 * @version 0.1a
 * @abstract
 */
abstract class BaseModelXmlController extends BaseModelController {

		 protected function __construct() {

		 		   parent::__construct();
		 }

		 /**
		  * Creates an XML document representing a model. If the 'id' parameter is set, a
		  * lookup is performed for the model with the specified 'id' and the XML is returned with
		  * data populated from the database result set.. If there is no 'id' set, the model's property
		  * nodes will be null. A custom controller and action can be set to modify default behavior.
		  * 
		  * @param $controller The controller to use for add/update/delete operations. Defaults to the controller
		  * 				   that invoked this method.
		  * @param $action The controllers action method to invoke. Defaults to the model name (lowercase) followed
		  * 			   by the action mode 'Add' or 'Edit' which is determined by whether or not an 'id' parameter
		  * 			   has been specified. For example, a user model would be either 'userAdd' or 'userEdit' 
		  * 			   depending on whether or not an 'id' parameter is specified. If no 'id' has been specified
		  * 			   the default action would be 'userAdd', or 'userEdit' if an 'id' parameter has been set.
		  * @param $params An array of parameters to 
	      */
	     protected function getModelAsFormXML( $controller = null, $action = null, $params = null ) {

  			 	   $thisController = new ReflectionClass( $this );
  			 	   $c = ($controller) ? $controller : $thisController->getName();
  			 	   $a = ($action) ? $action : 'modelAction';

  			 	   $xml = '<Form>
  			 	   			<' . $this->getModelName() . '>';

  			 	   $fieldCount = 0;

  			 	   $isMerge = false;
  			 	   $table = $this->getPersistenceManager()->getTableByModel( $this->getModel() );
  			 	   $pkeyColumns = $table->getPrimaryKeyColumns();
  			 	   foreach( $pkeyColumns as $column ) {

  			 	   	        $accessor = $this->toAccessor( $column->getName() );
  			 	   	        if( $this->getModel()->$accessor() )
  			 	   	        	$isMerge = true;
  			 	   }

  			 	   if( $isMerge ) {

  			 	   	   $model = $this->getPersistenceManager()->find( $this->getModel() );

  			 	   	   foreach( $table->getColumns() as $column ) {

  			 	   	   			$accessor = $this->toAccessor( $column->getModelPropertyName() );

  			 	   	   			$fieldCount++;
  			 	   	   	     	if( is_object( $model->$accessor() ) ) continue;

  			 	   	   	     	$xml .= ($column->getType() == 'bit') ? 
  			 	   	   	     				'<' . $column->getModelPropertyName() . '>' . ( (ord($model->$accessor()) == 1) ? '1' : '0') . '</' . $column->getModelPropertyName() . '>'
  			 	   	   	     				: '<' . $column->getModelPropertyName() . '>' . $model->$accessor() . '</' . $column->getModelPropertyName() . '>';
  			 	   	   			
  			 	   	   }
  			 	   }
  			 	   else {

  			 	   	   $modelRefl = new ReflectionClass( $this->getModelName() );
  			 	   	   $properties = $modelRefl->getProperties();

  			 	   	   foreach( $properties as $property ) {

  			 	   	   			$fieldCount++;
		     		   	        $xml .= '<' . $property->name . '/>';
  			 	   	   }
  			 	   }

	  			   $xml .= '</' . $this->getModelName() . '>
	  			 	   		<controller>' . $c . '</controller>
	  			 	   		<action>' . $a . '</action>';
	  			   $xml .= ($params ? '<params>' . $params . '</params>' : '');
	  			   $xml .= '<fieldCount>' . $fieldCount . '</fieldCount>
	  			 	   	</Form>';

	  			   Logger::getInstance()->debug( 'BaseModelXmlController::getModelAsFormXML called with parameters controller = ' . $controller . ', action = ' . $action );
	  			   Logger::getInstance()->debug( 'BaseModelXmlController::getModelAsFormXML returning xml ' . $xml );

  			 	   return $xml;
	     }

		 /**
	      * Returns a result set from the database as XML. The XML document is returned
	      * with the root node 'ResultList' containing an element named after the model
	      * which then contains each of the models properties and values as children.
	      * For example:
	      * <ResultList>
	      * 	<your_model_name>
	      * 		<model_prop1>*</model_prop1>
	      * 		<model_prop2>*</model_prop2>
	      * 	</your_model_name>
	      * </ResultList>. 
	      * 
	      * @return An XML document representing the result list
	      */
	     protected function getResultListAsXML() {

	  	     	   $doc = new DomDocument( '1.0' );
      	     	   $root = $doc->createElement( 'ResultList' );
             	   $root = $doc->appendChild( $root );

             	   if( !$this->getResultList() )
             	   	   throw new AgilePHP_Exception( 'BaseModelXmlController::getResultListAsXml() requires a valid result set to transform to XML.' );

			 	   foreach( $this->getResultList() as $stdClass  ) {

			 	   	        $modelName = $doc->createElement( $this->getModelName() );
				  		    $modelName = $root->appendChild( $modelName );

			 	   	   		foreach( get_object_vars( $stdClass ) as $prop => $val ) {

			 	   	   			 $child = $doc->createElement( $prop );
				  				 $child = $modelName->appendChild( $child );
		                  		 $fieldvalue = mb_convert_encoding( html_entity_decode( $val ), 'UTF-8', 'ISO-8859-1' );
						  		 $value = $doc->createTextNode( $fieldvalue );
						  		 $value = $child->appendChild( $value );
			 	   	   		}
			 	   }

			 	   $xml = $doc->saveXML();

			 	   Logger::getInstance()->debug( 'BaseModelXmlController::getResultListAsXML returning xml ' . $xml );

	  			   return $xml; 
	     }

		 /**
	      * Returns a paged result set from the database as XML (including foreign model instances with their
	      * primary keys set). The XML document is returned in the following format:
	      * 
	      * <ResultList>
	      * 	<Model>
	      * 		<your_model_name>
		  *     		<foreign_model_name>
		  *     			<primary_key1>*</primary_key1>
		  *     			<primary_key2>*</primary_key2> 
		  *     		</foreign_model_name>
	      * 			<model_prop1>*</model_prop1>
	      * 			<model_prop2>*</model_prop2>
	      *			</your_model_name>
	      *		</Model>
	      *		<Pagination>
	      *			<page>*</page>
	      *	        <pageCount>*</pageCount>
	      *	        <nextExists>*</nextExists>
	      *	       	<previousExists>*</previousExists>
	      *			<controller>*</controller>
	      *			<action>*</action>
	      *		</Pagination>
	      * </ResultList> 
	      * 
	      * @return An XML document representing the result list
	      */
	     protected function getResultListAsPagedXML( $controller = null, $action = null, $params = null ) {

	     		   $c = (!$controller) ? new ReflectionClass( $this ) : new ReflectionClass( $controller );
   		   		   $a = (!$action) ? 'modelList' : $action;
   		   		   $table = $this->getPersistenceManager()->getTableByModelName( $this->getModelName() );

   		   		   $fkeyColumns = $table->getForeignKeyColumns();
   		   		   $hasFkeyColumns = count( $fkeyColumns ) > 0 ? true : false;

	  	     	   $doc = new DomDocument( '1.0' );
      	     	   $root = $doc->createElement( 'ResultList' );
             	   $root = $doc->appendChild( $root );

             	   $model = $doc->createElement( 'Model' );
             	   $model = $root->appendChild( $model );

             	   if( $this->getResultList() ) {

             	   	   foreach( $this->getResultList() as $stdClass  ) {

				 	   	        $modelName = $doc->createElement( $this->getModelName() );
				 	   	        $model->appendChild( $modelName );

				 	   	        // Handle foreign keys
             	   	   			if( $table->hasForeignKey() ) {

					      		    $bProcessedKeys = array();
					   		  	    $fKeyColumns = $table->getForeignKeyColumns();
					   		  	    for( $i=0; $i<count( $fKeyColumns ); $i++ ) {

					   	  	  		  	 $fk = $fKeyColumns[$i]->getForeignKey();

					   		  	  		 if( in_array( $fk->getName(), $bProcessedKeys ) )
					   		  	  		     continue;

					     	  	       	 // Get foreign keys which are part of the same relationship
					     	  	       	 $relatedKeys = $table->getForeignKeyColumnsByKey( $fk->getName() );

				         		   	 	 $foreignModelName = $doc->createElement( $relatedKeys[0]->getReferencedTableInstance()->getModel() );
				    	        		 $foreignModel = $modelName->appendChild( $foreignModelName );

					         		   	 for( $j=0; $j<count( $relatedKeys ); $j++ ) {

					     	  	       		  // Loop through result set looking for a matching property name to extract the foreign key values
					     	  	       		  foreach( get_object_vars( $stdClass ) as $prop => $val ) {

					     	  	       		 	  	   if( $prop == $relatedKeys[$j]->getColumnInstance()->getName() ) {

					   	  	  	       		   	 		   $child = $doc->createElement( $relatedKeys[$j]->getReferencedColumnInstance()->getModelPropertyName() );
											  			   $child = $foreignModel->appendChild( $child );
									                  	   $fieldvalue = mb_convert_encoding( html_entity_decode( $val ), 'UTF-8', 'ISO-8859-1' );
													  	   $value = $doc->createTextNode( $fieldvalue );
													  	   $value = $child->appendChild( $value );
					     	  	       		   	 	   }
					     	  	       		   }
					   		  	  		   	   array_push( $bProcessedKeys, $fk->getName() );
					         		   	  }
					   		  	     }
					            }

					            // Handle the model
				 	   	   		foreach( get_object_vars( $stdClass ) as $prop => $val ) {

				 	   	   			     if( PersistenceRenderer::isBit( $table, $prop ) )
				 	   	   			         $val = (ord($val) == 1) ? 'Yes' : 'No';

				 	   	   			 	 $child = $doc->createElement( $this->getPersistenceManager()->getPropertyNameByColumn( $table, $prop  ) );
					  				 	 $child = $modelName->appendChild( $child );
			                  		 	 $fieldvalue = mb_convert_encoding( html_entity_decode( $val ), 'UTF-8', 'ISO-8859-1' );
							  		 	 $value = $doc->createTextNode( $fieldvalue );
							  		 	 $value = $child->appendChild( $value );
				 	   	   		}
				 	    }
	     		   }

			 	   $pagination = $doc->createElement( 'Pagination' );
			 	   $pagination = $root->appendChild( $pagination );

			 	   $page = $doc->createElement( 'page', $this->getPage() );
			 	   $pagination->appendChild( $page );

			 	   $pageCount = $doc->createElement( 'pageCount', $this->getPageCount() );
			 	   $pagination->appendChild( $pageCount );
			 	   
			 	   $nextExists = $doc->createElement( 'nextExists', ($this->nextExists() == true) ? 1 : 0 );
			 	   $pagination->appendChild( $nextExists );

			 	   $prevExists = $doc->createElement( 'previousExists', ($this->previousExists()) ? 1 : 0 );
			 	   $pagination->appendChild( $prevExists );

			 	   $resultCount = $doc->createElement( 'resultCount', $this->getResultCount() );
			 	   $pagination->appendChild( $resultCount );

			 	   $recordCount = $doc->createElement( 'recordCount', $this->getCount() );
			 	   $pagination->appendChild( $recordCount );

			 	   $start = ($this->getPage() * $this->getMaxResults()) - ($this->getMaxResults() - 1);
			 	   if( !$this->getCount() ) $start = 0;

			 	   $recordStart = $doc->createElement( 'recordStart', ($start <= 0) ? 0 : $start );
			 	   $pagination->appendChild( $recordStart );

			 	   $end = $start + ($this->getMaxResults() - 1);
			 	   if( $end > $this->getCount() ) $end = $this->getCount();

			 	   $recordEnd = $doc->createElement( 'recordEnd', $end );
			 	   $pagination->appendChild( $recordEnd );

			 	   $controller = $doc->createElement( 'controller', $c->getName() );
			 	   $pagination->appendChild( $controller );

			 	   $action = $doc->createElement( 'action', $a );
			 	   $pagination->appendChild( $action );

			 	   if( $params ) {

			 	   	   $paramz = $doc->createElement( 'params', $params );
			 	   	   $pagination->appendChild( $paramz );
			 	   }

	  			   $xml = $doc->saveXML();
	  			   
	  			   Logger::getInstance()->debug( 'BaseModelXmlController::getResultListAsPagedXML executed with parameters $controller = ' .
	  			   				 $c->getName() . ', $action = ' . $a );
	  			   Logger::getInstance()->debug( 'BaseModelXmlController::getResultListAsPagedXML returning XML data: ' . $xml );

	  			   return $xml;
	     }

	     /**
	      * Returns the type of action which the controllers should take when deciphering
	      * whether the operation is a persist or merge operation. If the primary key(s)
	      * contain a value, the action is assumed a merge. If the primary key(s) do not
	      * contain a value, the action is assumed persist.
	      * 
	      * @return 'persist' if the primary key value(s) are not present, 'merge' if
	      * 	    the primary keys are present.
	      */
	     protected function getModelPersistenceAction() {

	     		   $table = $this->getPersistenceManager()->getTableByModel( $this->getModel() );
	     		   $pkeyColumns = $table->getPrimaryKeyColumns();
  			 	   foreach( $pkeyColumns as $column ) {

  			 	   			$accessor = 'get' . ucfirst( $column->getModelPropertyName() );
  			 	   			if( !$this->getModel()->$accessor() )
  			 	   				return 'persist';
  			 	   }

  			 	   return 'merge';
	     }

	     /**
	      * Adds foreign model element with primary key child element(s) for each
	      * of the columns which are referenced in the child table.
	      *   
	      * @param $table The 'Table' element to search
	      * @param $doc The SimpleXML DOM document
	      * @param $node The XML DOM to join the foreign model xml to
	      * @return The new XML node with the added foreign key model references. If
	      * 		the table does not contain any foreign keys references the $node
	      * 		is returned untouched.
	      */
	     private function joinForeignKeyXML( $table, $doc, $node ) {

	             if( $table->hasForeignKey() ) {

	      		     $bProcessedKeys = array();
	   		  	     $fKeyColumns = $table->getForeignKeyColumns();
	   		  	     for( $i=0; $i<count( $fKeyColumns ); $i++ ) {

	   	  	  		  	  $fk = $fKeyColumns[$i]->getForeignKey();

	   		  	  		  if( in_array( $fk->getName(), $bProcessedKeys ) )
	   		  	  		      continue;

	     	  	       	  // Get foreign keys which are part of the same relationship
	     	  	       	  $relatedKeys = $table->getForeignKeyColumnsByKey( $fk->getName() );
	         		   	  for( $j=0; $j<count( $relatedKeys ); $j++ ) {

	   		  	  			   $foreignModelName = $doc->createElement( $relatedKeys[$j]->getReferencedTableInstance()->getModel() );
	    	        	       $foreignModel = $node->appendChild( $foreignModelName );

	     	  	       		   array_push( $bProcessedKeys, $relatedKeys[$j]->getName() );

	     	  	       		   // Loop through result set looking for a matching property name to extract the foreign key values
	     	  	       		   foreach( get_object_vars( $stdClass ) as $prop => $val ) {
	
	     	  	       		    	    if( $prop == $relatedKeys[$j]->getColumnInstance()->getName() ) {

	   	  	  	       		   	 		    $child = $doc->createElement( $relatedKeys[$j]->getReferencedColumnInstance()->getModelPropertyName() );
							  			    $child = $foreignModel->appendChild( $child );
					                  	    $fieldvalue = mb_convert_encoding( html_entity_decode( $val ), 'UTF-8', 'ISO-8859-1' );
									  	    $value = $doc->createTextNode( $fieldvalue );
									  	    $value = $child->appendChild( $value );
	     	  	       		   	 	    }

	     	  	       		   	 		if( ($j+1) < count( $relatedKeys ) )
	     	  	       		   	 	  	    $sql .= ', ';
	     	  	       		   }

	   		  	  		   	   array_push( $bProcessedKeys, $fk->getName() );
	         		   	  }
	   		  	     }
	             }

	     		 return $node;
	     }
}
?>