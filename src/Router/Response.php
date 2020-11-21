<?php

namespace AssignmentFive\Router;

abstract class Response
{
	protected array $headers;

	protected function redirect(string $location): void
	{
		$this->addHeader("Location: " . \Url::path($location));
	}

	protected function addHeader(string $header): void
	{
		$this->headers[] = $header;
	}

	public abstract function setResponse(array $data): self;
}
