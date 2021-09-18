@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<style>
    span.select2-selection.select2-selection--single {
        height: calc(1.6em + 0.75rem + 2px);
    }
</style>
<script>
$(document).ready(function(){
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('.js-example-basic-single').select2();

    /**
     *
     */
    $('select[name=blueprint_id]').on('change', function() {
        updateProviderList([{id:0, title:'-- Loading --'}], false)
        updateVariantsList([{id:0, title:'-- Loading --'}], false)

        $.post(
            "{{url('/order/print-providers')}}",
            {blueprint_id: this.value}
        ).done(function(res) {
            updateProviderList(res.data, true)
        }).fail(function() {
            alert( "error" );
        });
    }).trigger('change');

    /**
     *
     */
    function updateProviderList(data, isTriggerChange) {
        var $this = $('select[name=print_provider_id]').empty()

        if (data && data.length > 0) {
            data.forEach(element => {
                $('select[name=print_provider_id]').append(`<option value="${element.id}">${element.title}</option>`)
            });
        }

        if (isTriggerChange) {
            $this.trigger('change')
        }
    }

    /**
     *
     */
    $('select[name=print_provider_id]').on('change', function() {
        updateVariantsList([{id:0, title:'-- Loading --'}], false)

        $.post(
            "{{url('/order/variants')}}",
            {
                blueprint_id: $('select[name=blueprint_id]').val(),
                print_provider_id: this.value
            }
        ).done(function(res) {
            updateVariantsList(res.data.variants, true)
        }).fail(function() {
            alert( "error" );
        });
    })

    /**
     *
     */
    function updateVariantsList(data, isTriggerChange) {
        var $this = $('select[name=variant_id]').empty()

        if (data && data.length > 0) {
            data.forEach(element => {
                $('select[name=variant_id]').append(`<option value="${element.id}">${element.title}</option>`)
            });
        }
    }
});

/**
 *
 */
function submitOrderForm() {
    var formdata = $('#orderForm').serializeArray().reduce((total, cur)=>{
        total[cur.name] = cur.value
        return total
    }, {})
    console.log('formdata=', formdata)
}

</script>
<div class="container" style="border:1px solid #cecece; padding:20px">
    <form id='orderForm'>
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
                            <select class="custom-select" name='blueprint_id'>
                                <option value="6">Unisex T-Shirt - 5000</option>
                                <option value="466">Women's T-Shirt - 5000L</option>
                            </select>
                        </div>
                        <div class="col">
                            <label class="text-danger">Print Provider</label>
                            <select class="custom-select" name='print_provider_id'>
                                <option selected>Open this select menu</option>
                                <option value="1">One</option>
                                <option value="2">Two</option>
                                <option value="3">Three</option>
                            </select>
                        </div>
                        <div class="col">
                            <label class="text-danger">Variants of a Style</label>
                            <select class="custom-select js-example-basic-single" name="variant_id">
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
                <button type="button" class="btn btn-primary col-12" onclick="submitOrderForm()">Submit Order</button>
            </div>
        </div>
    </form>
</div>
@endsection
