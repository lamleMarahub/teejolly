@extends('layouts.app')

@section('content')
<script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $("#shop_check_all").change(function() {
            checked = this.checked;
            $('input[name*=shop_ids]').each(function() {
                if ($(this).attr('is_active') == 1) {
                    $(this).prop('checked', checked);
                } else if (!checked) {
                    $(this).prop('checked', checked);
                }
            });
        });

        $(".shop_row").click(function(event) {            
            if ((event.target.type !== 'checkbox') && (!$(event.target).hasClass('feedShop'))) {
                var checked = $(':checkbox', this).prop('checked');
                $(':checkbox', this).prop('checked', !checked);
                if (!checked) {
                    $('#shop_check_all').prop('checked', false);
                }
            }
        });

        $("#filter_form").find("select").on('change', function (e) {        
            $("#filter_form").submit();
        });

    });

    function feedShops() {        
        $("input[name*=shop_ids]:checked").each(function(event, obj){            
            //console.log(obj); 
            var id = $(obj).attr('shop_id');
            //console.log(id); 
            if (id != '') {      
                feedShop(obj);
            }
        });
    }

    function feedShop(element) {
        var shop_id = $(element).attr('shop_id');
        $('#shop_loading'+shop_id).removeClass('d-none');
        $('#shop_check'+shop_id).addClass('d-none');

        var posting = $.post("{{url('/etsy/feedShop')}}", {
            id: shop_id
        });
        posting.done(function(response) {
            //console.log(response);

            if (response.success == 1) {
                // $('.shop_name'+shop_id).html(response.data.shop_name);                              
                // showAlert(response.message);
                $('.shop_updated_at'+shop_id).html(response.data.updated_at);
            } else {
                // showAlert(response.message);
                // $('.shop_name'+shop_id).html(response.message);    
                $('.shop_updated_at'+shop_id).html(response.message);
            }

            $('#shop_loading'+shop_id).addClass('d-none');
            $('#shop_check'+shop_id).removeClass('d-none');
        });
        posting.fail(function(response) {
            showAlert("Error: " + response);
        });
        posting.always(function(response) {
            showAlert(response.message);
        });
    }
    
    function deleteShops(){
        $("input[name*=shop_ids]:checked").each(function(event, obj){            
            var id = $(obj).attr('shop_id');

            $('#shop_loading'+id).removeClass('d-none');
            $('#shop_check'+id).addClass('d-none');

            var posting = $.post("{{url('/etsy/ajaxDelete')}}", {
                id: id,
                '_token': $('input[name="_token"]').val(),
            });

            posting.done(function(response) {
                console.log(response);
                if (response.success == 1) {
                    showAlert('shops was deleted')
                    $('#shop_loading'+id).addClass('d-none');
                    $('#shop_check'+id).removeClass('d-none');
                    // location.reload(true);
                } else {
                    showAlert('something was wrong!');
                }
            });
            posting.fail(function(response) {
                showAlert("Error: " + response);
                // console.log(response);
            });
            posting.always(function(response) {
                // alert( "finished" );
            });        
        });  
    }
    
    function archiveShops(){
        $("input[name*=shop_ids]:checked").each(function(event, obj){            
            var id = $(obj).attr('shop_id');
            
            $('#shop_loading'+id).removeClass('d-none');
            $('#shop_check'+id).addClass('d-none');

            var posting = $.post("{{url('/etsy/ajaxArchive')}}", {
                id: id,
                '_token': $('input[name="_token"]').val(),
            });

            posting.done(function(response) {
                console.log(response);
                if (response.success == 1) {
                    showAlert('shops was archived')
                    $('#shop_loading'+id).addClass('d-none');
                    $('#shop_check'+id).removeClass('d-none');
                } else {
                    showAlert('something was wrong!');
                }
            });
            posting.fail(function(response) {
                // console.log(response);
            });
            posting.always(function(response) {
                // console.log(response);
            });        
        });  
    }
    function getReceipts(){
        if (!$("input[name*=shop_ids]").is(":checked")) {
            showAlert('please select orders');
        }
        
        $("input[name*=shop_ids]:checked").each(function(event, obj){            
            var id = $(obj).attr('shop_id');
            $('#shop_loading'+id).removeClass('d-none');
            var getting = $.get("{{url('/etsy')}}/"+id+"/order");
            
            getting.done(function(response) {
                if(response.success==1){
                    showAlert(response.data);
                    $('#shop_loading'+id).addClass('d-none');
                    $('.shop_updated_at'+id).html('success');
                }else{
                    showAlert(response.data);
                    $('.shop_updated_at'+id).html('false');
                }
            });
            getting.fail(function(response) {
                console.log(response);
            });
            getting.always(function(response) {
                console.log(response);
            });        
        });  
    }
    
</script>
<style type="text/css">
a:hover {
    text-decoration: none;
}
</style>

<div class="container">
    
    <div class="row">
        <div class="col-sm-12 btn-toolbar justify-content-between" role="toolbar">
            <h4>etsy shops <small class="text-muted">{{$data->firstItem()}}-{{$data->lastItem()}}/{{$data->total()}} </small></h4>
            <div class="justify-content-end">
                <form id="filter_form" action="{{ url('etsy') }}" method="get">
                @csrf
                <div class="input-group float-left">
                    <input type="search" class="form-control form-control-sm" id="keyword" name="keyword" placeholder="search by shop name/email" value="{{ $filters['keyword'] }}"> 
                    <a type="button" class="btn btn-sm btn-link input-group-append" onclick="document.getElementById('filter_form').submit();"><i class="fas fa-search m-auto"></i>&nbsp;</a>
                </div>
                @if (Auth::user()->id == 1)
                <div class="btn-group btn-group-sm ml-2 float-left" role="group">                    
                    <select id="owner_id" name="owner_id" class="btn btn-sm form-control form-control-sm border bg-light" style="width:auto;">
                        <option value="0" @if ($owner_id == 0) ? selected : "" @endif>-all sellers-</option>
                        @foreach ($users as $u)
                        @if ($u->isActive() && !$u->isDeleted() && $u->isSeller())
                        <option value="{{ $u->id }}" @if ($owner_id == $u->id) ? selected : '' @endif>{{ $u->name }}</option>
                        @endif
                        @endforeach
                    </select>   
                </div>
                @endif
                <div class="btn-group btn-group-sm ml-2 float-left" role="group">
                    <a class="btn btn-sm btn-primary ml-2" data-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">create new</a>
                    <!--<a href="javascript:feedShops();" class="btn btn-sm btn-primary ml-2" role="button">check sales</a>-->
                    <a href="javascript:getReceipts();" class="btn btn-sm btn-danger ml-2" role="button">receipts</a>
                    @if (Auth::user()->id == 1)
                    <a href="javascript:deleteShops();" class="btn btn-sm btn-danger ml-2" role="button" onclick="return confirm('Are you sure?')">delete</a>
                    <a href="javascript:archiveShops();" class="btn btn-sm btn-danger ml-2" role="button" onclick="return confirm('Are you sure?')">archive</a>
                    @endif
                </div>
                </form>
            </div>
        </div>
        
        <div class="col-12 collapse" id="collapseExample" style="padding-top: 10px">
            <div class="card card-body">
                <form action="{{ asset('etsy/store') }}" method="post" class="form">
                    @csrf                   
                    <div class="input-group col-10 mx-auto">
                        <div class="input-group-prepend">
                            <span class="input-group-text">shop url</span>
                        </div>
                        <input type="text" class="form-control" id="shop_url" name="shop_url" placeholder="full shop url: https://www.etsy.com/shop/<Shop_Name>" autofocus>
                        
                        <div class="input-group-append">                            
                            <button type="submit" class="btn btn-primary">create new</button>                                
                        </div>
                    </div>                    
                </form>                
            </div>
        </div>
        <div class="col-sm-12 btn-toolbar justify-content-between mt-2" role="toolbar">            
            <div class="pagination pagination-sm mx-auto">{{ $data->appends($filters)->links() }}</div>            
        </div>
    </div>
    <div class="row mt-3 p-0">   
        <div class="col-12 my-2 table-responsive">
            <table class="table table-hover">
                <thead class="thead-light">
                    <tr>            
                    <th scope="col"><input type="checkbox" id="shop_check_all"></th>       
                    <th scope="col"></th> 
                    <th scope="col">title</th>
                    <th scope="col" class="text-right">revenue</th>
                    <th scope="col" class="text-right">fulfillment</th>
                    <th scope="col" class="text-right">bank account</th>
                    <th scope="col" class="text-right">archived</th>
                    <th scope="col" class="text-right">last checked</th>
                    <th scope="col">action</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($data as $d)
                <tr class="shop_row {{ $d->is_active == false ? 'table-danger' : '' }} {{ $d->note != null ? 'table-warning' : '' }}">    
                    <th scope="row" class="align-middle">
                        <input type="checkbox" name="shop_ids[]" shop_id="{{$d->id}}" id="checkbox{{$d->id}}" is_active={{$d->is_active}}>
                    </th>
                    <th scope="row" class="align-middle text-center" style="width:30px;">
                        <a href="javascript:feedShop($('#checkbox{{$d->id}}'))" class="feedShop">
                        <label class="form-check-label align-middle d-none" id="shop_loading{{$d->id}}">
                            <img style="width:16px" src="{{asset('images/loading.gif')}}" alt='checking...'>
                        </label>    
                        <i class="fas fa-redo-alt" id="shop_check{{$d->id}}"></i> check
                        </a>                        
                    </th>
                    <th scope="row">
                        <a href="{{ $d->shop_url }}" target="_blank">
                        @if (Auth::user()->id == 1)
                            <span class="badge badge-pill badge-secondary">{{ $users[$d->owner_id]->name }}</span>&nbsp;
                        @endif
                        <span class="shop_name{{$d->id}}">{{ $d->shop_name }}</span> <span class="badge badge-pill badge-light">{{ App\CollectionsEtsy::where('shop_id',$d->id)->count()}}</span><br/>
                        <small class="text-muted">                            
                            {{ $d->shop_url }}                     
                        </small>
                        </a>
                    </th>
                    <td class="align-middle text-right" nowrap>
                        <span class="text-primary">{{ number_format(App\EtsyOrder::where('seller_user_id',$d->user_id)->sum('grandtotal'),2)}} {{App\EtsyOrder::where('seller_user_id',$d->user_id)->value('currency_code')}}</span></br>
                        <small class="text-muted">                            
                            {{ number_format(App\EtsyOrder::where('seller_user_id',$d->user_id)->sum('revenue'),2)}} USD                           
                        </small>
                    </td>
                    <td class="align-middle text-right" nowrap>
                    	<span class="text-danger">{{ number_format(App\EtsyOrder::where('seller_user_id',$d->user_id)->sum('fulfillment_cost'),2)}} USD</span>
                    </td>
                    <td class="align-middle text-right" nowrap>
                        {{$d->bank_account}}
                    </td>
                    <td class="align-middle text-center" nowrap>
                        <input type="checkbox" {{$d->archived == 1 ? 'checked' : ''}} disabled>
                    </td>
                    <td class="align-middle shop_updated_at{{$d->id}} text-right" nowrap>
                        {{ $d->updated_at->diffForHumans() }}
                    </td>  
                    <td class="align-middle" nowrap>     
                        <a href="{{ url('etsy/'.$d->id.'/edit') }}" class="btn btn-primary btn-sm" role='button'>setting</a>
                    </td>                           
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="col-sm-12 d-flex justify-content-center pagination pagination-sm">{{ $data->links() }}</div>
    </div>

</div>

@endsection