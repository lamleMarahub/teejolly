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

function showOrderModal(id) {
    $('#etsy_order_id').val(id); // input hidden orrder id 
    $('#design_modal').modal('show');
    $('#design_loading').show().removeClass('d-none').addClass('d-block');

    var posting = $.post("{{url('/etsy/showOrderModal')}}",{id: id});

    posting.done(function(response) {

        console.log('>> order number: '+response.data[0].receipt_id);
        
        if(response.success == 1){

            $('#receipt_id').val(response.data[0].receipt_id);
            $('#order_id').val(response.data[0].order_id);
            // $('#was_paid').val(response.data[0].was_paid);
            // $('#was_shipped').val(response.data[0].was_shipped);
            // $('#shipped_date').val(timeConverter(response.data[0].shipped_date));
            // $('#payment_method').val(response.data[0].payment_method);
            // $('#payment_email').val(response.data[0].payment_email);
            // $('#message_from_payment').val(response.data[0].message_from_payment);
            // $("#is_update").val(response.data[0].is_update).change();
            
            $('#name').val(response.data[0].name);
            $('#formatted_address').val(response.data[0].formatted_address.replace(/[\r\n]+/g," "));
            
            
            $('#full_name').val(response.data[0].name);
            $('#address').val(response.data[0].first_line);
            $('#second_line').val(response.data[0].second_line);
            $('#city').val(response.data[0].city);
            $('#postcode').val(response.data[0].zip);
            $('#state').val(response.data[0].state);
            $('#buyer_email').val(response.data[0].buyer_email);
            $('#message_from_buyer').val(response.data[0].message_from_buyer);
            $('#note').val(response.data[0].note);

            $("#fulfillment_by").val(response.data[0].fulfillment_by).change();
            $('#fulfillment_id').val(response.data[0].fulfillment_id);
            $('#tracking_number').val(response.data[0].tracking_code);
            $('#fulfillment_carrier').val(response.data[0].fulfillment_carrier);
            $('#fulfillment_cost').val(response.data[0].fulfillment_cost);
            $('#shop_name').val(response.data[2].shop_name);
            $('#bank_account').val(response.data[2].bank_account);
            
            if(response.data[2].is_active == 0){
                $('#shop_status').val("Uh Oh !"); 
            }else{
                $('#shop_status').val("Live");
            }
            
            $("#order_status").val(response.data[0].status).change();
            
            var th = "<tr>"                
                +"<th>Product Details</th>"
                +"<th class='text-center'>Quantity</th>"
                // +"<th class='text-center'>Digital</th>"
                +"</tr>";

            var tableContent = '<div class="table-responsive"><table id="tblData" class="table table-sm table-bordered"><thead>' + th + '</thead>'; 
            tableContent += '<tbody>';

            $.each(response.data[1], function(index,item) {
                var row = '<tr>';
                row += '<td><p><strong> <a href="https://www.etsy.com/listing/'+ item.listing_id + '" target="_blank">' + item.title +'('+item.variation_1 +' ; '+item.variation_2+')' +'</a></strong></p></td>'; 
                // row += '<td class="align-middle text-center text-danger" nowrap><strong>' + item.variation_1 + '</strong></td>';
                // row += '<td class="align-middle text-center text-danger" nowrap><strong>' + item.variation_2 + '</strong></td>';
                row += '<td class="align-middle text-center text-danger" nowrap><strong>' + item.quantity + '</strong></td>';
                // row += '<td class="align-middle text-center text-danger" nowrap><strong>' + item.is_digital + '</strong></td>';
                row += '</tr>';

                tableContent += row;
            });

            tableContent += '</tbody></table></div>';

            $('#items').html(tableContent);
            
            $('#design_loading').hide().removeClass('d-block');      

        }
    });

    posting.fail(function(response) {
        showAlert( "Error: " + response );
    });

    posting.always(function(response) {
        // console.log('>> order detail');     
    });
}

function submitUpdateOrder() {

    $('#design_loading').show().removeClass('d-none').addClass('d-block');
    
    var posting = $.post("{{url('/etsy/updateOrderModel')}}", $("#design_form").serialize());

    posting.done(function(response) {
        console.log(response);
        if (response.success == 1)
        {   
            $('#design_modal').modal('hide');
            showAlert('order was updated!');
            location.reload();  
        }else{
            showAlert('something went wrong');
        }
        $('#design_loading').hide().removeClass('d-block');
    });
    posting.fail(function(response) {
        showAlert( "Error: " + response );
        console.log(response);
    });
    posting.always(function(response) {
        console.log(response);
    });
}

function getOrders() {    
    if (!$("input[name*=order_ids]").is(":checked")) {
      showAlert('please select orders');
    }

    $("input[name*=order_ids]:checked").each(function(event, obj){            
        var id = $(obj).attr('order_id');
        if (id != '') {      
            getOrder(obj);
        }
    });
}

function getOrder(element) {
    var order_id = $(element).attr('order_id');
    
    $('#order_loading'+order_id).removeClass('d-none');
    $('#order_check'+order_id).addClass('d-none');

    var posting = $.post("{{url('/etsy/getTracking')}}", {id: order_id});

    posting.done(function(response) {
        $('#order_loading'+order_id).addClass('d-none');
        $('#order_check'+order_id).removeClass('d-none');
    });

    posting.fail(function(response) {
        showAlert("Error: " + response);
    });

    posting.always(function(response) {
        console.log('>> order detail: '+ order_id);
        console.log(response);
    });
}

function submitTrackings() {    
    if (!$("input[name*=order_ids]").is(":checked")) {
      showAlert('please select orders');
    }

    $("input[name*=order_ids]:checked").each(function(event, obj){            
        var id = $(obj).attr('order_id');
        // var order_id = $(obj).attr('order_id');
        
        $('#order_loading'+id).removeClass('d-none');
        $('#order_check'+id).addClass('d-none');

        var posting = $.post("{{url('/etsy/submitTracking')}}", {id: id});

        posting.done(function(response) {
            $('#order_loading'+id).addClass('d-none');
            $('#order_check'+id).removeClass('d-none');
            // location.reload();
        });
    
        posting.fail(function(response) {
            showAlert("Error: " + response);
            // console.log(response);
        });
    
        posting.always(function(response) {
            console.log('>> order id: '+ id);
            console.log(response.data);
        });
    });
}

function deleteOrders() {    
    if (!$("input[name*=order_ids]").is(":checked")) {
      showAlert('please select orders');
    }

    $("input[name*=order_ids]:checked").each(function(event, obj){            
        var id = $(obj).attr('order_id');
        // var order_id = $(obj).attr('order_id');
        
        $('#order_loading'+id).removeClass('d-none');
        $('#order_check'+id).addClass('d-none');

        var posting = $.post("{{url('/etsy/deleteOrder')}}", {id: id});

        posting.done(function(response) {
            $('#order_loading'+id).addClass('d-none');
            $('#order_check'+id).removeClass('d-none');
            showAlert('Order id #'+id+ ' was deleted');
            // console.log(response);
            // location.reload(); 
        });
    
        posting.fail(function(response) {
            showAlert("Error: " + response);
        });
    
        posting.always(function(response) {
            console.log(response);
        });
    });
}

</script>

<div class="container-fluid">
    <form id="filter_form" action="{{ url('etsy/orders') }}" method="get">
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
                        @foreach(App\User::where('is_seller',1)->where('is_active',1)->get() as $u)
                        <option value="{{$u->id}}" @if ($filters['seller'] == $u->id) ? selected : '' @endif> 
                            {{$u->name}}
                        </option>
                        @endforeach
                    </select> 
                </div>
                @endif
                <div class="btn-group btn-group-sm ml-2 float-left" role="group">                    
                    <select id="shop_id" name="shop_id" class="btn btn-sm form-control form-control-sm border bg-success">
                        <option value="all" @if ($filters['shop_id'] == 'all') ? selected : '' @endif>- all shops -</option>
                        @foreach($seller_ids as $seller_id)
                        <option value="{{$seller_id}}" @if ($filters['shop_id'] == $seller_id) ? selected : '' @endif> 
                            {{App\EtsyShop::where('user_id',$seller_id)->value('shop_name')}}
                        </option>
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
                        <!--<option value="customcat" @if ($filters['fulfillment'] == "customcat") ? selected : '' @endif>customcat</option>-->
                        <option value="teescape" @if ($filters['fulfillment'] == "teescape") ? selected : '' @endif>teescape</option> 
                    </select> 
                </div>
                <div class="btn-group btn-group-sm ml-1 float-left" role="group">
                    <a class="btn btn-sm btn-primary ml-1" href="javascript:getOrders();" role="button">get tracking</a>                    
                    <a class="btn btn-sm btn-primary ml-1" href="javascript:submitTrackings();" role="button">submit tracking</a>        
                    @if(Auth::user()->isAdmin())
                    <a class="btn btn-sm btn-danger ml-1" href="javascript:deleteOrders();" role="button" onclick="return confirm('Are you sure?')">delete order</a>                    
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
                        <th scope="col">order detail</th>
                        <th scope="col">shop name</th>
                        <th scope="col" class="text-center" nowrap>country</th>
                        <th scope="col" class="text-right" nowrap>order date</th>
                        <th scope="col" class="text-right" nowrap>shipped date</th>
                        <th scope="col" class="text-center" nowrap>order status</th>
                        <th scope="col" class="text-right" nowrap>cost</th>
                        <th scope="col">tracking code</th>
                        <th scope="col" class="text-right" nowrap>updated</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($data as $d)
                <tr class="shop_row @if((App\EtsyShop::where('user_id',$d->seller_user_id)->value('is_active') == 0) && $d->status != 2) table-danger @elseif($d->status == 2) table-success @else @endif">
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
                        <a href="javascript:showOrderModal({{$d->id}})"><img class="card-img-top" src="#" alt="">{{$d->order_id}} {{$d->name}}</a> 
                        @if(App\EtsyShop::where('user_id',$d->seller_user_id)->value('bank_account') == '')
                        <span class="badge badge-warning">{{$d->grandtotal}} {{$d->currency_code}}</span>
                        @else
                        <span class="badge badge-light">{{$d->grandtotal}} {{$d->currency_code}}</span>
                        @endif
                    </td>
                    @if(App\EtsyShop::where('user_id',$d->seller_user_id)->value('is_active') == 0)
                    <td class="align-middle text-danger" nowrap>
                    @else
                    <td class="align-middle" nowrap>
                    @endif
                        @if (Auth::user()->id == 1)
                            <span class="badge badge-pill badge-secondary">{{ $d->getOwner($d->owner_id)->name }}</span>&nbsp;
                        @endif
                        {{App\EtsyShop::where('user_id',$d->seller_user_id)->value('shop_name')}}
                    </td>
                    <td class="align-middle text-center" nowrap>
                        {{$d->country_code}}
                    </td>
                    <td class="text-right" nowrap>
                        {{\Carbon\Carbon::createFromTimestamp($d->creation_tsz)->diffForHumans()}}
                    </td>
                    <td class="text-right" nowrap>
                        {{\Carbon\Carbon::createFromTimestamp($d->shipped_date)->diffForHumans()}}
                    </td>
                    <td class="text-center" nowrap>
                        @if($d->status == 0) <span class="badge badge-warning">new order</span> @elseif($d->status == 1) <span class="badge badge-warning">in production</span> @elseif($d->status == 2) <span class="badge badge-warning">shipped</span> @else <span class="badge badge-dark">cancelled</span> @endif
                    </td>
                    <td class="text-right" nowrap>
                        {{number_format($d->fulfillment_cost,2)}}
                    </td>
                    <td class="align-middle" nowrap>
                        @if($d-> tracking_code != '') {{$d->tracking_code}} @else <span class="text-danger">{{$d->note}}</span> @endif
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
    <div class="d-flex justify-content-center pagination pagination-sm">{{ $data->appends($filters)->links() }}</div>
</div>

<!--------- BEGIN DESIGN MODAL -------->
<div id="design_modal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">order details<span id="design_label" class="text-muted"></span> <img id="design_loading" class="d-none" style="width:25px;" src="{{asset('images/loading.gif')}}" alt='loading...'>   </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">      
                 <form id="design_form" class="form">
                    @csrf
                    <input type="hidden" id="etsy_order_id" name="etsy_order_id"/>
                    <div class="row">
                        <div class="col-12"> 
                            <div class="form-group row">                                
                                <div class="col-2">
                                    <label for="number">receipt_id</label>
                                    <input type="text" id="receipt_id" name="receipt_id" value="receipt_id" class="form-control form-control-sm" disabled>                        
                                </div>
                                <div class="col-2">
                                    <label for="order_id">order_id</label>
                                    <input type="text" id="order_id" name="order_id" value="order_id" class="form-control form-control-sm" disabled>                        
                                </div>
                                <div class="col-3">
                                    <label for="shop_name">shop name</label>
                                    <input type="text" id="shop_name" name="shop_name" value="shop_name" class="form-control form-control-sm" disabled>                       
                                </div>
                                <div class="col-1">
                                    <label for="shop_status">status</label>
                                    <input type="text" id="shop_status" name="shop_status" value="shop_status" class="form-control form-control-sm" disabled>                       
                                </div>
                                <div class="col-2">
                                    <label for="bank_account">bank account</label>
                                    <input type="text" id="bank_account" name="bank_account" value="bank_account" class="form-control form-control-sm" disabled>                       
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
                                    <label for="fulfillment_by">printer</label>
                                    <select class="form-control form-control-sm" id="fulfillment_by" name="fulfillment_by">
                                        <option value="gearment">gearment</option>
                                        <option value="printify">printify</option>
                                        <option value="teezily">teezily</option>
                                        <!--<option value="customcat">customcat</option>-->
                                        <option value="teescape">teescape</option>
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
                                    <label for="fulfillment_carrier">carrier</label>
                                    <input type="text" id="fulfillment_carrier" name="fulfillment_carrier" value="fulfillment_carrier" class="form-control form-control-sm">                        
                                </div>
                                <div class="col-2">
                                    <label for="fulfillment_cost">base cost</label>
                                    <input type="text" id="fulfillment_cost" name="fulfillment_cost" value="fulfillment_cost" class="form-control form-control-sm" disabled>    
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-4">
                                    <label for="full_name">full name</label>
                                    <input type="text" id="full_name" name="full_name" value="full_name" class="form-control form-control-sm keyup">                       
                                </div>
                                <div class="col-4">
                                    <label for="address">address</label>
                                    <input type="text" id="address" name="address" value="address" class="form-control form-control-sm keyup">                       
                                </div>
                                <div class="col-4">
                                    <label for="second_line">address 2</label>
                                    <input type="text" id="second_line" name="second_line" value="second_line" class="form-control form-control-sm keyup">                       
                                </div>
                            </div>
                           
                            <div class="form-group row">
                                <div class="col-4">
                                    <label for="city">city</label>
                                    <input type="text" id="city" name="city" value="city" class="form-control form-control-sm keyup">                        
                                </div>                                 
                                <div class="col-2">
                                    <label for="postcode">post code</label>
                                    <input type="text" id="postcode" name="postcode" value="postcode" class="form-control form-control-sm">                        
                                </div>
                                <div class="col-2">
                                    <label for="state">state</label>
                                    <input type="text" id="state" name="state" value="state" class="form-control form-control-sm">
                                </div>
                                <div class="col-4">
                                    <label for="buyer_email">buyer email</label>
                                    <input type="text" id="buyer_email" name="buyer_email" value="buyer_email" class="form-control form-control-sm">
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-12">
                                    <label for="formatted_address">formatted address</label>
                                    <input type="text" id="formatted_address" name="formatted_address" value="formatted_address" class="form-control form-control-sm" disabled>                        
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-12">
                                    <label for="message_from_buyer">message from buyer</label>
                                    <input type="text" id="message_from_buyer" name="message_from_buyer" value="message_from_buyer" class="form-control form-control-sm text-danger" disabled>                        
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-12">
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