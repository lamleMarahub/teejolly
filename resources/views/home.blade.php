@extends('layouts.app')

@section('content')
<script>

function submitUpdateSetting(){
    var obj = $('#cookie_ts').val();
    console.log(obj);
    var posting = $.post("{{url('/user/updateSetting')}}", {
            cookie_ts: obj,
            '_token': $('input[name="_token"]').val(),
        });
    // console.log(posting);
    posting.done(function(response) {
        console.log(response);

        if (response.success == 1) {
            showAlert('OK')
            // location.reload(true);
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
}

function onKeywordKeyup(event) {
    // console.log('submitUpdateOrder');
    if (event.keyCode == 13) submitUpdateSetting();
}

$(document).ready(function() {
    $('input[type="checkbox"]').click(function(){
        var user_id = $(this).attr('user_id');
        if($(this).is(":checked")){
            showAlert("checked user_id: "+user_id);
        }else{
            showAlert("un-checked user_id: "+user_id);
        }
    });
});

</script>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">dashboard</div>
                <div class="card-body">
                    <form id="design_form" class="form">
                        @csrf
                        <input type="hidden" id="design_id" name="design_id"/>
                        <label for="cookie_ts">cookie ts</label>
                        <input type="text" class="form-control" id="cookie_ts" name="cookie_ts" value="{{Auth::user()->cookie}}" onkeyup="onKeywordKeyup(event)" required>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
