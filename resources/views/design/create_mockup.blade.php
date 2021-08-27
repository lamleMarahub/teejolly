@extends('layouts.app')

@section('content')
<script>
$(document).ready(function(){
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    doAjax();
});

function doAjax() {
    $('.img').each(function(i, obj) {
        console.log(obj);
        loadImage(obj);
    });
}

function loadImage(element) {
    var design_id = $(element).attr('design_id');
    var mockup_id = $(element).attr('id');
    var posting = $.post("{{url('/design/new_mockup')}}",{mockup_id:mockup_id, design_id:design_id});
    posting.done(function(response) {
        console.log(response);

        if (response.success == 1)
        {
            $(element).attr('src', response.data);
        } else {
            showAlert('Something wrong!');
        }
    });
    posting.fail(function(response) {
        showAlert( "Error: " + response );
    });
    posting.always(function(response) {
        //alert( "finished" );
    });
}
</script>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <h4>create mockups</h4>            
            <a class="btn btn-success" href="{{ url('/design/uploadmockup') }}" role="button">Upload Mockups</a>
        </div>
    </div>
    <div class="row mt-3 p-0">
        @foreach ($data['mockups'] as $m)        
        <div class="col-12 col-sm-6 col-md-4 col-lg-3 h-100 mb-3">
            <div class="card">
                <img class="img card-img-top" design_id="{{ $data['design_id'] }}" id="{{ $m->id }}" src="{{asset('images/loading.gif')}}">
            </div>                        
        </div>
        @endforeach
    </div>
</div>

@endsection