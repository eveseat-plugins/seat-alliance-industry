<?php

namespace RecursiveTree\Seat\AllianceIndustry\Notifications;

use RecursiveTree\Seat\AllianceIndustry\AllianceIndustrySettings;
use RecursiveTree\Seat\AllianceIndustry\Models\OrderItem;
use RecursiveTree\Seat\TreeLib\Helpers\PrioritySystem;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Seat\Notifications\Notifications\AbstractSlackNotification;

class OrderNotificationSlack extends AbstractSlackNotification implements ShouldQueue
{
    use SerializesModels;

    private $orders;

    public function __construct($orders){
        $this->orders = $orders;
    }

    public function populateMessage(SlackMessage $message, $notifiable)
    {
        $orders = $this->orders;

        $pings = implode(" ", array_map(function ($role){
            return "<@&$role>";
        }, AllianceIndustrySettings::$ORDER_CREATION_PING_ROLES->get([])));

        $message
            ->success()
            ->from("SeAT Alliance Industry");

        if($pings !== "") {
            $message->content($pings);
        }

        $message->attachment(function ($attachment) use ($orders) {
                $attachment
                    ->title("New SeAT Alliance Industry Orders:",  route("allianceindustry.orders"));
                foreach ($orders as $order){
                    $item_text = OrderItem::formatOrderItemsList($order, true);
                    $location = $order->location()->name;
                    $price = number_metric($order->price);
                    $totalPrice = number_metric($order->price * $order->quantity);
                    $priority = PrioritySystem::getPriorityData()[$order->priority]["name"] ?? trans("seat.web.unknown");

                    $attachment->field(function ($field) use ($item_text, $priority, $totalPrice, $price, $location) {
                        $field
                            ->long()
                            ->title($item_text)
                            ->content("Priority: $priority | $price ISK/unit |  $totalPrice ISK total  | $location");
                    });
                }
            });
        return $message;
    }
}