<?php

namespace App\Helpers;

use GooglePlaces;

class GooglePlacesHelper
{
    /**
     * Returns nearby places.
     * @param  float   $lat     Latitude
     * @param  float   $lng     Longitude
     * @param  array   $options Request options.
     * @param  integer $radius  Radius in meters, default: 1.6 km or 1 mile
     * @param  integer $limit   Should be divisible by 20 as Google Places API returns pages with 20 items, default: 20 items
     * @return \Illuminate\Support\Collection
     */
    public static function getNearbyPlaces($lat, $lng, $options, $radius = 1609, $limit = 20)
    {
        $places = collect();

        $options['pagetoken'] = $options['pagetoken'] ?? null;

        $requestsNeeded = round($limit / 20);

        for ($i = 1; $i <= $requestsNeeded; $i++) {
            $newPlaces = GooglePlaces::nearbySearch($lat . ',' . $lng, $radius, $options);

            $options['pagetoken'] = $newPlaces->get('next_page_token');

            $places = $places->merge($newPlaces->get('results', []));

            if ($i !== $requestsNeeded) {
                // Google Places API requires 2 second delay between requests
                // This is due to a bug on Google's side
                sleep(2);
            }
        }

        return $places;
    }

    /**
     * Returns place details with all possible fields.
     * @param  string $placeId Google Place ID
     * @return array
     */
    public static function getPlace($placeId)
    {
        $place = GooglePlaces::placeDetails($placeId);

        return $place;
    }

    /**
     * Returns array of all Google Places place types that can be used for filtering in Places API.
     * See more at: https://developers.google.com/places/supported_types
     * Last updated: 2019-03-08
     *
     * @return array
     */
    public static function getFilterablePlaceTypes()
    {
        return [
            'accounting',
            'airport',
            'amusement_park',
            'aquarium',
            'art_gallery',
            'atm',
            'bakery',
            'bank',
            'bar',
            'beauty_salon',
            'bicycle_store',
            'book_store',
            'bowling_alley',
            'bus_station',
            'cafe',
            'campground',
            'car_dealer',
            'car_rental',
            'car_repair',
            'car_wash',
            'casino',
            'cemetery',
            'church',
            'city_hall',
            'clothing_store',
            'convenience_store',
            'courthouse',
            'dentist',
            'department_store',
            'doctor',
            'electrician',
            'electronics_store',
            'embassy',
            'fire_station',
            'florist',
            'funeral_home',
            'furniture_store',
            'gas_station',
            'gym',
            'hair_care',
            'hardware_store',
            'hindu_temple',
            'home_goods_store',
            'hospital',
            'insurance_agency',
            'jewelry_store',
            'laundry',
            'lawyer',
            'library',
            'liquor_store',
            'local_government_office',
            'locksmith',
            'lodging',
            'meal_delivery',
            'meal_takeaway',
            'mosque',
            'movie_rental',
            'movie_theater',
            'moving_company',
            'museum',
            'night_club',
            'painter',
            'park',
            'parking',
            'pet_store',
            'pharmacy',
            'physiotherapist',
            'plumber',
            'police',
            'post_office',
            'real_estate_agency',
            'restaurant',
            'roofing_contractor',
            'rv_park',
            'school',
            'shoe_store',
            'shopping_mall',
            'spa',
            'stadium',
            'storage',
            'store',
            'subway_station',
            'supermarket',
            'synagogue',
            'taxi_stand',
            'train_station',
            'transit_station',
            'travel_agency',
            'veterinary_care',
            'zoo',
        ];
    }

    /**
     * Returns array of additional Google Places place types that can be returned after searching via Places API.
     * See more at: https://developers.google.com/places/supported_types
     * Last updated: 2019-03-08
     *
     * @return array
     */
    public static function getAdditionalPlaceTypes()
    {
        return [
            'administrative_area_level_1',
            'administrative_area_level_2',
            'administrative_area_level_3',
            'administrative_area_level_4',
            'administrative_area_level_5',
            'colloquial_area',
            'country',
            'establishment',
            'finance',
            'floor',
            'food',
            'general_contractor',
            'geocode',
            'health',
            'intersection',
            'locality',
            'natural_feature',
            'neighborhood',
            'place_of_worship',
            'political',
            'point_of_interest',
            'post_box',
            'postal_code',
            'postal_code_prefix',
            'postal_code_suffix',
            'postal_town',
            'premise',
            'room',
            'route',
            'street_address',
            'street_number',
            'sublocality',
            'sublocality_level_4',
            'sublocality_level_5',
            'sublocality_level_2',
            'sublocality_level_3',
            'sublocality_level_1',
            'subpremise',
        ];
    }

    /**
     * Returns array of all Google Places place types that can be used for filtering in Places API but adjusted for select Input.
     *
     * @return array
     */
    public static function getFilterablePlaceTypesForSelect()
    {
        $types = collect(self::getFilterablePlaceTypes());

        $types = $types->mapWithKeys(function ($type) {
            $typeFormatted = ucfirst($type);
            $typeFormatted = str_replace('_', ' ', $typeFormatted);

            return [$type => $typeFormatted];
        });

        return $types->toArray();
    }
}
