<?php

use Ramsey\Uuid\Uuid;
use crick\Security\Api\ApiKeyGenerator;

class ApiKeyTest extends PHPUnit_Framework_TestCase
{
    protected $app;
    protected $client;
    protected $uuid;
    protected $email;
    protected $password;
    protected $role;
    protected $api;
    protected $query;

    protected function setUp()
    {
        $this->client = new GuzzleHttp\Client(['base_uri' => 'http://localhost/crick/crick/web/index.php/api/']);

        $this->uuid = Uuid::uuid1()->toString();
        $this->email = 'toto@foo.com';
        $this->password = 'totobar58';
        $this->api = ApiKeyGenerator::generateKey();

        $pomm = require __DIR__.'/../.pomm_cli_bootstrap.php';
        $this->query = $pomm['db'];

        $this->query->getModel('db\Db\PublicSchema\UsersModel')
                ->createAndSave([
                    'uuid' => $this->uuid,
                    'email' => $this->email,
                    'password' => $this->password,
                    'role' => 'ROLE_USER',
                    'api' => $this->api,
        ]);
    }

    public function testApiKeyGetPongStatusCode()
    {
        $response = $this->client->request('GET', 'ping', ['headers' => ['Accept' => 'application/json'], 'query' => ['api_key' => $this->api]]);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testApiKeyGetPongJson()
    {
        $excepted = array('username' => 'toto@foo.com', 'message' => 'pong');
        $exceptedjson = json_encode($excepted);
        $response = $this->client->request('GET', 'ping', ['headers' => ['Accept' => 'application/json'], 'query' => ['api_key' => $this->api]]);
        $this->assertEquals($exceptedjson, $response->getBody());
    }

    public function testApiKeyGetPongHtml()
    {
        $excepted = '<h1>pong: '.$this->email.'</h1>';
        $response = $this->client->request('GET', 'ping', ['headers' => ['Accept' => 'text/html'], 'query' => ['api_key' => $this->api]]);
        $this->assertEquals($excepted, $response->getBody());
    }

    protected function tearDown()
    {
        $this->query->getModel('db\Db\PublicSchema\TagModel')
                ->deleteAll();
        $this->query->getModel('db\Db\PublicSchema\FrameModel')
                ->deleteAll();
        $this->query->getModel('db\Db\PublicSchema\ProjectModel')
                ->deleteAll();
        $this->query->getModel('db\Db\PublicSchema\UsersModel')
                ->deleteAll();
    }
}
