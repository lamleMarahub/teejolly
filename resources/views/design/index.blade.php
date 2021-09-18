@extends('layouts.app')

@section('content')
<script>
var asset ="{{ asset('') }}";
var collection_changed = false;
var design_changed = false;
var users = {};
var selected_design_ids = new Map();

@foreach ($users as $u)
    users[{{ $u->id }}] ="{{ $u->name }}";
@endforeach

//console.log(users);

$(document).ready(function(){
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('#collections_modal').on('shown.bs.modal', function (e) {
        doCollectionSearch();
    })

    $('#collections_modal').on('hidden.bs.modal', function (e) {
        //if (collection_changed) location.reload();
        if (!collection_changed) {
            var design_id = parseInt($('#selected_design_id').val());
            selectItem(design_id);
            console.log('not change:' + design_id);
        }
    })

    $("#collections_keyword").on('search', function () {
        doCollectionSearch();
    });

    $("#collection_check_all").change(function() {
        $('input[name*=collection_ids]').prop('checked', this.checked);
    });

    // $("#design_title" ).keyup(function() {
    //     processDesignTitle();
    // });

    $("#design_title80" ).keyup(function() {
        processDesignTitle80();
    });

    $("#design_tags" ).keyup(function() {
        processDesignTags();
    });

    $('#design_modal').on('shown.bs.modal', function (e) {

    })

    //Hover button group
    $( ".card" ).hover(
      function() {
        $( this ).find(".card-top-toolbar").show();
      },
      function() {
        $( this ).find(".card-top-toolbar").hide();
      }
    );

    $("#keyword").on('search', function () {
        $("#filter_form").submit();
    });

    $("#filter_form").find("select").on('change', function (e) {
        $("#filter_form").submit();
    });

    // radio: artwork/mockup
    if ({{ $filters['shared'] }}==0) {
        $('#shared_0').attr('checked',true).parent().button('toggle');
        $('#shared_1').attr('checked',false).parent().button('dispose');
    } else {
        $('#shared_1').attr('checked',true).parent().button('toggle');
        $('#shared_0').attr('checked',false).parent().button('dispose');
    }
});

function processDesignTitle() {
    var result = true;

    var obj = $('#design_title');
    var t = $(obj).val().replace('  ', ' ');

    if ( t.length > 100) {
        obj.addClass('text-danger');
        result = false;
    } else {
        obj.removeClass('text-danger');
    }

    var classtext = t.length > 100 ? "text-danger" : "text-muted";

    $('#design_title_small').html(' <span class="'+ classtext +'">('+ t.length + '/100 characters)</span>');
    $('#design_title').val(t);

    return result;
}

function processDesignTitle80() {
    var result = true;
    var obj = $('#design_title80');
    var t = $(obj).val().replace('  ', ' ');

    if ( t.length > 150) {
        obj.addClass('text-danger');
        result = false;
    } else {
        obj.removeClass('text-danger');
    }

    var classtext = t.length > 150 ? "text-danger" : "text-muted";

    $('#design_title80_small').html(' <span class="'+ classtext +'">('+ t.length + '/150 characters)</span>');
    $('#design_title80').val(t);

    return result;
}

function processDesignTags() {
    var result = true;
    var obj = $('#design_tags');
    //console.log( "Handler for .keyup() called: " + $(obj).val());
    var t = $(obj).val().replace('  ',' ').replace(' ,',',').replace(', ',',');
    var a = t.split(",");

    if ( a.length > 25) {
        obj.addClass('text-danger');
        result = false;
    } else {
        obj.removeClass('text-danger');
    }

    var tags = "";
    var existing = [];
    for (var i=0; i<a.length; i++) {
        if (a[i].length>50) {
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
    $('#design_tags').val(t);

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

function deleteItem(id,divId) {
    //if (confirm('Are you sure?') == false) return;

    var posting = $.post("{{url('/design/delete')}}",{id:id});
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

function showUpdateDesignModal(id) {
    $('#design_id').val(id);
    $('#design_modal').modal('show');

    $('#design_loading').show().removeClass('d-none').addClass('d-block');

    var posting = $.post("{{url('/design/ajaxGetDesign')}}",{id: id});
    posting.done(function(response) {
        //console.log(response);

        if (response.success == 1)
        {
            var d = response.data;
            // 2021-07: move to S3
            // $('#design_thumbnail').attr('src',asset + 'storage/' + d.thumbnail);
            $('#design_thumbnail').attr('src', d.thumbnail_url);
            $('#design_label').html(d.title);
            // $('#design_title').val(d.title);
            $('#design_title80').val(d.title80);
            $('#design_tags').val(d.tags);
            $('#design_type').val(d.type);

            $('#bullet_point2').val(d.bullet_point2);
            $('#bullet_point3').val(d.bullet_point3);

            if (d.color=='dark') {
                $('#design_color_dark').attr('checked',true).parent().button('toggle');
                $('#design_color_light').attr('checked',false).parent().button('dispose');
            } else {
                $('#design_color_light').attr('checked',true).parent().button('toggle');
                $('#design_color_dark').attr('checked',false).parent().button('dispose');
            }

            $('#design_owner'+d.owner_id).attr('checked',true).parent().button('toggle');
            $('#design_designer'+d.designer_id).attr('checked',true).parent().button('toggle');

            $("#credit").val(d.credit).change();
            // $(newrow).click(function(event) {
            //     if (event.target.type !== 'checkbox') {
            //         var checked = $(':checkbox', this).prop('checked');
            //         $(':checkbox', this).prop('checked', !checked);
            //         if (!checked) { $('#collection_check_all').prop('checked', false); }
            //     }
            // });

            // processDesignTitle();
            processDesignTitle80();
            processDesignTags();

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

function submitUpdateDesign() {
    // if (processDesignTitle() == false) {
    //   $('#design_title').focus();
    //   return;
    // }

    if (processDesignTitle80() == false) {
       $('#design_title80').focus();
       return;
    }

    if (processDesignTags() == false) {
       $('#design_tags').focus();
       return;
    }

    $('#design_loading').show().removeClass('d-none').addClass('d-block');

    // var form = $('#update_design_form');
    // var formData = new FormData($('#update_design_form'));

    var posting = $.post("{{url('/design/ajaxUpdateDesign')}}", $("#design_form").serialize());
    posting.done(function(response) {
        console.log(response);

        if (response.success == 1)
        {
            //$('#design_loading').hide().removeClass('d-block');
            $('#design_modal').modal('hide');

            updateDesignInfo();
            showAlert('Design is updated!');
        } else if (response.success == -1) {
            alert("You don't have permission to update this design!");
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

function updateDesignInfo(){
    var design_id=$('#design_id').val();
    var div=$('#d'+design_id);
    // $(div).find('a.title').html($('#design_title').val());
    $(div).find('a.title80').html($('#design_title80').val());
    $(div).find('a.tags').html('<small class="text-muted"><i class="fas fa-tags"></i>' + $('#design_tags').val() + '</small>');
    if ($("input[name='design_color']:checked").val() == 'dark') {
        $(div).find('div.card').addClass('border-dark');
    } else {
        $(div).find('div.card').removeClass('border-dark');
    }

    $('#owner'+design_id).html(users[$("input[name='design_owner']:checked").val()]);
    $('#designer'+design_id).html(users[$("input[name='design_designer']:checked").val()]);
}

function showCollectionModal(design_id) {
    collection_changed = false;
    var element = ('#select_item' + design_id);
    if (!selected_design_ids.has(design_id)) {
        selected_design_ids.set(design_id,1);
        $(element).addClass('text-purple').removeClass('text-light');
    }
    //console.log('selected_design_ids:' + selected_design_ids);

    $('#selected_design_id').val(design_id);
    $('#collections_modal_num_designs').html(selected_design_ids.size);
    $('.new_collection_row').remove();
    $('#collections_modal').modal('show');
}

function doCollectionSearch() {
    $('#searching').show().removeClass('d-none').addClass('d-block');
    $('input[name*=collection_ids]').prop('checked', false);
    $('#collection_check_all').prop('checked', false);
    var keyword = $('#collections_keyword').val();
    var design_id = $('#selected_design_id').val();

    //console.log(design_id + ':'+ keyword);

    var posting = $.post("{{url('/design/ajaxSearchCollection')}}",{id:design_id,keyword:keyword});
    posting.done(function(response) {
        //console.log(response);

        if (response.success == 1)
        {
            $('.new_collection_row').remove();
            //for (var m in response.data) {
            $.each(response.data,function(index, m){
                //console.log(m);
                var clone = $('#collection_row');
                var newrow = $(clone).clone().insertBefore(clone).removeClass('d-none').addClass('new_collection_row').attr('id', 'collection_row'+m.id);
                $(newrow).find('th > input').attr('value', m.id);
                $(newrow).find('.collection_details > .collection_title').html(m.title);
                $(newrow).find('.collection_details > .collection_desc').html(m.created_at);

                $(newrow).click(function(event) {
                    if (event.target.type !== 'checkbox') {
                        var checked = $(':checkbox', this).prop('checked');
                        $(':checkbox', this).prop('checked', !checked);
                        if (!checked) { $('#collection_check_all').prop('checked', false); }
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

function addCollections() {
    collection_changed = true;
    var collection_ids = [];
    var boxes = $("input[name*=collection_ids]:checked").each(function(event, obj){
        var cid = $(obj).attr('value');
        if (cid != '') {
            collection_ids.push(cid);
            //console.log(cid);
        }
    });

    //var design_id = $('#selected_design_id').val();
    var design_ids = [];
    selected_design_ids.forEach(function (value, key, map) {
        design_ids.push(key);
    });
    console.log('designs: '+ design_ids);
    console.log('collections: '+ collection_ids);

    var posting = $.post("{{url('/design/ajaxAddCollections')}}",{design_ids:design_ids, collection_ids:collection_ids});
    posting.done(function(response) {
        console.log(response);
        if (response.success == 1)
        {
            //doCollectionSearch();
            $('#collections_modal').modal('hide');
            selected_design_ids.forEach(function (value, key, map) {
                selectItem(key);
            });
            selected_design_ids.clear();
            showAlert('Added to collections!');
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

function copyDesign(design_id) {
    var posting = $.post("{{url('/design/copy')}}",{id:design_id});
    posting.done(function(response) {
        console.log(response);
        if (response.success == 1)
        {
            showAlert('copied to clipboard');
        } else {
            showAlert('copy wrong!');
        }
    });
    posting.fail(function(response) {
        showAlert( "Error: " + response );
    });
    posting.always(function(response) {
        //alert( "finished" );
    });
}

function pasteDesign(design_id) {
    var posting = $.post("{{url('/design/paste')}}",{id:design_id});
    posting.done(function(response) {
        console.log(response);
        if (response.success == 1)
        {
            //var design_id=response.data.id;
            var div=$('#d'+design_id);
            $(div).find('a.title').html(response.data.title);
            $(div).find('a.title80').html(response.data.title80);
            $(div).find('a.tags').html('<small class="text-muted"><i class="fas fa-tags"></i>' + response.data.tags + '</small>');
            showAlert('pasted: ' + response.data.title);
        } else {
            showAlert('paste wrong!');
        }
    });
    posting.fail(function(response) {
        showAlert( "Error: " + response );
    });
    posting.always(function(response) {
        //alert( "finished" );
    });
}

function selectItem(design_id) {
    var element = ('#select_item' + design_id);
    if (selected_design_ids.has(design_id)) {
        selected_design_ids.delete(design_id);
        $(element).addClass('text-light').removeClass('text-purple');
    } else {
        selected_design_ids.set(design_id,1);
        $(element).addClass('text-purple').removeClass('text-light');
    }
    console.log(selected_design_ids);
}
</script>

<style type="text/css">
.card-top-toolbar{ position: absolute; left: 2px; top: 2px; z-index:999; background: #F2F0E3}
.card-top-toolbar-select-item{ position: absolute; right: 0px; top: 0px; z-index:999;}
/* .card-img-top:hover {
    opacity: 0.9;
    filter: alpha(opacity=90); /* For IE8 and earlier */
} */
.title:hover, .tags:hover {
    background-color: #E4D8B4;
}
a:hover {
    text-decoration: none;
}
.highlight {
    background: #E2CD6D;
    color: #E86F68;
}
</style>

<div class="container">
    <form id="filter_form" action="{{ url('design') }}" method="get">
    @csrf
    <div class="row">
        <div class="col-sm-12 btn-toolbar justify-content-between" role="toolbar">
            <h4>designs <small class="text-muted">{{$data->firstItem()}}-{{$data->lastItem()}}/{{$data->total()}}</small></h4>
            <div class="justify-content-end">
                <div class="input-group float-left">
                    <input type="search" class="form-control form-control-sm" id="keyword" name="keyword" placeholder="search by id/title/tags..." value="{{ $keyword }}">
                    <a type="button" class="btn btn-sm btn-link input-group-append" onclick="document.getElementById('filter_form').submit();"><i class="fas fa-search m-auto"></i>&nbsp;</a>
                </div>

                <div class="btn-group btn-group-sm ml-2 float-left" role="group">
                    <select id="owner_id" name="owner_id" class="btn btn-sm form-control form-control-sm border bg-light" style="width:auto;">
                        <option value="0" @if ($owner_id == 0) ? selected : "" @endif>-all sellers-</option>
                        @foreach ($users as $u)
                        @if ($u->isActive() && !$u->isDeleted() && $u->isSeller())
                        <option value="{{ $u->id }}" @if ($owner_id == $u->id) ? selected : '' @endif>{{ $u->name }}</option>
                        @endif
                        @endforeach
                    </select>
                    <select id="designer_id" name="designer_id" class="btn btn-sm form-control form-control-sm border bg-light" style="width:auto;">
                        <option value="0" @if ($designer_id == 0) ? selected : "" @endif>-all designers-</option>
                        @foreach ($users as $u)
                        @if ($u->isActive() && !$u->isDeleted() && $u->isDesigner())
                        <option value="{{ $u->id }}" @if ($designer_id == $u->id) ? selected : '' @endif>{{ $u->name }}</option>
                        @endif
                        @endforeach
                    </select>
                </div>

                <div class="btn-group btn-group-sm ml-2 float-left" role="group">
                    <select id="collection_id" name="collection_id" class="btn btn-sm form-control form-control-sm border bg-success">
                        <option value="0" @if ($collection_id == 0) ? selected : '' @endif>-all collections-</option>
                        @foreach ($collections as $c)
                        <option value="{{ $c->id }}" @if ($collection_id == $c->id) ? selected : '' @endif>{{ $c->title }}</option>
                        @endforeach
                    </select>
                </div>

                @if (Auth::user()->isSeller())
                <div class="btn-group btn-group-sm ml-2 float-left btn-group-toggle" role="group" data-toggle="buttons">
                    <label class="btn btn-sm btn-info shared @if ($filters['shared'] == 1) ? active : '' @endif">
                        <input type="radio" name="shared" id="shared_1" value="1" @if ($filters['shared'] == 1) ? selected : '' @endif onchange="document.getElementById('filter_form').submit();">artwork
                    </label>
                    <label class="btn btn-sm btn-info shared @if ($filters['shared'] == 0) ? active : '' @endif">
                        <input type="radio" name="shared" id="shared_0" value="0" @if ($filters['shared'] == 0) ? selected : '' @endif onchange="document.getElementById('filter_form').submit();">mockup
                    </label>
                </div>
                @endif

                <div class="btn-group btn-group-sm ml-2 float-left" role="group">
                    <a class="btn btn-sm btn-primary" href="{{ url('/design/upload') }}" role="button"><i class="fas fa-cloud-upload-alt"></i> upload</a>
                </div>
            </div>

        </div>
        <div class="col-sm-12 btn-toolbar justify-content-between mt-2" role="toolbar">
            <div class="pagination pagination-sm mx-auto">{{ $data->appends($filters)->links() }}</div>
        </div>
    </div>
    </form>

    <div class="row mt-3 p-0">
        @foreach ($data as $d)
        @if ($d->color=='dark')
        <?php $borderclass = 'border-dark'; ?>
        @else
        <?php $borderclass = ''; ?>
        @endif
        <div class="col-12 col-sm-6 col-md-4 col-lg-3 h-100 mb-3" style="font-size: 75%;" id="d{{ $d->id }}">
            <div class="card {{ $borderclass }}">
                <span class="card-top-toolbar text-center rounded p-0" style="display:none">
                    @if ($d->isOwnedOrDesignedOrAdmin(Auth::user()))
                    <a class="btn btn-transparent text-secondary m-0 p-2" href="javascript:copyDesign({{ $d->id }});" data-toggle="tooltip" data-placement="bottom" title="copy design to clipboard" ><i class="fas fa-copy"></i></a><br/>
                    <a class="btn btn-transparent text-purple m-0 p-2" href="javascript:pasteDesign({{ $d->id }});" data-toggle="tooltip" data-placement="bottom" title="paste design from clipboard" ><i class="fas fa-clipboard"></i></a><br/>
                    @endif
                    @if ($d->isOwnerOrAdmin(Auth::user()))
                    <!-- 2021-07: move to S3 -->
{{--                <a class="btn btn-transparent text-primary m-0 p-2" href="{{ asset('storage/'.$d->filename) }}" data-toggle="tooltip" data-placement="bottom" title="download artwork" target="_blank"><i class="fas fa-download"></i></a><br/>--}}
                    <a class="btn btn-transparent text-primary m-0 p-2" href="{{ $d->file_url }}" data-toggle="tooltip" data-placement="bottom" title="download artwork" target="_blank"><i class="fas fa-download"></i></a><br/>
                    @endif
                    <a class="btn btn-transparent text-info m-0 p-2" href="{{ url('/design/create_mockup/'. $d->id) }}" data-toggle="tooltip" data-placement="bottom" title="get random mockups" target="_blank"><i class="fas fa-tshirt"></i></a><br/>
                    @if ($d->isOwnedOrDesignedOrAdmin(Auth::user()))
                    <a class="btn btn-transparent text-danger m-0 p-2" href="javascript:deleteItem({{ $d->id }},'#d{{ $d->id }}');" onclick="return confirm('Are you sure?')" data-toggle="tooltip" data-placement="bottom" title="delete design" ><i class="fas fa-trash-alt"></i></a>
                    @endif
                </span>
                <span class="card-top-toolbar-select-item text-center rounded p-0" style="">
                    <a id="select_item{{ $d->id }}" class="btn btn-transparent text-light m-0 px-2 py-0" style="font-size: 20px;" onclick="javascript:selectItem({{ $d->id }});" data-toggle="tooltip" data-placement="bottom" title="select"><i class="fas fa-check"></i></a>
                </span>
                <!-- 2021-07: move to S3 -->
{{--                <a href="javascript:showUpdateDesignModal({{ $d->id }})"><img class="card-img-top" src="{{ asset('storage/'.$d->thumbnail) }}" alt="{{ $d->title }}"></a>--}}
                <a href="javascript:showUpdateDesignModal({{ $d->id }})"><img class="card-img-top" src="{{ $d->thumbnail_url }}" alt="{{ $d->title }}"></a>
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between">
                        <a href="" target="_blank"><i class="fas fa-user-plus"></i> <span id="owner{{ $d->id }}">{{ $users[$d->owner_id]->name }}</span></a>
                        <span class="text-muted" style="white-space: nowrap;overflow: hidden;text-overflow: ellipsis;">{{ $d->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="d-flex justify-content-between text-muted">
                        <a href="" target="_blank"><i class="fas fa-user-edit"></i> <span id="designer{{ $d->id }}">{{ $users[$d->designer_id]->name }}</span></a>
                        <span><small><script>document.write(formatBytes({{ $d->size }}))</script> {{ $d->width }}x{{ $d->height }}</small></span>
                    </div>
                </div>
                <div class="card-footer text-muted p-2">
                    <div class="d-flex justify-content-between">
                        <span>({{$d->id}})</span>
                        <div class="group">
                            <a class="text-purple" href="javascript:showCollectionModal({{ $d->id }})"><i class="fas fa-plus"></i> collection</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="m-0 mt-2 p-0">
                <a class="title" href="javascript:showUpdateDesignModal({{ $d->id }})">@if ($keyword == null) {{ $d->title80 }} @else {!! preg_replace('/(' . $keyword . ')/i', "<span class='highlight'>$1</span>", $d->title80) !!} @endif</a>
            </div>
            <div class="m-0 p-0">
                <a class="title80" href="javascript:showUpdateDesignModal({{ $d->id }})"><small class="text-secondary">@if ($keyword == null) {{ $d->title }} @else {!! preg_replace('/(' . $keyword . ')/i', "<span class='highlight'>$1</span>", $d->title) !!} @endif</small></a>
            </div>
            <div class="m-0 mb-2 p-0">
                <a class="tags" href="javascript:showUpdateDesignModal({{ $d->id }})"><small class="text-muted"><i class="fas fa-tags"></i> @if ($keyword == null) {{ $d->tags }} @else {!! preg_replace('/(' . $keyword . ')/i', "<span class='highlight'>$1</span>", $d->tags) !!} @endif</small></a>
            </div>
        </div>
        @endforeach
    </div>
    <div class="d-flex justify-content-center pagination pagination-sm">{{ $data->links() }}</div>
</div>

<!--------- BEGIN COLLECTION MODAL -------->
<div id="collections_modal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">select collections for <strong id="collections_modal_num_designs">1</strong> designs.
                    <img id="searching" class="mx-auto d-block float-right" src="{{asset('images/loading.gif')}}" style="width:25px" alt='searching...'>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="input-group w-100">
                    <input type="search" class="form-control form-control-sm" id="collections_keyword" name="collections_keyword" placeholder="search collections by id/title...">
                    <a type="button" class="btn btn-sm btn-link input-group-append" href="javascript:doCollectionSearch();"> search</a>
                    <a type="button" class="btn btn-sm btn-success input-group-append" href="javascript:addCollections();">add to selected collections</a>
                </div>

                <input type="hidden" id="selected_design_id"/>

                <div id="listcollections" class="col-12 mt-3 p-0">
                    <table class="table table-sm table-hover" id="collection_table">
                        <thead class="thead-light">
                            <tr>
                            <th scope="col"><input type="checkbox" id="collection_check_all"></th>
                            <th scope="col">collection details</th>
                            </tr>
                        </thead>
                        <tbody>

                        <tr class="collection_row d-none" id="collection_row">
                            <th scope="row" class="align-middle">
                                <input type="checkbox" name="collection_ids[]" value="">
                                <label class="form-check-label align-middle d-none" id="collection_loading">
                                    <img style="width:25px" src="{{asset('images/loading.gif')}}" alt='adding...'>
                                </label>
                            </th>
                            <td class="align-middle collection_details">
                                <span class="collection_title">collection title</span><br/>
                                <small class="collection_desc text-muted">collection desc</small>
                            </td>
                        </tr>

                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <a type="button" class="btn btn-success btn-block" href="javascript:addCollections();">add to selected collections</a>
            </div>
        </div>
    </div>
</div>
<!--------- END COLLECTION MODAL -------->

<!--------- BEGIN DESIGN MODAL -------->
<div id="design_modal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">edit design <span id="design_label" class="text-muted"></span> <img id="design_loading" class="d-none" style="width:25px;" src="{{asset('images/loading.gif')}}" alt='loading...'>   </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="design_form" class="form">
                    @csrf
                    <input type="hidden" id="design_id" name="design_id"/>

                    <div class="row">
                        <div class="col-3">
                            <img class="img-thumbnail" id="design_thumbnail" src="{{asset('images/loading.gif')}}" alt='thumbnail'>
                            <a type="button" class="btn btn-success btn-block mt-3" href="">replace artwork</a>
                        </div>

                        <div class="col-9">
                            <!--<div class="form-group">-->
                            <!--    <label for="design_title">title for mug, mask, bag...<small id="design_title_small" class="text-muted">(max 130 characters)</small></label>-->
                            <!--    <input type="text" id="design_title" name="design_title" value="design_title" class="form-control form-control-sm keyup" required>                                -->
                            <!--</div>-->

                            <div class="form-group">
                                <label for="design_title80">title <small id="design_title80_small" class="text-muted">(max 120 characters)</small></label>
                                <input type="text" id="design_title80" name="design_title80" value="design_title80" class="form-control form-control-sm keyup" required>
                            </div>

                            <div class="form-group">
                                <label for="design_tags">tags <small class="text-muted">(seperate by comma)</small></label>
                                <input type="text" id="design_tags" name="design_tags" value="design_tags" class="form-control form-control-sm keyup">
                                <span id="design_tags_13">etsy, amazon (max 25 tags):</span>
                            </div>

                            <div class="form-group row">
                                <div class="col">
                                    <label for="design_color">color <small class="text-muted">(artwork's color)</small></label>
                                    <div class="btn-group btn-group-toggle btn-block" data-toggle="buttons">
                                        <label class="btn btn-sm btn-light">
                                            <input type="radio" name="design_color" id="design_color_dark" autocomplete="off" class="form-control form-control-sm" value="dark"> dark
                                        </label>
                                        <label class="btn btn-sm btn-light">
                                            <input type="radio" name="design_color" id="design_color_light" autocomplete="off" class="form-control form-control-sm" value="light"> light
                                        </label>
                                    </div>
                                </div>
                                <div class="col">
                                    <label for="design_type">type <small class="text-muted">(tee/blanket/shoes..)</small></label>
                                    <input type="text" id="design_type" name="design_type" value="design_type" class="form-control form-control-sm">
                                </div>
                                <div class="col-2">
                                    <label for="design_type">credits</small></label>
                                    <select class="form-control form-control-sm" id="credit" name="credit">
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="design_owner">seller <small class="text-muted"></small></label>
                                <div class="btn-group btn-group-toggle btn-block" data-toggle="buttons">
                                    @foreach ($users as $u)
                                    @if ($u->isActive() && !$u->isDeleted() && $u->isSeller())
                                    <label class="btn btn-sm btn-light">
                                        <input type="radio" name="design_owner" id="design_owner{{$u->id}}" autocomplete="off" class="form-control form-control-sm" value="{{$u->id}}"> {{ $u->name }}
                                    </label>
                                    @endif
                                    @endforeach
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="design_designer">designer <small class="text-muted"></small></label>
                                <div class="btn-group btn-group-toggle btn-block" data-toggle="buttons">
                                    @foreach ($users as $u)
                                    @if ($u->isActive() && !$u->isDeleted() && $u->isDesigner())
                                    <label class="btn btn-sm btn-light">
                                        <input type="radio" name="design_designer" id="design_designer{{$u->id}}" autocomplete="off" class="form-control form-control-sm" value="{{$u->id}}"> {{ $u->name }}
                                    </label>
                                    @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <a type="button" class="btn btn-success btn-block" href="javascript:submitUpdateDesign();">update design</a>
            </div>
        </div>
    </div>
</div>
<!--------- END DESIGN MODAL -------->

@endsection
