<script type='text/javascript'>
// ///////////////////////////////////////////////////////
// dreamship
$(document).ready(function() {
    var ROOT_PATH = "{{url('')}}";

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        async:true
    });

    var $dreamshipSelects = $('select[name$=product_id].dreamship').empty()
    $dreamshipSelects.append(`<option value=0>-- loading --</option>`)

    $.ajax({
        url : "{{url('/print-providers/dreamship/categories')}}",
        type: 'POST',
        data: {},
        async: true,
        success : function(res) {
            const categories = res.data.data.reduce((acc, item) => {
                const subItems = item.sub_categories.reduce((subAcc, subItem) => {
                    return [...subAcc, {...subItem, parentName: item.name}]
                }, [])

                return [...acc, ...subItems];
            },[]);

            $dreamshipSelects.empty()
            $dreamshipSelects.append(`<option value=0>-- choose product --</option>`)
            categories.forEach(element => {
                $dreamshipSelects.append(`<option value="${element.id}">${element.parentName} &gt; ${element.name}</option>`)
            });
        },
        error: function(err) {
            alert( "error" );
        }
    })

    $dreamshipSelects.on('change', function() {
        const categoryId = this.value
        const $itemDom = $(`select[name$=item_id].dreamship`).empty()
            .append(`<option value="0">-- loading --</option>`);

        $.ajax({
            url : ROOT_PATH + "/print-providers/dreamship/categories/"+ categoryId +"/items",
            type: 'POST',
            data: {},
            async: true,
            success : function(res) {
                const items = res.data.data.reduce((acc, item) => {
                    return [...acc, {id: item.id, name: item.name}];
                },[]);

                $itemDom.empty();
                items.forEach(element => {
                    $itemDom.append(`<option value="${element.id}">${element.name}</option>`);
                });

                $itemDom.trigger('change');
            },
            error: function(err) {
                alert( "error" );
            }
        })
    })

    $('select[name$=item_id].dreamship').on('change', function() {
        const rootPath = "{{url('')}}";
        const itemId = this.value
        const $varianDom = $(`select[name$=variant_id].dreamship`).empty()
            .append(`<option value="0">-- loading --</option>`);

        $.ajax({
            url : ROOT_PATH + "/print-providers/dreamship/items/" + itemId,
            type: 'POST',
            data: {},
            async: true,
            success : function(res) {
                const variants = res.data.item_variants.reduce((acc, item) => {
                    return [...acc, {...item}];
                },[]);

                $varianDom.empty();
                variants.forEach(element => {
                    $varianDom.append(`<option ${element.availability === 'in_stock' ? '' : 'disabled="disabled"'} value="${element.id}">${element.name}</option>`);
                });
            },
            error: function(err) {
                alert( "error" );
            }
        })
    })
})

function submitDreamshipForm() {
    var emptyDesigns = {};

    for (const element of $('#dreamshipForm').serializeArray()) {
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

    var formdata = $('#dreamshipForm').serializeArray().reduce((total, cur) => {
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
        address: {
          first_name: "{{$order->full_name}}",
          last_name: "",
          street1: "{{$order->address_1}}",
          street2: "{{$order->address_2}}",
          city: "{{$order->city}}",
          state: "{{$order->state}}",
          country: "US",
          zip: "{{$order->zip_code}}"
        },
        line_items: processedFormdata.map(item => {
            const areas = [];

            if (item.design_img_url) {
                areas.push({key: "front", url: item.design_img_url, position: "top_center", resize: "fit"})
            }

            if (item.design_img_url_2) {
                areas.push({key: "back", url: item.design_img_url, position: "top_center", resize: "fit"})
            }

            return {
               print_areas: areas,
               quantity: +item.quantity,
               item_variant: +item.variant_id
            }
        }),
    }

    console.log('----------------[data]', postdata)

    $.ajax({
        url : "{{url('/print-providers/dreamship/create')}}",
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

// END dreamship
// ///////////////////////////////////////////////////////
</script>

<div class="tab-pane" id="dreamship" role="tabpanel" aria-labelledby="dreamship-tab">
    <form id='dreamshipForm'>
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
                                <select class="dreamship js-example-basic-single" style="width: 100%" name="{{$item->id}}_product_id">
                                    <option value="0">-- loading --</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-3">
                            <label class="text-danger">Items</label>
                            <div class="">
                                <select class="dreamship js-example-basic-single" style="width: 100%" name="{{$item->id}}_item_id">
                                    <option value="0">-- loading --</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-3">
                            <label class="text-danger">Variants of a Items</label>
                            <div class="">
                                <select class="dreamship form-control" style="width: 100%" name="{{$item->id}}_variant_id">
                                    <option value="0">-- loading --</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-3">
                            <label class="text-danger">Design ID (FRONT SIDE)</label>
                            <input type="number" class="form-control" name="{{$item->id}}_design_id" provider="dreamship" placeholder="">
                            <input type="hidden" class="form-control" name="{{$item->id}}_design_img_url" provider="dreamship">
                            <input type="hidden" name="{{$item->id}}_quantity" provider="dreamship" value="{{$item->quantity}}">
                            <div>
                                <img class="{{$item->id}}_design_img" provider="dreamship" src='' alt='' style='width: 100%; padding-top: 5px' />
                            </div>
                        </div>
                        <div class="col-9"></div>
                        <div class="col-3">
                            <label class="text-danger">Design ID (BACK SIDE)</label>
                            <input type="number" class="form-control" name="{{$item->id}}_design_id_2" provider="dreamship" placeholder="">
                            <input type="hidden" class="form-control" name="{{$item->id}}_design_img_url_2" provider="dreamship">
                            <div>
                                <img class="{{$item->id}}_design_img_2" provider="dreamship" src='' alt='' style='width: 100%; padding-top: 5px' />
                            </div>
                        </div>

                    </div>
                </td>
            </tr>
            @endforeach
        </table>
        <div class="row" style="padding-top:20px">
            <div class="col">
                <button type="button" id='gearSubmitBtn' class="btn btn-primary col-12 submit-button" onclick="submitDreamshipForm()" provider="dreamship">Submit Dreamship Order</button>
                <pre id='dreamshipPostData'></pre>
            </div>
        </div>
    </form>
</div>
