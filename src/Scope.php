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
 * Includes all scope dependencies
 */
require_once 'scope/ApplicationScope.php';
require_once 'scope/RequestScope.php';
require_once 'scope/Session.php';
require_once 'scope/SessionScope.php';

/**
 * Facade for AgilePHP scopes.
 *
 * @author Jeremy Hahn
 * @copyright Make A Byte, inc
 * @package com.makeabyte.agilephp
 * @abstract
 */
abstract class Scope {

    private function __construct() {}
    private function __clone() {}

    /**
     * Returns a singleton instance of ApplicationScope.
     *
     * @return ApplicationScope A singleton instance of ApplicationScope
     * @static
     */
    public static function getApplicationScope() {
        return ApplicationScope::getInstance();
    }

    /**
     * Returns a singleton instance of RequestScope.
     *
     * @return SessionScope A singleton instance of SessionScope
     * @static
     */
    public static function getSessionScope() {
        return SessionScope::getInstance();
    }

    /**
     * Returns a singleton instance of RequestScope.
     *
     * @return RequestScope A singleton instance of RequestScope
     * @static
     */
    public static function getRequestScope() {
        return RequestScope::getInstance();
    }
}
?>