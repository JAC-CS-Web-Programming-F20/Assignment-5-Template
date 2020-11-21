<?php

namespace AssignmentFiveTests\RouterTests;

use AssignmentFiveTests\AssignmentFiveTest;
use Faker\Factory;
use GuzzleHttp\Client;

class RouterTest extends AssignmentFiveTest
{
	protected static Client $client;

	public static function setUpBeforeClass(): void
	{
		self::$faker = Factory::create();
	}

	public function setUp(): void
	{
		self::$client = new Client([
			'base_uri' => \Url::base(),
			'headers' => ['Accept' => 'application/json'],
			'cookies' => true
		]);
	}

	protected function getResponse(string $method = 'GET', string $url = '', array $data = [], bool $isJson = true)
	{
		$request = $this->buildRequest($method, $url, $data);
		$response = self::$client->request(
			$request['method'],
			$request['url'],
			$request['body']
		)->getBody();
		$jsonResponse = json_decode($response, true);
		return $jsonResponse;
	}

	protected function buildRequest(string $method, string $url, array $data): array
	{
		$body['form_params'] = [];

		foreach ($data as $key => $value) {
			$body['form_params'][$key] = $value;
		}

		return [
			'method' => $method,
			'url' => $url,
			'body' => $body
		];
	}
}
