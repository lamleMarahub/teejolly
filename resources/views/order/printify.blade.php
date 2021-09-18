@extends('layouts.app')

@section('content')
<script>
$(document).ready(function(){
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    $('select').on('change', function() {
        alert( this.value );
    });
    
});
</script>
<div class="container" style="border:1px solid #cecece; padding:20px">
    <h3>Order Id: #{{$order->amz_order_id}}</h3>
    <table class="table table-borderless table-hover">
        @foreach($orderItems as $item)
        <tr>
            <td><img src="{{str_replace("._SCLZZZZZZZ__SX55_", "", $item->thumbnail)}}" alt="{{$item->product_name}}" class="img-thumbnail" style="max-width: 150px; max-height: 150px;"></td>
            <td><h3 style="padding-top:10px">{{$item->product_name}} ({{$item->style}}; {{$item->size}}; {{$item->color}})</h3></td>
        </tr>
        <tr class="table-warning">
            <td colspan="2">
                <div class="row">        
                    <div class="col">
                        <label class="text-danger">Styles</label>
                        <select class="custom-select">
                            <option value="6">Unisex T-Shirt - 5000</option>
                            <option value="466">Women's T-Shirt - 5000L</option>
                        </select>
                    </div>
                    <div class="col">
                        <label class="text-danger">Print Provider</label>
                        <select class="custom-select">
                            <option selected>Open this select menu</option>
                            <option value="1">One</option>
                            <option value="2">Two</option>
                            <option value="3">Three</option>
                        </select>
                    </div>
                    <div class="col">
                        <label class="text-danger">Variants of a Style</label>
                        <select class="custom-select">
                            <option value="1">One</option>
                            <option value="2">Two</option>
                            <option value="3">Three</option>
                        </select>
                    </div>
                    <div class="col">
                        <label class="text-danger">Design</label>
                        <input type="text" class="form-control" placeholder="123456">
                    </div>
                </div>
            </td>
        </tr>
        @endforeach
    </table>
    <div class="row" style="padding-top:20px">
        <div class="col">
            <button type="button" class="btn btn-primary col-12">Submit Order</button>
        </div>
    </div>
</div>
@endsection
