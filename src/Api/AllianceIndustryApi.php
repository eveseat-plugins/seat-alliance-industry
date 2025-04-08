<?php

namespace RecursiveTree\Seat\AllianceIndustry\Api;

use RecursiveTree\Seat\AllianceIndustry\AllianceIndustrySettings;
use Seat\Eveapi\Models\Universe\UniverseStation;
use Seat\Eveapi\Models\Universe\UniverseStructure;

class AllianceIndustryApi
{
    public static function create_orders($data){
        $location_id = $data["location"] ?? AllianceIndustrySettings::$DEFAULT_ORDER_LOCATION->get(60003760);;

        $multibuy = $data["asEveText"] ?? $data["items"]->toMultibuy();

        $stations = UniverseStation::all();
        $structures = UniverseStructure::all();
        $mpp = AllianceIndustrySettings::$MINIMUM_PROFIT_PERCENTAGE->get(2.5);

        $default_price_provider = AllianceIndustrySettings::$DEFAULT_PRICE_PROVIDER->get();

        $allowPriceProviderSelection = AllianceIndustrySettings::$ALLOW_PRICE_PROVIDER_SELECTION->get(false);

        return view("allianceindustry::createOrder",compact("stations","default_price_provider", "structures","mpp","location_id","multibuy","allowPriceProviderSelection"));
    }
}