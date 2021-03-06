<?php
/**
 * @package com.makeabyte.agilephp.test.webservice.rest
 */
class UserRestServiceTest extends PHPUnit_Framework_TestCase {

    private $endpoint = 'http://localhost/test/index.php/users';

    /**
     * @test
     * @expectedException RestClientException
     */
    public function getXmlUnauthenticated() {

        $client = new RestClient($this->endpoint);
        $response = $client->get();

        PHPUnit_Framework_Assert::assertEquals(401, $client->getResponseCode(), 'Failed to get HTTP 401 Unauthorized');
    }

    /**
     * @test
     */
    public function getXml() {

        $client = new RestClient($this->endpoint);
        $client->authenticate('admin', 'test');
        $response = $client->get();

        $xml = simplexml_load_string($response);
        PHPUnit_Framework_Assert::assertNotNull($response, 'Failed to get a response from the REST service.');
        PHPUnit_Framework_Assert::assertNotNull($xml, 'Failed to get XML response from the REST service.');
        PHPUnit_Framework_Assert::assertInstanceOf('SimpleXMLElement', $xml, 'Failed to convert response to SimpleXMLElement');
        PHPUnit_Framework_Assert::assertEquals(200, $client->getResponseCode(), 'Failed to get HTTP 200 OK');
    }

    /**
     * @test
     * @expectedException RestClientException
     */
    public function getJson() {

        $client = new RestClient($this->endpoint . '/test');
        $client->authenticate('admin', 'test');
        $client->setHeaders(array(
				'Accept: application/json',
				'Content-Type: application/json',
        ));
        $client->put('this is some data that should never make it to the server because the service has a #@ProduceMime which we arent accepting.');
    }

    /**
     * @test
     */
    public function putXMLgetXML() {

        $client = new RestClient($this->endpoint . '/test');
        $client->authenticate('admin', 'test');
        $client->setHeaders(array(
				'Accept: application/xml',
				'Content-Type: application/xml',
        ));

        $orm = ORM::getDialect();

        $username = 'phpunit';
        $password = 'test';
        $email = 'root@localhost';
        $enabled = true;
        $newRole = 'rest-test';
        $created = 'now';

        $user = new User();
        $user->setUsername($username);
        $user->setPassword($password);
        $user->setCreated($created);
        $user->setEmail($email);
        $user->persist();

        // Update the user that was just created
        $user->setPassword('test2');
        $user->setEmail('root@localhost.localdomain');
        $user->setEnabled(false);

        // Create new role
        $role = new Role();
        $role->setName($newRole);

        // Assign admin role to test account
        $user->setRole($role);

        // The server will be expecting XML as indicated in the Content-Type header above
        $data = XmlRenderer::render($user);

        $response = $client->put($data);

        $xml = simplexml_load_string($response);
        PHPUnit_Framework_Assert::assertNotNull($response, 'Failed to get a response from the REST service.');
        PHPUnit_Framework_Assert::assertNotNull($xml, 'Failed to get XML response from the REST service.');
        PHPUnit_Framework_Assert::assertInstanceOf('SimpleXMLElement', $xml, 'Failed to convert response to SimpleXMLElement');
        PHPUnit_Framework_Assert::assertEquals($username, (string)$xml->username, 'Expected username \'' . $username . '\'.');
        PHPUnit_Framework_Assert::assertEquals('root@localhost.localdomain', (string)$xml->email, 'Expected email \'' . $email . '\'.');
        PHPUnit_Framework_Assert::assertEquals($newRole, (string)$xml->Role->name, 'Expected role \'' . $newRole . '\'.');
        PHPUnit_Framework_Assert::assertEquals(202, $client->getResponseCode(), 'Failed to get HTTP 202 Accepted');

        // clean up after unit test
        ORM::delete(ORM::get(new User($username)));
    }

    /**
     * @test
     */
    public function putJSONgetJSON() {

        $client = new RestClient($this->endpoint . '/test/json');
        $client->authenticate('admin', 'test');
        $client->setHeaders(array(
				'Accept: application/json',
				'Content-Type: application/json',
        ));

        $username = 'phpunit-json';
        $password = 'test';
        $email = 'root@localhost';
        $enabled = true;
        $newRole = 'rest-test';
        $created = 'now';

        $user = new User();
        $user->setUsername($username);
        $user->setPassword($password);
        $user->setCreated($created);
        $user->setEmail($email);
        $user->persist();

        // Update some values
        $user->setPassword('test2');
        $user->setEmail('root@localhost.localdomain');
        $user->setEnabled(false);

        // Create new role
        $role = new Role();
        $role->setName($newRole);

        // Assign admin role to test account
        $user->setRole($role);

        // The server will be expecting JSON as indicated in the Content-Type header above
        $data = $user->toJson();

        $response = $client->put($data);

        $json = json_decode($response);
        PHPUnit_Framework_Assert::assertInstanceOf('stdClass', $json, 'Failed to decode JSON data');
        PHPUnit_Framework_Assert::assertNotNull($response, 'Failed to get a response from the REST service.');
        PHPUnit_Framework_Assert::assertNotNull($json, 'Failed to get JSON response from the REST service.');
        PHPUnit_Framework_Assert::assertEquals($username, $json->username, 'Expected username \'' . $username . '\'.');
        PHPUnit_Framework_Assert::assertEquals('root@localhost.localdomain', $json->email, 'Expected email \'' . $email . '\'.');
        PHPUnit_Framework_Assert::assertEquals($newRole, $json->Role->name, 'Expected role \'' . $newRole . '\'.');
        PHPUnit_Framework_Assert::assertEquals(202, $client->getResponseCode(), 'Failed to get HTTP 202 Accepted');

        // clean up after unit test
        ORM::delete(ORM::get(new User($username)));
    }

    /**
     * @test
     */
    public function putJSONgetXML() {

        $client = new RestClient($this->endpoint . '/test/wildcard');
        $client->authenticate('admin', 'test');
        $client->setHeaders(array(
				'Accept: application/xml',
				'Content-Type: application/json',
        ));

        $username = 'phpunit-json2';
        $password = 'test';
        $email = 'root@localhost';
        $enabled = true;
        $newRole = 'rest-test';
        $created = 'now';

        $user = new User();
        $user->setUsername($username);
        $user->setPassword($password);
        $user->setCreated($created);
        $user->setEmail($email);
        $user->persist();

        // Update some values
        $user->setPassword('test2');
        $user->setEmail('root@localhost.localdomain');
        $user->setEnabled(false);

        // Create new role
        $role = new Role();
        $role->setName($newRole);

        // Assign admin role to test account
        $user->setRole($role);

        // The server will be expecting XML as indicated in the Content-Type header above
        $data = JsonRenderer::render($user);

        $response = $client->put($data);

        $xml = simplexml_load_string($response);
        PHPUnit_Framework_Assert::assertNotNull($response, 'Failed to get a response from the REST service.');
        PHPUnit_Framework_Assert::assertNotNull($xml, 'Failed to get XML response from the REST service.');
        PHPUnit_Framework_Assert::assertInstanceOf('SimpleXMLElement', $xml, 'Failed to convert response to SimpleXMLElement');
        PHPUnit_Framework_Assert::assertEquals($username, (string)$xml->username, 'Expected username \'' . $username . '\'.');
        PHPUnit_Framework_Assert::assertEquals('root@localhost.localdomain', (string)$xml->email, 'Expected email \'' . $email . '\'.');
        PHPUnit_Framework_Assert::assertEquals($newRole, (string)$xml->Role->name, 'Expected role \'' . $newRole . '\'.');
        PHPUnit_Framework_Assert::assertEquals(202, $client->getResponseCode(), 'Failed to get HTTP 202 Accepted');

        // clean up after unit test
        ORM::delete(ORM::get(new User($username)));
    }

    /**
     * Starting to get confusing now... Using /test/wildcard to put JSON and get YAML.
     * Since the resource method does not have a #@ConsumeMime or #@ProduceMime, the HTTP
     * Accept and Content-Type headers are used to negotiate the data transformation/exchange.
     *
     * @test
     */
    public function putJSONgetYAML() {

        $client = new RestClient($this->endpoint . '/test/wildcard');
        $client->authenticate('admin', 'test');
        $client->setHeaders(array(
				'Accept: application/x-yaml',
				'Content-Type: application/json',
        ));

        $username = 'phpunit-json2';
        $password = 'test';
        $email = 'root@localhost';
        $enabled = true;
        $newRole = 'rest-test';
        $created = 'now';

        $user = new User();
        $user->setUsername($username);
        $user->setPassword($password);
        $user->setCreated($created);
        $user->setEmail($email);
        $user->persist();

        // Update some user values
        $user->setPassword('test2');
        $user->setEmail('root@localhost.localdomain');
        $user->setEnabled(false);

        // Create new role
        $role = new Role();
        $role->setName($newRole);

        // Assign admin role to test account
        $user->setRole($role);

        // The server will be expecting XML as indicated in the Content-Type header above
        $data = JsonRenderer::render($user);

        $response = $client->put($data);

        $yaml = yaml_parse($response);
        PHPUnit_Framework_Assert::assertNotNull($response, 'Failed to get a response from the REST service.');
        PHPUnit_Framework_Assert::assertNotNull($yaml, 'Failed to get YAML response from the REST service.');
        PHPUnit_Framework_Assert::assertInstanceOf('User', $yaml, 'Failed to convert response to YAML');
        PHPUnit_Framework_Assert::assertEquals($username, $yaml->getUsername(), 'Expected username \'' . $username . '\'.');
        PHPUnit_Framework_Assert::assertEquals('root@localhost.localdomain', $yaml->getEmail(), 'Expected email \'' . $email . '\'.');
        PHPUnit_Framework_Assert::assertEquals($newRole, $yaml->getRole()->getName(), 'Expected role \'' . $newRole . '\'.');
        PHPUnit_Framework_Assert::assertEquals(202, $client->getResponseCode(), 'Failed to get HTTP 202 Accepted');

        // clean up after unit test
        ORM::delete(ORM::get(new User($username)));
    }

    /**
     * Same thing... this time put YAML and get XML back
     *
     * @test
     */
    public function putYAMLgetXML() {

        $client = new RestClient($this->endpoint . '/test/wildcard');
        $client->authenticate('admin', 'test');
        $client->setHeaders(array(
				'Accept: application/xml',
				'Content-Type: application/x-yaml',
        ));

        $username = 'phpunit-yaml';
        $password = 'test';
        $email = 'root@localhost';
        $enabled = true;
        $newRole = 'rest-test';
        $created = 'now';

        $user = new User();
        $user->setUsername($username);
        $user->setPassword($password);
        $user->setCreated($created);
        $user->setEmail($email);
        $user->persist();

        // Update user values
        $user->setPassword('test2');
        $user->setEmail('root@localhost.localdomain');
        $user->setEnabled(false);

        // Create new role
        $role = new Role();
        $role->setName($newRole);

        // Assign admin role to test account
        $user->setRole($role);

        // The server will be expecting XML as indicated in the Content-Type header above
        $data = YamlRenderer::render($user);
        $response = $client->put($data);

        $xml = simplexml_load_string($response);
        PHPUnit_Framework_Assert::assertNotNull($response, 'Failed to get a response from the REST service.');
        PHPUnit_Framework_Assert::assertNotNull($xml, 'Failed to get XML response from the REST service.');
        PHPUnit_Framework_Assert::assertInstanceOf('SimpleXMLElement', $xml, 'Failed to convert response to SimpleXMLElement');
        PHPUnit_Framework_Assert::assertEquals($username, (string)$xml->username, 'Expected username \'' . $username . '\'.');
        PHPUnit_Framework_Assert::assertEquals('root@localhost.localdomain', (string)$xml->email, 'Expected email \'' . $email . '\'.');
        PHPUnit_Framework_Assert::assertEquals($newRole, (string)$xml->Role->name, 'Expected role \'' . $newRole . '\'.');
        PHPUnit_Framework_Assert::assertEquals(202, $client->getResponseCode(), 'Failed to get HTTP 202 Accepted');

        // clean up after unit test
        ORM::delete(ORM::get(new User($username)));
    }

    /**
     * @test
     */
    public function postXMLgetXML() {

        $client = new RestClient($this->endpoint . '/phpunit2');
        $client->authenticate('admin', 'test');
        $client->setHeaders(array(
				'Accept: application/xml',
				'Content-Type: application/xml',
        ));

        $user = new User();
        $user->setUsername('phpunit2');
        $user->setPassword('test');
        $user->setCreated('now');
        $user->setEmail('root@localhost');
        $user->setEnabled(false);

        // Create new role
        $role = new Role();
        $role->setName('rest-test');

        // Assign admin role to test account
        $user->setRole($role);

        // The server will be expecting XML as indicated in the Content-Type header above
        $data = XmlRenderer::render($user);
        $response = $client->post($data);

        $xml = simplexml_load_string($response);
        PHPUnit_Framework_Assert::assertNotNull($response, 'Failed to get a response from the REST service.');
        PHPUnit_Framework_Assert::assertNotNull($xml, 'Failed to get XML response from the REST service.');
        PHPUnit_Framework_Assert::assertInstanceOf('SimpleXMLElement', $xml, 'Failed to convert response to SimpleXMLElement');
        PHPUnit_Framework_Assert::assertEquals('phpunit2', (string)$xml->username, 'Expected username phpunit2.');
        PHPUnit_Framework_Assert::assertEquals('root@localhost', (string)$xml->email, 'Expected email root@localhost.');
        PHPUnit_Framework_Assert::assertEquals('rest-test', (string)$xml->Role->name, 'Expected role rest-test.');
        PHPUnit_Framework_Assert::assertEquals(201, $client->getResponseCode(), 'Failed to get HTTP 201 Created');

        // clean up after unit test
        ORM::delete(ORM::get(new User('phpunit2')));
    }
     
    /**
     * @test
     */
    public function postJSONgetJSON() {

        $client = new RestClient($this->endpoint . '/test/json');
        $client->authenticate('admin', 'test');
        $client->setHeaders(array(
				'Accept: application/json',
				'Content-Type: application/json',
        ));

        $username = 'phpunit-json3';
        $password = 'test';
        $email = 'root@localhost';
        $enabled = true;
        $newRole = 'rest-test';
        $created = 'now';

        $user = new User();
        $user->setUsername($username);
        $user->setPassword($password);
        $user->setCreated($created);
        $user->setEmail($email);
        $user->persist();

        // Update some values
        $user->setPassword('test2');
        $user->setEmail('root@localhost.localdomain');
        $user->setEnabled(false);

        // Create new role
        $role = new Role();
        $role->setName($newRole);

        // Assign admin role to test account
        $user->setRole($role);

        // The server will be expecting XML as indicated in the Content-Type header above
        $data = JsonRenderer::render($user);

        $response = $client->post($data);

        $json = json_decode($response);

        PHPUnit_Framework_Assert::assertInstanceOf('stdClass', $json, 'Failed to decode JSON data');
        PHPUnit_Framework_Assert::assertInstanceOf('User', JsonToModel::transform($response, 'User'), 'Failed to transform JSON data to User instance');
        PHPUnit_Framework_Assert::assertNotNull($response, 'Failed to get a response from the REST service.');
        PHPUnit_Framework_Assert::assertNotNull($json, 'Failed to get JSON response from the REST service.');
        PHPUnit_Framework_Assert::assertEquals($username, $json->username, 'Expected username \'' . $username . '\'.');
        PHPUnit_Framework_Assert::assertEquals('root@localhost.localdomain', $json->email, 'Expected email \'' . $email . '\'.');
        PHPUnit_Framework_Assert::assertEquals($newRole, $json->Role->name, 'Expected role \'' . $newRole . '\'.');
        PHPUnit_Framework_Assert::assertEquals(201, $client->getResponseCode(), 'Failed to get HTTP 201 Created');

        // clean up after unit test
        ORM::delete(ORM::get(new User($username)));
    }

    /**
     * @test
     */
    public function deleteXMLgetXML() {

        $user = new User();
        $user->setUsername('phpunit3');
        $user->setPassword('rest-test');
        $user->setEmail('root@localhost');
        $user->setCreated('now');

        $role = new Role();
        $role->setName('rest-test');
        $user->setRole($role);
        $user->persist();

        $client = new RestClient($this->endpoint . '/phpunit3');
        $client->authenticate('admin', 'test');
        $response = $client->delete();

        PHPUnit_Framework_Assert::assertEquals(204, $client->getResponseCode(), 'Failed to get HTTP 204 no content');
    }

    /**
     * @test
     */
    public function transformXML() {

        $data = '<User><username>admin</username><password>9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08</password><email>root@localhost</email><created>2009-09-06 15:27:44</created><lastLogin>1969-12-31 19:00:00</lastLogin><enabled>1</enabled><Role><name>admin</name><description>This is an administrator account</description></Role><Roles></Roles></User>';
        $o = XmlToModel::transform($data);
        PHPUnit_Framework_Assert::assertInstanceOf('User', $o, 'Failed to transform XML data to PHP object');
    }

    /**
     * @test
     */
    public function transformJSON() {

        $data = ' { "username" : "admin", "password" : "9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08", "email" : "root@localhost", "created" : "2009-09-06 15:27:44", "lastLogin" : "1969-12-31 19:00:00", "enabled" : "1", "Role" : { "name" : "admin", "description" : "This is an administrator account"}  , "Roles" : null  }';
        $o = JsonToModel::transform($data, 'User');
        PHPUnit_Framework_Assert::assertInstanceOf('User', $o, 'Failed to transform JSON data to PHP object');
    }

    /**
     * @test
     */
    public function transformYAML() {

        $data = '--- !php/object "O:4:\"User\":1:{s:12:\"\0User\0object\";O:16:\"User_Intercepted\":8:{s:26:\"\0User_Intercepted\0username\";s:5:\"admin\";s:26:\"\0User_Intercepted\0password\";s:64:\"9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08\";s:23:\"\0User_Intercepted\0email\";s:14:\"root@localhost\";s:25:\"\0User_Intercepted\0created\";s:19:\"2009-09-06 15:27:44\";s:27:\"\0User_Intercepted\0lastLogin\";s:19:\"1969-12-31 19:00:00\";s:25:\"\0User_Intercepted\0enabled\";s:1:\"1\";s:22:\"\0User_Intercepted\0Role\";O:4:\"Role\":1:{s:12:\"\0Role\0object\";O:16:\"Role_Intercepted\":2:{s:22:\"\0Role_Intercepted\0name\";s:5:\"admin\";s:29:\"\0Role_Intercepted\0description\";s:32:\"This is an administrator account\";}}s:23:\"\0User_Intercepted\0Roles\";N;}}" ...';
        $o = YamlToModel::transform($data);
        PHPUnit_Framework_Assert::assertInstanceOf('User', $o, 'Failed to transform YAML data to PHP object');
    }
}
?>