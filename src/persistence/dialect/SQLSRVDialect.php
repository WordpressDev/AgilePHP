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
 * @package com.makeabyte.agilephp.persistence.dialect
 */

/**
 * Responsible for MSSQL specific database operations.
 * NOTE: This class uses the "Microsft SQL Server Driver for PHP".
 * 
 * @author Jeremy Hahn
 * @copyright Make A Byte, inc
 * @package com.makeabyte.agilephp.persistence.dialect
 * @version 0.3a
 */
class SQLSRVDialect extends BasePersistence implements SQLDialect {

	  private $conn;
	  private $stmt;
	  private $statement;

	  /**
	   *  Initalize SQLSRVDialect.
	   *  
	   * @param Database $db The Database object representing persistence.xml
	   * @return void
	   */
	   public function __construct( Database $db ) {

	   		  $params = array( 'Database' => $db->getName(), 'UID' => $db->getUsername(), 'PWD' => $db->getPassword() );
	   		  $noDbParams = array( 'UID' => $db->getUsername(), 'PWD' => $db->getPassword() );

	  	      if( !$this->conn = sqlsrv_connect( $db->getHostname(), $params ) )
			 	  if( !$this->conn = sqlsrv_connect( $db->getHostname(), $noDbParams ) ) // Create statement needs to bind to server
			 	  	  throw new AgilePHP_PersistenceException( print_r( sqlsrv_errors(), true ) );

	 	      $this->database = $db;
	  }

	  /**
	   * (non-PHPdoc)
	   * @see src/persistence/dialect/SQLDialect#create()
	   */
	  public function create() {

	  		 $this->query( 'CREATE DATABASE ' . $this->database->getName() . ';' );
	  		 
	  		 // Close the connection to the server and bind to the new database.
	  		 $this->close();
	  		 $params = array( 'Database' => $this->database->getName(), 'UID' => $this->database->getUsername(), 'PWD' => $this->database->getPassword() );
	  		 if( !$this->conn = sqlsrv_connect( $this->database->getHostname(), $params ) )
	  		 	 throw new AgilePHP_PersistenceException( print_r( sqlsrv_errors(), true ) );
	  		 	 
			 $constraintFails = array();

	  		 foreach( $this->database->getTables() as $table ) {

	  		 		  $sql = 'CREATE TABLE ' . $table->getName() . ' ( ';

	  		 		  foreach( $table->getColumns() as $column ) {

	  		 				   $sql .= '[' . $column->getName() . '] ' . $column->getType() . 
	  		 						   (($column->getLength()) ? '(' . $column->getLength() . ')' : '') .
	  		 						   (($column->isRequired() == true) ? ' NOT NULL' : '') .
	  		 						   (($column->isAutoIncrement() === true) ? ' IDENTITY(1,1)' : '') .
	  		 						   (($column->getDefault() && $column->getType() != 'datetime' && 
	  		 						   	 		$column->getType() != 'timestamp' && !$column->isAutoIncrement() &&
	  		 						   	 		!$column->isPrimaryKey()) ?
	  		 						   	 		' DEFAULT ' . $column->getDefault() : '') .
	  		 						   ((!$column->getDefault() && !$column->isRequired() && !$column->isAutoIncrement() &&
	  		 						   			!$column->isPrimaryKey()) ? ' DEFAULT NULL' : '') . ', ';
	  		 		  }

	  		 		  $pkeyColumns = $table->getPrimaryKeyColumns();
	  		 		  if( count( $pkeyColumns ) ) {

  	 				  	  $sql .= ' PRIMARY KEY ( ';
	  	 				  for( $i=0; $i<count( $pkeyColumns ); $i++ ) {

	  	 					   $sql .= '[' . $pkeyColumns[$i]->getName() . ']';
	
	  	 						   if( ($i+1) < count( $pkeyColumns ) )
	  	 						   	   $sql .= ', ';
	  	 				  }
	  	 				  $sql .= ' ), ';
  	 				  }

			   		  if( $table->hasForeignKey() ) {

			      		  $bProcessedKeys = array();
			   		  	  $foreignKeyColumns = $table->getForeignKeyColumns();
			   		  	  for( $h=0; $h<count( $foreignKeyColumns ); $h++ ) {

			   		  	  		   $fk = $foreignKeyColumns[$h]->getForeignKey();

		   		  	  		       if( in_array( $fk->getName(), $bProcessedKeys ) )
			   		  	  		       continue;

			   		  	  		   $fk->setOnUpdate( str_replace( '_', ' ', $fk->getOnUpdate() ) );
			   		  	  		   $fk->setOnDelete( str_replace( '_', ' ', $fk->getOnDelete() ) );

	   		  	  	       		   // Get foreign keys which are part of the same relationship
	   		  	  	       		   $relatedKeys = $table->getForeignKeyColumnsByKey( $fk->getName() );

	   		  	  	       		   $sql .= ' CONSTRAINT ' . $fk->getName() . '';
   	  	  	       		   	 	   $sql .= ' FOREIGN KEY ( ';
	   		  	  		    	   for( $j=0; $j<count( $relatedKeys ); $j++ ) {
 
	   		  	  	       		   	 	$sql .= '' . $relatedKeys[$j]->getColumnInstance()->getName() . '';
	   		  	  	       		   		if( ($j+1) < count( $relatedKeys ) )
	   		  	  	       		   		    $sql .= ', ';
	   		  	  	       		   }
								   $sql .= ' ) REFERENCES ' . $fk->getReferencedTable() . ' ( ';
	   		  	  		    	   for( $j=0; $j<count( $relatedKeys ); $j++ ) {
 
   	  	  	       		   		 	    $sql .= '' . $relatedKeys[$j]->getReferencedColumn() . '';
	   		  	  	       		   	    if( ($j+1) < count( $relatedKeys ) )
	   		  	  	       		   		     $sql .= ', ';
	   		  	  		    	   }
	   		  	  	       		   $sql .= ' ) ';
	   		  	  	       		   $sql .= (($fk->getOnUpdate()) ? ' ON UPDATE ' . $fk->getOnUpdate() : '' );
   		  	  		   			   $sql .= (($fk->getOnDelete()) ? ' ON DELETE ' . $fk->getOnDelete() : '' ) . ', ';

			   		  	  		   array_push( $bProcessedKeys, $fk->getName() );
			   		  	  }
			   		  }

					  $sql .= ');';
			   		  if( !$this->query( $sql ) ) {

			   		  	  $error = sqlsrv_errors();

			   		  	  // This saves the create operation from blowing up if persistence.xml defines a table
			   		  	  // that references a table further down the persistence.xml file that has not been
			   		  	  // created yet. Is there a cleaner way - like disabling constraint checks?
			   		  	  if( stristr( $error[0]['message'], 'references invalid table' ) ) {
			   		  	  
			   		  	  	  array_push( $constraintFails, $sql );
			   		  	  	  continue;
			   		  	  }

			   		  	  throw new AgilePHP_PersistenceException( print_r( sqlsrv_errors(), true ) );
			   		  }
	  		 }

	  		 // Constraint hack continued
	  		 if( count( $constraintFails ) )
	  		 	 foreach( $constraintFails as $sql )
	  		 	 		if( !$this->query( $sql ) )
		  		 	 		throw new AgilePHP_PersistenceException( print_r( sqlsrv_errors(), true ) );	  		 
	  }

	  /**
	   * (non-PHPdoc)
	   * @see src/AgilePHP/persistence/BasePersistence#beginTransaction()
	   */
	  public function beginTransaction() {
	  	
	  		 Logger::getInstance()->debug( 'SQLSRVDialect::beginTransaction Beginning transaction' );
	  }
	  
	  /**
	   * (non-PHPdoc)
	   * @see src/AgilePHP/persistence/BasePersistence#commit()
	   */
	  public function commit() {

	  		 sqlsrv_commit( $this->conn );

	  		 Logger::getInstance()->debug( 'SQLSRVDialect::commit Transaction successfully committed.' );
	  }
	  
	  /**
	   * (non-PHPdoc)
	   * @see src/AgilePHP/persistence/BasePersistence#rollBack($message, $code)
	   */
	  public function rollBack( $message = null, $code = 0 ) {

	  		 Logger::getInstance()->debug( 'SQLSRVDialect::rollBack ' . (($message == null) ? '' : ' ' . $message ) );

	  		 $this->transactionInProgress = false;
	  		 sqlsrv_rollback( $this->conn );

	  		 if( $message ) throw new AgilePHP_PersistenceException( $message, $code );
	  }

	  /**
	   * A statement resource. If the statement cannot be created and/or executed, false is returned.
	   * 
	   * @param String $sql The SQL statement to execute
	   * @param array $params Optional array of values that correspond to parameters in a parameterized query.
	   * @return A statement resource. If the statement cannot be created and/or executed, false is returned.
	   * @see src/AgilePHP/persistence/BasePersistence#query($sql)
	   */
	  public function query( $sql, $params = array() ) {

	  		 return sqlsrv_query( $this->conn, $sql, (count( $params )) ? $params : null );
	  }

	  /**
	   * (non-PHPdoc)
	   * @see src/persistence/BasePersistence#prepare($statement)
	   */
	  public function prepare( $statement ) {

	  		 $this->statement = $statement;
	  }
	  
	  /**
	   * (non-PHPdoc)
	   * @see src/persistence/BasePersistence#execute($inputParameters)
	   */
	  public function execute( array $inputParameters = array() ) {

	  		 // SQLSRV driver requires parameters passed to prepare be passed by reference
	  		 $params = array();
	  		 for( $i=0; $i<count( $inputParameters ); $i++ )
	  		 	 $params[$i] = &$inputParameters[$i];

	  		 if( !$this->stmt = sqlsrv_prepare( $this->conn, $this->statement, $params ) )
	  		 	 throw new AgilePHP_PersistenceException( print_r( sqlsrv_errors(), true ) );

	  		 if( !sqlsrv_execute( $this->stmt ) )
	  		 	 throw new AgilePHP_PersistenceException( print_r( sqlsrv_errors(), true ) );
	  }
	  
	  /**
	   * (non-PHPdoc)
	   * @see src/persistence/BasePersistence#truncate($model)
	   */
	  public function truncate( $model ) {

	  	     $table = $this->getTableByModel( $model );
	  		 $this->query( 'TRUNCATE table ' . $table->getName() . ';' );
	  }
	  
	  /**
	   * (non-PHPdoc)
	   * @see src/persistence/dialect/SQLDialect#drop()
	   */
	  public function drop() {

  	 	 	 $this->query( 'DROP DATABASE ' . $this->getDatabase()->getName() );
	  }

	  /**
	   * Overrides parent find method to provide MSSQL specific syntax.
	   * 
	   * @param $model A domain model object. Any fields which are set in the object are used to filter results.
	   * @throws AgilePHP_PersistenceException If any primary keys contain null values or any
	   * 		   errors are encountered executing queries
	   */
	  public function find( $model ) {

	    	 $table = $this->getTableByModel( $model );
			 $newModel = $table->getModelInstance();
			 $values = array();

			 Logger::getInstance()->debug( 'SQLSRVDialect::find Performing find on model \'' . $table->getModel() . '\'.' );

	  		 try {
	  		  	    $pkeyColumns = $table->getPrimaryKeyColumns();
	  		   		if( $this->isEmpty( $model ) ) {

	    	   	        $sql = 'SELECT';

	    	   	        if( $this->getDistinct() != null )	    	   	        	
	    	   	        	$sql .= ' DISTINCT ' . $this->getDistinct();
	    	   	        else 
	    	   	        		$sql .= ($this->getMaxResults() ? ' TOP ' . $this->getMaxResults() . ' *' : '');

	    	   	        $sql .= ' FROM ' . $table->getName();

	    	   	        $order = $this->getOrderBy();
	    	   	        $offset = $this->getOffset();
	    	   	        $groupBy = $this->getGroupBy();

    	   	         	$sql .= ($this->getRestrictions() != null) ? $this->createRestrictSQL() : '';
					 	$sql .= ($order != null) ? ' ORDER BY ' . $order['column'] . ' ' . $order['direction'] : '';
					 	$sql .= ($groupBy)? ' GROUP BY ' . $this->getGroupBy() : '';
    	   	         	$sql .= ';';

	   	   	         	$this->setDistinct( null );
    	   	         	$this->setRestrictions( array() );
    	   	         	$this->setRestrictionsLogicOperator( 'AND' );
    	   	         	$this->setOrderBy( null, 'ASC' );
    	   	         	$this->setGroupBy( null );
	    	   		 }
	    	   		 else {
	    	   		 		$where = '';

	    	   		 		$columns = $table->getColumns();
							for( $i=0; $i<count( $columns ); $i++ ) {

							 	 $accessor = $this->toAccessor( $columns[$i]->getModelPropertyName() );
						     	 if( $model->$accessor() == null ) continue;

						     	 $where .= (count($values) ? ' AND ' : ' ') . $columns[$i]->getName() . '=?';
								 array_push( $values, $model->$accessor() );
						    }
						    $sql = 'SELECT * FROM ' . $table->getName() . ' WHERE' . $where;
						    $sql .= ' LIMIT ' . $this->maxResults . ';';
	    	   		 }

					 $this->prepare( $sql );
					 $this->execute( $values );

					 if( !sqlsrv_has_rows( $this->stmt ) ) { 

					 	Logger::getInstance()->debug( 'SQLSRVDialect::find Empty result set for model \'' . $table->getModel() . '\'.' );
					 	return null;
					 }

				 	 $index = 0;
				 	 $models = array();
					 while( $stdClass = sqlsrv_fetch_object( $this->stmt ) ) {

					 		  $m = $table->getModelInstance();
					 	   	  foreach( get_object_vars( $stdClass ) as $name => $value ) {

					 	   	  		   if( $value == null ) continue;

					 	   	  		   $modelProperty = $this->getPropertyNameForColumn( $table, $name );

					 	   	  		   if( is_object( $value ) )
					 	   	  		   	   $value = $this->cast( $value );

							 	   	   // Create foreign model instances from foreign values
						 	 		   foreach( $table->getColumns() as $column ) {

						 	 		  		    if( $column->isForeignKey() && $column->getName() == $name ) {

						 	 		  		   	    $foreignModel = $column->getForeignKey()->getReferencedTableInstance()->getModel();
						 	 		  		   	    $foreignInstance = new $foreignModel();

						 	 		  		   	    $foreignMutator = $this->toMutator( $column->getForeignKey()->getReferencedColumnInstance()->getModelPropertyName() );
						 	 		  		   	    $foreignInstance->$foreignMutator( $value );

						 	 		  		   	    $persisted = $this->find( $foreignInstance );

						 	 		  		   	    $instanceMutator = $this->toMutator( $foreignModel );
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
					 	   	  if( $index == $this->getMaxResults() )  break;
				     }

				     return $models;
	  		 }
	  		 catch( Exception $e ) {

	  		 		throw new AgilePHP_PersistenceException( $e->getMessage(), $e->getCode() );
	  		 }

	  		 return null;
	  }

	  /**
	   * (non-PHPdoc)
	   * @see src/AgilePHP/persistence/BasePersistence#close()
	   */
	  public function close() {

	  		 if( $this->conn )
	  		 	sqlsrv_close( $this->conn );
	  }

	  /**
	   * (non-PHPdoc)
	   * @see src/persistence/dialect/SQLDialect#reverseEngineer()
	   */
	  public function reverseEngineer() {

	  		 $lengthables = array( 'binary', 'char', 'decimal', 'nchar', 'numeric', 'nvarchar', 'varbinary', 'varchar' );

	  		 $Database = new Database();
	  		 $Database->setId( $this->database->getId() );
	  		 $Database->setName( $this->database->getName() );
	  		 $Database->setType( $this->database->getType() );
	  		 $Database->setHostname( $this->database->getHostname() );
	  		 $Database->setUsername( $this->database->getUsername() );
	  		 $Database->setPassword( $this->database->getPassword() );

	  		 $stmt = $this->prepare( 'select * from information_schema.tables;' );
	  		 $this->execute();
			 $tables = array();
			 while( $stdClass = sqlsrv_fetch_object( $this->stmt ) )
			 	array_push( $tables, $stdClass );

			 $stmt2 = $this->query( 'SELECT 
							    FK_Table  = FK.TABLE_NAME, 
							    FK_Column = CU.COLUMN_NAME, 
							    PK_Table  = PK.TABLE_NAME, 
							    PK_Column = PT.COLUMN_NAME, 
							    Constraint_Name = C.CONSTRAINT_NAME 
							FROM 
							    INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS C 
							    INNER JOIN 
							    INFORMATION_SCHEMA.TABLE_CONSTRAINTS FK 
							        ON C.CONSTRAINT_NAME = FK.CONSTRAINT_NAME 
							    INNER JOIN 
							    INFORMATION_SCHEMA.TABLE_CONSTRAINTS PK 
							        ON C.UNIQUE_CONSTRAINT_NAME = PK.CONSTRAINT_NAME 
							    INNER JOIN 
							    INFORMATION_SCHEMA.KEY_COLUMN_USAGE CU 
							        ON C.CONSTRAINT_NAME = CU.CONSTRAINT_NAME 
							    INNER JOIN 
							    ( 
							        SELECT 
							            i1.TABLE_NAME, i2.COLUMN_NAME 
							        FROM 
							            INFORMATION_SCHEMA.TABLE_CONSTRAINTS i1 
							            INNER JOIN 
							            INFORMATION_SCHEMA.KEY_COLUMN_USAGE i2 
							            ON i1.CONSTRAINT_NAME = i2.CONSTRAINT_NAME 
							            WHERE i1.CONSTRAINT_TYPE = \'PRIMARY KEY\' 
							    ) PT 
							    ON PT.TABLE_NAME = PK.TABLE_NAME' );

			 $foreignKeys = array();
			 while( $stdClass = sqlsrv_fetch_object( $stmt2 ) )
			 	array_push( $foreignKeys, $stdClass );

			 foreach( $tables as $table ) {

			 		// ignore system tables
				 	if( substr( $table->TABLE_NAME, 0, 3 ) == 'sys' || $table->TABLE_NAME == 'dtproperties' )
				 	 	continue;
				 	 
			 		$stmt3 = $this->query( 'SELECT [name]
									 FROM syscolumns 
									 WHERE [id] IN (SELECT [id] 
									                  FROM sysobjects 
									                 WHERE [name] = \'' . $table->TABLE_NAME . '\' )
									   AND colid IN (SELECT SIK.colid 
									                   FROM sysindexkeys SIK 
									                   JOIN sysobjects SO ON SIK.[id] = SO.[id]  
									                  WHERE SIK.indid = 1
									                    AND SO.[name] = \'' . $table->TABLE_NAME . '\')' );

			 		
					$primaryKeys = array();

					if( $stmt3 ) {

						while( $stdClass = sqlsrv_fetch_object( $stmt3 ) )
							array_push( $primaryKeys, $stdClass );
					}

			 		$Table = new Table();
			 		$Table->setName( $table->TABLE_NAME );
			 		$Table->setModel( ucfirst( $table->TABLE_NAME ) );

			 		$this->prepare( 'exec sp_columns ' . $table->TABLE_NAME );
			  		$this->execute();
					$columns = array();
					while( $stdClass = sqlsrv_fetch_object( $this->stmt ) )
						array_push( $columns, $stdClass );

					foreach( $columns as $column ) {

							$type = preg_match_all( '/^(.*)\\s+(identity).*$/i', $column->TYPE_NAME, $matches );
							$identity = null;

							if( count( $matches ) == 3 && !empty( $matches[1] ) ) {

								$type = $matches[1][0];
	      	      		   		$identity = $matches[2][0];
							}
							else {

								$type = $column->TYPE_NAME;
							}

							$Column = new Column( null, $table->TABLE_NAME );
							$Column->setName( $column->COLUMN_NAME );
							$Column->setType( $type );

							if( in_array( $column->TYPE_NAME, $lengthables ) )
								$Column->setLength( ($column->LENGTH == 2147483647) ? 8000 : $column->LENGTH );

							$Column->setRequired( ($column->IS_NULLABLE == 'YES') ? true : false );

							if( $identity )
								$Column->setAutoIncrement( true );

							foreach( $primaryKeys as $pkey ) {

								if( $column->COLUMN_NAME == $pkey->name )
									$Column->setPrimaryKey( true );
							}

							foreach( $foreignKeys as $fkey ) {

								if( $fkey->FK_Table == $table->TABLE_NAME &&
									$fkey->FK_Column == $column->COLUMN_NAME ) {

										$ForeignKey = new ForeignKey( null, $fkey->FK_Table, $fkey->FK_Column );
										$ForeignKey->setName( $fkey->Constraint_Name );
										$ForeignKey->setType( 'one-to-many' );
										$ForeignKey->setReferencedTable( $fkey->PK_Table );
										$ForeignKey->setReferencedColumn( $fkey->PK_Column );
										$ForeignKey->setReferencedController( ucfirst( $fkey->FK_Table ) . 'Controller' );
										$ForeignKey->setOnDelete( ($Column->isRequired()) ? 'CASCADE' : 'SET_NULL' );
										$ForeignKey->setOnUpdate( 'CASCADE' );

										$Column->setForeignKey( $ForeignKey );
									}
							}

							$Table->addColumn( $Column );
					}

					$Database->addTable( $Table );
			 }

			 // sp_stored_procedures

			 return $Database;
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

	     	 $this->prepare( $sql );
	     	 $this->execute();
  			 $result = sqlsrv_fetch_object( $this->stmt );

  			 return ($result == null) ? 0 : $result->count;
	  }

	  /**
	   * Provides unitlity method for casting SQL SERVER objects to PHP values.
	   * 
	   * @param Object $value The SQL SERVER object to extract the PHP value from.
	   * @return mixed The extracted PHP value.
	   */
	  private function cast( $value ) {

	  		  if( $value instanceof DateTime )
	  		  	  if( isset( $value->date ) )
	  		  	  	  return $value->date;
	  }
}
?>