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
	$address = APP_ENV['prefix'] . $address;

	$results = getGeoInfo('geocode=' . urlencode($address) . '&results=10');

	$data = [];
	foreach ($results as $result) {
		$pos = $result['GeoObject']['Point']['pos'];

		$house = getGeoInfo('geocode=' . urlencode($pos) . '&kind=house')[0];
		$street = getGeoInfo('geocode=' . urlencode($pos) . '&kind=street')[0];
		$district = getGeoInfo('geocode=' . urlencode($pos) . '&kind=district')[0];

		$metro = [];	
		$metroStations = getGeoInfo('geocode=' . urlencode($pos) . '&kind=metro&results=5');
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
