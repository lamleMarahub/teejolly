@extends('layouts.app')

@section('content')
<script>
$(document).ready(function(){
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
});

function formatBytes(bytes, decimals = 2) {
    if (bytes === 0) return '0 Bytes';

    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

function deleteItem(id,divId) {
    if (confirm('Are you sure?') == false) return;

    var posting = $.post("{{url('/mockup/delete')}}",{id:id});
    posting.done(function(data) {
        console.log(data);

        if (data == 1)
        {
            $(divId).fadeOut(300, function(){
                $(this).remove();
                showAlert('Mockup is deleted!');
            });
        } else {
            showAlert('Something wrong!');
        }
    });
    posting.fail(function(data) {
        showAlert( "Error: " + data );
    });
    posting.always(function(data) {
        //alert( "finished" );
    });
}
</script>

<style type="text/css">
.card-top-toolbar{ position: absolute; left: 0px; top: 0px; z-index:999;}
.card-img-top:hover {
    opacity: 0.9;
    filter: alpha(opacity=90); /* For IE8 and earlier */
}
.title:hover, {
  background-color: #E4D8B4;
}
a:hover {
    text-decoration: none;
}
</style>

<div class="container">
    <div class="row">
        <div class="col-sm-12 btn-toolbar justify-content-between" role="toolbar">
            <h4>mockups</h4>
            <div class="pagination pagination-sm">{{ $data->links() }}</div>
            <div class="">
                <a class="btn btn-sm btn-secondary" href="{{ url('mockup/create') }}" role="button"><i class="fas fa-upload"></i> create mockup</a>
            </div>
        </div>
    </div>

    <div class="row mt-3 p-0">
        @foreach ($data as $d)
        @if ($d->color=='dark')
        <?php $borderclass = 'border-dark'; ?>
        @else
        <?php $borderclass = ''; ?>
        @endif
        <div class="col-12 col-sm-6 col-md-4 col-lg-3 h-100 mb-3" style="font-size: 75%;" id="m{{ $d->id }}">
            <div class="card {{ $borderclass }}">
                <!-- 2021-07: move to S3 -->
{{--                <span class="card-top-toolbar"><a class="btn btn-transparent text-primary" href="{{ asset('storage/'.$d->filename) }}" target="_blank"><i class="fas fa-download"></i></a></span>--}}
{{--                <img class="card-img-top" src="{{ asset('storage/'.$d->filename) }}" alt="{{ $d->title }}">--}}
                <span class="card-top-toolbar"><a class="btn btn-transparent text-primary" href="{{ $d->file_url }}" target="_blank"><i class="fas fa-download"></i></a></span>
                <img class="card-img-top" src="{{ $d->file_url }}" alt="{{ $d->title }}">

                <div class="card-body p-2">
                    <div class="d-flex justify-content-between">
                        <a href="" target="_blank">{{ $d->color }}</a>
                        <span class="text-muted" style="white-space: nowrap;overflow: hidden;text-overflow: ellipsis;">{{ $d->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="d-flex justify-content-between text-muted">
                        <a href="" target="_blank">{{ $d->type }}</a>
                        <span><small><script>document.write(formatBytes({{ $d->size }}))</script> {{ $d->width }}x{{ $d->height }}</small></span>
                    </div>
                    <div class="d-flex justify-content-between text-muted">
                        <a href="" target="_blank">x,y</a>
                        <span><small>{{ $d->design_x }}x{{ $d->design_y }}</small></span>
                    </div>
                    <div class="d-flex justify-content-between text-muted">
                        <a href="" target="_blank">w,h</a>
                        <span><small>{{ $d->design_width }}x{{ $d->design_height }}</small></span>
                    </div>
                </div>

                <div class="card-footer text-muted p-2">
                    <div class="d-flex justify-content-between">
                        <span><label class="btn btn-sm m-0" style="background-color:{{ $d->color_code }}">&nbsp;&nbsp;&nbsp;</label> {{ $d->color_name }} <i class="fas fa-long-arrow-alt-right"></i> {{ $d->color_map }}</span>
                        <div class="group align-middle">
                            <a class="text-danger" style="height:25px; line-height:25px;" href="javascript:deleteItem({{ $d->id }},'#m{{ $d->id }}');"><i class="fas fa-trash-alt"></i> delete</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="m-0 my-2 p-0">
                <a class="title" href=""><i class="fas fa-edit"></i> {{ $d->title }}</a>
            </div>
        </div>
        @endforeach
    </div>
    <div class="d-flex justify-content-center pagination pagination-sm">{{ $data->links() }}</div>
</div>

@endsection
