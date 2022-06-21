<script type='text/javascript'>
// ///////////////////////////////////////////////////////
// teezily
var ROOT_PATH = "{{url('')}}";
var TEEZILY_CATEGORIES = [];

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    async:true
});

$.ajax({
    url : "{{url('/print-providers/teezily/categories')}}",
    type: 'POST',
    data: {},
    async: false,
    success : function(res) {
        TEEZILY_CATEGORIES = res.data.results;
    },
    error: function(err) {
        alert( "teezily error" );
    }
})

$(document).ready(function() {
    var $customcatSelects = $('select[name$=product_id].teezily').empty()
    $customcatSelects.append(`<option value=0>-- choose product --</option>`)
    TEEZILY_CATEGORIES.forEach(element => {
        $customcatSelects.append(`<option value="${element.id}">${element.name}</option>`)
    });

    $customcatSelects.on('change', function() {
        const categoryId = this.value
        const selectName = $(this).attr('name')
        const orderItemId = selectName.split('_')[0]
        const $variantDom = $(`select[name$=${orderItemId}_variant_id].teezily`).empty()
            .append(`<option value="0">-- loading --</option>`);

        const category = TEEZILY_CATEGORIES.find(item => item.id == categoryId);

        $variantDom.empty();
        category.variants.forEach(element => {
            $variantDom.append(`<option value="${element.reference}">${element.name}</option>`);
        });

        $variantDom.trigger('change');
    })
});

function submitTeezilyForm() {
    var emptyDesigns = {};

    for (const element of $('#teezilyForm').serializeArray()) {
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

    var formdata = $('#teezilyForm').serializeArray().reduce((total, cur) => {
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

    const postdata = {
        email: "no-email@teezily.com",
        address: {
            first_name: "{{$order->full_name}}",
            last_name: "{{$order->full_name}}",
            street1: "{{$order->address_1}}",
            street2: "{{$order->address_2}}",
            city: "{{$order->city}}",
            postcode: "{{$order->zip_code}}",
            state: "{{$order->state}}",
            country_code: "US",
        },
        line_items: processedFormdata.map(item => {
            const designs = [];

            if (item.design_img_url) {
                designs.push({key: "front", url: item.design_img_url})
            }

            if (item.design_img_url_2) {
                designs.push({key: "back", url: item.design_img_url_2})
            }

            return {
                variant_reference: item.variant_id,
                quantity: +item.quantity,
                designs: designs
            }
        }),
    }

    console.log('----------------[data]', postdata)

    $.ajax({
        url : "{{url('/print-providers/teezily/create')}}",
        type: 'POST',
        data: {order_id: "{{$order->id}}", postdata: postdata, order_type: 1},
        async: true,
        success : function(res) {
            if(res.success == 1){
                $('#showAlert').html(res.message)
                console.log(res.message)
                $('#showAlert').show()
            }else{
                $('#showAlert').html(res.message?.[0]?.source + ': ' + res.message?.[0]?.detail)
                $('#showAlert').show()
            }
            console.log(res.data)
        },
        error: function(err) {
            alert(err.responseJSON.data.errors.reason)
        }
    })
}

// END teezily
// ///////////////////////////////////////////////////////
</script>

<div class="tab-pane" id="teezily" role="tabpanel" aria-labelledby="teezily-tab">
    <form id='teezilyForm'>
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
                                <select class="teezily js-example-basic-single" style="width: 100%" name="{{$item->id}}_product_id">
                                    <option value="0">-- loading --</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-3">
                            <label class="text-danger">Colors</label>
                            <div class="">
                                <select class="teezily js-example-basic-single" style="width: 100%" name="{{$item->id}}_variant_id">
                                    <option value="0">-- loading --</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-3">
                        </div>
                        <div class="col-3">
                            <label class="text-danger">Design ID (FRONT SIDE)</label>
                            <input type="number" class="form-control" name="{{$item->id}}_design_id" provider="teezily" placeholder="">
                            <input type="hidden" class="form-control" name="{{$item->id}}_design_img_url" provider="teezily">
                            <input type="hidden" name="{{$item->id}}_quantity" provider="teezily" value="{{$item->quantity}}">
                            <div>
                                <img class="{{$item->id}}_design_img" provider="teezily" src='' alt='' style='width: 100%; padding-top: 5px' />
                            </div>
                        </div>
                        <div class="col-9"></div>
                        <div class="col-3">
                            <label class="text-danger">Design ID (BACK SIDE)</label>
                            <input type="number" class="form-control" name="{{$item->id}}_design_id_2" provider="teezily" placeholder="">
                            <input type="hidden" class="form-control" name="{{$item->id}}_design_img_url_2" provider="teezily">
                            <div>
                                <img class="{{$item->id}}_design_img_2" provider="teezily" src='' alt='' style='width: 100%; padding-top: 5px' />
                            </div>
                        </div>

                    </div>
                </td>
            </tr>
            @endforeach
        </table>
        <div class="row" style="padding-top:20px">
            <div class="col">
                <button type="button" id='teezilySubmitBtn' class="btn btn-primary col-12 submit-button" onclick="submitTeezilyForm()" provider="teezily">Submit Teezily Order</button>
            </div>
        </div>
    </form>
</div>
