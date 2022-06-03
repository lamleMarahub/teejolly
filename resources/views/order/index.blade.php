@extends('layouts.app')

@section('content')
<script>
var asset ="{{ asset('') }}";
$(document).ready(function(){
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $("#order_check_all").change(function() {
        // showAlert('check all');
        checked = this.checked;
        $('input[name*=order_ids]').each(function() {
            if ($(this).attr('status') != 2) {
                $(this).prop('checked', checked);
            } else if (!checked) {
                $(this).prop('checked', checked);
            }
        });
    });

    $(".shop_row").click(function(event) {
        if ((event.target.type !== 'checkbox') && (!$(event.target).hasClass('getOrder'))) {
            var checked = $(':checkbox', this).prop('checked');
            $(':checkbox', this).prop('checked', !checked);
            if (!checked) {
                $('#order_check_all').prop('checked', false);
            }
        }
    });

    $("#filter_form").find("select").on('change', function (e) {
        $("#filter_form").submit();
    });
});

function onKeywordKeyup(event) {
    // console.log('submitUpdateOrder');
    if (event.keyCode == 13) submitUpdateOrder();
}

function timeConverter(UNIX_timestamp){
    var a = new Date(UNIX_timestamp * 1000);
    var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    var year = a.getFullYear();
    var month = months[a.getMonth()];
    var date = a.getDate();
    var hour = a.getHours();
    var min = a.getMinutes();
    var sec = a.getSeconds();
    var time = date + ' ' + month + ' ' + year + ' ' + hour + ':' + min + ':' + sec ;
    return time;
}

function showUpdateDesignModal(id) {
    $('#design_id').val(id);
    $('#design_modal').modal('show');
    // console.log($('#design_id').val(id));
    $('#design_loading').show().removeClass('d-none').addClass('d-block');

    var posting = $.post("{{url('/order/ajaxGetOrder')}}",{id: id});
    posting.done(function(response) {

        if (response.success == 1)
        {
            var d = response.data;
            var item = response.item;
            $('#design_label').html('#'+d.amz_order_id);

            $('#brand').val(d.brand);
            $('#amz_order_id').val(d.amz_order_id);
            $('#amz_order_date').val(timeConverter(d.amz_order_date));

            $('#fulfillment_id').val(d.fulfillment_id);
            $('#full_name').val(d.full_name);
            $('#address_1').val(d.address_1);
            $('#address_2').val(d.address_2);
            $('#city').val(d.city);
            $('#state').val(d.state);
            $('#zip_code').val(d.zip_code);
            $('#tracking_number').val(d.tracking_number);
            $('#carrier').val(d.carrier);
            $('#fulfillment_cost').val(d.fulfillment_cost);
            $("#order_status").val(d.status).change();
            $("#fulfillment_by").val(d.fulfillment_by).change();
            $("#note").val(d.note);

            var th = "<tr>"
                +"<th>Image</th>"
                +"<th>Product Details</th>"
                +"<th>Qty</th>"
                +"<th>Price</th>"
                +"<th>Shipping</th>"
                +"<th>Total</th>"
                +"</tr>";

            var tableContent = '<div class="table-responsive"><table id="tblData" class="table table-sm table-bordered"><thead>' + th + '</thead>';
            tableContent += '<tbody>';

            $.each(item, function(index,value) {
                console.log(value);
                var row = '<tr design_id=' + index + '>';
                var thumbnail = value.thumbnail;
                if(thumbnail != null){
                    thumbnail = value.thumbnail.replace('._SCLZZZZZZZ__SX55_','')
                }
                row += '<td class="align-middle text-center" nowrap>' + '<img class="card-img-top" src="'+thumbnail+'" style="max-width: 200px; max-height: 200px;">' + '</td>';
                row += '<td>' +
                            '<p><strong> ' + value.product_name +'</strong></p>'+
                            '<p><strong>SKU: </strong>' +value.sku+' - <strong>ASIN: </strong><a href="https://www.amazon.com/dp/'+ value.asin + '" target="_blank">' +value.asin+'</a> </p>'+
                            '<h5 class="text-danger">'+''+ value.style +' ; '+value.size+' ; '+value.color+'</h5>' +
                        '</td>';
                row += '<td class="align-middle text-center text-danger text-bold" nowrap><h5>' + value.quantity + '</h5></td>';
                row += '<td class="align-middle text-center text-danger" nowrap><h5>' + value.price + '</h5></td>';
                row += '<td class="align-middle text-center text-danger" nowrap><h5>' + value.shippingAmount + '</h5></td>';
                row += '<td class="align-middle text-center text-danger" nowrap><h5>' + value.totalAmount + '</h5></td>';
                row += '</tr>';

                tableContent += row;
            });

            tableContent += '</tbody></table></div>';
            $('#items').html(tableContent);

            $('#design_loading').hide().removeClass('d-block');
        } else {
            //showAlert( "Error: " + response );
        }
    });
    posting.fail(function(response) {
        showAlert( "Error: " + response );
    });
    posting.always(function(response) {
        //alert( "finished" );
    });
}

function processOrderId() {
    var result = true;

    var obj = $('#fulfillment_id');
    var t = $(obj).val().replace('  ', ' ');

    if ( t.length > 30) {
        obj.addClass('text-danger');
        result = false;
    } else {
        obj.removeClass('text-danger');
    }

    return result;
}

function submitUpdateOrder() {
    if (processOrderId() == false) {
       $('#fulfillment_id').focus();
       return;
    }

    $('#design_loading').show().removeClass('d-none').addClass('d-block');

    var posting = $.post("{{url('/order/ajaxUpdateOrder')}}", $("#design_form").serialize());
    posting.done(function(response) {
        console.log(response);

        if (response.success == 1)
        {
            $('#design_modal').modal('hide');
            showAlert('Order is updated!');
            location.reload();

        } else if (response.success == -1) {
            alert("You don't have permission to update this order!");
        } else {
            alert("Something went wrong!");
        }
        $('#design_loading').hide().removeClass('d-block');
    });
    posting.fail(function(response) {
        showAlert( "Error: " + response );
    });
    posting.always(function(response) {
        //alert( "finished" );
    });
}

function getOrders() {
    $("input[name*=order_ids]:checked").each(function(event, obj){
        // console.log(obj);
        var id = $(obj).attr('order_id');
        // console.log(id);
        if (id != '') {
            getOrder(obj);
        }
    });
}

function getOrder(element) {
    var order_id = $(element).attr('order_id');
    $('#order_loading'+order_id).removeClass('d-none');
    $('#order_check'+order_id).addClass('d-none');

    var posting = $.post("{{url('/order/ajaxTeescape')}}", {
        id: order_id,
        '_token': $('input[name="_token"]').val(),
    });
    posting.done(function(response) {
        console.log(response);
        showAlert(response.message);
        $('#order_loading'+order_id).addClass('d-none');
        $('#order_check'+order_id).removeClass('d-none');
    });
    posting.fail(function(response) {
        showAlert("Error: " + response);
        console.log(response);
    });
    posting.always(function(response) {
        //alert( "finished" );
    });
}

function deleteOrder() {

    if (!$("input[name*=order_ids]").is(":checked")) {
      showAlert('please select orders');
    }

    $("input[name*=order_ids]:checked").each(function(event, obj){
        var id = $(obj).attr('order_id');
        $('#order_loading'+id).removeClass('d-none');
        $('#order_check'+id).addClass('d-none');
        var posting = $.post("{{url('/order/ajaxDelete')}}", {
            id: id,
            '_token': $('input[name="_token"]').val(),
        });

        posting.done(function(response) {
            console.log(response);
            if (response.success == 1) {
                showAlert('orders was deleted');
                $('#order_loading'+id).addClass('d-none');
                $('#order_check'+id).removeClass('d-none');
            } else {
                showAlert('Something wrong!');
            }
        });
        posting.fail(function(response) {
            showAlert("Error: " + response);
            console.log(response);
        });
        posting.always(function(response) {
            // alert( "finished" );
        });
    });
    // location.reload(true);
}


</script>

<div class="container-fluid">
    <form id="filter_form" action="{{ url('orders') }}" method="get">
    @csrf
    <div class="row">
        <div class="col-sm-12 btn-toolbar justify-content-between" role="toolbar">
            <h4>orders <small class="text-muted">{{$data->firstItem()}}-{{$data->lastItem()}}/{{$data->total()}}</small></h4>
            <div class="justify-content-end">
                <div class="input-group float-left">
                    <input type="search" class="form-control form-control-sm" id="keyword" name="keyword" placeholder="search by order id/name" value="{{ $filters['keyword'] }}">
                    <a type="button" class="btn btn-sm btn-link input-group-append" onclick="document.getElementById('filter_form').submit();"><i class="fas fa-search m-auto"></i>&nbsp;</a>
                </div>
                @if(in_array(Auth::user()->id,[1]))
                <div class="btn-group btn-group-sm ml-2 float-left" role="group">
                    <select id="seller" name="seller" class="btn btn-sm form-control form-control-sm border bg-success">
                        <option value="0" @if ($filters['seller'] == '0') ? selected : '' @endif>- all seller -</option>
                        @foreach($sellers as $u)
                        <option value="{{$u->id}}" @if ($filters['seller'] == $u->id) ? selected : '' @endif>
                            {{$u->name}}
                        </option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="btn-group btn-group-sm ml-2 float-left" role="group">
                    <select id="brand_name" name="brand_name" class="btn btn-sm form-control form-control-sm border bg-success">
                        <option value="all" @if ($filters['brand_name'] == 'all') ? selected : '' @endif>- all brands -</option>
                        @foreach($brand as $b)
                        <option value="{{strtolower($b)}}" @if ($filters['brand_name'] == strtolower($b)) ? selected : '' @endif> @if($b == null) -- @else {{strtolower($b)}} @endif </option>
                        @endforeach
                    </select>
                </div>
                <div class="btn-group btn-group-sm ml-2 float-left" role="group">
                    <select id="status" name="status" class="btn btn-sm form-control form-control-sm border bg-success">
                        <option value="4" @if ($filters['status'] == 4) ? selected : '' @endif>- all status -</option>
                        <option value="0" @if ($filters['status'] == 0) ? selected : '' @endif>new order</option>
                        <option value="1" @if ($filters['status'] == 1) ? selected : '' @endif>in production</option>
                        <option value="2" @if ($filters['status'] == 2) ? selected : '' @endif>shipped</option>
                        <option value="3" @if ($filters['status'] == 3) ? selected : '' @endif>cancelled</option>
                    </select>
                </div>
                <div class="btn-group btn-group-sm ml-2 float-left" role="group">
                    <select id="fulfillment" name="fulfillment" class="btn btn-sm form-control form-control-sm border bg-success">
                        <option value="all" @if ($filters['fulfillment'] == "all") ? selected : '' @endif>- all printer -</option>
                        <option value="gearment" @if ($filters['fulfillment'] == "gearment") ? selected : '' @endif>gearment</option>
                        <option value="printify" @if ($filters['fulfillment'] == "printify") ? selected : '' @endif>printify</option>
                        <option value="teezily" @if ($filters['fulfillment'] == "teezily") ? selected : '' @endif>teezily</option>
                        <option value="teescape" @if ($filters['fulfillment'] == "teescape") ? selected : '' @endif>teescape</option>
                    </select>
                </div>
                <div class="btn-group btn-group-sm ml-1 float-left" role="group">
                    <a class="btn btn-sm btn-primary ml-2" href="javascript:getOrders();" role="button">get tracking</a>
                    @if(in_array(Auth::user()->id,[1,13])) <!--admin + sinh-->
                    <a class="btn btn-sm btn-danger ml-2" href="javascript:deleteOrder();" role="button" onclick="return confirm('Are you sure?')">delete order</a>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-sm-12 btn-toolbar justify-content-between mt-2" role="toolbar">
            <div class="pagination pagination-sm mx-auto">{{ $data->appends($filters)->links() }}</div>
        </div>
    </div>
    </form>
    <div class="row mt-3 p-0">
        <div class="col-12 my-2 table-responsive">
            <table class="table table-hover table-striped">
                <thead class="thead-light">
                    <tr>
                    <th scope="col"><input type="checkbox" id="order_check_all"></th>
                    <th scope="col"></th>
                    <th scope="col">order details</th>
                    <th scope="col">shop name</th>
                    <th scope="col" class="text-right" nowrap>order date</th>
                    <th scope="col" class="text-center" nowrap>order status</th>
                    <th scope="col" class="text-right" nowrap>cost</th>
                    <th scope="col" class="align-middle" nowrap>tracking code</th>
                    <th scope="col" class="text-right" nowrap>updated at</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($data as $d)
                <tr class="shop_row {{ $d->status == 2 ? 'table-success' : '' }}">
                    <th scope="row" class="align-middle">
                        <input type="checkbox" name="order_ids[]" order_id="{{$d->id}}" id="checkbox{{$d->id}}" status={{$d->status}}>
                    </th>
                    <th scope="row" class="align-middle text-center" style="width:30px;">
                        <a href="javascript:getOrder($('#checkbox{{$d->id}}'))" class="getOrder">
                        <label class="form-check-label align-middle d-none" id="order_loading{{$d->id}}">
                            <img style="width:16px" src="{{asset('images/loading.gif')}}" alt='checking...'>
                        </label>
                        <i class="fas fa-sync-alt" id="order_check{{$d->id}}"></i>
                        </a>
                    </th>
                    <td scope="row" class="align-middle">
                        <a href="javascript:showUpdateDesignModal({{$d->id}})">
                            <img class="card-img-top" src="#" alt="">{{$d->amz_order_id}} {{$d->full_name}} <span class="badge badge-light">{{number_format(App\OrderItem::where('order_id',$d->id)->sum('totalAmount'),2)}} USD </span>
                        </a>
                    </td>
                    <td scope="row" class="align-middle">
                        @if (Auth::user()->id == 1)
                            <span class="badge badge-pill badge-secondary">{{ $d->getOwner($d->owner_id)->name }}</span>&nbsp;
                        @endif
                        {{$d->brand}}
                    </td>
                    <td class="text-right" nowrap>
                        {{\Carbon\Carbon::createFromTimestamp($d->amz_order_date)->diffForHumans()}}
                    </td>
                    <td class="text-center" nowrap>
                        @if($d->status == 0) <a href="order/create/{{$d->id}}" class="btn btn-sm btn-warning" target="_blank">new order </a> @elseif($d->status == 1) <span class="badge badge-warning">in production</span> @elseif($d->status == 2) <span class="badge badge-warning">shipped</span> @else <span class="badge badge-dark">cancelled</span> @endif
                    </td>
                    <td class="text-right" nowrap>
                        {{$d->fulfillment_cost}}
                    </td>
                    <td class="align-middle" nowrap>
                        @if($d-> tracking_number != '') {{$d->tracking_number}} @else <span class="text-danger">{{$d->note}}</span> @endif
                    </td>
                    <td class="text-right" nowrap>
                        {{ $d->updated_at->diffForHumans() }}
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="d-flex justify-content-center pagination pagination-sm">{{ $data->links() }}</div>
</div>

<!--------- BEGIN DESIGN MODAL -------->
<div id="design_modal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">edit order <span id="design_label" class="text-muted"></span> <img id="design_loading" class="d-none" style="width:25px;" src="{{asset('images/loading.gif')}}" alt='loading...'>   </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="design_form" class="form">
                    @csrf
                    <input type="hidden" id="design_id" name="design_id"/>

                    <div class="row">
                        <div class="col-12">
                            <div class="form-group row">
                                <div class="col-4">
                                    <label for="brand">brand name</label>
                                    <input type="text" id="brand" name="brand" value="brand" class="form-control form-control-sm keyup" disabled>
                                </div>
                                <div class="col-4">
                                    <label for="amz_order_id">order id</label>
                                    <input type="text" id="amz_order_id" name="amz_order_id" value="amz_order_id" class="form-control form-control-sm" disabled>
                                </div>
                                <div class="col-2">
                                    <label for="amz_order_date">order date</label>
                                    <input type="text" id="amz_order_date" name="amz_order_date" value="amz_order_date" class="form-control form-control-sm" disabled>
                                </div>

                                <div class="col-2">
                                    <label for="order_status">order status</label>
                                    <select class="form-control form-control-sm" id="order_status" name="order_status">
                                        <option value="0">new order</option>
                                        <option value="1">in production</option>
                                        <option value="2">shipped</option>
                                        <option value="3">cancelled</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                               <div class="col-2">
                                    <label for="fulfillment_by">all printer</label>
                                    <select class="form-control form-control-sm" id="fulfillment_by" name="fulfillment_by">
                                        <option value="gearment">gearment</option>
                                        <option value="printify">printify</option>
                                        <option value="teezily">teezily</option>
                                        <option value="teescape">teescape</option>
                                        <option value="customcat">customcat</option>
                                    </select>
                                </div>
                                <div class="col-2">
                                    <label for="fulfillment_id">order id</label>
                                    <input type="text" id="fulfillment_id" name="fulfillment_id" value="fulfillment_id" class="form-control form-control-sm" onkeyup="onKeywordKeyup(event)" required>
                                </div>
                                <div class="col-4">
                                    <label for="tracking_number">tracking code</label>
                                    <input type="text" id="tracking_number" name="tracking_number" value="tracking_number" class="form-control form-control-sm">
                                </div>
                                <div class="col-2">
                                    <label for="carrier">carrier</label>
                                    <input type="text" id="carrier" name="carrier" value="carrier" class="form-control form-control-sm">
                                </div>
                                <div class="col-2">
                                    <label for="fulfillment_cost">cost</label>
                                    <input type="text" id="fulfillment_cost" name="fulfillment_cost" value="fulfillment_cost" class="form-control form-control-sm" disabled>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-4">
                                    <label for="full_name">full name</label>
                                    <input type="text" id="full_name" name="full_name" value="full_name" class="form-control form-control-sm keyup">
                                </div>
                                <div class="col-4">
                                    <label for="address_1">address 1</label>
                                    <input type="text" id="address_1" name="address_1" value="address_1" class="form-control form-control-sm keyup">
                                </div>
                                <div class="col-4">
                                    <label for="address_2">address 2</label>
                                    <input type="text" id="address_2" name="address_2" value="address_2" class="form-control form-control-sm keyup">
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-4">
                                    <label for="city">city</label>
                                    <input type="text" id="city" name="city" value="city" class="form-control form-control-sm keyup">
                                </div>
                                <div class="col-2">
                                    <label for="zip_code">zip code</label>
                                    <input type="text" id="zip_code" name="zip_code" value="zip_code" class="form-control form-control-sm">
                                </div>
                                <div class="col-2">
                                    <label for="state">state</label>
                                    <input type="text" id="state" name="state" value="state" class="form-control form-control-sm" disabled>
                                </div>
                                <div class="col-4">
                                    <label for="note">note</label>
                                    <input type="text" id="note" name="note" value="note" class="form-control form-control-sm text-danger">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="row">
                    <div id="items" class="col-12"></div>
                </div>
            </div>
            <div class="modal-footer">
                <a type="button" class="btn btn-success btn-block" href="javascript:submitUpdateOrder();">update order</a>
            </div>
        </div>
    </div>
</div>
<!--------- END DESIGN MODAL -------->

@endsection
