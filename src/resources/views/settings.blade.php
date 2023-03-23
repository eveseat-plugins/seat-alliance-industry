@extends('web::layouts.grids.12')

@section('title', "Settings")
@section('page_header', "Settings")


@section('full')
    <div class="card">
        <div class="card-body">
            <h4 class="card-header">
                Settings
            </h4>
            <div class="card-text my-3 mx-3">
                <form action="{{ route("allianceindustry.saveSettings") }}" method="POST">
                    @csrf
                    <h5>Price Settings</h5>

                    <div class="form-group">
                        <label for="priceprovider">Default Price Provider</label>
                        <select id="priceprovider" class="form-control" name="defaultPriceProvider">
                            <option value="{{ $default_price_provider['class'] }}" selected>{{$default_price_provider['name']}}</option>
                        </select>
                        <small class="text-muted">The default price provider for orders.</small>
                    </div>

                    <div class="form-group">
                        <label for="mpp">Minimum Profit Percentage</label>
                        <input type="number" value="{{ $mpp }}" min="0" step="0.1" id="mpp" name="minimumprofitpercentage" class="form-control">
                        <small class="text-muted">To incentive production, the plugin applies this % of the item value on top of the price. While creating an order, you can always choose to give a higher profit, but to avoid players ripping off others, they can't go below this value.</small>
                    </div>

                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" id="allowPriceBelowAutomatic" class="form-check-input" name="allowPriceBelowAutomatic" @checked($allowPriceBelowAutomatic)>
                            <label for="allowPriceBelowAutomatic" class="form-check-label">Allow manual prices below automatic prices</label>
                        </div>
                        <small class="text-muted">To avoid scam orders, manual prices are ignored if they are for less than the automatic price.</small>
                    </div>

                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" id="allowPriceProviderSelection" class="form-check-input" name="allowPriceProviderSelection" @checked($allowPriceProviderSelection)>
                            <label for="allowPriceProviderSelection" class="form-check-label">Allows users to change the price provider when creating orders</label>
                        </div>
                        <small class="text-muted">To avoid scam orders, it is recommended to leave this option disabled.</small>
                    </div>

                    <h5>General Settings</h5>

                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" id="removeExpiredDeliveries" class="form-check-input" name="removeExpiredDeliveries" @checked($removeExpiredDeliveries)>
                            <label for="removeExpiredDeliveries" class="form-check-label">Remove expired deliveries</label>
                        </div>
                        <small class="text-muted">If a delivery isn't fulfilled when the order expires, the delivery will be deleted.</small>
                    </div>

                    <div class="form-group">
                        <label for="defaultLocation">Default Location</label>
                        <select id="defaultLocation" class="form-control" name="defaultLocation">
                            @foreach($stations as $station)
                                <option value="{{ $station->station_id }}" @selected($station->station_id == $defaultOrderLocation )>{{ $station->name }}</option>
                            @endforeach
                            @foreach($structures as $structure)
                                <option value="{{ $structure->structure_id }}" @selected($structure->structure_id == $defaultOrderLocation )>{{ $structure->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">
                            Controls the preselected location when creating new orders
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="pingRolesOrderCreation">Notifications: Roles to ping on order creation</label>
                        <input type="text" id="pingRolesOrderCreation" name="pingRolesOrderCreation" class="form-control" value="{{ $orderCreationPingRoles }}">
                        <small class="text-muted">Please copy&paste the discord role ids separated by a space. If you enable developer mode in your settings, you can get the IDs by clicking the role.</small>
                    </div>

                    <h5>Price Provider Settings</h5>

                    <p>Not all settings apply to all price providers.</p>

                    <div class="form-group">
                        <label for="market">Market Hub</label>
                        <select name="market" id="market" class="form-control">
                            <option value="jita" @selected($marketHub==="jita")>Jita</option>
                            <option value="perimeter" @selected($marketHub==="perimeter")>Perimeter</option>
                            <option value="universe" @selected($marketHub==="universe")>Universe</option>
                            <option value="amarr" @selected($marketHub==="amarr")>Amarr</option>
                            <option value="dodixie" @selected($marketHub==="dodixie")>Dodixie</option>
                            <option value="hek" @selected($marketHub==="hek")>Hek</option>
                            <option value="rens" @selected($marketHub==="rens")>Rens</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Price Type</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="pricetype" id="markettypesell" value="sell" @checked($priceType==="sell")>
                            <label class="form-check-label" for="markettypesell">
                                Sell Prices
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="pricetype" id="markettypebuy" value="buy" @checked($priceType==="buy")>
                            <label class="form-check-label" for="markettypebuy">
                                Buy Prices
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="industryTimeCost1">Manufacturing ISK time modifier</label>
                        <input type="number" id="industryTimeCost1" name="industryTimeCostManufacturingModifier" class="form-control" value="{{ $industryTimeCostManufacturingModifier }}" min="0">
                        <small class="text-muted">Manufacturing ISK modifier for the <i>Item Build Time</i> price provider in ISK/s.</small>
                    </div>

                    <div class="form-group">
                        <label for="industryTimeCost2">Reaction ISK time modifier</label>
                        <input type="number" id="industryTimeCost2" name="industryTimeCostReactionsModifier" class="form-control" value="{{ $industryTimeCostReactionsModifier }}" min="0">
                        <small class="text-muted">Reaction ISK modifier for the <i>Item Build Time</i> price provider in ISK/s.</small>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Update Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
@push("javascript")
    <script>
        $(document).ready( function () {
            $("#defaultLocation").select2()
            $('[data-toggle="tooltip"]').tooltip()
            $('.data-table').DataTable();
            $("#priceprovider").select2({
                ajax:{
                    url: "{{ route("treelib.priceProviderLookup") }}",
                    dataType: "json"
                }
            })
        });
    </script>
@endpush