<?php
	class DegiroApiConfig {
		private $username;
		private $password;
		private $cookieFile;
		private $debug;
		private $sessionId;
		private $clientId;
		private $tradingUrl;
		private $intAccount;

		public function __construct() {
			$this->setUsername('your-user');
			$this->setPassword('your-password');
			$this->setCookieFile(__DIR__ . '/cookie.txt');
			$this->setDebug(true);
		}

		public function getUsername(){
			return $this->username;
		}

		public function setUsername($username){
			$this->username = $username;
		}

		public function getPassword(){
			return $this->password;
		}

		public function setPassword($password){
			$this->password = $password;
		}

		public function getCookieFile(){
			return $this->cookieFile;
		}

		public function setCookieFile($cookieFile){
			$this->cookieFile = $cookieFile;
		}

		public function getDebug(){
			return $this->debug;
		}

		public function setDebug($debug){
			$this->debug = $debug;
		}

		public function getSessionId(){
			return $this->sessionId;
		}

		public function setSessionId($sessionId){
			$this->sessionId = $sessionId;
		}

		public function getClientId(){
			return $this->clientId;
		}

		public function setClientId($clientId){
			$this->clientId = $clientId;
		}

		public function getTradingUrl(){
			return $this->tradingUrl;
		}

		public function setTradingUrl($tradingUrl){
			$this->tradingUrl = $tradingUrl;
		}

		public function getIntAccount(){
			return $this->intAccount;
		}

		public function setIntAccount($intAccount){
			$this->intAccount = $intAccount;
		}
	}
