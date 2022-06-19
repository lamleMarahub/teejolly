<script type='text/javascript'>
// ///////////////////////////////////////////////////////
// customcat
var ROOT_PATH = "{{url('')}}";
var CUSTOMCAT_CATEGORIES = [];

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    async:true
});

$.ajax({
    url : "{{url('/print-providers/customcat/categories')}}",
    type: 'POST',
    data: {},
    async: false,
    success : function(res) {
        CUSTOMCAT_CATEGORIES = res.data;
    },
    error: function(err) {
        alert( "customcat error" );
    }
})

$(document).ready(function() {
    var $customcatSelects = $('select[name$=product_id].customcat').empty()
    $customcatSelects.append(`<option value=0>-- choose product --</option>`)
    CUSTOMCAT_CATEGORIES.forEach(element => {
        $customcatSelects.append(`<option value="${element.catalog_product_id}">${element.product_name}</option>`)
    });

    $customcatSelects.on('change', function() {
        const categoryId = this.value
        const selectName = $(this).attr('name')
        const orderItemId = selectName.split('_')[0]
        const $colorDom = $(`select[name$=${orderItemId}_color_id].customcat`).empty()
            .append(`<option value="0">-- loading --</option>`);

        const category = CUSTOMCAT_CATEGORIES.find(item => item.catalog_product_id == categoryId);

        $colorDom.empty();
        category.product_colors.forEach(element => {
            $colorDom.append(`<option value="${element.product_color_id}">${element.color}</option>`);
        });

        $colorDom.trigger('change');
    })

    $('select[name$=color_id].customcat').on('change', function() {
        const colorId = this.value
        const selectName = $(this).attr('name')
        const orderItemId = selectName.split('_')[0]
        const categoryId = $(`select[name=${orderItemId}_product_id].customcat`).val();
        const $sizeDom = $(`select[name$=${orderItemId}_sku_id].customcat`).empty()
            .append(`<option value="0">-- loading --</option>`);

        const category = CUSTOMCAT_CATEGORIES.find(item => item.catalog_product_id == categoryId);
        const color = category.product_colors.find(item => item.product_color_id == colorId)

        $sizeDom.empty();
        color.skus.forEach(element => {
            $sizeDom.append(`<option ${element.in_stock >= 1 ? '' : 'disabled="disabled"'} value="${element.catalog_sku_id}">${element.size}</option>`);
        });
    })
});

function submitCustomCatForm() {
    var emptyDesigns = {};

    for (const element of $('#customCatForm').serializeArray()) {
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

    var formdata = $('#customCatForm').serializeArray().reduce((total, cur) => {
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
        shipping_first_name: "{{$order->full_name}}",
        shipping_last_name: "{{$order->full_name}}",
        shipping_address1: "{{$order->address_1}}",
        shipping_address2: "{{$order->address_2}}",
        shipping_city: "{{$order->city}}",
        shipping_state: "{{$order->state}}",
        shipping_country: "US",
        shipping_zip: "{{$order->zip_code}}",
        shipping_email: "no-email@customcat.com",
        shipping_phone: "555-555-5555",
        shipping_method: "Economy",
        sandbox: "1",
        items: processedFormdata.map(item => {
            return {
               catalog_sku: +item.sku_id,
               ...(item.design_img_url && {design_url: item.design_img_url}),
               ...(item.design_img_url_2 && {design_url_back: item.design_img_url_2}),
               quantity: +item.quantity
            }
        }),
    }

    console.log('----------------[data]', postdata)

    $.ajax({
        url : "{{url('/print-providers/customcat/create')}}",
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
}

// END customcat
// ///////////////////////////////////////////////////////
</script>

<div class="tab-pane" id="customcat" role="tabpanel" aria-labelledby="customcat-tab">
    <form id='customCatForm'>
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
                                <select class="customcat js-example-basic-single" style="width: 100%" name="{{$item->id}}_product_id">
                                    <option value="0">-- loading --</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-3">
                            <label class="text-danger">Colors</label>
                            <div class="">
                                <select class="customcat js-example-basic-single" style="width: 100%" name="{{$item->id}}_color_id">
                                    <option value="0">-- loading --</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-3">
                            <label class="text-danger">Sizes</label>
                            <div class="">
                                <select class="customcat form-control" style="width: 100%" name="{{$item->id}}_sku_id">
                                    <option value="0">-- loading --</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-3">
                            <label class="text-danger">Design ID (FRONT SIDE)</label>
                            <input type="number" class="form-control" name="{{$item->id}}_design_id" provider="customcat" placeholder="">
                            <input type="hidden" class="form-control" name="{{$item->id}}_design_img_url" provider="customcat">
                            <input type="hidden" name="{{$item->id}}_quantity" provider="customcat" value="{{$item->quantity}}">
                            <div>
                                <img class="{{$item->id}}_design_img" provider="customcat" src='' alt='' style='width: 100%; padding-top: 5px' />
                            </div>
                        </div>
                        <div class="col-9"></div>
                        <div class="col-3">
                            <label class="text-danger">Design ID (BACK SIDE)</label>
                            <input type="number" class="form-control" name="{{$item->id}}_design_id_2" provider="customcat" placeholder="">
                            <input type="hidden" class="form-control" name="{{$item->id}}_design_img_url_2" provider="customcat">
                            <div>
                                <img class="{{$item->id}}_design_img_2" provider="customcat" src='' alt='' style='width: 100%; padding-top: 5px' />
                            </div>
                        </div>

                    </div>
                </td>
            </tr>
            @endforeach
        </table>
        <div class="row" style="padding-top:20px">
            <div class="col">
                <button type="button" id='gearSubmitBtn' class="btn btn-primary col-12 submit-button" onclick="submitCustomCatForm()" provider="customcat">Submit Dreamship Order</button>
                <pre id='dreamshipPostData'></pre>
            </div>
        </div>
    </form>
</div>
