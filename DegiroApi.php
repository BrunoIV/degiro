<?php
require_once(__DIR__ . '/Config.php');

class DegiroApi {
	private $config;

	CONST URL_LOGIN = 'https://trader.degiro.nl/login/secure/login';
	const URL_CONFIG = 'https://trader.degiro.nl/login/secure/config';

	public function __construct() {
		$this->config = new DegiroApiConfig();
		$this->login();
	}

	public function login() {

		//Try to get the userConfig using cookie file
		$success = $this->loadUserConfig();

		//If request fails, try to login with user/password and creates the cookie
		if(!$success) {
			$this->webLogin();
			$success = $this->loadUserConfig();
		}

		//If 2n request fails can't login. Check your credentials
		if(!$success) {
			throw new \Exception("Error al iniciar sesión", 1);
		}
	}

	/**
	 * Try to obtain the sessionId and clientId of the user using the cookie file
	 * @return true (OK) || false (KO)
	 */
	public function loadUserConfig(){

		//Don't try to get config if there's no cookie file
		if(!file_exists($this->config->getCookieFile())){
			return false;
		}

		$ch = curl_init();
		curl_setopt_array($ch, [
			CURLOPT_URL				=> self::URL_CONFIG,
			CURLOPT_RETURNTRANSFER	=> true,
			CURLOPT_COOKIEFILE		=> $this->config->getCookieFile()
		]);

		$result = json_decode(curl_exec($ch), true);

		//Test if response is a valid JSON
		if(!$this->isValidResult($result)) return false;

		if(!empty($result) && !empty($result['data'])) {
			$this->config->setSessionId($result['data']['sessionId']);
			$this->config->setClientId($result['data']['clientId']);
			$this->config->setTradingUrl($result['data']['tradingUrl']);
		}
		curl_close($ch);

		$paUrl = $result['data']['paUrl'] . 'client?sessionId=' . $this->config->getSessionId();

		$ch = curl_init();
		curl_setopt_array($ch, [
			CURLOPT_URL				=> $paUrl,
			CURLOPT_RETURNTRANSFER	=> true,
			CURLOPT_COOKIEFILE		=> $this->config->getCookieFile()
		]);
		$result = curl_exec($ch);

		$result = json_decode($result, true);
		curl_close($ch);

		//Test if response is a valid JSON
		if(!$this->isValidResult($result)) return false;

		$this->config->setIntAccount($result['data']['intAccount']);
		return true;
	}

	public function webLogin(){
		$cookieFile = $this->config->getCookieFile();
		$ch = curl_init();

		$params = '{"username":"' . $this->config->getUsername() . '","password":"' . $this->config->getPassword() . '","isPassCodeReset":false,"isRedirectToMobile":false,"queryParams":{}}';
		$headers[] = 'Content-Type: application/json;charset=UTF-8';

		curl_setopt_array($ch, [
			CURLOPT_URL				=> self::URL_LOGIN,
			CURLOPT_RETURNTRANSFER	=> true,
			CURLOPT_HTTPHEADER		=> $headers,
			CURLOPT_COOKIEJAR		=> $cookieFile, //save in file
			CURLOPT_POST			=> true,
			CURLOPT_POSTFIELDS		=> $params
		]);
		$result = curl_exec($ch);
		$info = curl_getinfo($ch);
		return ($info['http_code'] == 200);
	}

	/**
	 * Retrieve the active buy/sell orders
	 * https://trader.degiro.nl/trader/#/orders/open
	 */
	public function getOpenOrders(){
		$url = $this->config->getTradingUrl() . "v5/update/" . $this->config->getIntAccount() . ";jsessionid=" . $this->config->getSessionId() . "?orders=0";

		$ch = curl_init();
		curl_setopt_array($ch, [
			CURLOPT_URL				=> $url,
			CURLOPT_RETURNTRANSFER	=> true
		]);
		$result = curl_exec($ch);
		$result = json_decode($result,true);

		$orders = array();
		foreach($result["orders"]["value"] as $k => $order){
			foreach($order["value"] as $o){
				$name = $o["name"];
				$value = $o["value"];
				$orders["$k"]["$name"] = $value;
			}
		}
		return $orders;
	}


	/**
	 * Get a list with the ids of the products marked as favorite
	 * https://trader.degiro.nl/trader/#/favourites/1153120
	 */
	public function getFavoritesIds() {
		$url = "https://trader.degiro.nl/pa/secure/favourites/lists?" . $this->config->getIntAccount() . "&sessionId=" . $this->config->getSessionId();
		$ch = curl_init();

		curl_setopt_array($ch, [
			CURLOPT_URL		=> $url,
			CURLOPT_RETURNTRANSFER	=> true
		]);

		$result = curl_exec($ch);
		$result = json_decode($result,true);
		$this->isValidResult($result);
		curl_close($ch);

		//FIXME: In the position 1, 2, 3 is your own favs lists
		return $result['data'][0]['productIds'];
	}


	private function isValidResult($result) {
		if($result != json_decode(json_encode($result), true)){
			$this->debug("Invalid JSON");
			return false;
		}

		if(!empty($result['errors'])) {
			$errors = '';
			foreach ($result['errors'] as $error) {
				$errors .= $error['text'] . "\n";
			}

			if(!empty($errors)) {
				$this->debug($errors);
				return false;
			}
		}

		if(empty($result) || empty($result['data'])) {
			$this->debug("Empty result");
			return false;
		}

		return true;
	}

	private function debug($str) {
		if($this->config->getDebug() === true) {
			echo $str . "\n";
		}
	}
}
