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
 * @package com.makeabyte.agilephp.identity
 */

/**
 * Interface for AgilePHP Identity domain model.
 * 
 * @author Jeremy Hahn
 * @copyright Make A Byte, inc
 * @package com.makeabyte.agilephp.identity
 */
interface IdentityModel {

		  /**
		   * Sets the username of the identity.
		   * 
		   * @param String $username The username to assign
		   * @return void
		   */
		  public function setUsername( $username );

		  /**
		   * Returns the username of the identity.
		   * 
		   * @return String The username of the identity
		   */
		  public function getUsername();

		  /**
		   * Sets the identity password.
		   * 
		   * @param String $password The password to assign
		   * @return void
		   */
		  public function setPassword( $password );

		  /**
		   * Returns the password of the identity.
		   *  
		   * @return String The password of the identity.
		   */
		  public function getPassword();

		  /**
		   * Sets the email address of the identity.
		   * 
		   * @param String $email The email address
		   * @return void
		   */
		  public function setEmail( $email );

		  /**
		   * Returns the email address of the identity.
		   *  
		   * @return String The email address of the identity.
		   */
		  public function getEmail();

		  /**
		   * Sets the date and time that this account was created.
		   * 
		   * @param Date $dateTime ISO-8601 formatted date indicating when the identity was created.
		   * @return void
		   */
		  public function setCreated( $dateTime );

		  /**
		   * Returns the ISO-8601 formatted date.
		   *  
		   * @return Date ISO-8601 date indicating when the identity was created 
		   */
		  public function getCreated();

		  /**
		   * Sets the last time the identity successfully logged in.
		   * 
		   * @param Date $dateTime ISO-8601 date indicating when the identity last logged in
		   * @return void
		   */
		  public function setLastLogin( $dateTime );

		  /**
		   * Returns the date and time the identity last logged in successfully.
		   * 
		   * @return Date The last date and time the identity logged in
		   */
		  public function getLastLogin();
		  
		  /**
		   * Sets the foreign key value for the role which this user belongs
		   * @param String $roleId The foreign key value (primary key for the role) which this user belongs
		   * @return void
		   */
		  public function setRoleId( $roleId );

		  /**
		   * Returns the primary key for the role which this user belongs
		   * 
		   * @return String The role id
		   */
		  public function getRoleId();

		  /**
		   * A role component instance to assign to the identity.
		   * 
		   * @param Role $role The role instance to assign to the identity
		   * @return void
		   */
		  public function setRole( Role $role );

		  /**
		   * Returns the AgilePHP Role instance assigned to the identity.
		   * 
		   * @return Role The role instance assigned to the identity
		   */
		  public function getRole();

		  /**
		   * Sets the AgilePHP session id assigned to the identity.
		   * 
		   * @param String $sessionId An AgilePHP session id
		   * @return void
		   */
		  public function setSessionId( $sessionId );

		  /**
		   * Returns the session id currently assigned to the identity.
		   * 
		   * @return String The session id belonging to the identity
		   */
		  public function getSessionId();

		  /**
		   * Enables or disabled the identity from login.
		   * 
		   * @param bool $value True to enable login, false to disable
		   * @return void
		   */
		  public function setEnabled( $value );

		  /**
		   * Returns boolean value indicating whether the identity is allowed
		   * to successfully login.
		   * 
		   * @return bool True if the identiy can login, false otherwise.
		   */
		  public function getEnabled();
}
?>