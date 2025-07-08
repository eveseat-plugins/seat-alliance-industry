@extends('web::layouts.grids.12')

@section('title', "Order Marketplace")
@section('page_header', "Order Marketplace")


@section('full')
    <div class="card">
        <div class="card-body">
            <h5 class="card-header d-flex flex-row align-items-baseline">
                Open Orders
                @can("allianceindustry.create_orders")
                    <a href="{{ route("allianceindustry.createOrder") }}" class="btn btn-primary ml-auto">Create Order</a>
                @endcan
            </h5>
            <div class="card-text pt-3">
                @include("allianceindustry::partials.orderTable",["orders"=>$orders])
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-header d-flex flex-row align-items-baseline">
                Your Orders

                @if($personalOrders->where("completed",true)->isNotEmpty())
                    <form action="{{ route("allianceindustry.deleteCompletedOrders") }}" method="POST" class="ml-auto">
                        @csrf
                        <button class="btn btn-danger">Close all completed Orders</button>
                    </form>
                @endif
            </h5>
            <div class="card-text pt-3">
                @include("allianceindustry::partials.orderTable",["orders"=>$personalOrders])
            </div>
        </div>
    </div>

    @can("allianceindustry.view_all_orders")
        <div class="card">
            <div class="card-body">
                <h5 class="card-header d-flex flex-row align-items-baseline">
                    All Orders
                </h5>
                <div class="card-text pt-3">
                    @include("allianceindustry::partials.orderTable",["orders"=>$allOrders])
                </div>
            </div>
        </div>
    @endcan
@stop

@push("javascript")
    <script>
        $(document).ready( function () {
            $('[data-toggle="tooltip"]').tooltip()
            $('.data-table').DataTable({
                stateSave: true,
            });
        });
    </script>
@endpush