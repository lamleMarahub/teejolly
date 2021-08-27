@extends('layouts.app')

@section('content')
<style>
.mockup_thumbnail img, .design_thumbnail img{
    width: 100px;
}
.card-top-toolbar{ position: absolute; right: 0px; top: 0px; z-index:999;}
.card-img-top:hover {
    opacity: 0.9;
    filter: alpha(opacity=90); /* For IE8 and earlier */
}
a:hover {
    text-decoration: none;
}
</style>

<script>
var asset ="{{ asset('') }}";
var design_changed = false;
var mockup_changed = false;
$(document).ready(function(){
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('#mockups_modal').on('shown.bs.modal', function (e) {
        doMockupSearch();
    })

    $('#mockups_modal').on('hidden.bs.modal', function (e) {
        if (mockup_changed) location.reload();
    })

    $("#mockups_keyword").on('search', function () {
        doMockupSearch();
    });

    $("#mockup_check_all").change(function() {
        $('input[name*=mockup_ids]').prop('checked', this.checked);
    });

    $('#designs_modal').on('shown.bs.modal', function (e) {
        doDesignSearch();
    })

    $('#designs_modal').on('hidden.bs.modal', function (e) {
        if (design_changed) location.reload();
    })

    $("#designs_keyword").on('search', function () {
        doDesignSearch();
    });

    $("#design_check_all").change(function() {
        $('input[name*=design_ids]').prop('checked', this.checked);
    });

    $("#tags" ).keyup(function() {
        processDesignTags();
    });

});

function processDesignTags() {
    var result = true;
    var obj = $('#tags');
    //console.log( "Handler for .keyup() called: " + $(obj).val());
    var t = $(obj).val().replace('  ',' ').replace(' ,',',').replace(', ',',');
    var a = t.split(",");

    if ( a.length > 13) {
        obj.addClass('text-danger');
        result = false;
    } else {
        obj.removeClass('text-danger');
    }

    var tags = "";
    var existing = [];
    for (var i=0; i<a.length; i++) {
        if (a[i].length>20) {
            tagclass="danger";
            result = false;
        } else {
            tagclass = "light";
        }

        if (existing.includes(a[i].trim())) {
            tagclass = "purple";
            result = false;
        }

        tags += '<a href="#" class="mr-1 badge badge-'+tagclass+'">'+a[i]+'</a>';
        existing.push(a[i].trim());
    }

    $('#design_tags_13').html('<small>' + a.length + ' tags </small>' + tags + '</span>');
    $('#dtags').val(t);

    return result;
}

function formatBytes(bytes, decimals = 2) {
    if (bytes === 0) return '0 Bytes';

    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

function doMockupSearch() {
    $('#searching').show().removeClass('d-none').addClass('d-block');
    $('input[name*=mockup_ids]').prop('checked', false);
    $('#mockup_check_all').prop('checked', false);
    var keyword = $('#mockups_keyword').val();
    var collection_id = $('#id').val();

    //console.log(collection_id + ':'+ keyword);

    var posting = $.post("{{url('/collection/ajaxSearchMockup')}}",{id:collection_id,keyword:keyword});
    posting.done(function(response) {
        //console.log(response);

        if (response.success == 1)
        {
            $('.new_mockup_row').remove();
            //for (var m in response.data) {
            $.each(response.data,function(index, m){
                //console.log(m);
                var clone = $('#mockup_row');
                var newrow = $(clone).clone().insertBefore(clone).removeClass('d-none').addClass('new_mockup_row').attr('id', 'mockup_row'+m.id);
                // 2021-07: move to S3
                // $(newrow).find('.mockup_thumbnail > img').attr('src', asset + '/storage/' + m.filename);
                $(newrow).find('.mockup_thumbnail > img').attr('src', m.file_url);
                $(newrow).find('th > input').attr('value', m.id);
                $(newrow).find('.mockup_details > .mockup_title').html(m.title);
                $(newrow).find('.mockup_details > .mockup_desc').html('<label class="btn btn-sm m-0" style="background-color:' + m.color_code + '">&nbsp;&nbsp;&nbsp;</label> ' + m.color_name + ' <i class="fas fa-long-arrow-alt-right"></i> ' + m.color_map + ' (' + m.color + ') <br/>'+ formatBytes(m.size) + ' (' + m.width + 'x' + m.height + ')');
                $(newrow).find('th > label').attr('id', 'mockup_loading'+m.id);

                $(newrow).click(function(event) {
                    //console.log('click')
                    if (event.target.type !== 'checkbox') {
                        var checked = $(':checkbox', this).prop('checked');
                        $(':checkbox', this).prop('checked', !checked);
                        if (!checked) { $('#mockup_check_all').prop('checked', false); }
                    }
                });
            });

            $('#searching').hide().removeClass('d-block');
        } else {

        }
        //$('#searching').hide();
    });
    posting.fail(function(response) {
        showAlert( "Error: " + response );
    });
    posting.always(function(response) {
        //alert( "finished" );
    });
}

function addMockups() {
    mockup_changed = true;
    var data = [];
    var boxes = $("input[name*=mockup_ids]:checked").each(function(event, obj){
        var mid = $(obj).attr('value');
        if (mid != '') {
            $('#mockup_loading'+mid).show();//removeClass('d-none').addClass('d-block');
            data.push(mid);
            console.log(mid);
        }
    });

    var collection_id = $('#id').val();
    //console.log(collection_id + ':'+ data);
    var posting = $.post("{{url('/collection/ajaxAddMockups')}}",{id:collection_id, mockup_ids:data});
    posting.done(function(response) {
        //console.log(response);
        if (response.success == 1)
        {
            $.each(response.data,function(index, m) {
                //console.log(m);
                $('#mockup_row'+m).remove();
            });
            doMockupSearch();
        }
    });
    posting.fail(function(response) {
        showAlert( "Error: " + response );
    });
    posting.always(function(response) {
        //alert( "finished" );
    });
}

function removeMockups(mid) {
    if (!(mid && mid.length)) return;
    showLoading();
    var collection_id = $('#id').val();
    var data = mid;

    var posting = $.post("{{url('/collection/ajaxRemoveMockups')}}",{id:collection_id, mockup_ids:data});
    posting.done(function(response) {
        //console.log(response);
        if (response.success == 1)
        {
            $.each(response.data,function(index, m) {
                //console.log(m);
                $('#mockup'+m).remove();
            });
        }
        hideLoading();
    });
    posting.fail(function(response) {
        showAlert( "Error: " + response );
    });
    posting.always(function(response) {
        //alert( "finished" );
    });
}

//////////////////////// DESIGN
function doDesignSearch() {
    $('#searching_design').show().removeClass('d-none').addClass('d-block');
    $('input[name*=design_ids]').prop('checked', false);
    $('#design_check_all').prop('checked', false);

    var keyword = $('#designs_keyword').val();
    var collection_id = $('#id').val();

    var posting = $.post("{{url('/collection/ajaxSearchDesign')}}",{id:collection_id,keyword:keyword});
    posting.done(function(response) {
        //console.log(response);

        if (response.success == 1)
        {
            $('.new_design_row').remove();
            $.each(response.data,function(index, d){
                //console.log(m);
                var clone = $('#design_row');
                var newrow = $(clone).clone().insertBefore(clone).removeClass('d-none').addClass('new_design_row').attr('id', 'design_row'+d.id);
                // 2021-07: move to S3
                // $(newrow).find('.design_thumbnail > img').attr('src', asset + '/storage/' + d.thumbnail);
                $(newrow).find('.design_thumbnail > img').attr('src', d.thumbnail_url);
                $(newrow).find('th > input').attr('value', d.id);
                $(newrow).find('.design_details > .design_title').html(d.title);
                $(newrow).find('.design_details > .design_title80').html(d.title80);
                $(newrow).find('.design_details > .design_tags').html(d.tags);
                $(newrow).find('.design_details > .design_desc').html(formatBytes(d.size) + ' (' + d.width + 'x' + d.height + ')');
                $(newrow).find('th > label').attr('id', 'design_loading'+d.id);

                $(newrow).click(function(event) {
                    //console.log('click')
                    if (event.target.type !== 'checkbox') {
                        var checked = $(':checkbox', this).prop('checked');
                        $(':checkbox', this).prop('checked', !checked);
                        if (!checked) { $('#design_check_all').prop('checked', false); }
                    }
                });
            });

            $('#searching_design').hide().removeClass('d-block');
        } else {

        }
        //$('#searching').hide();
    });
    posting.fail(function(response) {
        showAlert( "Error: " + response );
    });
    posting.always(function(response) {
        //alert( "finished" );
    });
}

function addDesigns() {
    design_changed = true;
    var data = [];
    var boxes = $("input[name*=design_ids]:checked").each(function(event, obj){
        var mid = $(obj).attr('value');
        if (mid != '') {
            $('#design_loading'+mid).show();//removeClass('d-none').addClass('d-block');
            data.push(mid);
            console.log(mid);
        }
    });

    var collection_id = $('#id').val();
    console.log(collection_id + ':'+ data);
    var posting = $.post("{{url('/collection/ajaxAddDesigns')}}",{id:collection_id, design_ids:data});
    posting.done(function(response) {
        //console.log(response);
        if (response.success == 1)
        {
            $.each(response.data,function(index, m) {
                //console.log(m);
                $('#design_row'+m).remove();
            });
        }
        doDesignSearch();
    });
    posting.fail(function(response) {
        showAlert( "Error: " + response );
    });
    posting.always(function(response) {
        //alert( "finished" );
    });
}

function removeDesigns(mid) {
    if (!(mid && mid.length)) return;
    showLoading();
    var collection_id = $('#id').val();
    var data = mid;

    var posting = $.post("{{url('/collection/ajaxRemoveDesigns')}}",{id:collection_id, design_ids:data});
    posting.done(function(response) {
        console.log(response);
        if (response.success == 1)
        {
            $.each(response.data,function(index, m) {
                console.log(m);
                $('#design'+m).remove();
            });
        }
        hideLoading();
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
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        <!--------- BEGIN COLLECTION INFO -------->
        <form action="{{ url('collection/update') }}" method="post" class="form w-100">
            @csrf
            @method('PUT')
            <input type="hidden" id="id" name="id" value="{{ $data['collection']->id }}">
            <div class="col-sm-12 btn-toolbar justify-content-between align-middle" role="toolbar">
                <h3 class="h-100 my-auto">collection #{{ $data['collection']->id }} {{ $data['collection']->title }} <small class="text-muted">by {{ $data['collection']->getOwner()->name }}</small></h3>
                <div class="">
                    <button class="btn btn-link text-danger" href="">delete this collection</button>
                    <button class="btn btn-primary" type="submit">update</button>
                    <a role="button" class="btn btn-primary" href="{{ url('collection/export/?id='.$data['collection']->id.'&target=etsycsv') }}">export csv</a>
                </div>
            </div>

            <div class="col-12 mt-3">
                <div class="card card-body">
                    <div class="form-group row">
                        <label for="title" class="col-sm-3 col-form-label">title <small class="text-muted">(max 140 characters)</small></label>
                        <div class="col-sm-9">
                            <input type="text" id="title" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ $data['collection']->title }}">
                            <small id="titleHelpBlock" class="form-text text-muted">
                                ex: bonanza collection for halloween 2019 (account: abc@example.com)
                            </small>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="description" class="col-sm-3 col-form-label">description <small class="text-muted">(max 1000 characters)</small></label>
                        <div class="col-sm-9">
                            <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" rows="5" style="white-space: pre-wrap;">{{ $data['collection']->description }}</textarea>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="image_url_1" class="col-sm-3 col-form-label">image url 1 <small class="text-muted">(color chart)</small></label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control @error('image_url_1') is-invalid @enderror" value="{{ $data['collection']->image_url_1 }}" id="image_url_1" name="image_url_1" placeholder="image url 1">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="image_url_2" class="col-sm-3 col-form-label">image url 2 <small class="text-muted">(size chart)</small></label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control @error('image_url_2') is-invalid @enderror" value="{{ $data['collection']->image_url_2 }}" id="image_url_2" name="image_url_2" placeholder="image url 2">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="image_url_3" class="col-sm-3 col-form-label">image url 3 <small class="text-muted">(shipping policy)</small></label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control @error('image_url_3') is-invalid @enderror" value="{{ $data['collection']->image_url_3 }}" id="image_url_3" name="image_url_3" placeholder="image url 3">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="tags" class="col-sm-3 col-form-label">keywords <small class="text-muted">(seperate by comma)</small></label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control @error('tags') is-invalid @enderror keyup" value="{{ $data['collection']->tags }}" id="tags" name="tags" placeholder="keywords">
                            <span id="design_tags_13">etsy (13 tags):</span>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="uid" class="col-sm-3 col-form-label">collection unique id <small class="text-muted">(Amazon only)</small></label>
                        <div class="col-sm-3">
                            <input type="text" class="form-control @error('uid') is-invalid @enderror" value="{{ $data['collection']->uid }}" id="uid" name="uid" placeholder="Amazon prefix SKU">
                        </div>
                        <label for="brand_name" class="col-sm-3 col-form-label">brand name <small class="text-muted">(Amazon only)</small></label>
                        <div class="col-sm-3">
                            <input type="text" class="form-control @error('brand_name') is-invalid @enderror" value="{{ $data['collection']->brand_name }}" id="brand_name" name="brand_name" placeholder="Amazon brand name">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="" class="col-sm-3 col-form-label"></label>
                        <div class="col-sm-9">
                            <button class="btn btn-primary" type="submit">update</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <!--------- END COLLECTION INFO -------->

        <!--------- BEGIN MOCKUPS BLOCK -------->
        <div class="col-sm-12 btn-toolbar justify-content-between align-middle mt-5 mb-3" role="toolbar">
            <h3 class="h-100 my-auto">mockups ({{count($data['mockups'])}})</h3>
            <div class="">
                <a class="text-danger" href="javascript:removeMockups([{{ $data['mockups']->implode('id', ', ') }}]);" onclick="return confirm('Are you sure?')">remove all mockups</a>
                <a class="btn btn-primary text-white" role="button" data-toggle="modal" data-target="#mockups_modal">add mockups</a>
            </div>
        </div>

        @foreach ($data['mockups'] as $d)
        @if ($d->color=='dark')
        <?php $borderclass = 'border-dark'; ?>
        @else
        <?php $borderclass = ''; ?>
        @endif
        <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-3 h-100" style="font-size: 75%;" id="mockup{{ $d->id }}">
            <div class="card {{ $borderclass }}">
                <span class="card-top-toolbar"><a class="btn btn-transparent text-danger" href="javascript:removeMockups([{{ $d->id }}]);"><i class="fas fa-times"></i></a></span>
                <!-- 2021-07: move to S3 -->
{{--                <img class="card-img-top" src="{{ asset('storage/'.$d->filename) }}" alt="{{ $d->title }}">--}}
                <img class="card-img-top" src="{{ $d->file_url }}" alt="{{ $d->title }}">

                <div class="card-footer text-muted p-2">
                    <div class="d-flex justify-content-between text-muted">
                        <span><label class="btn btn-sm m-0" style="background-color:{{ $d->color_code }}">&nbsp;&nbsp;&nbsp;</label> {{ $d->color_name }} <i class="fas fa-long-arrow-alt-right"></i> {{ $d->color_map }} ({{ $d->color }})</span>

                    </div>
                </div>
            </div>
            <div class="m-0 p-0 text-muted">
                {{ $d->title }} (<span><small><script>document.write(formatBytes({{ $d->size }}))</script> {{ $d->width }}x{{ $d->height }}</small></span>)
            </div>
        </div>
        @endforeach

        <!--------- BEGIN MOCKUP MODAL -------->
        <div id="mockups_modal" class="modal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">select mockups
                            <img id="searching" class="mx-auto d-block float-right" src="{{asset('images/loading.gif')}}" style="width:25px" alt='searching...'>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                            <div class="input-group w-100">
                                <input type="search" class="form-control form-control-sm" id="mockups_keyword" name="mockups_keyword" placeholder="search mockups by id/title/color/type...">
                                <a type="button" class="btn btn-sm btn-link input-group-append" href="javascript:doMockupSearch();"> search</a>
                                <a type="button" class="btn btn-sm btn-success input-group-append" href="javascript:addMockups();">add selected mockups</a>
                            </div>

                            <div id="listMockups" class="col-12 mt-3 p-0">
                                <table class="table table-sm table-hover" id="mockup_table">
                                    <thead class="thead-light">
                                        <tr>
                                        <th scope="col"><input type="checkbox" id="mockup_check_all"></th>
                                        <th scope="col">thumbnail</th>
                                        <th scope="col">mockup details</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    <tr class="mockup_row d-none" id="mockup_row">
                                        <th scope="row" class="align-middle">
                                            <input type="checkbox" name="mockup_ids[]" value="">
                                            <label class="form-check-label align-middle d-none" id="mockup_loading">
                                                <img style="width:25px" src="{{asset('images/loading.gif')}}" alt='adding...'>
                                            </label>
                                        </th>
                                        <td class="align-middle mockup_thumbnail"><img class="img-thumbnail" src="{{ asset('artwork.png') }}"/></td>
                                        <td class="align-middle mockup_details">
                                            <span class="mockup_title">mockup title</span><br/>
                                            <small class="mockup_desc text-muted">mockup desc</small>
                                        </td>
                                    </tr>

                                    </tbody>
                                </table>
                            </div>
                    </div>
                    <div class="modal-footer">
                        <a type="button" class="btn btn-block btn-success" href="javascript:addMockups();">add selected mockups</a>
                    </div>
                </div>
            </div>
        </div>
        <!--------- END MOCKUP MODAL -------->
        <!--------- END MOCKUPS BLOCK -------->


        <!--------- BEGIN DESIGNS BLOCK -------->
        <div class="col-sm-12 btn-toolbar justify-content-between align-middle mt-5 mb-3" role="toolbar">
            <h3 class="h-100 my-auto">designs ({{count($data['designs'])}})</h3>
            <div class="">
                <a class="text-danger" href="javascript:removeDesigns([{{ $data['designs']->implode('id', ', ') }}]);" onclick="return confirm('Are you sure?')">remove all designs</a>
                <a class="btn btn-primary text-white" role="button" data-toggle="modal" data-target="#designs_modal">add designs</a>
            </div>
        </div>

        @foreach ($data['designs'] as $d)
        @if ($d->color=='dark')
        <?php $borderclass = 'border-dark'; ?>
        @else
        <?php $borderclass = ''; ?>
        @endif
        <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-3 h-100" style="font-size: 75%;" id="design{{ $d->id }}">
            <div class="card {{ $borderclass }}">
                <span class="card-top-toolbar"><a class="btn btn-transparent text-danger" href="javascript:removeDesigns([{{ $d->id }}]);"><i class="fas fa-times"></i></a></span>
                <!-- 2021-07: move to S3 -->
{{--                <img class="card-img-top" src="{{ asset('storage/'.$d->thumbnail) }}" alt="{{ $d->title }}">--}}
                <img class="card-img-top" src="{{ $d->thumbnail_url }}" alt="{{ $d->title }}">
                <div class="card-footer text-muted p-2">
                    <div class="d-flex justify-content-between text-muted">
                        {{ $d->color }} - {{$d->id}}
                        <span><small><script>document.write(formatBytes({{ $d->size }}))</script> {{ $d->width }}x{{ $d->height }}</small></span>
                    </div>
                </div>
            </div>
            <div class="m-0 p-0 text-muted">
                {{ $d->title }}
            </div>
        </div>
        @endforeach

        <!--------- BEGIN DESIGN MODAL -------->
        <div id="designs_modal" class="modal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">select designs
                            <img id="searching_design" class="mx-auto d-block float-right" src="{{asset('images/loading.gif')}}" style="width:25px" alt='searching...'>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                            <div class="input-group w-100">
                                <input type="search" class="form-control form-control-sm" id="designs_keyword" name="designs_keyword" placeholder="search designs by id/title/color/type/tags..">
                                <a type="button" class="btn btn-sm btn-link input-group-append" href="javascript:doDesignSearch();"> search</a>
                                <a type="button" class="btn btn-sm btn-success input-group-append" href="javascript:addDesigns();">add selected designs</a>
                            </div>

                            <div id="listDesigns" class="col-12 mt-3 p-0">
                                <table class="table table-sm table-hover" id="design_table">
                                    <thead class="thead-light">
                                        <tr>
                                        <th scope="col"><input type="checkbox" id="design_check_all"></th>
                                        <th scope="col">thumbnail</th>
                                        <th scope="col">details</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    <tr class="design_row d-none" id="design_row">
                                        <th scope="row" class="align-middle">
                                            <input type="checkbox" name="design_ids[]" value="">
                                            <label class="form-check-label align-middle d-none" id="design_loading">
                                                <img style="width:25px" src="{{asset('images/loading.gif')}}" alt='adding...'>
                                            </label>
                                        </th>
                                        <td class="align-middle design_thumbnail"><img class="img-thumbnail" src="{{ asset('artwork.png') }}"/></td>
                                        <td class="align-middle design_details">
                                            <span class="design_title">design title</span><br/>
                                            <small class="design_title80 text-muted text-secondary">design title80</small>
                                            <small class="design_tags text-muted">design tags</small>
                                            <small class="design_desc text-muted">design desc</small>
                                        </td>
                                    </tr>

                                    </tbody>
                                </table>
                            </div>
                    </div>
                    <div class="modal-footer">
                        <a type="button" class="btn btn-block btn-success" href="javascript:addDesigns();">add selected designs</a>
                    </div>
                </div>
            </div>
        </div>
        <!--------- END DESIGN MODAL -------->
        <!--------- END DESIGNS BLOCK -------->
    </div>
</div>

@endsection
