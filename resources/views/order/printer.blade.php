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

var GEARMENT_PRODUCTS = [];
var PRINTHIGH_PRODUCTS = [];

const AMAZON_IMG_PREFIX = 'https://s3.amazonaws.com/teejolly-prod/'
const FULL_NAME = "{{$order->full_name}}"
const ADDRESS = {
    first_name: FULL_NAME.split(' ')[0],
    last_name: FULL_NAME.split(' ').slice(1).join(' '),
    email: "sales@mkthumb.com",
    phone: "0327570057[string]",
    country: "US",
    region: "{{$order->state}}",
    address1: "{{$order->address_1}}",
    address2: "{{$order->address_2}}[string]",
    city: "{{$order->city}}",
    zip: "{{$order->zip_code}}[string]"
}

const ADDRESS_2 = {
    firstName: FULL_NAME.split(' ')[0],
    lastName: FULL_NAME.split(' ').slice(1).join(' '),
    email: "sales@mkthumb.com",
    phone: "0327570057[string]",
    country: "US",
    state: "{{$order->state}}",
    address1: "{{$order->address_1}}",
    address2: "{{$order->address_2}}[string]",
    city: "{{$order->city}}",
    zip: "{{$order->zip_code}}[string]"
}

$(document).ready(function(){
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        async:true
    });

    $('#showAlert').hide()

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

        updateProviderList(itemId, [{id:0, title:'-- loading --'}], false)
        updateVariantsList(itemId, [{id:0, title:'-- loading --'}], false)

        $.ajax({
            url : "{{url('/print-providers/printify/providers')}}",
            type: 'POST',
            data: {blueprint_id: this.value},
            async: true,
            success : function(res) {
                updateProviderList(itemId, res.data, true)
            },
            error: function(err) {
                alert( "error" );
            }
        })

        // $.post(
        //     "{{url('/print-providers/printify/providers')}}",
        //     {blueprint_id: this.value}
        // ).done(function(res) {
        //     updateProviderList(itemId, res.data, true)
        // }).fail(function() {
        //     alert( "error" );
        // });
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

        updateVariantsList(itemId, [{id:0, title:'-- loading --'}], false)

        $.ajax({
            url : "{{url('/print-providers/printify/variants')}}",
            type: 'POST',
            data: {
                blueprint_id: $(`select[name=${itemId}_blueprint_id].printify`).val(),
                print_provider_id: this.value
            },
            async: true,
            success : function(res) {
                updateVariantsList(itemId, res.data.variants, true)
            },
            error: function(err) {
                alert( "error" );
            }
        })

        // $.post(
        //     "{{url('/print-providers/printify/variants')}}",
        //     {
        //         blueprint_id: $(`select[name=${itemId}_blueprint_id].printify`).val(),
        //         print_provider_id: this.value
        //     }
        // ).done(function(res) {
        //     updateVariantsList(itemId, res.data.variants, true)
        // }).fail(function() {
        //     alert( "error" );
        // });
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
    $gearmentSelects.append(`<option value=0>-- loading --</option>`)

    $.ajax({
        url : "{{url('/print-providers/gearment/products')}}",
        type: 'POST',
        data: {},
        async: true,
        success : function(res) {
            GEARMENT_PRODUCTS = res.data.result

            $gearmentSelects.empty()

            $gearmentSelects.append(`<option value=0>-- choose product --</option>`)
            res.data.result.forEach(element => {
                $gearmentSelects.append(`<option value="${element.product_id}">${element.product_name}</option>`)
            });
        },
        error: function(err) {
            alert( "error" );
        }
    })

    // $.post(
    //     "{{url('/print-providers/gearment/products')}}",
    //     {}
    // ).done(function(res) {
    //     GEARMENT_PRODUCTS = res.data.result

    //     $gearmentSelects.empty()

    //     $gearmentSelects.append(`<option value=0>-- Choose product --</option>`)
    //     res.data.result.forEach(element => {
    //         $gearmentSelects.append(`<option value="${element.product_id}">${element.product_name}</option>`)
    //     });

    // }).fail(function() {
    //     alert( "error" );
    // });

    $gearmentSelects.on('change', function() {
        const chooseProductId = this.value
        const selectName = $(this).attr('name')
        const itemId = selectName.split('_')[0]
        const choosenProduct = GEARMENT_PRODUCTS.find(item => item.product_id == chooseProductId)

        const $variant = $(`select[name=${itemId}_variant_id].gearment`).empty()

        choosenProduct.variants.forEach(element => {
            // $variant.append(`<option ${element.availability_status === 'in_stock' ? '' : 'disabled="disabled"'}" value="${element.variant_id}">${element.size} / ${element.color}</option>`);
            $variant.append(`<option ${element.availability_status === 'in_stock' ? '' : 'disabled="disabled"'} value="${element.variant_id}">${element.size} / ${element.color} </option>`);
            // alert(`<option ${element.availability_status === 'in_stock' ? '' : 'disabled="disabled"'} value="${element.variant_id}">${element.color} - ${element.size}</option>`)
        });

        // $(`img.${itemId}_mockup_img`).attr('src', choosenProduct.product_img + '?x=' + new Date().getTime())

        $variant.trigger('change')
    })

    $('select[name$=variant_id].gearment').on('change', function() {
        const selectName = $(this).attr('name')
        const itemId = selectName.split('_')[0]
        const productId = $(`select[name$=${itemId}_product_id].gearment`).val()
        const choosenProduct = GEARMENT_PRODUCTS.find(item => item.product_id == productId)
        const choosenVariant = choosenProduct.variants.find(item => item.variant_id == this.value)

        // $(`img.${itemId}_mockup_img`).css("background", `#${choosenVariant.hex_color_code}`);
    })

    $('select[name$=shipping_method].gearment').on('change', function() {
        const selectName = $(this).attr('name')
        const itemId = selectName.split('_')[0]
        const productId = $(`select[name$=${itemId}_shipping_method].gearment`).val()
        $('select[name$=shipping_method]').val(productId).change()
    })
    // END GEARMENT
    // ///////////////////////////////////////////////////////

    // ///////////////////////////////////////////////////////
    // PRINTHIGH

    // var $printhighSelects = $('select[name$=product_id].printhigh').empty()
    // $printhighSelects.append(`<option value=0>-- loading --</option>`)

    // $.ajax({
    //     url : "{{url('/print-providers/printhigh/products')}}",
    //     type: 'POST',
    //     data: {},
    //     async: true,
    //     success : function(res) {
    //         PRINTHIGH_PRODUCTS = res.data
    //         $printhighSelects.empty()
    //         $printhighSelects.append(`<option value=0>-- choose product --</option>`)
    //         res.data.forEach(element => {
    //             $printhighSelects.append(`<option value="${element.value}">${element.text}</option>`)
    //         });
    //     },
    //     error: function(err) {
    //         alert( "error" );
    //     }
    // })

    // $printhighSelects.on('change', function() {
    //     const chooseProductId = this.value
    //     const selectName = $(this).attr('name')
    //     const itemId = selectName.split('_')[0]
    //     const choosenProduct = PRINTHIGH_PRODUCTS.find(item => item.value == chooseProductId)

    //     const $variant = $(`select[name=${itemId}_variant_id].printhigh`).empty()

    //     // console.log(choosenProduct)

    //     choosenProduct.colors.forEach(color => {
    //         // $variant.append(`<option ${element.availability_status === 'in_stock' ? '' : 'disabled="disabled"'}" value="${element.variant_id}">${element.color} - ${element.size}</option>`);
    //         // $variant.append(`<option ${element.availability_status === 'in_stock' ? '' : 'disabled="disabled"'} value="${element.variant_id}">${element.size} / ${element.color} </option>`);
    //         // $variant.append(`<option value="${element.product_color_id}">${element.color} / ${element.color} </option>`);

    //         choosenProduct.sizes.forEach(size=>{
    //             $variant.append(`<option value="${color}/${size}">${color} / ${size} </option>`);
    //         });
    //         // obj.colors.forEach(element=>{
    //         //     // console.log(element.in_stock)
    //         //     $variant.append(`<option value="${element}">${element} / ${element} </option>`);
    //         //     // $variant.append(`<option value="${element.catalog_sku_id}">${element.size} / ${element.color} </option>`);
    //         // });
    //         // alert(`<option ${element.availability_status === 'in_stock' ? '' : 'disabled="disabled"'} value="${element.variant_id}">${element.color} - ${element.size}</option>`)
    //     });

    //     // $(`img.${itemId}_mockup_img`).attr('src', choosenProduct.product_img + '?x=' + new Date().getTime())

    //     $variant.trigger('change')
    // })

    $('select[name$=variant_id].printhigh').on('change', function() {
        const selectName = $(this).attr('name')
        const itemId = selectName.split('_')[0]
        const productId = $(`select[name$=${itemId}_product_id].printhigh`).val()
        console.log(">> catalog_product_id: "+ productId)
        const choosenProduct = PRINTHIGH_PRODUCTS.find(item => item.value == productId)
        // const choosenVariant = choosenProduct.product_colors.find(item => item.product_color_id == this.value)
        const choosenVariant = choosenProduct.colors.find(obj => {
            choosenProduct.sizes.find(item => item == this.value)
        });
        console.log(">> choosenProduct: " ,choosenProduct)
        console.log(">> variants: "+this.value)
        // $(`img.${itemId}_mockup_img`).css("background", `#${choosenVariant.hex_color_code}`);
    })

    $('select[name$=shipping_method_ph].printhigh').on('change', function() {
        const selectName = $(this).attr('name')
        const itemId = selectName.split('_')[0]
        const productId = $(`select[name$=${itemId}_shipping_method_ph].printhigh`).val()
        // console.log(productId)
        $('select[name$=shipping_method_ph]').val(productId).change()
    })
    // END PRINTHIGH
    // ///////////////////////////////////////////////////////


    $('input[name$=design_id]').on('change', function() {
        const selectName = $(this).attr('name')
        const itemId = selectName.split('_')[0]
        const designId = this.value
        const provider = $(this).attr('provider')
        const $targetImg = $(`img[class=${itemId}_design_img][provider=${provider}]`)
        const $designImgUrlInput = $(`input[name=${itemId}_design_img_url][provider=${provider}]`)
        const submitButton = $('.submit-button').prop('disabled', true);

        $.ajax({
            url : "{{url('/design/img')}}",
            type: 'POST',
            data: {design_id: designId},
            async: true,
            success : function(res) {
                $targetImg.attr('src', AMAZON_IMG_PREFIX + res.thumbnail + '?x=' + new Date().getTime())
                $designImgUrlInput.val(AMAZON_IMG_PREFIX + res.filename)
                submitButton.prop('disabled', false);
            },
            error: function(err) {
                $targetImg.attr('src', '#').prop('alt', err.responseJSON.message)
                $designImgUrlInput.val('')
                submitButton.prop('disabled', false);
            }
        })
    })

    $('input[name$=design_id_2]').on('change', function() {
        const selectName = $(this).attr('name')
        const itemId = selectName.split('_')[0]
        const designId = this.value
        const provider = $(this).attr('provider')
        const $targetImg = $(`img[class=${itemId}_design_img_2][provider=${provider}]`)
        const $designImgUrlInput = $(`input[name=${itemId}_design_img_url_2][provider=${provider}]`)
        const submitButton = $('.submit-button').prop('disabled', true);

        $.ajax({
            url : "{{url('/design/img')}}",
            type: 'POST',
            data: {design_id: designId},
            async: true,
            success : function(res) {
                $targetImg.attr('src', AMAZON_IMG_PREFIX + res.thumbnail + '?x=' + new Date().getTime())
                $designImgUrlInput.val(AMAZON_IMG_PREFIX + res.filename)
                submitButton.prop('disabled', false);
            },
            error: function(err) {
                $targetImg.attr('src', '#').prop('alt', err.responseJSON.message)
                $designImgUrlInput.val('')
                submitButton.prop('disabled', false);
            }
        })
    })

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

    const postdata = {
        // external_id: UUID,
        external_id: "{{$order->amz_order_id}}",
        label: "{{$order->amz_order_id}}",
        line_items: processedFormdata.map(item=> (
            {
                print_provider_id: +item.print_provider_id,
                blueprint_id: +item.blueprint_id,
                variant_id: +item.variant_id,
                print_areas: {
                    front: item.design_img_url
                },
                quantity: +item.quantity
            }
        )),
        shipping_method: 1,
        send_shipping_notification: false,
        address_to: ADDRESS
    }

    $.ajax({
        url : "{{url('/print-providers/printify/create')}}",
        type: 'POST',
        data: {order_id: "{{$order->id}}", postdata: postdata, order_type: 1},
        async: true,
        success : function(res) {
            if(res.success){
                $('#showAlert').html(res.message + " - #" + res.data.id)
                $('#showAlert').show()
            }else{
                $('#showAlert').html(res.message)
                $('#showAlert').show()
            }
        },
        error: function(err) {
            // alert(err.responseJSON.data.errors.reason)
            $('#showAlert').html(err.responseJSON.data.errors.reason)
            $('#showAlert').show()
        }
    })

    // console.log('postdata=', postdata)
    // var jsonPretty = JSON.stringify(postdata, null, '\t');
    // $('#printifyPostData').text(jsonPretty)
}

/**
 *
 */
function submitGearmentForm() {
    var emptyDesigns = {};

    for (const element of $('#gearmentForm').serializeArray()) {
        var id = element.name.split('_')[0]

        if (!element.value) {
            if (element?.name?.endsWith("design_img_url") || element?.name?.endsWith("design_img_url_2") ) {
                emptyDesigns = {...emptyDesigns, [id]: (emptyDesigns[id] ?? 0) + 1}
            } else if (element?.name?.endsWith("_design_id") || element?.name?.endsWith("_design_id_2") ) {
                // nothing
            } else {
                alert('Hãy chọn đầy đủ thông tin')
                return;
            }
        }
    }

    // console.log('----------------[emptyDesigns]', emptyDesigns)

    if (Object.values(emptyDesigns).find(item => item >= 2)) {
        alert('Hãy điền design ID front/back')
        return;
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

    const postdata = {
        api_key: "",
        api_signature: "",
        shipping_name: "{{$order->full_name}}",
        shipping_phone: "",
        shipping_company_name: "",
        shipping_address1: "{{$order->address_1}}",
        shipping_address2: " {{$order->address_2}}",
        shipping_province_code: "{{$order->state}}",
        shipping_city: "{{$order->city}}",
        shipping_zipcode: "{{$order->zip_code}}",
        shipping_country_code: "US",
        shipping_method: processedFormdata[0].shipping_method,
        line_items: processedFormdata.map(item=> (
            {
                variant_id: +item.variant_id,
                quantity: +item.quantity,
                design_link: item.design_img_url,
                design_link_back: item.design_img_url_2
            }
        )),
    }

    $.ajax({
        url : "{{url('/print-providers/gearment/create')}}",
        type: 'POST',
        data: {order_id: "{{$order->id}}", postdata: postdata, order_type: 1},
        async: true,
        success : function(res) {
            if(res.data.status == "success"){
                $('#showAlert').html(res.message + " - #" + res.data.result.name)
                console.log(res.data.result.name)
                $('#showAlert').show()
            }else{
                $('#showAlert').html(res.message)
                $('#showAlert').show()
            }
            console.log(res.data)
        },
        error: function(err) {
            alert(err.responseJSON.data.errors.reason)
        }
    })

    // console.log('postdata=', postdata)
    // var jsonPretty = JSON.stringify(postdata, null, '\t');
    // $('#gearmentPostData').text(jsonPretty)

}

/**
 *
 */
function submitPrintHighForm() {
    var emptyDesigns = {};

    for (const element of $('#printhighForm').serializeArray()) {
        var id = element.name.split('_')[0]

        if (!element.value) {
            if (element?.name?.endsWith("design_img_url") || element?.name?.endsWith("design_img_url_2") ) {
                emptyDesigns = {...emptyDesigns, [id]: (emptyDesigns[id] ?? 0) + 1}
            } else if (element?.name?.endsWith("_design_id") || element?.name?.endsWith("_design_id_2") ) {
                // nothing
            } else {
                alert('Hãy chọn đầy đủ thông tin')
                return;
            }
        }
    }

    if (Object.values(emptyDesigns).find(item => item >= 2)) {
        alert('Hãy điền design ID front/back')
        return;
    }

    var formdata = $('#printhighForm').serializeArray().reduce((total, cur) => {
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

    function getDesignImage(item) {
        var designImages = [];

        if (item.design_img_url) {
            designImages.push({location: "front", imageUrl: item.design_img_url});
        }

        if (item.design_img_url_2) {
            designImages.push({location: "back", imageUrl: item.design_img_url_2});
        }

        return designImages;
    }

    const postdata = {
        address: ADDRESS_2,
        sellerOrderId: "{{$order->amz_order_id}}",
        shippingMethod: processedFormdata[0].shipping_method_ph,
        items: processedFormdata.map(item=> (
            {
                catalogId: +item.product_id,
                quantity: +item.quantity,
                designs: getDesignImage(item),
                size: item.variant_id.split('/')[1],
                color: item.variant_id.split('/')[0]
            }
        )),
    }

    $.ajax({
        url : "{{url('/print-providers/printhigh/create')}}",
        type: 'POST',
        data: {order_id: "{{$order->id}}", postdata: postdata, order_type: 1},
        async: true,
        success : function(res) {
            if(res.success){
                $('#showAlert').html(res.message + " - #" + res.data.order.id)
                $('#showAlert').show()
            }else{
                $('#showAlert').html(res.message)
                $('#showAlert').show()
            }
            console.log(res.data)
        },
        error: function(err) {
            alert(err.responseJSON.data)
        }
    })

    // console.log('postdata=', postdata)
    // var jsonPretty = JSON.stringify(postdata, null, '\t');
    // $('#printhighPostData').text(jsonPretty)

}

</script>
<div class="container" style="border:1px solid #cecece; padding:20px">
    <div class="alert alert-danger" role="alert" id="showAlert"></div>

    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link active" id="printifyTab" data-toggle="tab" href="#printify" role="tab" aria-controls="printify" aria-selected="true">1. printify</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="gearmentTab" data-toggle="tab" href="#gearment" role="tab" aria-controls="gearment" aria-selected="false">2. gearment</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="printhighTab" data-toggle="tab" href="#printhigh" role="tab" aria-controls="printhigh" aria-selected="false">3. printhigh</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="dreamshipTab" data-toggle="tab" href="#dreamship" role="tab" aria-controls="dreamship" aria-selected="false">4. Dreamship</a>
        </li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane active" id="printify" role="tabpanel" aria-labelledby="printify-tab">
            <form id='printifyForm'>
                <h4 style="padding-top:10px">Order Id: #{{$order->amz_order_id}}  @if($order->note) - Note: <b>{{$order->note}}</b> @endif</h4>
                <table class="table table-borderless table-hover">
                    @foreach($orderItems as $item)
                    <tr>
                        <td><img src="{{str_replace("._SCLZZZZZZZ__SX55_", "", $item->thumbnail)}}" alt="{{$item->product_name}}" class="img-thumbnail" style="max-width: 150px; max-height: 150px;"></td>
                        <td>
                            <h4 style="padding-top:10px">{{$item->product_name}}</h4>
                            <h5>ASIN: <strong><a href="https://amazon.com/dp/{{$item -> asin}}" target="_blank">{{$item -> asin}}</a></strong> - SKU: <strong>{{$item -> sku}}</strong> - SHIPPING TOTAL: <strong>{{$item -> shippingAmount}}</strong></h5>
                            <!--<h4>Style: <strong> {{$item->style}}; </strong> Size: <strong>{{$item->size}}; </strong> Color: <strong>{{$item->color}}</strong> Custom: <strong>{{$item->customization}}</strong></h4>-->
                            <h4><strong> {{$item->style}};  {{$item->size}};  {{$item->color}}; {{$item->customization}}</strong></h4>
                        </td>
                    </tr>
                    <tr class="table-warning">
                        <td colspan="2">
                            <div class="row">
                                <div class="col">
                                    <label class="text-danger">Products</label>
                                    <select class="custom-select printify" name="{{$item->id}}_blueprint_id">
                                        <option value="6">Unisex Heavy Cotton Tee - 5000</option>
                                        <option value="466">Women's Heavy Cotton Tee - 5000L</option>
                                        <option value="12">Unisex Jersey Short Sleeve Tee - 3001</option>
                                        <option value="88">Women's Softstyle Tee - 64000L</option>
                                        <option value="14">Women's The Boyfriend Tee - 3900</option>
                                        <option value="472">Women's Premium Tee - 6400</option>
                                        <option value="48">Unisex Jersey Short Sleeve V-Neck Tee - 3005</option>
                                        <option value="157">Kids Heavy Cotton™ Tee - 5000B</option>
                                        <option value="81">Kids Softstyle Tee - 64000B</option>
                                        <option value="34">Infant Fine Jersey Tee - 3322</option>
                                        <option value="32">Kid's Fine Jersey Tee - 3321</option>
                                        <option value="33">Infant Fine Jersey Bodysuit - 4424</option>
                                        <option value="31">Infant Long Sleeve Bodysuit - 4411</option>
                                        <option value="561">Infant Baby Rib Bodysuit - 4400</option>
                                        <option value="41">Unisex Jersey Long Sleeve Tee - 3501</option>
                                        <option value="80">Ultra Cotton Long Sleeve Tee - 2400</option>
                                        <option value="49">Unisex Heavy Blend™ Crewneck Sweatshirt - 18000</option>
                                        <option value="77">Unisex Heavy Blend™ Hooded Sweatshirt - 18500</option>
                                        <option value="314">Youth Heavy Blend Hooded Sweatshirt - 18500B</option>
                                        <option value="446">Unisex Premium Crewneck Sweatshirt - Lane Seven LS14004</option>
                                    </select>
                                </div>
                                <div class="col">
                                    <label class="text-danger">Print Provider</label>
                                    <select class="custom-select printify" name="{{$item->id}}_print_provider_id">
                                    </select>
                                </div>
                                <div class="col">
                                    <label class="text-danger">Variants of a Products</label>
                                    <select class="custom-select printify js-example-basic-single" name="{{$item->id}}_variant_id">
                                    </select>
                                </div>
                                <div class="col">
                                    <label class="text-danger">Design ID</label>
                                    <input type="number" class="form-control" name="{{$item->id}}_design_id" provider="printify" placeholder="">
                                    <input type="hidden" name="{{$item->id}}_design_img_url" provider="printify">
                                    <input type="hidden" name="{{$item->id}}_quantity" provider="printify" value="{{$item->quantity}}">
                                    <div>
                                        <img class="{{$item->id}}_design_img" provider="printify" src='' alt='' style='width: 100%; padding-top: 5px' />
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </table>
                <div class="row" style="padding-top:20px">
                    <div class="col">
                        <button type="button" class="btn btn-primary col-12 submit-button" onclick="submitPrintifyForm()" provider="printify">Submit Printify Order</button>
                        <pre id='printifyPostData'></pre>
                    </div>
                </div>
            </form>
        </div>

        <div class="tab-pane" id="gearment" role="tabpanel" aria-labelledby="gearment-tab">
            <form id='gearmentForm'>
                <h4 style="padding-top:10px">Order Id: #{{$order->amz_order_id}}  @if($order->note) - Note: <b>{{$order->note}}</b> @endif</h4>
                <table class="table table-borderless table-hover">
                    @foreach($orderItems as $item)
                    <tr>
                        <td><img src="{{str_replace("._SCLZZZZZZZ__SX55_", "", $item->thumbnail)}}" alt="{{$item->product_name}}" class="img-thumbnail" style="max-width: 150px; max-height: 150px;"></td>
                        <td>
                            <h4 style="padding-top:10px">{{$item->product_name}}</h4>
                            <h5>ASIN: <strong><a href="https://amazon.com/dp/{{$item -> asin}}" target="_blank">{{$item -> asin}}</a></strong> - SKU: <strong>{{$item -> sku}}</strong> - SHIPPING TOTAL: <strong>{{$item -> shippingAmount}}</strong></h5>
                            <!--<h4>Style: <strong> {{$item->style}}; </strong> Size: <strong>{{$item->size}}; </strong> Color: <strong>{{$item->color}}</strong> Custom: <strong>{{$item->customization}}</strong></h4>-->
                            <h4><strong> {{$item->style}};  {{$item->size}};  {{$item->color}}; {{$item->customization}}</strong></h4>
                        </td>
                    </tr>
                    <tr class="table-warning">
                        <td colspan="2">
                            <div class="row">
                                <div class="col-3">
                                    <label class="text-danger">Products</label>
                                    <div>
                                        <select class="gearment js-example-basic-single" style="width: 100%" name="{{$item->id}}_product_id">
                                            <option value="0">-- loading --</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <label class="text-danger">Variants of a Products</label>
                                    <div class="">
                                        <select class="gearment js-example-basic-single" style="width: 100%" name="{{$item->id}}_variant_id">
                                            <option value="0">-- loading --</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <label class="text-danger">Shipping Service</label>
                                    <!-- <div class="">
                                        <img class="{{$item->id}}_mockup_img" src="https://account.gearment.com/sellerv2/assets/custom/no-photo.png" style="max-width: 100px;">
                                    </div> -->
                                    <div class="">
                                        <select class="gearment form-control" style="width: 100%" name="{{$item->id}}_shipping_method">
                                            <option value="1" selected>Standard</option>
                                            <option value="2">2 Days</option>
                                            <option value="3">Ground</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <label class="text-danger">Design ID (FRONT SIDE)</label>
                                    <input type="number" class="form-control" name="{{$item->id}}_design_id" provider="gearment" placeholder="">
                                    <input type="hidden" class="form-control" name="{{$item->id}}_design_img_url" provider="gearment">
                                    <input type="hidden" name="{{$item->id}}_quantity" provider="gearment" value="{{$item->quantity}}">
                                    <div>
                                        <img class="{{$item->id}}_design_img" provider="gearment" src='' alt='' style='width: 100%; padding-top: 5px' />
                                    </div>
                                </div>
                                <div class="col-9"></div>
                                <div class="col-3">
                                    <label class="text-danger">Design ID (BACK SIDE)</label>
                                    <input type="number" class="form-control" name="{{$item->id}}_design_id_2" provider="gearment" placeholder="">
                                    <input type="hidden" class="form-control" name="{{$item->id}}_design_img_url_2" provider="gearment">
                                    <div>
                                        <img class="{{$item->id}}_design_img_2" provider="gearment" src='' alt='' style='width: 100%; padding-top: 5px' />
                                    </div>
                                </div>

                            </div>
                        </td>
                    </tr>
                    @endforeach
                </table>
                <div class="row" style="padding-top:20px">
                    <div class="col">
                        <button type="button" id='gearSubmitBtn' class="btn btn-primary col-12 submit-button" onclick="submitGearmentForm()" provider="gearment">Submit Gearment Order</button>
                        <pre id='gearmentPostData'></pre>
                    </div>
                </div>
            </form>
        </div>

        <div class="tab-pane" id="printhigh" role="tabpanel" aria-labelledby="printhigh-tab">
            <form id='printhighForm'>
                <h4 style="padding-top:10px">Order Id: #{{$order->amz_order_id}}  @if($order->note) - Note: <b>{{$order->note}}</b> @endif</h4>
                <table class="table table-borderless table-hover">
                    @foreach($orderItems as $item)
                    <tr>
                        <td><img src="{{str_replace("._SCLZZZZZZZ__SX55_", "", $item->thumbnail)}}" alt="{{$item->product_name}}" class="img-thumbnail" style="max-width: 150px; max-height: 150px;"></td>
                        <td>
                            <h4 style="padding-top:10px">{{$item->product_name}}</h4>
                            <h5>ASIN: <strong><a href="https://amazon.com/dp/{{$item -> asin}}" target="_blank">{{$item -> asin}}</a></strong> - SKU: <strong>{{$item -> sku}}</strong> - SHIPPING TOTAL: <strong>{{$item -> shippingAmount}}</strong></h5>
                            <!--<h4>Style: <strong> {{$item->style}}; </strong> Size: <strong>{{$item->size}}; </strong> Color: <strong>{{$item->color}}</strong> Custom: <strong>{{$item->customization}}</strong></h4>-->
                            <h4><strong> {{$item->style}};  {{$item->size}};  {{$item->color}}; {{$item->customization}}</strong></h4>
                        </td>
                    </tr>
                    <tr class="table-warning">
                        <td colspan="2">
                            <div class="row">
                                <div class="col-3">
                                    <label class="text-danger">Products</label>
                                    <div>
                                        <select class="printhigh js-example-basic-single" style="width: 100%" name="{{$item->id}}_product_id">
                                            <option value="0">-- loading --</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <label class="text-danger">Variants of a Products</label>
                                    <div class="">
                                        <select class="printhigh js-example-basic-single" style="width: 100%" name="{{$item->id}}_variant_id">
                                            <option value="0">-- loading --</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <label class="text-danger">Shipping Service</label>
                                    <div class="">
                                        <select class="printhigh form-control" style="width: 100%" name="{{$item->id}}_shipping_method_ph">
                                            <option value="standard" selected>Standard</option>
                                            <option value="expedited">Expedited</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <label class="text-danger">Design ID (FRONT SIDE)</label>
                                    <input type="number" class="form-control" name="{{$item->id}}_design_id" provider="printhigh" placeholder="">
                                    <input type="hidden" class="form-control" name="{{$item->id}}_design_img_url" provider="printhigh">
                                    <input type="hidden" name="{{$item->id}}_quantity" provider="printhigh" value="{{$item->quantity}}">
                                    <div>
                                        <img class="{{$item->id}}_design_img" provider="printhigh" src='' alt='' style='width: 100%; padding-top: 5px' />
                                    </div>
                                </div>

                                <div class="col-9"></div>
                                <div class="col-3">
                                    <label class="text-danger">Design ID (BACK SIDE)</label>
                                    <input type="number" class="form-control" name="{{$item->id}}_design_id_2" provider="printhigh" placeholder="">
                                    <input type="hidden" class="form-control" name="{{$item->id}}_design_img_url_2" provider="printhigh">
                                    <div>
                                        <img class="{{$item->id}}_design_img_2" provider="printhigh" src='' alt='' style='width: 100%; padding-top: 5px' />
                                    </div>
                                </div>

                            </div>
                        </td>
                    </tr>
                    @endforeach
                </table>
                <div class="row" style="padding-top:20px">
                    <div class="col">
                        <button type="button" class="btn btn-primary col-12 submit-button" onclick="submitPrintHighForm()" provider="printhigh">Submit PrintHigh Order YYY</button>
                        <pre id='printhighPostData'></pre>
                    </div>
                </div>
            </form>
        </div>

        @include('order.dreamship')

    </div>

</div>
@endsection
