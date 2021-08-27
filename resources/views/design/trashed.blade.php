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
    //if (confirm('Are you sure?') == false) return;

    var posting = $.post("{{url('/design/forceDelete')}}",{id:id});
    posting.done(function(data) {
        console.log(data);

        if (data == 1)
        {
            $(divId).fadeOut(300, function(){
                $(this).remove();
                showAlert('Design is deleted!');
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

function restoreItem(id,divId) {
    //if (confirm('Are you sure?') == false) return;

    var posting = $.post("{{url('/design/restore')}}",{id:id});
    posting.done(function(data) {
        console.log(data);

        if (data == 1)
        {
            $(divId).fadeOut(300, function(){
                $(this).remove();
                showAlert('Design is restored!');
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
.title:hover, .tags:hover {
  background-color: #E4D8B4;
}
a:hover {
    text-decoration: none;
}
</style>

<div class="container">
    <div class="row">
        <div class="col-sm-12 btn-toolbar justify-content-between" role="toolbar">
            <h4>designs <span class="text-danger"> [trashed]</span></h4>
            <div class="pagination pagination-sm">{{ $data->links() }}</div>
            <div class="">
                <a class="btn btn-sm btn-primary" href="{{ url('/design/upload') }}" role="button"><i class="fas fa-file-upload"></i> designs</a>
                <a class="btn btn-sm btn-secondary" href="{{ url('/design/uploadmockup') }}" role="button"><i class="fas fa-upload"></i> mockups</a>
            </div>
        </div>
    </div>

    <div class="row mt-3 p-0">
        @foreach ($data as $d)
        <div class="col-12 col-sm-6 col-md-4 col-lg-3 h-100 mb-3" style="font-size: 75%;" id="d{{ $d->id }}">
            <div class="card">
                <span class="card-top-toolbar">
                    <!-- 2021-07: move to S3 -->
{{--                <a class="btn btn-transparent text-primary" href="{{ asset('storage/'.$d->filename) }}" target="_blank"><i class="fas fa-download"></i></a>--}}
                    <a class="btn btn-transparent text-primary" href="{{ $d->file_url }}" target="_blank"><i class="fas fa-download"></i></a>
                </span>
                <!-- 2021-07: move to S3 -->
{{--                <img class="card-img-top" src="{{ asset('storage/'.$d->thumbnail) }}" alt="{{ $d->title }}">--}}
                <img class="card-img-top" src="{{ $d->thumbnail_url }}" alt="{{ $d->title }}">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between">
                        <a href="" target="_blank"><i class="fas fa-user-plus"></i> owner</a>
                        <span class="text-muted" style="white-space: nowrap;overflow: hidden;text-overflow: ellipsis;">{{ $d->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="d-flex justify-content-between text-muted">
                        <a href="" target="_blank"><i class="fas fa-user-edit"></i> designer</a>
                        <span><small><script>document.write(formatBytes({{ $d->size }}))</script> {{ $d->width }}x{{ $d->height }}</small></span>
                    </div>
                </div>

                <div class="card-footer text-muted p-2">
                    <div class="d-flex justify-content-between">
                        <span>0 collection, 0 sale</span>
                        <div class="group">
                            <a href="javascript:restoreItem({{ $d->id }},'#d{{ $d->id }}');" class="text-success"><i class="fas fa-undo-alt"></i> restore</a> &nbsp;
                            <a href="javascript:deleteItem({{ $d->id }},'#d{{ $d->id }}');" class="text-danger"><i class="fas fa-trash-alt"></i> delete</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="m-0 p-0">
                <a class="title" href=""><i class="fas fa-edit"></i> {{ $d->title }}</a>
            </div>
            <div class="m-0 mb-2 p-0">
                <a class="tags" href=""><small><i class="fas fa-tags"></i> <em>{{ $d->tags }}</em></small></a>
            </div>
        </div>
        @endforeach
    </div>
    <div class="d-flex justify-content-center pagination pagination-sm">{{ $data->links() }}</div>
</div>

@endsection
