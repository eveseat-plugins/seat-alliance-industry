<?php

namespace RecursiveTree\Seat\AllianceIndustry\Models;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Market\Price;
use Seat\Eveapi\Models\Sde\InvType;
use Seat\Eveapi\Models\Universe\UniverseStation;
use Seat\Eveapi\Models\Universe\UniverseStructure;
use Seat\Web\Models\User;


class Order extends Model
{
    public $timestamps = false;

    protected $table = 'seat_alliance_industry_orders';

    public function deliveries(){
        return $this->hasMany(Delivery::class,"order_id","id");
    }

    public function items(){
        return $this->hasMany(OrderItem::class,"order_id","id");
    }

    public function user(){
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function station()
    {
        return $this->hasOne(UniverseStation::class, 'station_id', 'location_id');
    }

    public function structure()
    {
        return $this->hasOne(UniverseStructure::class, 'structure_id', 'location_id');
    }


    public function market_price()
    {
        return $this
            ->hasManyThrough(Price::class,OrderItem::class, "order_id", "type_id", null, "type_id")
            ->select("market_prices.*","seat_alliance_industry_order_items.quantity");
    }

    public function getMarketPrice()
    {
        return max($this->market_price->sum(function ($item){
            return $item->quantity * $item->sell_price;
        }),1.0);
    }

    public function location(){
        return $this->station ?: $this->structure;
    }

    public function assignedQuantity(){
        return $this->deliveries->sum("quantity");
    }

    public function hasPendingDeliveries(){
        return $this->deliveries()->where("completed",false)->exists();
    }
}