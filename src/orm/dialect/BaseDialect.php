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
 * @package com.makeabyte.agilephp.orm.dialect
 */

/**
 * Base ORM class which assists with common dialect tasks.
 * 
 * @author Jeremy Hahn
 * @copyright Make A Byte, inc
 * @package com.makeabyte.agilephp.orm.dialect
 * @abstract
 */
abstract class BaseDialect {

		 protected $PDOStatement;			 // Internally used PDO::Statement
		 private $maxResults = 25;			 // Used during a call to 'find'
		 private $distinct;					 // Sets SQL DISTINCT clause
		 private $restrictions;				 // WHERE clause restrictions
	     private $restrictionsLogic = 'AND'; // Logic operator to use in WHERE clause (and|or)
	     private $comparisonLogic = '=';	 // Logic operator to use in WHERE clause (=|<|>|LIKE)
	     private $orderBy;					 // Stores the column name to sort the result set by
	     private $orderDirection;			 // The direction to sort the result set (Default is 'ASC')
	     private $offset;					 // Stores the offset for a LIMIT clause.
	     private $groupBy;					 // GROUP BY clause

		 protected $pdo;					 // PHP Data Objects
	     protected $model;					 // Domain model object (ActiveRecord)
		 protected $database;				 // Database object
		 protected $transactionInProgress;	 // True when a transaction is in progress

		 /**
		  * Returns the PDO instance in use by the ORM framework.
		  * 
		  * @return The PDO instance in use by the framework.
		  */
		 public function getPDO() {

		 		return $this->pdo;
		 }

		 /**
		  * Returns the 'Database' object being used by the ORM framework.
		  * 
		  * @return The 'Database' object in use by the ORM framework.
		  */
		 public function getDatabase() {

		 		return $this->database;
		 }

		 /**
		  * Adds an SQL distinct clause to 'find' operation.
		  * 
		  * @param $columnName The column name to get the distinct values for
		  * @return void
		  */
		 public function setDistinct( $columnName ) {

		 		$this->distinct = $columnName;
		 }

		 /**
		  * Returns the 'distinct' column to use in an SQL SELECT statement
		  * if one has been defined.
		  * 
		  * @return The DISTINCT column name or null if a column name has not been defined. 
		  */
		 public function isDistinct() {

		 		return $this->distinct;
		 }

		 /**
		  * Sets the 'maxResults' property value which is used during
		  * a 'find' operation which contains an empty model.
		  *  
		  * @param $maxResults The maximum number of results to return
		  * @return void
		  */
		 public function setMaxResults( $maxResults = 25 ) {

		 		$this->maxResults = $maxResults;
		 }

		 /**
		  * Returns the 'maxResults' property value which is used during
		  * a 'find' operation which contains an empty model. 
		  * 
		  * @return The 'maxResults'
		  */
		 public function getMaxResults() {

		 		return $this->maxResults;
		 }

		 /**
		  * Sets the offset used in a SQL LIMIT clause.
		  * 
		  * @param Integer $offset The limit offset.
		  * @return void
		  */
		 public function setOffset( $offset ) {

		 		$this->offset = $offset;
		 }

		 /**
		  * Returns the SQL LIMIT offset value.
		  * 
		  * @return Integer The LIMIT offset.
		  */
		 public function getOffset() {

		 		return $this->offset;
		 }
		 
		 /**
	      * Sets the SQL 'group by' clause.
	      * 
	      * @param $column The column name to group the result set by
	      * @return void
	      */
	     public function setGroupBy( $column ) {

	     		   $this->groupBy = $column;
	     }

	     /**
	      * Returns SQL GROUP BY clause.
	      * 
	      * @return String GROUP BY value
	      */
	     public function getGroupBy() {

	     		return $this->groupBy;
	     }

	  	 /**
	  	  * Begins a transaction
	  	  * 
	  	  * @return void
	  	  * @throws ORMException
	  	  * @see http://us2.php.net/manual/en/pdo.transactions.php
	  	  * @see http://usphp.com/manual/en/function.PDO-beginTransaction.php
	  	  */
	  	 public function beginTransaction() {

	  		    Log::debug( 'BaseDialect::beginTransaction Beginning transaction' );

	  		    try {
	  		   	 	  $this->pdo->beginTransaction();
	  		   	 	  $this->transactionInProgress = true;
	  		    }
	  		    catch( PDOException $e ) {

	  		   		   throw new ORMException( $e->getMessage(), $e->getCode() );
	  		    }
	  	 }

	  	 /**
	  	  * Commits an already started transaction.
	  	  * 
	  	  * @return void
	  	  * @throws ORMException
	  	  * @see http://us2.php.net/manual/en/pdo.transactions.php
	  	  * @see http://usphp.com/manual/en/function.PDO-commit.php
	  	  */
	  	 public function commit() {

	  		    Log::debug( 'BaseDialect::commit Transaction successfully committed' );

	  		    try {
	  		   		  $this->pdo->commit();
	  		   		  $this->transactionInProgress = false;
	  		    }
	  		    catch( PDOException $e ) {

	  		   		   throw new ORMException( $e->getMessage(), $e->getCode() );
	  		    }
	  	 }

	  	 /**
	  	  * Rolls back a transaction.
	  	  * 
	  	  * @param $message Error/reason why the transaction was rolled back
	  	  * @param $code An error/reason code
	  	  * @return void
	  	  * @throws ORMException
	  	  * @see http://us2.php.net/manual/en/pdo.transactions.php
	  	  * @see http://usphp.com/manual/en/function.PDO-rollBack.php
	  	  */
	  	 public function rollBack( $message = null, $code = 0 ) {

	  		    Log::debug( 'BaseDialect::rollBack' . (($message == null) ? '' : ' ' . $message ));

	  		    try {
	  		    	  $this->pdo->rollBack();
	  		    	  $this->transactionInProgress = false;
	  		    }
	  		    catch( PDOException $e ) {

	  		   		   throw new ORMException( $e->getMessage(), $e->getCode() );
	  		    }

	  		    if( $message ) throw new ORMException( $message, $code );
	  	 }

	  	 /**
		  * Prepares an SQL prepared statement
		  * 
		  * @param $statement The SQL statement to prepare
		  * @return False if the statement could not execute successfully
		  * @see http://usphp.com/manual/en/function.PDO-prepare.php
	  	  */
	  	 public function prepare( $statement ) {

	  		    Log::debug( 'BaseDialect::prepare Preparing' . 
			  	     					(($this->transactionInProgress) ? ' (transactional) ' : ' ') . 
			  	     					'statement ' . $statement );

				try {
						if( !$this->PDOStatement = $this->pdo->prepare( $statement ) ) {

					  	  	$info = $this->pdo->errorInfo();

					  	  	if( $this->transactionInProgress )
			  	 		    	$this->rollBack( $info[2], $info[1] );

						  	throw new ORMException( $info[2], $info[1] );
					    }
				}
				catch( PDOException $e ) {

					   throw new ORMException( $e->getMessage(), $e->getCode() );
				}

	  		    return $this->PDOStatement;
	  	 }

	  	 /**
	  	  * Executes a prepared statement with optional parameters
	  	  * 
	  	  * @param Array $inputParameters Optional array of input parameters
	  	  * @return True if successful, false on fail
	  	  * @see http://usphp.com/manual/en/function.PDOStatement-execute.php
	  	  */
	  	 public function execute( array $inputParameters = array() ) {

	  		    Log::debug( 'BaseDialect::execute Executing' . 
			  	     					(($this->transactionInProgress) ? ' (transactional) ' : ' ') . 
			  	     					'prepared statement with $inputParameters ' . print_r( $inputParameters, true ) );

			  	if( count( $inputParameters ) ) {

		  			for( $i=0; $i<count( $inputParameters ); $i++ ) {

		  				 // Make sure intended null values get stored in SQL as null
		  				 ($inputParameters[$i] == 'NULL') ?
		  				 	 	$this->PDOStatement->bindValue( ($i+1), NULL ) :
		  				 		$this->PDOStatement->bindValue( $i+1, $inputParameters[$i] );
		  			}
			  	}

			  	try {
					  	if( !$this->PDOStatement->execute() ) {
		
						    $info = $this->PDOStatement->errorInfo();
					            
					        if( $this->transactionInProgress )
					  			$this->rollBack( $info[2], $info[1] );

						  	throw new ORMException( $info[2], $info[1] );
					    }
			  	}
			  	catch( PDOException $e ) {

			  		   if( $this->transactionInProgress )
					  	   $this->rollBack();

			  		   throw new ORMException( $e->getMessage(), $e->getCode() );
			  	}

			    return $this->PDOStatement;
	  	 }

	  	 /**
	  	  * Executes an SQL statement and returns the number of rows affected by the query.
	  	  * 
	  	  * @param $statement The SQL statement to execute.
	  	  * @return The number of rows affected by the query.
	  	  * @see http://usphp.com/manual/en/function.PDO-exec.php
	  	  */
	  	 public function exec( $statement ) {

	  		    Log::debug( 'BaseDialect::exec Executing raw' . 
			  	     					(($this->transactionInProgress) ? ' (transactional) ' : ' ') . 
			  	     					'PDO::exec query ' . $sql );

	  		    return $this->pdo->exec( $statement );
	  	 }

		 /**
	   	  * Executes a raw SQL query
	   	  * 
	   	  * @param $sql The SQL statement to execute
	   	  * @return The PDOStatement returned by PDO::query
	   	  * @throws ORMException
	   	  * @see http://usphp.com/manual/en/function.PDO-query.php
	   	  */
	  	 public function query( $sql ) {

	  		    Log::debug( 'BaseDialect::query Executing' . 
			  	     					(($this->transactionInProgress) ? ' (transactional) ' : ' ') . 
			  	     					'raw PDO::query ' . $sql );

	  		    $stmt = $this->pdo->query( $sql );

	  	        if( $this->pdo->errorCode() > 0 ) {

                    $info = $this->pdo->errorInfo();

                    if( $this->transactionInProgress )
			  			$this->rollBack( $info[2], $info[1] );

	  	     	    throw new ORMException( $info[2], $this->pdo->errorCode() );
	  	        }

	  	        return $stmt;
	  	}
	  	
	  	/**
	  	 * Quotes a string so its theoretically safe to pass into a statement
	  	 * 
	  	 * @param $data The data to quote
	  	 * @return The quoted data
	  	 * @see http://www.php.net/manual/en/pdo.quote.php
	  	 */
	  	public function quote( $data ) {

	  		   return $this->pdo->quote( $data );
	  	}

	  	/**
	   	 * Persists a domain model object
	     * 
	     * @param $model The domain model object to persist
	     * @return PDOStatement
	     * @throws ORMException
	     */
	    public function persist( $model ) {

	    	   $this->model = $model;

	   		   $values = array();
			   $table = $this->getTableByModel( $model );

			   Log::debug( 'BaseDialect::persist Performing persist on model \'' . $table->getModel() . '\'.' );

	   		   $this->validate( $table, true );

			   $sql = 'INSERT INTO ' . $table->getName() . '( ';

			   $columns = $table->getColumns();
			   for( $i=0; $i<count( $columns ); $i++ ) {

			   		if( $columns[$i]->isAutoIncrement() ) continue;
			   		if( $columns[$i]->isLazy() ) continue;

			   		$sql .= $columns[$i]->getName();

			   		if( ($i + 1) < count( $columns ) )
			   			$sql .= ', ';
			   }
			   $sql .= ' ) VALUES ( ';
			   for( $i=0; $i<count( $columns ); $i++ ) {

			   		if( $columns[$i]->isAutoIncrement() ) continue;
			   		if( $columns[$i]->isLazy() ) continue;

			   		$sql .= '?';

			   	    $accessor = $this->toAccessor( $columns[$i]->getModelPropertyName() );
			   	    if( $columns[$i]->isForeignKey() ) {

			   	    	if( is_object( $model->$accessor() ) ) {

			   	    		$refAccessor = $this->toAccessor( $columns[$i]->getForeignKey()->getReferencedColumnInstance()->getModelPropertyName() );
			   	    		// Get foreign key value from the referenced field/instance accessor
			   	    		if( $model->$accessor()->$refAccessor() != null ) {

			   	    			try {
			   	    				  // Try to persist the referenced entity first
					   	    		  $this->persist( $model->$accessor() );
					   	    		  array_push( $values, $model->$accessor()->$refAccessor() );
			   	    			}
			   	    			catch( Exception $e ) {

			   	    				   // The referenced entity doesnt exist yet, persist it
			   	    				   if( preg_match( '/duplicate/i', $e->getMessage() ) ) {

			   	    				   	   $this->merge( $model->$accessor() );
			   	    				   	   array_push( $values, $model->$accessor()->$refAccessor() );
			   	    				   }
			   	    			}
			   	    		}
			   	    	}
			   	    	else {

			   	    		array_push( $values, null );
			   	    	}
			   	    }
			   	    else // No foreign key
			   	    	array_push( $values, (($model->$accessor() == '') ? NULL : $model->$accessor()) );

			   		if( ($i + 1) < count( $columns ) )
				   		$sql .= ', ';
			   }
			   $sql .= ' );';

	   		   $this->prepare( $sql );
	  		   return $this->execute( $values );
	    }

	    /**
	     * Merges/updates a persisted domain model object
	     * 
	     * @param $model The model object to merge/update
	     * @return PDOStatement
	     * @throws ORMException
	     */
	    public function merge( $model ) {

	    	   $this->model = $model;
	    	   $table = $this->getTableByModel( $model );

	    	   Log::debug( 'BaseDialect::merge Performing merge on model \'' . $table->getModel() . '\'.' );

	    	   $this->model = $model;
	  	       $values = array();
	  	       $cols = array();
			   $this->validate( $table );

			   $sql = 'UPDATE ' . $table->getName() . ' SET ';

	  		   $columns = $table->getColumns();
	  		   $naCount = 0;
			   for( $i=0; $i<count( $columns ); $i++ ) {

			   	    if( $columns[$i]->isPrimaryKey() || $columns[$i]->isAutoIncrement() ) continue;
			   		if( $columns[$i]->isLazy() ) continue;

			   		$accessor = $this->toAccessor( $columns[$i]->getModelPropertyName() );
			   		// Extract foreign key value from the referenced column
			   	    if( $columns[$i]->isForeignKey() ) {

			   	    	if( is_object( $model->$accessor() ) ) {

			   	    		// Create name for the foreign model's instance accessor
			   	    		$refAccessor = $this->toAccessor( $columns[$i]->getForeignKey()->getReferencedColumnInstance()->getModelPropertyName() );

			   	    		// Get foreign key value from the referenced instance
			   	    		if( $model->$accessor()->$refAccessor() != null ) {

		   	    			    $this->merge( $model->$accessor() );
				   	    		array_push( $values, $model->$accessor()->$refAccessor() );
			   	    		}
			   	    		else {
			   	    			// Persist the referenced model instance, and use its new id as the foreign key value
				   	    		$this->persist( $model->$accessor() );
				   	    		array_push( $values, $this->pdo->lastInsertId() );
			   	    		}
			   	    	}
			   	    	else {

			   	        	array_push( $values, null );
			   	        }
			   	    }
			   	    else // not a foreign key
			   	    	array_push( $values, $model->$accessor() );

			   	    array_push( $cols, $columns[$i]->getName() );
			   }

			   $sql .= implode( $cols, '=?, ' ) . '=? WHERE ';

			   $pkeyColumns = $table->getPrimaryKeyColumns();
			   for( $i=0; $i<count( $pkeyColumns ); $i++ ) {

			  	    $accessor = $this->toAccessor( $columns[$i]->getModelPropertyName() );
			  		$sql .= $columns[$i]->getName() . '=\'' . $model->$accessor() . '\'';

			  		if( ($i+1) < count( $pkeyColumns ) )
			  		    $sql .= ' AND ';
			   }

			   $sql .= ';';

		       $this->prepare( $sql );
	  	       return $this->execute( $values );
	    }

	    /**
	     * Deletes a persisted domain model object (ActiveRecord)
	     * 
	     * @param $model The domain model object to delete
	     * @return PDOStatement
	     * @throws ORMException
	     */
	    public function delete( $model ) {

	      	   $table = $this->getTableByModel( $model );

	      	   Log::debug( 'BaseDialect::delete Performing delete on model \'' . $table->getModel() . '\'.' );

	    	   $values = array();
		       $columns = $table->getPrimaryKeyColumns();
		       $sql = 'DELETE FROM ' . $table->getName() . ' WHERE ';

		       for( $i=0; $i<count( $columns ); $i++ ) {

		   		    if( $columns[$i]->isPrimaryKey() ) {

		   		        $accessor = $this->toAccessor( $columns[$i]->getModelPropertyName() );
		   			    $sql .= '' . $columns[$i]->getName() . '=?';
		   			    $sql .= ($i+1) < count( $columns )? ' AND ' : ';';

		   			    array_push( $values, $model->$accessor() );
		   		    }
		       }

		       $this->prepare( $sql );
		       return $this->execute( $values );
	    }

 	    /**
	     * Truncates the table for the specified domain model object
	     * 
	     * @param $model A domain model object
	     * @return PDOStatement
	     * @throws ORMException
	     */
	    public function truncate( $model ) {

			   $table = $this->getTableByModel();
			   $sql = 'TRUNCATE TABLE ' . $table->getName() . ';';
			   $this->prepare( $sql );
			   return $this->execute();
	    }

	    /**
	     * Attempts to locate the specified model by values. Any fields set in the object are used
	     * in search criteria. Alternatively, setRestrictions and setOrderBy methods can be used to
	     * filter results.
	     * 
	     * @param $model A domain model object. Any fields which are set in the object are used to filter results.
	     * @throws ORMException If any primary keys contain null values or any
	     * 		   errors are encountered executing queries
	     */
	    public function find( $model ) {

	    	   $table = $this->getTableByModel( $model );
			   $newModel = $table->getModelInstance();
			   $values = array();

			   Log::debug( 'BaseDialect::find Performing find on model \'' . $table->getModel() . '\'.' );

	  		   try {
	  		   		 if( $this->isEmpty( $model ) ) {

	    	   	         $sql = 'SELECT ' . (($this->isDistinct() == null) ? '*' : 'DISTINCT ' . $this->isDistinct()) . ' FROM ' . $table->getName();

	    	   	         $order = $this->getOrderBy();
	    	   	         $offset = $this->getOffset();
	    	   	         $groupBy = $this->getGroupBy();

    	   	         	 $sql .= ($this->restrictions != null) ? $this->createRestrictSQL() : '';
					 	 $sql .= ($order != null) ? ' ORDER BY ' . $order['column'] . ' ' . $order['direction'] : '';
					 	 $sql .= ($groupBy)? ' GROUP BY ' . $this->getGroupBy() : '';
					 	 $sql .= ($offset && $this->getMaxResults()) ? ' LIMIT ' . $offset . ', ' . $this->getMaxResults() : '';
					 	 $sql .= (!$offset && $this->getMaxResults()) ? ' LIMIT ' . $this->getMaxResults() : '';
    	   	         	 $sql .= ';';

	   	   	         	 $this->setDistinct( null );
    	   	         	 $this->setRestrictions( array() );
    	   	         	 $this->setRestrictionsLogicOperator( 'AND' );
    	   	         	 $this->setOrderBy( null, 'ASC' );
    	   	         	 $this->setGroupBy( null );
	    	   		 }
	    	   		 else {
	    	   		 		$where = '';
	    	   		 		$order = $this->getOrderBy();
	    	   	         	$offset = $this->getOffset();
	    	   	         	$groupBy = $this->getGroupBy();

	    	   		 		$columns = $table->getColumns();
							for( $i=0; $i<count( $columns ); $i++ ) {

							 	 if( $columns[$i]->isLazy() ) continue;

							 	 $accessor = $this->toAccessor( $columns[$i]->getModelPropertyName() );
						     	 if( $model->$accessor() == null ) continue;

						     	 $where .= (count($values) ? ' ' . $this->restrictionsLogic . ' ' : ' ') . $columns[$i]->getName() . ' ' . $this->comparisonLogic . ' ?';

						     	 if( is_object( $model->$accessor() ) ) {
						     	 	 $refAccessor = $this->toAccessor( $columns[$i]->getForeignKey()->getReferencedColumnInstance()->getModelPropertyName() );
						     	 	 array_push( $values, $model->$accessor()->$refAccessor() );
						     	 }
						     	 else {
				     	 	     	 array_push( $values, $model->$accessor() );
						     	 }
						    }
						    $sql = 'SELECT * FROM ' . $table->getName() . ' WHERE' . $where;

					 	    $sql .= ($order != null) ? ' ORDER BY ' . $order['column'] . ' ' . $order['direction'] : '';
					 	 	$sql .= ($groupBy)? ' GROUP BY ' . $this->getGroupBy() : '';
					 	 	$sql .= ($offset && $this->getMaxResults()) ? ' LIMIT ' . $offset . ', ' . $this->getMaxResults() : '';
					 	 	$sql .= (!$offset && $this->getMaxResults()) ? ' LIMIT ' . $this->getMaxResults() : '';
    	   	         	 	$sql .= ';';
	    	   		 }

					 $this->prepare( $sql );
					 $this->PDOStatement->setFetchMode( PDO::FETCH_OBJ );
					 $result = $this->execute( $values );

					 if( !count( $result ) ) {

					 	 Log::debug( 'BaseDialect::find Empty result set for model \'' . $table->getModel() . '\'.' );
					 	 return null;
					 }

				 	 $index = 0;
				 	 $models = array();
					 foreach( $result as $stdClass  ) {

					 		  $m = $table->getModelInstance();
					 	   	  foreach( get_object_vars( $stdClass ) as $name => $value ) {

					 	   	  		   if( !$value ) continue;
					 	   	  		   $modelProperty = $this->getPropertyNameForColumn( $table, $name );

							 	   	   // Create foreign model instances from foreign values
						 	 		   foreach( $table->getColumns() as $column ) {

						 	 		   		    if( $column->getName() != $name ) continue;
						 	 		   		    if( $column->isLazy() ) continue;

						 	 		  		    if( $column->isForeignKey() ) {

						 	 		  		   	    $foreignModel = $column->getForeignKey()->getReferencedTableInstance()->getModel();
						 	 		  		   	    $foreignInstance = new $foreignModel();

						 	 		  		   	    $foreignMutator = $this->toMutator( $column->getForeignKey()->getReferencedColumnInstance()->getModelPropertyName() );
						 	 		  		   	    $foreignInstance->$foreignMutator( $value );

						 	 		  		   	    $persisted = $this->find( $foreignInstance );

						 	 		  		   	    // php namespace support - remove \ character from fully qualified paths
							 	 		  		   	$foreignModelPieces = explode( '\\', $foreignModel );
							 	 		  		   	$foreignClassName = array_pop( $foreignModelPieces );

						 	 		  		   	    $instanceMutator = $this->toMutator( $foreignClassName );
						 	 		  		   	    $m->$instanceMutator( $persisted[0] );
						 	 		  		    }
						 	 		  		    else {

						 	 		  		   		$mutator = $this->toMutator( $modelProperty );
					 	   	   		  				$m->$mutator( $value );
						 	 		  		    }
						 	 		   }
					 	   	  }

					 	   	  array_push( $models, $m );
					 	   	  $index++;
					 	   	  if( $index == $this->maxResults )  break;
				     }

				     return $models;
	  		 }
	  		 catch( Exception $e ) {

	  		 		throw new ORMException( $e->getMessage(), $e->getCode() );
	  		 }
	  }

	  /**
	   * Returns the total number of records in the specified model.
	   * 
	   * @param Object $model The domain object to get the count for.
	   * @return Integer The total number of records in the table.
	   */
	  public function count( $model ) {

	  		 $sql = 'SELECT count(*) as count FROM ' . $this->getTableByModel( $model )->getName();
			 $sql .= ($this->createRestrictSQL() == null) ? '' : $this->createRestrictSQL();
			 $sql .= ';';

	     	 $stmt = $this->query( $sql );
  			 $stmt->setFetchMode( PDO::FETCH_OBJ );
  			 $result = $stmt->fetchAll();

  			 return ($result == null) ? 0 : $result[0]->count;
	  }

	  /**
	   * Closes the connection to the database.
	   * 
	   * @return void
	   */
	  public function close() {

	  	     $this->pdo = null;
	  }

	  /**
	   * Sets the SQL 'order by' clause.
	   * 
	   * @param $column The column name to order the result set by
	   * $param $direction The direction to sort the result set (ASC|DESC).
	   * @return void
	   */
	  public function setOrderBy( $column, $direction ) {

	         $this->orderBy = $column;
	     	 $this->orderDirection = $direction;
	  }

	  /**
	   * Returns an associative array containing the current 'orderBy' clause. The results
	   * are returned with the name of the column as the index and the direction as the value.
	   * 
	   * @return An associative array containing the name of the column to sort as the key/index
	   * 		and the direction of the sort order (ASC|DESC) as the value. 
	   */
	  public function getOrderBy() {

	  		 if( !$this->orderBy )
	  		 	 return null;

	  	     return array( 'column' => $this->orderBy, 'direction' => $this->orderDirection );
	  }

	  /**
	   * Sets WHERE clause restrictions
	   * 
	   * @param $restrictions An associative array containing WHERE clause restrictions. (For example: array( 'id' => 21 ) )
	   * @return void
	   */
	  public function setRestrictions( array $restrictions ) {

	   		 $this->restrictions = $restrictions;
	  }
	  
	  /**
	   * Returns the WHERE clause restrictions
	   * 
	   * @return Array SQL WHERE clause restrictions
	   */
	  public function getRestrictions() {

	  		 return $this->restrictions;
	  }

	  /**
	   * Sets the restriction operator (and|or) used in SQL WHERE clause.
	   * 
	   * @param $operator The logical operator 'and'/'or' to be used in SQL WHERE clause. Default is 'AND'.
	   * @return void
	   */
	  public function setRestrictionsLogicOperator( $operator ) {

	   	     if( strtolower( $operator ) !== 'and' && strtolower( $operator ) !== 'or' )
	     	     throw new ORMException( 'Restrictions logic operator must be either \'and\' or \'or\'. Found \'' . $operator . '\'.' );

	     	 $this->restrictionsLogic = $operator;
	  }

	  /**
	   * Returns restriction logic operator used to filter SELECT / find operations.
	   * 
	   * @return string Retrictions logic operator (AND|OR)
	   */
	  public function getRestrictionsLogicOperator() {

	  			return $this->restrictionsLogic;
	  }

	  /**
	   * Returns the comparison logic operator.
	   * 
	   * @return string Comparison logic operator (LIKE|<|>|?|=)
	   */
	  public function getComparisonLogicOperator() {

				return $this->comparisonLogic;
	  }

	  /**
	   * Sets the comparison operator (<|>|=|LIKE) used in SQL WHERE clause.
	   * 
	   * @param $operator The logical comparison operator used is SQL where clauses. Default is '='.
	   * @return void
	   */
	  public function setComparisonLogicOperator( $operator ) {

	  		 if( strtolower( $operator ) != 'like' && $operator !== '<' && $operator !== '>' && $operator !== '=' )
	     	     throw new ORMException( 'Comparison logic operator must be \'>\', \'<\', \'=\', or \'LIKE\'. Found \'' . $operator . '\'.' );

	     	 $this->comparisonLogic = $operator;
	  }

 	  /**
	   * Returns an SQL formatted string containing a WHERE clause built from setRestrictions and setRestrictionsLogicOperator.
	   * 
	   * @return The formatted SQL string
	   */
	  public function createRestrictSQL() {

	     	 $restricts = null;
			 if( count( $this->restrictions ) ) {

			  	 $restricts = ' WHERE ';
				 $index = 0;
				 foreach( $this->restrictions as $key => $val ) {

				   		  $index++;
				   		  $restricts .= $key . ' ' . $this->comparisonLogic . ' \'' . addslashes( $val ) . '\'';

				   		  if( $index < count( $this->restrictions ) )
				   			  $restricts .= ' ' . $this->restrictionsLogic . ' ';
				 }
			 }

			 return $restricts;
	  }

	  /**
	   * Returns the 'Table' object which is mapped to the specified 'Model'.
	   * 
	   * @param $model The domain model object to retrieve the table element for. Defaults to the model
	   * 			   currently being managed by the 'ORM'.
	   * @return The 'Table' object responsible for the model's ORM or null if a table
	   * 		 could not be located for the specified $model.
	   */
	  public function getProcedureByModel( $model ) {

	  		 $class = get_class( $model );

			 foreach( $this->database->getProcedures() as $proc ) {

			 	  	  if( $proc->getModel() == $class )
			 	  	      return $proc;
			 }

			 throw new ORMException( 'BaseDialect::getProcedureByModel Could not locate the requested model \'' . $class . '\' in orm.xml' );
	  }
	  
	  /**
	   * Returns the Procedure responsible for the specified model
	   * 
	   * @param string $modelName The name of the model class
	   * @return Procedure The procedure which maps to the specified model name
	   */
	  public function getProcedureByModelName( $modelName ) {

			 foreach( $this->database->getProcedures() as $proc )
			  	  	  if( $proc->getModel() == $modelName )
			 	  	      return $proc;

			 throw new ORMException( 'BaseDialect::getProcedureByModelName Could not locate the requested model \'' . $modelName . '\' in orm.xml' );
	  }

	  /**
	   * Returns the 'Table' object which is mapped to the specified 'Model'.
	   * 
	   * @param $model The domain model object to retrieve the table element for. Defaults to the model
	   * 			   currently being managed by the 'ORM'.
	   * @return The 'Table' object responsible for the model's ORM or null if a table
	   * 		 could not be located for the specified $model.
	   */
	  public function getTableByModel( $model = null ) {

	  		 try {
	  	     	   $class = new ReflectionClass( (($model == null) ? $this->model : $model) );
	  		 }
	  		 catch( ReflectionException $re ) {

	  		 		throw new ORMException( 'BaseDialect::getTableByModel Could not get table because \'' . $re->getMessage() . '\'.' );
	  		 }

			 foreach( $this->database->getTables() as $table ) {

			 	  	  if( $table->getModel() == $class->getName() )
			 	  	      return $table;
			 }

			 throw new ORMException( 'BaseDialect::getTableByModel Could not locate the requested model \'' . $class->getName() . '\' in orm.xml' );
	  }

	  /**
	   * Returns the 'Table' object responsible for the specified $modelName.
	   * 
	   * @param $modelName The name of the model
	   * @return The 'Table' object responsible for the specified model or
	   * 		 null if the table could not be found.
	   */
	  public function getTableByModelName( $modelName ) {

			 foreach( $this->database->getTables() as $table )
			  	  	  if( $table->getModel() == $modelName )
			 	  	      return $table;

			 throw new ORMException( 'BaseDialect::getTableByModelName Could not locate the requested model \'' . $modelName . '\' in orm.xml' );
	  }

	  /**
	   * Returns a 'Table' object by its name as configured in orm.xml
	   * 
	   * @param $tableName The value of the table's 'name' attribute
	   * @return The 'Table' object or null if the table was not found
	   */
	  public function getTableByName( $tableName ) {

	  		 foreach( $this->database->getTables() as $table )
	  		 		  if( $table->getName() == $tableName )
	  		 		  	  return $table;

	  		 throw new ORMException( 'BaseDialect::getTableByName Could not locate the requested table \'' . $tableName . '\' in orm.xml' );
	  }

	  /**
	   * Returns a 'Table' object representing the table configured in orm.xml as
	   * the AgilePHP 'Identity' table.
	   * 
	   * @return The 'Table' object which represents the AgilePHP 'Identity' table, or null
	   * 		 if an 'Identity' table has not been configured.
	   */
	  public function getIdentityTable() {

			 foreach( $this->database->getTables() as $table ) {
 
		 	  	      if( $table->isIdentity() )
		 	  	      	  return $table;
			 }

			 return null;
	  }

	  /**
	   * Returns an instance of the domain model object responsible for AgilePHP 
	   * 'Identity' ORM.
	   * 
	   * @return An instance of the domain model object responsible for 'Identity'
	   * 		 ORM.
	   */
	  public function getIdentityModel() {

	  		 foreach( $this->database->getTables() as $table ) {
 
		 	  	      if( $table->isIdentity() ) {

						  $modelName = $table->getModel();
		 	  	      	  $reflector = new ReflectionClass( $modelName );

	 	  	      	  	  $getUsernameExists = false;
		 	  	      	  $getPasswordExists = false;
		 	  	      	  $getEmailExists = false;
		 	  	      	  foreach( $reflector->getMethods() as $method ) {

		 	  	      	   		   if( $method->name == 'getUsername' )
		 	  	      	  	   		   $getUsernameExists = true;

		 	  	      	  	   	   if( $method->name == 'getPassword' )
	 	  	      	  	   			   $getPasswordExists = true;

		 	  	      	  	   	   if( $method->name == 'getEmail' )
		 	  	      	  	   		   $getEmailExists = true;

		 	  	      	  	   	   if( $getUsernameExists && $getPasswordExists && $getEmailExists )
		 	  	      	  	   		   break;
	 	  	      	  	   }
	 	  	      	  	   
	 	  	      	  	   if( !$getUsernameExists || !$getPasswordExists || !$getEmailExists )
							   throw new ORMException( 'BaseDialect::getIdentityModel Identity model must support methods \'getUsername\', \'getPassword\', and \'getEmail\' as enforced by the interface at ' . AgilePHP::getFrameworkRoot() . '/core/Identity.php.' );

	 	  	      	  	   return new $modelName();
		 	  	      }
			 }

			 return null;
	  }

	 /**
	   * Returns the 'Table' object that represents the table configured in orm.xml as
	   * an AgilePHP 'SessionScope' session table.
	   * 
	   * @return The 'Table' object instance containing the 'SessionScope' session table
	   * 		 or null if a session table has not been configured.
	   */
	  public function getSessionTable() {

			 foreach( $this->database->getTables() as $table ) {
 
		 	  	      if( $table->isSession() )
		 	  	      	  return $table;
			 }

			 return null;
	  }

	  /**
	   * Returns an instance of the domain model object responsible for AgilePHP
	   * 'SessionScope' session ORM.
	   * 
	   * @return An instance of the model responsible for AgilePHP 'SessionScope'
	   * 	     sessions.
	   */
	  public function getSessionModel() {

	  		 foreach( $this->database->getTables() as $table ) {

		 	  	      if( $table->isSession() ) {

		 	  	      	  $modelName = $table->getModel();
 	  	      	  		  return new $modelName();
		 	  	      }
			 }

			 return null;
	  }

	  /**
	   * Returns a custom display name as configured in orm.xml 'display' attribute
	   * for the specified column name. If the 'Column' name can not be matched, it is then
	   * compared against the 'Column' 'property' attribute value. If neither can be matched,
	   * the $columnName is returned.
	   * 
	   * @param $table The 'Table' object which contains the column to retrieve the display
	   * 			   name from.
	   * @param $columnName The name of the column to get the display name for
	   * @return Custom display name if configured, otherwise the $columnName is returned
	   */
	  public function getDisplayNameForColumn( $table, $columnName ) {

	  		 foreach( $table->getColumns() as $column ) {

	  		 	      if( $column->getName() == $columnName ) {

	  		 	      	  if( $column->getDisplay() )
	  		 	          	  return ucfirst( $column->getDisplay() );
	  		 	      }

	  		 		  if( $column->getProperty() == $columnName ) {

	  		 	      	  if( $column->getDisplay() )
	  		 	          	  return ucfirst( $column->getDisplay() );
					  }
	  		 }

	  		 return ucfirst( $columnName );
	  }

	  /**
	   * Returns the value of the 'property' attribute configured in orm.xml for the specified $columnName.
	   * If the property attribute does not exist, the column name is returned instead.
	   * 
	   * @param $table The 'Table' object containing the 'Column' to search.
	   * @param $columnName The name of the column to retrieve the property attribute value from
	   * @return The property name. If the property does not exist, the $columnName is returned instead
	   */
	  public function getPropertyNameForColumn( $table, $columnName, $caseSensitive = true ) {

	  		 foreach( $table->getColumns() as $column ) {

	  		 		  $col = ($caseSensitive) ? $column->getName() : strtolower( $column->getName() );
	  		 	      if( $col == $columnName )
	  		 	      	  if( $column->getProperty() )
	  		 	      	  	  return $column->getProperty();
	  		 }

	  		 return $columnName;
	  }

	  /**
	   * Returns the value of the 'name' attribute configured in orm.xml for the specified $propertyName.
	   * If the property attribute does not exist, a match is attempted against the column name. If the column
	   * name matches the expected $propertyName, the column name is returned. If neither can be matched, null is
	   * returned instead.
	   * 
	   * @param $table The 'Table' object containing the 'Column' to search.
	   * @param $propertyName The name of the property to retrieve the name attribute value from
	   * @return The column name. If the property does not exist and $propertyName matches a column name, the column
	   * 		 name is returned instead. If neither can be matched, null is returned.
	   */
	  public function getColumnNameForProperty( $table, $propertyName ) {

	  		 foreach( $table->getColumns() as $column ) {

	  		 	      if( $column->getProperty() == $propertyName )
	  		 	      	  return $column->getName();
	  		 }
	  		 
	  		 foreach( $table->getColumns() as $column ) {

	  		 	      if( $column->getName() == $propertyName )
	  		 	      	  return $column->getName();
	  		 }

	  		 return null;
	  }

	  /**
	   * Converts the specified parameter to a bigint.
	   * 
	   * @param $int The bigint value
	   * @return bigint
	   */
	  public function toBigInt( $number ) {

	  			$precision = ini_get( 'precision' );
				@ini_set( 'precision', 16 );
				$bigint = sprintf( '%.0f', $number );
				@ini_set( 'precision', $precision );

				return $bigint;
	  }

	  /**
	   * Checks the model in use by the ORM framework for the presence
	   * of property values. If the model does not contain any values, it is
	   * considered empty.
	   * 
	   * @return True if the model is empty, false if the model contains any property values.
	   */
	  public function isEmpty( $model ) {

	  		 $class = new ReflectionClass( $model );

	  		 // Need to grab the real model if this is an interceptor proxy.
	  		 try {
	  		 		$m = $class->getMethod( 'getInterceptedInstance' );

	  		 		$class = new ReflectionClass( $model->getInterceptedInstance() );
	  		 		$methods = $class->getMethods();
			  		foreach( $methods as $method ) {

			  		 		 $mName = $method->name;
			  		 		 if( $mName == 'getInstance' ) continue;
			  		 		 if( $mName == 'getInterceptedInstance' ) continue;
			  		 		 if( substr( $mName, 0, 3 ) == 'get' )
			  		 		 	 if( !is_object( $model->$mName() ) && $model->$mName() )  // Ignore models with a child object - problem?
			  		 		  	 	 return false;
			  		}

			  		return true;
	  		 }
	  		 catch( Exception $e ) {}

	  		 // This is a real model.
	  		 $methods = $class->getMethods();
	  		 foreach( $methods as $method ) {

	  		 		  $mName = $method->name;
	  		 		  if( substr( $mName, 0, 3 ) == 'get' )
	  		 		  	  if( $model->$mName() )
	  		 		  	  	  return false;
	  		 }

	  		 return true;
	  }

	  /**
	   * Compares domain model object $a with $b.
	   * 
	   * NOTE: This function assumes the model adheres to the property/getter/setter
	   * 	   model convention.
	   * 
	   * @param $a The first object
	   * @param $b The second object
	   * @return True if the objects test positive, false if the models do not match
	   */
	  public function compare( $a, $b ) {

	  		  try {
		  		    $classA = new ReflectionClass( $a );
		  		    $classB = new ReflectionClass( $b );
	
		  		    if( $classA->getName() !== $classB->getName() )
		  		  	    throw new Exception( 'model class names dont match' );
	
		  		    $propsA = $classA->getProperties();
		  		    $propsB = $classB->getProperties();
	
		  		    if( !count( $propsA ) || !count( $propsB ) )
		  		  	    throw new Exception( 'model property count doesnt match' );

		  		    for( $i=0; $i<count( $propsA ); $i++ ) {
	
		  		  	     if( $propsA[$i]->name !== $propsB[$i]->name )
		  		  	         throw new Exception( 'model property names dont match' );
	
		  		  	   	 $accessor = 'get' . ucfirst( $propsA[$i]->name );
		  		  	     if( $a->$accessor() !== $b->$accessor() )
		  		  	   	     throw new Exception( 'model property values dont match' );
		  		  	}
	  		  }
	  		  catch( Exception $e ) {

	  		  		 Log::debug( 'BaseDialect::compare ' . $e->getMessage() );
	  		  		 return false;
	  		  }

	  		  return true;
	  }
	  
	  /**
	   * Creates an accessor method from the $property parameter. The $property
	   * will be returned with the prefix 'get' and the first letter of the property
	   * uppercased.
	   * 
	   * @param $property The name of the property to convert to an accessor method name
	   * @return The accessor string
	   */
	  public function toAccessor( $property ) {

	   		 return 'get' . ucfirst( $property );
	  }

	  /**
	   * Creates a mutator method from the $property parameter. The $property
	   * will be returned with the prefix 'set' and the first letter of the property
	   * uppercased.
	   * 
	   * @param $property The name of the property to convert to a mutator method name
	   * @return The mutator string
	   */
	  public function toMutator( $property ) {

	  	     return 'set' . ucfirst( $property );
	  }

	  /**
	   * Copies the values from object $a to $b.
	   * 
	   * @param $a The first object
	   * @param $b The second object
	   * @return The same instance of object $b with its properties set as defined in object $a
	   */
	  private function copy( $a, $b ) {

	  		  $classA = new ReflectionClass( $a );
		  	  $classB = new ReflectionClass( $b );
	
		  	  if( $classA->getName() !== $classB->getName() )
		  	      throw new Exception( 'model class names dont match' );
	
		  	  $propsA = $classA->getProperties();
		  	  $propsB = $classB->getProperties();
	
		  	  if( !count( $propsA ) || !count( $propsB ) )
		  	      throw new Exception( 'model property count doesnt match' );

		      for( $i=0; $i<count( $propsA ); $i++ ) {
	
		  		   if( $propsA[$i]->name !== $propsB[$i]->name )
		  		       throw new Exception( 'model property names dont match' );

		  		   $accessor = 'get' . ucfirst( $propsA[$i]->name );
		  		   $mutator = 'set' . ucfirst( $propsB[$i]->name );
		  		   $b->$mutator( $a->$accessor() );
		  	  }

		  	  return $b;
	  }

		/**
	     * Performs automation logic for setting all of the foreign keys in the
	     * current model with the values of its related foreign model ActiveRecord.
	     * 
	     * @return void
	     */
	    private function setForeignKeyValues( $model, $foreignModel ) {

			   $table = $this->getTableByModel( $model );
			   $foreignTable = $this->getTableByModel( $foreignModel );

       	   	   foreach( $table->getForeignKeyColumns() as $column ) {

       	   	   			// Only process foreign keys for the $foreignModel table
       	   	   			if( $column->getForeignKey()->getReferencedTable() == $foreignTable->getName() ) {

       	   	   				// Create accessor and mutator methods for $this->model 
					    	$accessor = 'get' . ucfirst( $column->getModelPropertyName() );
					    	$mutator = 'set' . ucfirst( $column->getModelPropertyName() );

	   	   					// Create accessor method for the foreign model and set the foreign key property value for $this->model
    		   	   			$fModelAccessor = 'get' . ucfirst( $column->getForeignKey()->getReferencedColumnInstance()->getModelPropertyName() );
	    	   	   			$foreignModelValue = $foreignModel->$fModelAccessor();
	   	   	   				$model->$mutator( $foreignModelValue );
       	   	   			}
	    	   }

	    	   return $model;
	    }

	  /**
	   * Validates the domain model object's property values against orm.xml table/column configuration
	   * 
	   * @param $table The Table object representing the table in orm.xml configuration to validate.
	   * @param $isInsert True if validating a persist operation
	   * @return void
	   */
	  protected function validate( Table $table, $isPersist = false ) {

	  	        if( $table->getValidate() == false ) return;

			    foreach( $table->getColumns() as $column ) {

			  	       $accessor = 'get' . ucfirst( $column->getModelPropertyName() );

			  	       if( $isPersist == true && $column->isPrimaryKey() || $column->isAutoIncrement() )
			  	       	   continue;

			  	       // Verify length
			  	       if( $length = $column->getLength() && !is_object( $this->model->$accessor() ) ) {

			  	       	   $dataLen = strlen( $this->model->$accessor() );
			  	       	   if( $dataLen > $length ) {

			  	       	   	   $message = 'BaseDialect::validate ORM validation failed on \'' . $table->getModel() . '::' . $column->getModelPropertyName() . '\'. Length defined in orm.xml as \'' . $column->getLength() . '\' but the has a length of \'' . $dataLen . '\'.';
			  	       	   	   Log::debug( $message );
			  	       	   	   throw new ORMException( $message );
			  	       	   }
			  	       }

			  	       // Verify required fields contain data
			  		   if( $column->isRequired() && $this->model->$accessor() === null ) {

			  		   	   $message = 'BaseDialect::validate ORM validation failed on \'' . $table->getModel() . '::' . $column->getModelPropertyName() . '\'. Required field contains null value.';
			  		   	   Log::debug( $message );
			  		       throw new ORMException( $message );
			  		   }

			  		   // Use specified validator to verify data integrity
			  	       if( $validator = $column->getValidator() ) {

			  	       	   // Allow null values for columns that are not required
			  	       	   if( !$column->isRequired() && $this->model->$accessor() == null ) continue;

			  	       	   $o = new $validator( $this->model->$accessor() );
			  	       	   if( !$o->validate() ) {

			  	       	   	   $message = 'BaseDialect::validate ORM validation failed on \'' . $table->getModel() . '::' . $column->getModelPropertyName() . '\'. Expected data \'' . $this->model->$accessor() . '\' to be type \'' . $column->getType() . '\' but found \'' . gettype( $this->model->$accessor() ) . '\' using validator \'' . $validator . '\'.';
			  	       	   	   Log::debug( $message );
			  	       	   	   throw new ORMException( $message );
			  	       	   }			  	       	   	   
			  	       }
			  }
	  }

	  /**
	   * Closes the connection to the database.
	   * 
	   * @return void
	   */
	  public function __destruct() {

	  		 $this->close();
	  		 Log::debug('BaseDialect::__destruct');
	  }
}
?>