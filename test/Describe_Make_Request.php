<?php
require dirname(__FILE__).'/lib/site/limonade/limonade.php';
require dirname(__FILE__).'/../lib/make_request.php';
/**
 * @link http://github.com/plus3network/PHPHelpers PHPHelpers Git Repository
 * @author Christopher Cowan
 * @copyright Copyright (c) 2010, Christopher Cowan, Plus 3 Network Inc. All rights reserved.
 * @license http://www.opensource.org/licenses/lgpl-2.1.php GNU Lesser General Public License
 */
 
/**
 * Spec for WebRequest
 *
 * This will test the web_request method that we use for unit testing
 *
 * @package default
 * @author Christopher Cowan
 **/
class Describe_Make_Request extends PHPUnit_Framework_TestCase 
{
    /**
     * The URL for the test
     *
     * @var string
     **/
    private $test_url = 'http://localhost:8800/echo';
    
    /**
     * This will setup the test env
     *
     * @return void
     * @author Christopher Cowan
     **/
    public function setUp()
    {
        option('cookie_file', tempnam('/tmp', 'test-cookie-file-'));
    } // END function setUp()
    
    /**
     * It should have a way to read cookie files
     *
     * @return void
     * @author Christopher Cowan
     * @test
     **/
    public function It_should_have_a_way_to_read_cookie_files()
    {
        $cookies = get_cookies_from_file(dirname(__FILE__).'/lib/fixtures/cookies.txt');
        
        $this->assertEquals(count($cookies), 2);
        $this->assertEquals($cookies[0]['domain'], 'example.com');
        $this->assertEquals($cookies[0]['domain_only'], true);
        $this->assertEquals($cookies[0]['path'], '/');
        $this->assertEquals($cookies[0]['secure'], false);
        $this->assertEquals($cookies[0]['expires'], 0);
        $this->assertEquals($cookies[0]['key'], 'test_cookie');
        $this->assertEquals($cookies[0]['value'], 'test-value');
        
        $this->assertEquals($cookies[1]['domain'], 'example.com');
        $this->assertEquals($cookies[1]['domain_only'], false);
        $this->assertEquals($cookies[1]['path'], '/secure');
        $this->assertEquals($cookies[1]['secure'], true);
        $this->assertEquals($cookies[1]['expires'], 1291648153);
        $this->assertEquals($cookies[1]['key'], 'secure_cookie');
        $this->assertEquals($cookies[1]['value'], 'test-secure-value');
    } // END function It_should_have_a_way_to_read_cookie_files
    
    /**
     * It should have a way to set cookies in a cookie file
     *
     * @return void
     * @author Christopher Cowan
     * @test
     **/
    public function It_should_have_a_way_to_set_cookies_in_a_cookie_file()
    {
        $file = tempnam('/tmp','cookie-test-file-');
        echo "$file\n";
        $cookies = array(
            array(
                'domain'      => 'example.com',
                'domain_only' => true,
                'path'        => '/',
                'secure'      => false,
                'expires'     => 123456789,
                'key'         => 'my_test',
                'value'       => 'my_value'
            ),
            array(
                'domain'      => 'example.com',
                'domain_only' => false,
                'path'        => '/secure',
                'secure'      => true,
                'expires'     => 0,
                'key'         => 'my_secure_test',
                'value'       => 'my_secure_value'
            ),
        );
        set_cookies_in_file($cookies, $file);
        $fixture_contents = <<<COOKIEFILE
# Netscape HTTP Cookie File
# http://curl.haxx.se/rfc/cookie_spec.html
# This file was generated by libcurl! Edit at your own risk.

example.com	TRUE	/	FALSE	123456789	my_test	my_value
example.com	FALSE	/secure	TRUE	0	my_secure_test	my_secure_value

COOKIEFILE;
        $file_contents = file_get_contents($file);
        $this->assertEquals($file_contents, $fixture_contents);
        
    } // END function It_should_have_a_way_to_set_cookies_in_a_cookie_file
    
    
    
    /**
     * It should return an array with the response and header
     *
     * @return void
     * @author Christopher Cowan
     * @test
     **/
    public function It_should_return_an_array_with_the_response_and_header()
    {
        $response = make_request($this->test_url, 'GET');
        $this->assertArrayHasKey( 'body', $response );
        $this->assertArrayHasKey( 'headers', $response );
        $this->assertArrayHasKey( 'status_code', $response );
        $this->assertEquals( $response['status_code'], 200);
    } // END function It_should_return_an_array_with_the_response_and_header
    
    /**
     * It should return a numeric status code
     *
     * @return void
     * @author Christopher Cowan
     * @test
     **/
    public function It_should_return_a_numeric_status_code()
    {
        $response = make_request($this->test_url, 'GET');
        $this->assertTrue(is_numeric($response['status_code']));
        $this->assertEquals( $response['status_code'], 200);
    } // END function It_should_return_a_numeric_status_code
    
    /**
     * It should return an array for the headers
     *
     * @return void
     * @author Christopher Cowan
     * @test
     **/
    public function It_should_return_an_array_for_the_headers()
    {
        $response = make_request($this->test_url, 'GET');
        $this->assertTrue(is_array($response['headers']));
        $this->assertEquals( $response['status_code'], 200);
    } // END function It_should_return_an_array_for_the_headers
    
    /**
     * It should send post requests with post fields
     *
     * @return void
     * @author Christopher Cowan
     * @test
     **/
    public function It_should_send_post_requests_with_post_fields()
    {
        $post_fields = array('foo'=>'bar');
        $response = make_request($this->test_url, 'POST', array('post_fields'=>$post_fields));
        $data = json_decode($response['body'], true);
        $this->assertEquals( $response['status_code'], 200);
        $this->assertEquals( $post_fields, $data['_POST']);
        
    } // END function It_should_send_post_requests_with_post_fields
    
    /**
     * It should send a delete request
     *
     * @return void
     * @author Christopher Cowan
     * @test
     **/
    public function It_should_send_a_delete_request()
    {
        $response = make_request($this->test_url, 'DELETE');
        $data = json_decode($response['body'], true);
        $this->assertEquals( $response['status_code'], 200);
        $this->assertEquals( 'DELETE', $data['_SERVER']['REQUEST_METHOD']);
        
    } // END function It_should_send_a_delete_request
    
    /**
     * It should send a put request
     *
     * @return void
     * @author Christopher Cowan
     * @test
     **/
    public function It_should_send_a_put_request()
    {
        $response = make_request($this->test_url, 'PUT', array('headers'=>array('Content-Length'=>0)));
        $this->assertEquals( $response['status_code'], 200);
        $data = json_decode($response['body'], true);
        $this->assertEquals('PUT', $data['_SERVER']['REQUEST_METHOD']);
    } // END function It_should_send_a_put_request
    
    
    /**
     * It should send custom headers
     *
     * @return void
     * @author Christopher Cowan
     * @test
     **/
    public function It_should_send_custom_headers()
    {
        $response = make_request($this->test_url, 'GET', array('headers'=>array('X-Foo'=>'Bar')));
        $this->assertEquals( $response['status_code'], 200);
        $data = json_decode($response['body'],true);
        $this->assertEquals( 'Bar', $data['_HEADERS']['X-Foo']);
        
    } // END function It_should_send_custom_headers
    
    /**
     * It should set a cookie
     *
     * @return void
     * @author Christopher Cowan
     * @test
     **/
    public function It_should_set_a_cookie()
    {
        $response = make_request($this->test_url, 'GET', array('cookies'=>array(array('domain'=>'localhost','key'=>'foo','value'=>'"bar"'))));
        print_r($response);
        $this->assertEquals( $response['status_code'], 200 );
        $data = json_decode($response['body'], true);
        $this->assertEquals( $data['_COOKIE']['foo'], '"bar"' );
    } // END function It_should_set_a_cookie
    
    /**
     * It should have cookies in the response
     *
     * @return void
     * @author Christopher Cowan
     * @test
     **/
    public function It_should_have_cookies_in_the_response()
    {
        $response = make_request($this->test_url, 'GET');
        $this->assertArrayHasKey( 'cookies', $response );
        $this->assertContains( array(
            'domain'      => 'localhost',
            'domain_only' => false,
            'path'        => '/',
            'secure'      => false,
            'expires'     => 0,
            'key'         => 'example',
            'value'       => 'test',
        ), $response['cookies'] );
    } // END function It_should_have_cookies_in_the_response
    
    
    /**
     * It should have more then 1 item in the headers set cookie array
     *
     * @return void
     * @author Christopher Cowan
     * @test
     **/
    public function It_should_have_more_then_1_item_in_the_headers_set_cookie_array()
    {
        $response = make_request($this->test_url, 'GET');
        $this->assertGreaterThan( 1, count($response['headers']['Set-Cookie']));
        $this->assertGreaterThan( 1, count($response['cookies']) );
    } // END function It_should_have_more_then_1_item_in_the_headers_set_cookie_array
    
} // END class Describe_WebRequest 