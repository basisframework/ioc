<?php

/**
 * @codeCoverageIgnore
 */
class Bank {
	private $name;
	private $manager;
	private $teller;
	
	public function __construct(BankManagerService $manager, BankTellerService $teller, $name) {
		$this->name = $name;
		$this->manager = $manager;
		$this->teller = $teller;
	}

	public function getName() {
		return $this->name;
	}

	public function getManager() {
		return $this->manager;
	}
	public function getTeller() {
		return $this->teller;
	}
}