<?php

namespace App\Services;

use App\Enums\ELanguage;
use App\Repositories\CountryRepository;

class PriceService {

	private CountryRepository $countryRepository;

    public function __construct(CountryRepository $countryRepository) {
		$this->countryRepository = $countryRepository;
    }

	public function getPostPrice($post) {
		$result = array();
		$countryList = $this->countryRepository->getCountries();
		foreach ($countryList as $country) {
			$postPrice = $country->language_code == ELanguage::EN || $post->country->id == $country->id ? $post->prices->where('country_id', $country->id)->first() : null;

			if (!$post->prices->isEmpty() && $postPrice) {
				array_push($result, [
					'price' => $postPrice->price,
					'priceStr' => number_format($postPrice->price, $country->language_code === ELanguage::VI ? 0 : 2),
					'currency_sign' => $country->currency_sign,
					'country_id' => $country->id,
				]) ;
			} else if ($post->prices->isEmpty()) {
				array_push($result, [
					'price' => '',
					'priceStr' => '',
					'currency_sign' => $country->currency_sign,
					'country_id' => $country->id,
				]) ;
			}
		}
		return $result;
	}
}
