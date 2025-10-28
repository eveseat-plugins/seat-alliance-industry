<table class="data-table table table-striped table-hover">
    <thead>
    <tr>
        <th>Tags</th>
        <th>Order</th>
        <th>Quantity</th>
        <th>Completed</th>
        <th>Price over Jita</th>
        <th>Price</th>
        <th>Total Price</th>
        <th>Location</th>
        <th>Created</th>
        <th>Until</th>
        <th>Character</th>
    </tr>
    </thead>
    <tbody>
    @foreach($orders as $order)
        <tr>
            <td data-sort="{{ $order->priority }}" data-filter="_">
                @include("treelib::partials.priority",["priority"=>$order->priority])
                @if($order->is_repeating)
                    <span class="badge badge-secondary">Repeating</span>
                @endif
            </td>
            <td>
                <a href="{{ route("allianceindustry.orderDetails",$order->id) }}">{{ \RecursiveTree\Seat\AllianceIndustry\Models\OrderItem::formatOrderItemsList($order) }}</a>
            </td>
            <td data-sort="{{ $order->quantity - $order->assignedQuantity() }}" data-filter="_">
                {{number($order->assignedQuantity(),0)}}/{{ number($order->quantity,0) }}
            </td>
            <td data-sort="{{ $order->completed_at?carbon($order->completed_at)->timestamp:0 }}" data-filter="_">
                @include("allianceindustry::partials.boolean",["value"=>$order->completed])
                @if($order->completed_at)
                    @include("allianceindustry::partials.time",["date"=>$order->completed_at])
                @endif
            </td>
            <td data-sort="{{(float)$order->price / $order->getMarketPrice()}}" data-filter="_">
                {{ number((float)$order->price / $order->getMarketPrice() * 100 - 100) }} %
            </td>
            <td data-sort="{{$order->price}}" data-filter="_">
                {{ number($order->price) }} ISK
            </td>
            <td data-sort="{{ $order->price * $order->quantity }}" data-filter="_">
                {{ number($order->price * $order->quantity) }} ISK
            </td>
            <td data-sort="{{ $order->location_id }}" data-filter="{{ $order->location()->name }}">
                @include("allianceindustry::partials.longTextTooltip",["text"=>$order->location()->name,"length"=>25])
            </td>
            <td data-sort="{{ carbon($order->created_at)->timestamp }}" data-filter="_">
                @include("allianceindustry::partials.time",["date"=>$order->created_at])
            </td>
            <td data-sort="{{ carbon($order->produce_until)->timestamp }}" data-filter="_">
                @include("allianceindustry::partials.time",["date"=>$order->produce_until])
            </td>
            <td data-sort="{{ $order->user->id ?? 0 }}" data-filter="{{ $order->user->main_character->name ?? "deleted user"}}">
                @include("web::partials.character",["character"=>$order->user->main_character ?? null])
            </td>
        </tr>
    @endforeach
    </tbody>
</table>