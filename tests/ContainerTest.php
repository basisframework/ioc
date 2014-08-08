<?php
require dirname(__DIR__) . '/src/Container.php';


// Test classes
require __DIR__ . '/fixtures/BankTransferService.php';
require __DIR__ . '/fixtures/BankTellerService.php';
require __DIR__ . '/fixtures/BankManagerService.php';
require __DIR__ . '/fixtures/Bank.php';

class ContainerTest extends PHPUnit_Framework_TestCase {

	public function testResolvingAnUnboundClassReturnsNull() {
		$container = new Basis\Ioc\Container;

		$this->assertEquals(NULL, $container->resolve('Anything'));
	}

	/**
	 * Tests binding of a service by class name.
	 */
	public function testBindingAClass() {
		$container = new Basis\Ioc\Container;
		$container->bind('BankTransferService', 'BankTransferService');

		$service = $container->resolve('BankTransferService');
		$this->assertEquals('BankTransferService', get_class($service));
	}

	/**
	 * Tests binding of a service by instance.
	 */
	public function testBindingAnInstance() {
		$container = new Basis\Ioc\Container;
		$container->bind('BankTransferService', new BankTransferService);

		$service = $container->resolve('BankTransferService');
		$this->assertEquals('BankTransferService', get_class($service));
	}
	
	/**
	 * Test to ensure that binding to an interface the class doens't implement
	 * results in an exception.
	 * @expectedException Basis\Ioc\ContainerException
	 */
	public function testBindingAClassToAnInvalidInterfaceFails() {
		$container = new Basis\Ioc\Container;
		$container->bind('NonexistentInterface', 'BankTransferService');
	}
	
	/**
	 * Test to ensure that binding to an interface the object doesn't implement
	 * results in an exception.
	 * @expectedException Basis\Ioc\ContainerException
	 */
	public function testBindingAnInstanceToAnInvalidInterfaceFails() {
		$container = new Basis\Ioc\Container;
		$container->bind('NonexistentInterface', new BankTransferService);
	}

	/**
	 * Test accessing a service which depends on other services.
	 */
	public function testResolvingAnInjectedService() {
		$container = new Basis\Ioc\Container;
		$container->bind('BankTransferService', 'BankTransferService');
		$container->bind('BankManagerService', 'BankManagerService');

		$service = $container->resolve('BankManagerService');
		$this->assertEquals('BankManagerService', get_class($service));
	}

	/**
	 * Test creating an object that mixes injected and passed parameters.
	 */
	public function testCreatingAnInjectedObject() {
		$container = new Basis\Ioc\Container;
		$container->bind('BankTellerService', 'BankTellerService');
		$container->bind('BankTransferService', 'BankTransferService');
		$container->bind('BankManagerService', 'BankManagerService');

		$bank = $container->make('Bank', 'My Bank');
		
		$this->assertEquals('Bank', get_class($bank));
		$this->assertEquals('My Bank', $bank->getName());
		$this->assertEquals('BankManagerService', get_class($bank->getManager()));
	}

};
