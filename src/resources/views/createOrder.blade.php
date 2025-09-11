@extends('web::layouts.grids.12')

@section('title', "Create Order")
@section('page_header', "Create Order")


@section('full')
    <div class="card">
        <div class="card-body">
            <h5 class="card-header d-flex flex-row align-items-baseline">
                Create Order
                <a href="{{ route("allianceindustry.orders") }}" class="btn btn-danger ml-auto">Cancel</a>
            </h5>
            <p class="card-text">

            <form action="{{ route("allianceindustry.submitOrder") }}" method="POST" id="orderForm">
                @csrf

                <div class="form-group">
                    <label for="itemsTextarea">Items</label>
                    <textarea
                            id="itemsTextarea"
                            name="items"
                            class="form-control"
                            placeholder="{{"MULTIBUY:\nTristan 100\nOmen 100\nTritanium 30000\n\nFITTINGS:\n[Pacifier, 2022 Scanner]\n\nCo-Processor II\nCo-Processor II\n\nMultispectrum Shield Hardener II\nMultispectrum Shield Hardener II\n\nSmall Tractor Beam II\nSmall Tractor Beam II"}}"
                            rows="20">{{ $multibuy ?? "" }}</textarea>

                    <div class="form-check">
                        <input type="checkbox" id="splitOrders" class="form-check-input" name="splitOrders" checked>
                        <label for="splitOrders" class="form-check-label">Split items into separate orders</label>
                    </div>
                </div>

                @if($allowPriceProviderSelection)
                    <div class="form-group">
                        <label for="priceprovider">Price Provider</label>
                        @include("pricescore::utils.instance_selector",["id"=>"priceprovider","name"=>"priceprovider","instance_id"=>$default_price_provider])
                        <small class="text-muted">The source of the prices used to calculate the order price.</small>
                    </div>
                @endif

                <div class="form-group">
                    <label for="profit">Reward %</label>
                    <input type="number" id="profit" class="form-control" value="{{ $mpp }}" min="{{ $mpp }}" step="0.1"
                           name="profit">
                    <small class="text-muted">The minimal profit is {{$mpp}}%</small>

                    <div class="form-check">
                        <input type="checkbox" id="addProfitToManualPrices" class="form-check-input" checked
                               name="addProfitToManualPrices">
                        <label for="addProfitToManualPrices" class="form-check-label">Add Reward to Manual
                            Prices</label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="days">Days to complete</label>
                    <input type="number" id="days" class="form-control" name="days" min="1" step="1" value="30">
                </div>

                <div class="form-group">
                    <label for="location">Location</label>
                    <select id="location" class="form-control" name="location">
                        @foreach($stations as $station)
                            <option value="{{ $station->station_id }}" @selected($station->station_id ==
                                $location_id)>{{ $station->name }}</option>
                        @endforeach
                        @foreach($structures as $structure)
                            <option value="{{ $structure->structure_id }}" @selected($structure->structure_id ==
                                $location_id)>{{ $structure->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="priority">Priority</label>
                    <select id="priority" class="form-control" name="priority">
                        @foreach(\RecursiveTree\Seat\TreeLib\Helpers\PrioritySystem::getPriorityData() as $priority=>$data)
                            <option value="{{$priority}}">{{$data["name"]}}</option>
                        @endforeach
                    </select>
                </div>

                @if(\RecursiveTree\Seat\TreeLib\Helpers\SeatInventoryPluginHelper::pluginIsAvailable())
                    @can("inventory.edit_inventory")
                    <div class="form-group">
                        <label for="addToSeatInventory">Seat-Inventory</label>
                        <div class="form-check">
                            <input type="checkbox" id="addToSeatInventory" class="form-check-input" checked
                                   name="addToSeatInventory">
                            <label for="addToSeatInventory" class="form-check-label">Add as source to
                                seat-inventory</label>
                        </div>
                        <small class="text-muted">
                            As soon as a delivery for this order is created, a item source will be added to
                            seat-inventory.
                            Once the delivery is marked as completed, the source will be removed.
                            The source will be added to the <u>first</u> workspace containing
                            <code>add2allianceindustry</code> at any position in it's name.
                            You can rename workspaces <a href="{{ route("inventory.settings") }}">here.</a>
                        </small>
                    </div>
                    @endcan
                @endif

                <div class="form-group">
                    <label for="addToSeatInventory">Notifications</label>
                    <div class="form-check">
                        <input type="checkbox" id="pingDiscord" class="form-check-input" checked
                               name="pingDiscord">
                        <label for="pingDiscord" class="form-check-label">Allow pings when sending discord notifications.</label>
                    </div>
                </div>

                @can("allianceindustry.create_repeating_orders")
                    <div class="form-group">
                        <label for="repetition">Repetition</label>
                        <select id="repetition" name="repetition" class="form-control">
                            <option value="0" selected>Never</option>
                            <option value="7">Weekly</option>
                            <option value="14">Every 2 weeks</option>
                            <option value="28">Monthly</option>
                        </select>
                    </div>
                @endcan

                <button type="submit" class="btn btn-primary">Add Order</button>
            </form>

            </p>
        </div>
    </div>
@stop
@push("javascript")
    <script>
        $(document).ready(function () {
            $("#location").select2()
            $('[data-toggle="tooltip"]').tooltip()
            $('.data-table').DataTable();
        });
    </script>

@endpush