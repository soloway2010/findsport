<?php

require 'config.php';

/**
 * Получает информацию об объектах на карте с помощью сервиса Yandex Maps
 *
 * @param string $query Запрос к сервису
 * @return array Полученная информация
 */
function getGeoInfo($query)
{
	$apiUrl = APP_ENV['url'] . 'format=json&apikey=' . APP_ENV['key'] . '&' . $query;

	try {
		$result = file_get_contents($apiUrl);
	} catch (Exception $e) {
		$result = false;
	}

	if ($result === false) {
		return [];
	}

	return json_decode($result, true)['response']['GeoObjectCollection']['featureMember'];
}

/**
 * По адресу возвращает информацию по дому, улице, району и 5 ближайшим станициям метро
 *
 * @param string $address Адрес
 * @return array Информация в формате [{house: , street:, district: , metro:}, ...]
 */
function getAddressInfo($address)
{
	$address = urlencode(APP_ENV['prefix'] . $address);

	$results = getGeoInfo('geocode=' . $address . '&results=10');

	$data = [];
	foreach ($results as $result) {
		$pos = urlencode($result['GeoObject']['Point']['pos']);

		$house = getGeoInfo('geocode=' . $pos . '&kind=house&results=1')[0];
		$street = getGeoInfo('geocode=' . $pos . '&kind=street&results=1')[0];
		$district = getGeoInfo('geocode=' . $pos . '&kind=district&results=1')[0];

		$metro = [];	
		$metroStations = getGeoInfo('geocode=' . $pos . '&kind=metro&results=5');
		foreach ($metroStations as $metroStation) {
			$metro[] = $metroStation['GeoObject']['name'];
		}

		$data[] = [
			'house' => $house['GeoObject']['metaDataProperty']['GeocoderMetaData']['AddressDetails']['Country']['AddressLine'],
			'street' => $street['GeoObject']['metaDataProperty']['GeocoderMetaData']['AddressDetails']['Country']['AddressLine'],
			'district' => $district['GeoObject']['metaDataProperty']['GeocoderMetaData']['AddressDetails']['Country']['AddressLine'],
			'metro' => $metro,
		];
	}

	return $data;
}

print_r(json_encode(getAddressInfo($_GET['address'])));
