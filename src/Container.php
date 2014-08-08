<?php
namespace Basis\Ioc;
use ReflectionClass;

class ContainerException extends \RuntimeException {};

/**
 * @todo Create proxy which binds contracts to interfaces and checks for compliance around bindXXX() methods
 */
class Container {
	/**
	 * @var array
	 */
	private $instances = array();

	private $factories = array();

	public function bind($interface, $instance) {
		if(is_string($instance)) {
			$instance = $this->construct($instance);
		}

		if(!is_a($instance, $interface)) {
			throw new ContainerException(sprintf('Cannot bind "%s" to unrelated interface "%s"', get_class($instance), $interface));
		}

		$this->instances[$interface] = $instance;
	}

	public function bindFactory($interface, $factory) {
		if(is_string($factory)) {
			$factory = $this->construct($factory);
		}

		$this->factories[$interface] = $factory;
	}

	public function resolve($interface) {
		if(isset($this->instances[$interface])) {
			return $this->instances[$interface];
		}

		return NULL;
	}

	public function make($interface) {
		$args = func_get_args();
		array_shift($args);

		if(isset($this->factories[$interface])) {
			return call_user_func_array(array($this->factories[$interface], 'make'), $args);
		}
	}
	
	private function construct($class) {

		// Retrieve the constructor arguments
		$args = func_get_args();
		array_shift($args);
		
		// ReflectionClass used for constructing the class using arguments,
		// and checking for injectable parameters
		$reflector = new ReflectionClass($class);

		// Positional (i.e. non-named) arguments - consisiting of injected and
		// non-injected parameters
		$positional_args = array();

		// Try to inspect the class' constructor
		$constructor = $reflector->getConstructor();

		// If the constructor exists, examine parameters for injectable
		// services
		if($constructor) {
			$params = $constructor->getParameters();

			foreach($params as $param) {
				$hint = $param->getClass();

				// Param is type-hinted; try to find service to inject
				if($hint) {
					$service = $this->resolve($hint->name);

					// Service found; insert into arguments
					if($service) {
						$positional_args[$param->getPosition()] = $service;
					}
				}
			}

			$position = 0;
			foreach($args as $index => $arg) {

				// Fast-forward position past injected parameters
				while(isset($positional_args[$position])) {
					++$position;
				}

				// Insert argument and step to next position
				$positional_args[$position] = $arg;
				++$position;
			}

			// Create and return class instance
			return $reflector->newInstanceArgs($positional_args);
		}

		// No constructor - simply construct and return class
		return new $class;
	}
	
};
