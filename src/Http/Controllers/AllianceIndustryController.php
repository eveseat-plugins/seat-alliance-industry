<?php

namespace RecursiveTree\Seat\AllianceIndustry\Http\Controllers;

use RecursiveTree\Seat\AllianceIndustry\Helpers\SettingHelper;
use RecursiveTree\Seat\AllianceIndustry\Jobs\SendOrderNotifications;
use RecursiveTree\Seat\AllianceIndustry\Models\Order;
use RecursiveTree\Seat\AllianceIndustry\Models\Delivery;
use RecursiveTree\Seat\AllianceIndustry\Prices\EvePraisalPriceProvider;
use RecursiveTree\Seat\TreeLib\Helpers\ItemList;
use RecursiveTree\Seat\TreeLib\Helpers\Parser;
use RecursiveTree\Seat\TreeLib\Helpers\SimpleItem;
use Seat\Eveapi\Models\Universe\UniverseStation;
use Seat\Eveapi\Models\Universe\UniverseStructure;
use Seat\Web\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Gate;


class AllianceIndustryController extends Controller
{
    public function orders(){

        $orders = Order::with("deliveries")->where("completed",false)->get()->filter(function ($order){
            return $order->assignedQuantity() < $order->quantity;
        });

        $personalOrders = Order::where("user_id",auth()->user()->id)->get();

        return view("allianceindustry::orders", compact("orders","personalOrders"));
    }

    public function createOrder(){
        $stations = UniverseStation::all();
        $structures = UniverseStructure::all();

        $mpp = SettingHelper::getSetting("minimumProfitPercentage",2.5);

        $location_id = 60003760;

        return view("allianceindustry::createOrder",compact("stations", "structures","mpp","location_id"));
    }

    public function submitOrder(Request $request){
        $request->validate([
            "items"=>"required|string",
            "profit"=>"required|numeric|min:0",
            "days"=>"required|integer|min:1",
            "location"=>"required|integer",
            "addProfitToManualPrices"=>"nullable|in:on",
            "addToSeatInventory"=>"nullable|in:on"
        ]);

        $mpp = SettingHelper::getSetting("minimumProfitPercentage",2.5);
        if($request->profit < $mpp){
            $request->session()->flash("error","The minimal profit can't be lower than $mpp%");
            return redirect()->route("allianceindustry.createOrder");
        }

        if(!(UniverseStructure::where("structure_id",$request->location)->exists()||UniverseStation::where("station_id",$request->location)->exists())) {
            $request->session()->flash("error","Could not find structure/station.");
            return redirect()->route("allianceindustry.orders");
        }

        //parse items
        $parsed_multibuy = Parser::parseFitOrMultiBuy($request->items, true);

        //check item count, don't request prices without any items
        if($parsed_multibuy->items->count()<1){
            $request->session()->flash("warning","You need to add at least 1 item to the delivery");
            return redirect()->route("allianceindustry.orders");
        }

        $appraised_items = EvePraisalPriceProvider::getPrices($parsed_multibuy->items);

        $now = now();
        $produce_until = now()->addDays($request->days);
        $priceType = SettingHelper::getSetting("priceType","buy");
        $price_modifier = (1+(floatval($request->profit)/100.0));
        $allowManualPriceBelowAutomatic = SettingHelper::getSetting("allowPriceBelowAutomatic",false);
        $addToSeatInventory = $request->addToSeatInventory !== null;


        foreach ($appraised_items as $item){

            $has_manual_price = false;

            $order = new Order();

            $unit_price = $item->getUnitPrice();

            $manual_price = $manual_prices[$item->getTypeId()] ?? null;
            if(is_numeric($manual_price)){
                $manual_price = intval($manual_price);
                //only allow manual prices if they are above real prices, or lower prices are allowed
                if($manual_price > $unit_price || $allowManualPriceBelowAutomatic){
                    $unit_price = $manual_price;
                    $has_manual_price = true;
                }
            }


            if(!($has_manual_price && !$request->addProfitToManualPrices)){
                $unit_price = $unit_price * $price_modifier;
            }

            $order->type_id = $item->getTypeId();
            $order->quantity = $item->getAmount();
            $order->user_id = auth()->user()->id;
            $order->unit_price = $unit_price;
            $order->location_id = $request->location;
            $order->created_at = $now;
            $order->produce_until = $produce_until;
            $order->add_seat_inventory = $addToSeatInventory;
            $order->profit = floatval($request->profit);

            $order->save();
        }

        //send notification that orders have been put up. We don't do it in an observer so it only gets triggered once
        SendOrderNotifications::dispatch()->onQueue('notifications');

        $request->session()->flash("success","Successfully added new order");
        return redirect()->route("allianceindustry.orders");
    }

    public function updateOrderPrice(Request $request){
        $request->validate([
            "order"=>"required|integer"
        ]);
        $order = Order::find($request->order);
        if(!$order){
            $request->session()->flash("error","The order wasn't found");
            return redirect()->back();
        }

        Gate::authorize("allianceindustry.same-user",$order->user_id);

        $profit_multiplier = 1+($order->profit/100.0);

        $prices = EvePraisalPriceProvider::getPrices(new ItemList([new SimpleItem($order->type_id,$order->quantity)]));
        $price = $prices[0]->getUnitPrice() * $profit_multiplier;

        $order->unit_price = $price;
        $order->save();

        $request->session()->flash("success","Updated the price!");
        return redirect()->back();
    }

    public function orderDetails($id, Request $request){
        $order = Order::with("deliveries")->find($id);

        if(!$order){
            $request->session()->flash("error","Could not find order");
            return redirect()->route("allianceindustry.orders");
        }

        return view("allianceindustry::orderDetails",compact("order"));
    }

    public function addDelivery($orderId, Request $request){
        $request->validate([
            "quantity"=>"required|integer"
        ]);

        $order = Order::find($orderId);
        if(!$order){
            $request->session()->flash("error","Could not find order");
            return redirect()->route("allianceindustry.orders");
        }

        //quantity > 0
        if($request->quantity < 1){
            $request->session()->flash("error","Quantity must be larger than 0");
            return redirect()->route("allianceindustry.orders");
        }

        //quantity <= max remaining
        if($request->quantity > $order->quantity - $order->assignedQuantity()){
            $request->session()->flash("error","Quantity must be smaller than the remaining quantity");
            return redirect()->route("allianceindustry.orders");
        }

        $delivery = new Delivery();
        $delivery->order_id = $order->id;
        $delivery->user_id = auth()->user()->id;
        $delivery->quantity = $request->quantity;
        $delivery->completed = false;
        $delivery->accepted = now();

        $delivery->save();

        $request->session()->flash("success","Successfully added new delivery");
        return redirect()->back();
    }

    public function setDeliveryState($orderId,Request $request){
        $request->validate([
            "delivery"=>"required|integer",
            "completed"=>"required|boolean"
        ]);

        $delivery = Delivery::find($request->delivery);

        Gate::authorize("allianceindustry.same-user",$delivery->user_id);

        if($request->completed){
            $delivery->completed_at = now();
            $delivery->completed = true;
        } else {
            $delivery->completed_at = null;
            $delivery->completed = false;
        }
        $delivery->save();

        return redirect()->back();
    }

    public function deleteDelivery($orderId, Request $request){
        $request->validate([
            "delivery"=>"required|integer",
        ]);

        $delivery = Delivery::find($request->delivery);

        if($delivery) {
            Gate::authorize("allianceindustry.same-user", $delivery->user_id);

            if($delivery->completed){
                Gate::authorize("allianceindustry.admin");
            }

            $delivery->delete();

            $request->session()->flash("success","Successfully removed delivery");
        } else {
            $request->session()->flash("error","Could not find delivery");
        }

        return redirect()->back();
    }

    public function deliveries(){
        $user_id = auth()->user()->id;

        $deliveries = Delivery::with("order")->where("user_id",$user_id)->get();

        return view("allianceindustry::deliveries",compact("deliveries"));
    }

    public function deleteOrder(Request $request){
        $request->validate([
            "order"=>"required|integer"
        ]);

        $order = Order::find($request->order);
        if(!$order){
            $request->session()->flash("error","Could not find order");
            return redirect()->route("allianceindustry.orders");
        }

        Gate::authorize("allianceindustry.same-user",$order->user_id);

        if(!$order->deliveries->isEmpty() && !$order->completed && !auth()->user()->can("allianceindustry.admin")){
            $request->session()->flash("error","You cannot delete orders that people are currently manufacturing!");
            return redirect()->route("allianceindustry.orders");
        }

        $order->delete();

        $request->session()->flash("success","Successfully closed order!");
        return redirect()->route("allianceindustry.orders");
    }

    public function about(){
        return view("allianceindustry::about");
    }

    public function settings(){
        $marketHub = SettingHelper::getSetting("marketHub","jita");
        $mpp = SettingHelper::getSetting("minimumProfitPercentage",2.5);
        $priceType = SettingHelper::getSetting("priceType","buy");
        $orderCreationPingRoles =  implode(" ", SettingHelper::getSetting("orderCreationPingRoles",[]));
        $allowPriceBelowAutomatic = SettingHelper::getSetting("allowPriceBelowAutomatic",false);

        return view("allianceindustry::settings", compact("marketHub","mpp","priceType", "orderCreationPingRoles","allowPriceBelowAutomatic"));
    }

    public function saveSettings(Request $request){
        $request->validate([
            "market"=>"required|in:jita,perimeter,universe,amarr,dodixie,hek,rens",
            "pricetype"=>"required|in:sell,buy",
            "minimumprofitpercentage"=>"required|numeric|min:0",
            "pingRolesOrderCreation"=>"string|nullable",
            "allowPriceBelowAutomatic"=>"nullable|in:on"
        ]);

        $roles = [];
        if($request->pingRolesOrderCreation){
            //parse roles
            $roles = preg_replace('~\R~u', "\n", $request->pingRolesOrderCreation);
            $matches = [];
            preg_match_all("/\d+/m",$roles, $matches);
            $roles = $matches[0];
        }


        SettingHelper::setSetting("marketHub",$request->market);
        SettingHelper::setSetting("minimumProfitPercentage", floatval($request->minimumprofitpercentage));
        SettingHelper::setSetting("priceType",$request->pricetype);
        SettingHelper::setSetting("orderCreationPingRoles",$roles);
        SettingHelper::setSetting("allowPriceBelowAutomatic",boolval($request->allowPriceBelowAutomatic));

        $request->session()->flash("success","Successfully saved settings");
        return redirect()->route("allianceindustry.settings");
    }

    public function deleteCompletedOrders(){
        $orders = Order::where("user_id",auth()->user()->id)->where("completed",true)->get();
        foreach ($orders as $order){
            $order->delete();
        }

        return redirect()->back();
    }
}