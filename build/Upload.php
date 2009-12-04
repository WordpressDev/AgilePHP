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
 * AgilePHP :: Upload
 * File upload component
 * 
 * @author Jeremy Hahn
 * @copyright Make A Byte, inc
 * @package com.makeabyte.agilephp
 * @version 0.1a
 */
class Upload {

	  private $name;
	  private $directory;
	  
	  public function __construct() { }

	  /**
	   * Sets the name used in the file form field element.
	   * 
	   * @param $name The name attribute of the HTML file input element
	   * @return void
	   */
	  public function setName( $name ) {

	  		 $this->name = $name;
	  }

	  /**
	   * Returns the file form name.
	   * 
	   * @return The name attribute of the HTML file input element
	   */
	  public function getName() {

	  		 return $this->name;
	  }

	  /**
	   * Sets the destination directory for the upload.
	   * 
	   * @param $directory The upload destination directory
	   * @return void
	   */
	  public function setDirectory( $directory ) {
	  	
	  		 $this->directory = $directory;
	  }

	  /**
	   * Returns the destination directory for uploads.
	   * 
	   * @return void
	   */
	  public function getDirectory() {

	  		 return $this->directory;
	  }

	  /**
	   * Saves the upload contained in the $_FILES array for the specified
	   * file input $name.
	   * 
	   * @return The uploaded file path.
	   * @throws AgilePHP_Exception if any errors are encountered.
	   */
	  public function save() {

			 $target = $this->getDirectory() . '/' . $_FILES[ $this->getName() ]['name'];

			 Logger::getInstance()->debug( 'Upload::save Saving upload with name \'' . $this->getName() . '\' to target path \'' . $target . '\'.' );

			 if( !move_uploaded_file( $_FILES[ $this->getName() ]['tmp_name'], $target ) ) {

			 	 switch( $_FILES[ $this->getName() ]['error'] ) {

			 	 	case 1:
			 	 		$error = 'The uploaded file exceeds the upload_max_filesize directive in php.ini.';
			 	 		break;
			 	 		
			 	 	case 2:
			 	 		 $error = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.';
			 	 		 break;
			 	 		 
			 	 	case 3:
			 	 		 $error = 'The uploaded file was only partially uploaded.';
			 	 		 break;
			 	 		 
			 	 	case 4:
			 	 		 $error = 'No file was uploaded.';
			 	 		 break;
			 	 		 
			 	 	case 5:
			 	 		 $error = 'Missing a temporary folder. Introduced in PHP 4.3.10 and PHP 5.0.3.';
			 	 		 break;
			 	 		 
			 	 	case 6:
			 	 		 $error = 'Failed to write file to disk. Introduced in PHP 5.1.0.';
			 	 		 break;
			 	 		 
			 	 	case 7:
			 	 		 $error = 'File upload stopped by extension. Introduced in PHP 5.2.0.';
			 	 		 break;
			 	 }

			 	 Logger::getInstance()->debug( 'Upload::save Upload failed with code \'' . $_FILES[ $this->getName() ]['error'] . '\' and message \'' . $error . '\'.' );

			 	 throw new AgilePHP_Exception( $error, $_FILES[ $this->getName() ]['error'] );
			 }

			 chmod( $target, 0755 );

			 Logger::getInstance()->debug( 'Upload::save Upload successfully saved' );

			 return $target;
	  }

	  /**
	   * Deletes the uploaded file.
	   * 
	   * @return void
	   */
	  public function delete() {

	  		 if( !unlink( $this->getDirectory() .'/' . $_FILES[ $this->getName() ]['name'] ) )
	  		 	 Logger::getInstance()->debug( 'Upload::delete Failed to delete upload' );
	  		 else
	  		 	 Logger::getInstance()->debug( 'Upload::delete Delete successful' );
	  }
}
?>