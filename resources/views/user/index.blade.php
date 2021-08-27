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
    });
    
    
    function showUserModal(id) {
        $('#etsy_order_id').val(id); // input hidden orrder id 
        $('#design_modal').modal('show');
        $('#design_loading').show().removeClass('d-none').addClass('d-block');
    
        var posting = $.post("{{url('/user/showUserModal')}}",{id: id});
    
        posting.done(function(response) {
            
            if(response.success == 1){
                
                $('#user_id').val(response.data.id);
                $('#name').val(response.data.name);
                $('#email').val(response.data.email);
                $('#created_at').val(response.data.created_at);
                $('#updated_at').val(response.data.updated_at);
                
                $('#printify_shopid').val(response.data.printify_shopid);
                $('#printify_api').val(response.data.printify_api);
                $('#gearment_api_key').val(response.data.gearment_api_key);
                $('#gearment_api_signature').val(response.data.gearment_api_signature);
                $('#teezily_api').val(response.data.teezily_api);
                
                $("#is_active").val(response.data.is_active).change();
                $("#is_designer").val(response.data.is_designer).change();
                $("#is_seller").val(response.data.is_seller).change();
                
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
    
    function submitUpdateUser() {

        $('#design_loading').show().removeClass('d-none').addClass('d-block');
        
        var posting = $.post("{{url('/user/updateUserModel')}}", $("#design_form").serialize());
    
        posting.done(function(response) {
            // console.log(response);
            if (response.success == 1)
            {   
                $('#design_modal').modal('hide');
                showAlert('Infor was updated!');
                location.reload();  
            }else{
                showAlert('something went wrong');
            }
            $('#design_loading').hide().removeClass('d-block');
        });
        
        posting.fail(function(response) {
            showAlert( "Error: " + response );
        });
        
        posting.always(function(response) {
        });
    }

</script>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if(Auth::user()->id == 1)
            <table class="table table-hover">
                <thead class="thead-light">
                    <tr>
                    <th>#</th>
                    <th>full name</th>
                    <th>email</th>
                    <th class="align-middle text-center" nowrap>last login at</th>
                    <th class="align-middle text-center" nowrap>last login ip</th>
                    <th class="align-middle text-center" nowrap>updated_at</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($users as $user)
                <tr class="shop_row @if(!$user->is_active) table-danger @endif">
                    <td>{{$user->id}}</td>  
                    <td>
                        <a href="javascript:showUserModal({{$user->id}})"><img class="card-img-top" src="#" alt="">{{$user->name}}</a>
                    </td>
                    <td>{{$user->email}}</td>
                    <td class="align-middle text-center" nowrap>{{$user->last_login_at}}</td>
                    <td class="align-middle text-center" nowrap>{{$user->last_login_ip}}</td>
                    <td class="align-middle text-center" nowrap>{{$user->updated_at}}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>
</div>

<!--------- BEGIN DESIGN MODAL -------->
<div id="design_modal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">user details<span id="design_label" class="text-muted"></span> <img id="design_loading" class="d-none" style="width:25px;" src="{{asset('images/loading.gif')}}" alt='loading...'>   </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">      
                 <form id="design_form" class="form">
                    @csrf
                    <input type="hidden" id="user_id" name="user_id"/>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group row">
                                <div class="col-6">
                                    <label for="name">name</label>
                                    <input type="text" id="name" name="name" value="name" class="form-control form-control-sm">                       
                                </div>
                                <div class="col-6">
                                    <label for="email">email</label>
                                    <input type="text" id="email" name="email" value="email" class="form-control form-control-sm" disabled>                       
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-6">
                                    <label for="password">password</label>
                                    <input type="password" id="password" name="password" value="" class="form-control form-control-sm">                       
                                </div>
                                <div class="col-2">
                                    <label for="is_active">is_active</label>
                                    <select class="form-control form-control-sm text-danger" id="is_active" name="is_active">
                                        <option value="0">No</option>
                                        <option value="1">Yes</option>
                                    </select>
                                </div>
                                <div class="col-2">
                                    <label for="is_designer">is_designer</label>
                                    <select class="form-control form-control-sm text-danger" id="is_designer" name="is_designer">
                                        <option value="0">No</option>
                                        <option value="1">Yes</option>
                                    </select>
                                </div>
                                <div class="col-2">
                                    <label for="is_seller">is_seller</label>
                                    <select class="form-control form-control-sm text-danger" id="is_seller" name="is_seller">
                                        <option value="0">No</option>
                                        <option value="1">Yes</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-6">
                                    <label for="created_at">created_at</label>
                                    <input type="text" id="created_at" name="created_at" value="created_at" class="form-control form-control-sm" disabled>                       
                                </div>
                                <div class="col-6">
                                    <label for="updated_at">updated_at</label>
                                    <input type="text" id="updated_at" name="updated_at" value="updated_at" class="form-control form-control-sm" disabled>                       
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-6">
                                    <label for="printify_shopid">printify_shopid</label>
                                    <input type="text" id="printify_shopid" name="printify_shopid" value="printify_shopid" class="form-control form-control-sm">                       
                                </div>
                                <div class="col-6">
                                    <label for="printify_api">printify_api</label>
                                    <input type="text" id="printify_api" name="printify_api" value="printify_api" class="form-control form-control-sm">                       
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-6">
                                    <label for="gearment_api_key">gearment_api_key</label>
                                    <input type="text" id="gearment_api_key" name="gearment_api_key" value="gearment_api_key" class="form-control form-control-sm">                       
                                </div>
                                <div class="col-6">
                                    <label for="gearment_api_signature">gearment_api_signature</label>
                                    <input type="text" id="gearment_api_signature" name="gearment_api_signature" value="gearment_api_signature" class="form-control form-control-sm">                       
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-12">
                                    <label for="teezily_api">teezily_api</label>
                                    <input type="text" id="teezily_api" name="teezily_api" value="teezily_api" class="form-control form-control-sm">                       
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
                <a type="button" class="btn btn-success btn-block" href="javascript:submitUpdateUser();">update infor</a>                
            </div>
        </div>
    </div>
</div>        
<!--------- END DESIGN MODAL -------->
@endsection