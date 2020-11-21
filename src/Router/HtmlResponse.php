<?php

namespace AssignmentFive\Router;

use AssignmentFive\Views\View;

class HtmlResponse extends Response
{
	private View $view;

	public function __construct()
	{
		$this->addHeader('Content-Type: text/html');
		$this->view = new View();
	}

	public function setResponse(array $data): self
	{
		$data['payload'] = $data['payload'] ?? [];

		if (!empty($data['template'])) {
			$this->view->setTemplate($data['template']);
			$this->view->setData($data);
		}

		if (!empty($data['redirect'])) {
			$this->redirect($data['redirect']);
		}

		return $this;
	}

	public function __toString(): string
	{
		foreach ($this->headers as $header) {
			header($header);
		}

		return $this->view->render();
	}
}
