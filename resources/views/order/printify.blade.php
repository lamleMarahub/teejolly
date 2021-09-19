@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<style>
    span.select2-selection.select2-selection--single {
        height: calc(1.6em + 0.75rem + 2px);
    }
    .select2-container--default .select2-results > .select2-results__options {
        max-height: 400px;
    }
</style>
<script>
var gearmentProducts = [];

$(document).ready(function(){
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('.js-example-basic-single').select2({width: 'resolve'});

    // $('#myTab li:last-child a').tab('show')

    // ///////////////////////////////////////////////////////
    // PRINTIFY

    /**
     *
     */
    $('select[name$=blueprint_id].printify').on('change', function() {
        var selectName = $(this).attr('name')
        var itemId = selectName.split('_')[0]

        updateProviderList(itemId, [{id:0, title:'-- Loading --'}], false)
        updateVariantsList(itemId, [{id:0, title:'-- Loading --'}], false)

        $.post(
            "{{url('/print-providers/printify/providers')}}",
            {blueprint_id: this.value}
        ).done(function(res) {
            updateProviderList(itemId, res.data, true)
        }).fail(function() {
            alert( "error" );
        });
    }).trigger('change');

    /**
     *
     */
    function updateProviderList(itemId, data, isTriggerChange) {
        var $this = $(`select[name=${itemId}_print_provider_id].printify`).empty()

        if (data && data.length > 0) {
            data.forEach(element => {
                $this.append(`<option value="${element.id}">${element.title}</option>`)
            });
        }

        if (isTriggerChange) {
            $this.trigger('change')
        }
    }

    /**
     *
     */
    $('select[name$=print_provider_id].printify').on('change', function() {
        var selectName = $(this).attr('name')
        var itemId = selectName.split('_')[0]

        updateVariantsList(itemId, [{id:0, title:'-- Loading --'}], false)

        $.post(
            "{{url('/print-providers/printify/variants')}}",
            {
                blueprint_id: $(`select[name=${itemId}_blueprint_id].printify`).val(),
                print_provider_id: this.value
            }
        ).done(function(res) {
            updateVariantsList(itemId, res.data.variants, true)
        }).fail(function() {
            alert( "error" );
        });
    })

    /**
     *
     */
    function updateVariantsList(itemId, data, isTriggerChange) {
        var $this = $(`select[name=${itemId}_variant_id].printify`).empty()

        if (data && data.length > 0) {
            data.forEach(element => {
                $this.append(`<option value="${element.id}">${element.title}</option>`)
            });
        }
    }

    // END PRINTIFY
    // ///////////////////////////////////////////////////////

    // ///////////////////////////////////////////////////////
    // GEARMENT

    var $gearmentSelects = $('select[name$=product_id].gearment').empty()
    $gearmentSelects.append(`<option value=0>-- Loading --</option>`)
    $.post(
        "{{url('/print-providers/gearment/products')}}",
        {}
    ).done(function(res) {
        gearmentProducts = res.data.result

        $gearmentSelects.empty()

        $gearmentSelects.append(`<option value=0>-- Choose product --</option>`)
        res.data.result.forEach(element => {
            $gearmentSelects.append(`<option value="${element.product_id}">${element.product_name}</option>`)
        });

    }).fail(function() {
        alert( "error" );
    });

    $gearmentSelects.on('change', function() {
        const chooseProductId = this.value
        const selectName = $(this).attr('name')
        const itemId = selectName.split('_')[0]
        const choosenProduct = gearmentProducts.find(item => item.product_id == chooseProductId)

        const $variant = $(`select[name=${itemId}_variant_id].gearment`).empty()

        choosenProduct.variants.forEach(element => {
            $variant.append(`<option ${element.availability_status === 'in_stock' ? '' : 'disabled="disabled"'}" value="${element.variant_id}">${element.color} - ${element.size}</option>`)
        });

        $(`img.${itemId}_mockup_img`).attr('src', choosenProduct.product_img + '?x=' + new Date().getTime())

        $variant.trigger('change')
    })

    $('select[name$=variant_id].gearment').on('change', function() {
        const selectName = $(this).attr('name')
        const itemId = selectName.split('_')[0]
        const productId = $(`select[name$=${itemId}_product_id].gearment`).val()
        const choosenProduct = gearmentProducts.find(item => item.product_id == productId)
        const choosenVariant = choosenProduct.variants.find(item => item.variant_id == this.value)

        $(`img.${itemId}_mockup_img`).css("background", `#${choosenVariant.hex_color_code}`);
    })
    // END GEARMENT
    // ///////////////////////////////////////////////////////
});

/**
 *
 */
function submitPrintifyForm() {
    for (const element of $('#printifyForm').serializeArray()) {
        if (element.value == 0) {
            alert('Hãy chọn đầy đủ thông tin')
            return;
        }
    }

    var formdata = $('#printifyForm').serializeArray().reduce((total, cur) => {
        var itemId = cur.name.split('_')[0]
        var nameKey = cur.name.split('_').slice(1).join('_')

        if (!total[itemId]) {
            total[itemId] = {}
            total[itemId]['order_id'] = itemId
        }

        if (!total[itemId][nameKey]) {
            total[itemId][nameKey] = cur.value
        }

        return total
    }, {})

    var processedFormdata = Object.entries(formdata).map(item => item[1])
}

/**
 *
 */
function submitGearmentForm() {
    for (const element of $('#gearmentForm').serializeArray()) {
        if (element.value == 0) {
            alert('Hãy chọn đầy đủ thông tin')
            return;
        }
    }

    var formdata = $('#gearmentForm').serializeArray().reduce((total, cur) => {
        var itemId = cur.name.split('_')[0]
        var nameKey = cur.name.split('_').slice(1).join('_')

        if (!total[itemId]) {
            total[itemId] = {}
            total[itemId]['order_id'] = itemId
        }

        if (!total[itemId][nameKey]) {
            total[itemId][nameKey] = cur.value
        }

        return total
    }, {})

    var processedFormdata = Object.entries(formdata).map(item => item[1])

    console.log('processedFormdata=', processedFormdata)
}

</script>
<div class="container" style="border:1px solid #cecece; padding:20px">
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link active" id="printifyTab" data-toggle="tab" href="#printify" role="tab" aria-controls="printify" aria-selected="true">1. printify</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="gearmentTab" data-toggle="tab" href="#gearment" role="tab" aria-controls="gearment" aria-selected="false">2. gearment</a>
        </li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane active" id="printify" role="tabpanel" aria-labelledby="printify-tab">
            <form id='printifyForm'>
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
                                    <select class="custom-select printify" name="{{$item->id}}_blueprint_id">
                                        <option value="6">Unisex T-Shirt - 5000</option>
                                        <option value="466">Women's T-Shirt - 5000L</option>
                                    </select>
                                </div>
                                <div class="col">
                                    <label class="text-danger">Print Provider</label>
                                    <select class="custom-select printify" name="{{$item->id}}_print_provider_id">
                                        <option selected>Open this select menu</option>
                                        <option value="1">One</option>
                                        <option value="2">Two</option>
                                        <option value="3">Three</option>
                                    </select>
                                </div>
                                <div class="col">
                                    <label class="text-danger">Variants of a Style</label>
                                    <select class="custom-select printify js-example-basic-single" name="{{$item->id}}_variant_id">
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
                        <button type="button" class="btn btn-primary col-12" onclick="submitPrintifyForm()">Submit Printify Order</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="tab-pane" id="gearment" role="tabpanel" aria-labelledby="gearment-tab">
            <form id='gearmentForm'>
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
                                <div class="col-4">
                                    <label class="text-danger">Product</label>
                                    <div>
                                        <select class="gearment js-example-basic-single" style="width: 100%" name="{{$item->id}}_product_id">
                                            <option value="0">-- Loading --</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <label class="text-danger">Variants of a Style</label>
                                    <div class="">
                                        <select class="gearment js-example-basic-single" style="width: 100%" name="{{$item->id}}_variant_id">
                                            <option value="0">-- Loading --</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <label class="text-danger">Mock up</label>
                                    <div class="">
                                        <img class="{{$item->id}}_mockup_img" src="https://account.gearment.com/sellerv2/assets/custom/no-photo.png" style="max-width: 100px;">
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </table>
                <div class="row" style="padding-top:20px">
                    <div class="col">
                        <button type="button" class="btn btn-primary col-12" onclick="submitGearmentForm()">Submit Gearment Order</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
