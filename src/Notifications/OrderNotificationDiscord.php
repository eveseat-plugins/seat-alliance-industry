<?php

namespace RecursiveTree\Seat\AllianceIndustry\Notifications;

use RecursiveTree\Seat\AllianceIndustry\AllianceIndustrySettings;
use RecursiveTree\Seat\AllianceIndustry\Models\OrderItem;
use RecursiveTree\Seat\TreeLib\Helpers\PrioritySystem;
use Seat\Notifications\Notifications\AbstractDiscordNotification;
use Seat\Notifications\Notifications\AbstractNotification;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Seat\Notifications\Services\Discord\Messages\DiscordEmbed;
use Seat\Notifications\Services\Discord\Messages\DiscordEmbedField;
use Seat\Notifications\Services\Discord\Messages\DiscordMessage;

class OrderNotificationDiscord extends AbstractDiscordNotification implements ShouldQueue
{
    use SerializesModels;

    private $orders;

    public function __construct($orders){
        $this->orders = $orders;
    }


    private function eve_number_metric(int $number): string
    {
        $str = number_metric($number);
        return str_replace("G","B", $str);
    }


    protected function populateMessage(DiscordMessage $message, $notifiable)
    {
        $message->content(sprintf("New seat-alliance-industry orders are available! %s",route("allianceindustry.orders")));

        $displayed = $this->orders;
        $showMoreLink = false;
        if($this->orders->count() > 10){
            $showMoreLink = true;
            $displayed = $this->orders->take(9);
        }

        $message->embed(function (DiscordEmbed $embed) use ($showMoreLink, $displayed) {
            foreach ($displayed as $order){
                $item_text = OrderItem::formatOrderItemsList($order, true);
                $location = $order->location()->name;
                $price = $this->eve_number_metric($order->price);
                $totalPrice = $this->eve_number_metric($order->price * $order->quantity);
                $priority = PrioritySystem::getPriorityData()[$order->priority]["name"] ?? trans("seat.web.unknown");

                $embed->field(function (DiscordEmbedField $field) use ($totalPrice, $price, $priority, $item_text, $location) {
                    $field->name($item_text);
                    $field->value("Priority: $priority | $price ISK/unit |  $totalPrice ISK total  | $location");
                    $field->long();
                });
            }

            if($showMoreLink){
                $embed->field(function (DiscordEmbedField $field) {
                    $field->name("More Items");
                    $field->value(route("allianceindustry.orders"));
                    $field->long();
                });
            }
        });
    }

    protected function suppressMentions(): bool
    {


        return !$this->orders->some(function($item){
            return !$item->suppressPing;
        });
    }
}