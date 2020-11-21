<?php

namespace AssignmentFiveTests\BrowserTests;

use AssignmentFiveTests\AssignmentFiveTest;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverSelect;
use Faker\Factory;

class BrowserTest extends AssignmentFiveTest
{
	protected static RemoteWebDriver $driver;

	public static function setUpBeforeClass(): void
	{
		self::$driver = RemoteWebDriver::create("http://firefox:4444/wd/hub", DesiredCapabilities::firefox());
		self::$faker = Factory::create();
	}

	public static function tearDownAfterClass(): void
	{
		self::$driver->close();
	}

	protected function findElement(string $selector): RemoteWebElement
	{
		$element = self::$driver->findElement(WebDriverBy::cssSelector($selector));

		$this->scrollTo($element);
		return $element;
	}

	protected function findSelectElement(string $selector): WebDriverSelect
	{
		return new WebDriverSelect($this->findElement($selector));
	}

	protected function findElements(string $selector): array
	{
		return self::$driver->findElements(WebDriverBy::cssSelector($selector));
	}

	protected function findElementByLink(string $path, array $parameters = []): RemoteWebElement
	{
		return $this->findElement("a[href*=\"" . \Url::path($path, $parameters) . "\"]");
	}

	protected function clickOnLink(string $path, array $parameters = []): void
	{
		$this->findElementByLink($path, $parameters)->click();
	}

	protected function clickOnButton(string $selector): void
	{
		$this->findElement($selector)->click();
	}

	protected function doesElementExist(string $selector): bool
	{
		try {
			$this->findElement($selector);
		} catch (NoSuchElementException $noSuchElementException) {
			return false;
		}

		return true;
	}

	protected function scrollTo(RemoteWebElement $element): void
	{
		self::$driver->executeScript("arguments[0].scrollIntoView();", [$element]);
	}

	protected function goTo(string $path, array $parameters = []): void
	{
		self::$driver->get(\Url::path($path, $parameters));
	}

	protected function getCurrentUrl(): string
	{
		return self::$driver->getCurrentURL();
	}

	protected function login(string $email, string $password): void
	{
		$this->goTo('/');
		$this->clickOnLink('auth/login');

		$emailInput = $this->findElement("form#login-form input[name=\"email\"]");
		$passwordInput = $this->findElement("form#login-form input[name=\"password\"]");
		$submitButton = $this->findElement("form#login-form button");

		$emailInput->sendKeys($email);
		$passwordInput->sendKeys($password);
		$submitButton->click();
	}

	protected function logout(): void
	{
		$this->goTo('/');
		$this->clickOnLink('auth/logout');
	}
}
