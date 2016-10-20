<?php
namespace Tests;

require_once('src/GiphyApi.php');

use \main\GiphyApi;

class GiphyApiTest extends \PHPUnit_Framework_TestCase
{
    private $apiKey = 'dc6zaTOxFJmzC';
    private $giphy;

    public function setUp()
    {
        $this->giphy = new GiphyApi($this->apiKey);
    }

    public function testSearch()
    {
        $params = ['q' => 'dog cat', 'limit' => 40, 'offset' => 2];
        $result = $this->giphy->searchGifs($params);
        $result = json_decode($result);
        $this->assertEquals(200, $this->giphy->getHttpStatus());
        $count = count($result->data);
        $this->assertEquals(40, $count);
        $this->assertEquals(2, $result->pagination->offset);
    }
}
