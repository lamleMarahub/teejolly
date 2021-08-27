@extends('layouts.app')

@section('content')
<script>
    var collection = {!! $data['collection']->toJson() !!};
    var designs = {!! $data['designs']->keyBy('id')->toJson() !!};
    var mockups = {!! $data['mockups']->keyBy('id')->toJson() !!};
    var asset ="{{ asset('') }}";
    var total = 0;
    var current = 0;
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $("#design_check_all").change(function() {
            $('input[name*=design_ids]').prop('checked', this.checked);
            var x = document.querySelectorAll('input[name*=design_ids]:checked').length;
            $("#y").html(x);
        });

        $(".design_row").click(function(event) {
            if ((event.target.type !== 'checkbox') && (!$(event.target).hasClass('remockup'))) {
                var checked = $(':checkbox', this).prop('checked');
                $(':checkbox', this).prop('checked', !checked);
                if (!checked) {
                    $('#design_check_all').prop('checked', false);
                }
            }
            var x = document.querySelectorAll('input[name*=design_ids]:checked').length;
            $("#y").html(x);
        });

    });

    function makeMockups() {
        current = 0;
        total = $("input[name*=design_ids]:checked").length;

        $("input[name*=design_ids]:checked").each(function(event, obj){
            //console.log(obj);
            var id = $(obj).attr('design_id');
            console.log(id);
            if (id != '') {
                //data.push(id);
                loadMockup(obj);
            }
        });
    }

    function loadMockup(element) {
        var design_id = $(element).attr('design_id');
        $('#mockup'+design_id).attr('src', asset + 'images/loading.gif');

        var collection_id = collection.id;
        var etsyshop_id = $('#etsyshop_id').val();

        var posting = $.post("{{url('/collection/new_mockup2')}}", {
            collection_id: collection_id,
            design_id: design_id,
            etsyshop_id: etsyshop_id
        });
        posting.done(function(response) {
            console.log(response);

            if (response.success == 1) {
                $('#mockup'+design_id).attr('src', response.data);

                current++;
                console.log(current + ":" + total);
                if (current == total) {
                    //doExport();
                    $('#download-link').removeClass('d-none');
                    current++;
                }
            } else {
                showAlert('Something wrong!');
            }
        });
        posting.fail(function(response) {
            showAlert("Error: " + response);
        });
        posting.always(function(response) {
            //alert( "finished" );
        });
    }

    function getSelectedDesigns() {
        var selected_designs = array();
        $("input[name*=design_ids]:checked").each(function(event, obj){
            //console.log(obj);
            var id = $(obj).attr('design_id');
            console.log(id);
            if (id != '') {
                selected_designs.push(designs[id]);
            }
        });
        console.log(selected_designs);
    }

    function createListingDigitals() {

        var etsyshop_id = $('#etsyshop_id').val();

        $("input[name*=design_ids]:checked").each(function(event, obj){

            var id = $(obj).attr('design_id');
            var design = designs[id];

            var design_image = $('#mockup' + design.id).attr('src');

            var thumbnail = design_image ? design_image.replace(',', '%2C') : '';

            var posting = $.post("{{url('/etsy/createListingDigital')}}", {
                collection: collection,
                design_id: id,
                thumbnail: thumbnail,
                etsyshop_id: etsyshop_id,
            });

            $('#listing_id'+id).show().removeClass('d-none').addClass('d-block');

            posting.done(function(response) {
                if (response.success == 1) {
                    showAlert('success');
                    $('#listing_id'+id).show().removeClass('d-block');
                } else {
                    showAlert('Something wrong!');
                }
            });

            posting.fail(function(response) {
                console.log(response);
                showAlert("Error: " + response);
            });

            posting.always(function(response) {
                console.log(response);
                if(response.success == 1){
                    console.log('>> listing_id: '+response.data['listing_id']);
                    $('#'+response.data['design_id']).html(response.data['listing_id']);
                }else{
                    showAlert('Invalid auth/bad request');
                }
            });
        });
    }

    function createListings() {

        var etsyshop_id = $('#etsyshop_id').val();

        $("input[name*=design_ids]:checked").each(function(event, obj){

            var id = $(obj).attr('design_id');
            var design = designs[id];
            var design_image = $('#mockup' + design.id).attr('src');

            var thumbnail = design_image ? design_image.replace(',', '%2C') : '';

            var posting = $.post("{{url('/etsy/createListing')}}", {
                collection: collection,
                design_id: id,
                thumbnail: thumbnail,
                etsyshop_id: etsyshop_id,
            });

            $('#listing_id'+id).show().removeClass('d-none').addClass('d-block');

            posting.done(function(response) {
                if (response.success == 1) {
                    showAlert('success');
                    $('#listing_id'+id).show().removeClass('d-block');
                } else {
                    showAlert('Something wrong!');
                }
            });

            posting.fail(function(response) {
                console.log(response);
                showAlert("Error: " + response);
            });

            posting.always(function(response) {
                console.log(response);
                if(response.success == 1){
                    console.log('>> listing_id: '+response.data['listing_id']);
                    $('#'+response.data['design_id']).html(response.data['listing_id']);
                }else{
                    showAlert('Invalid auth/bad request');
                }
            });
        });
    }

    function createListingsForMug() {

        var etsyshop_id = $('#etsyshop_id').val();

        $("input[name*=design_ids]:checked").each(function(event, obj){

            var id = $(obj).attr('design_id');
            var design = designs[id];
            var design_image = $('#mockup' + design.id).attr('src');

            var thumbnail = design_image ? design_image.replace(',', '%2C') : '';

            var posting = $.post("{{url('/etsy/createListingForMug')}}", {
                collection: collection,
                design_id: id,
                thumbnail: thumbnail,
                etsyshop_id: etsyshop_id,
            });

            $('#listing_id'+id).show().removeClass('d-none').addClass('d-block');

            posting.done(function(response) {
                if (response.success == 1) {
                    showAlert('success');
                    $('#listing_id'+id).show().removeClass('d-block');
                } else {
                    showAlert('Something wrong!');
                }
            });

            posting.fail(function(response) {
                console.log(response);
                showAlert("Error: " + response);
            });

            posting.always(function(response) {
                console.log(response);
                if(response.success == 1){
                    console.log('>> listing_id: '+response.data['listing_id']);
                    $('#'+response.data['design_id']).html(response.data['listing_id']);
                }else{
                    showAlert('Invalid auth/bad request');
                }
            });
        });
    }

    function downloadMockups() {

        if (!$("input[name*=design_ids]").is(":checked")) {
            showAlert('please select designs');
        }
        var elements = [];
        var data = '';
        $("input[name*=design_ids]:checked").each(function(event, obj){

            var id = $(obj).attr('design_id');
            var design = designs[id];
            var design_image = $('#mockup' + design.id).attr('src');
            var thumbnail = design_image ? design_image.replace(',', '%2C') : '';

            var arr = (design.tags).split(",");

            if(arr.length >= 3){
                var title_arr = [];
                title_arr.push(design.title80);
                array_rand(arr,2).forEach(element => title_arr.push(arr[element]));
                title_arr.push(design.id);
                elements[titleCase(title_arr.join(', '))] = thumbnail;
            }else{
                console.log('> tags is null');
            }
        });
        downloadAll(elements);
    }

    function pause(msec) {
        return new Promise(
            (resolve, reject) => {
                setTimeout(resolve, msec || 1000);
            }
        );
    }

    async function downloadAll(elements) {
        var count = 0;
        for (var e in elements) {
            // download(elements[e]); // your custom download code here, click or whatever
            var link = document.createElement("a");
            link.href = elements[e];
            link.download = e; // file name
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            delete link;

            if (++count >= 10) {
                await pause(1000);
                count = 0;
            }
        }
    }

    function array_rand (array, num) {
        const keys = Object.keys(array);
        if (typeof num === 'undefined' || num === null) {
            num = 1;
        } else {
            num = +num;
        }
        if (isNaN(num) || num < 1 || num > keys.length) {
            return null;
        }
        // shuffle the array of keys
        for (let i = keys.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1)) // 0 ≤ j ≤ i
            const tmp = keys[j];
            keys[j] = keys[i];
            keys[i] = tmp;
        }
        return num === 1 ? keys[0] : keys.slice(0, num);
    }

    function titleCase(str) {
        return str.toLowerCase().replace(/\b(\w)/g, s => s.toUpperCase());
    }

    window.updateCount = function() {
        var x = $(".z:checked").length;
        document.getElementById("y").innerHTML = x;
    };

</script>

<div class="container">
    <div class="row">
        <div class="col-sm-12 btn-toolbar justify-content-between" role="toolbar">
            <h4>export collection: <strong>{{ $data['collection']->title }}</strong></h4>
            <div class="justify-content-end">
                <div class="btn-group btn-group-sm ml-2 float-left" role="group">
                    <a class="btn btn-link" href="{{ url('/etsy/'.$etsyshop->id.'/product') }}" role="button" target="_blank"><i class="far fa-edit"></i> edit products</a>
                </div>
            </div>
        </div>
    </div>

    <nav class="my-3">
        <div class="nav nav-tabs" id="nav-tab" role="tablist">
            <a class="nav-item nav-link active" id="nav-list-tab" data-toggle="tab" href="#nav-list" role="tab" aria-controls="nav-list" aria-selected="true">1. designs to listing</a>
            <a class="nav-item nav-link" id="nav-general-tab" data-toggle="tab" href="#nav-general" role="tab" aria-controls="nav-general" aria-selected="false">2. general information</a>
        </div>
    </nav>
    <div class="tab-content" id="nav-tabContent">
        <div class="tab-pane fade show active" id="nav-list" role="tabpanel" aria-labelledby="nav-list-tab">
            <div class="col-12 d-flex justify-content-between m-0 p-0">
                <h4>select designs and make mockups <span id="y" class="badge badge-danger">0</span></h4>
                <div class="btn-group" role="group">
                    <a href="javascript:makeMockups();" class="btn btn-primary" role="button">1. make mockups</a>
                    <!--<a href="javascript:createListingDigitals();" class="btn btn-primary ml-2" role="button">create listing digital</a>                    -->
                    <a href="javascript:createListings();" class="btn btn-primary ml-2" role="button">2. create listing</a>
                    <!--<a href="javascript:createListingsForMug();" class="btn btn-primary ml-2" role="button">create listing for mug</a>-->
                    @if(in_array(Auth::user()->id, [111]))
                    <a href="javascript:downloadMockups();" class="btn btn-primary ml-2" role="button">download mockups</a>
                    @endif
                </div>
            </div>
            <div id="listDesigns" class="col-12 mt-3 p-0">
                <input type="hidden" name="etsyshop_id" id="etsyshop_id" value="{{$etsyshop->id}}">
                <table class="table table-sm table-hover" id="design_table">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col"><input type="checkbox" id="design_check_all"></th>
                            <th scope="col"></th>
                            <th scope="col">thumbnail</th>
                            <th scope="col">details</th>
                            <th scope="col">listing id</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data['designs'] as $d)
                        <tr class="design_row" id="design_row{{$d->id}}">
                            <th scope="row" class="align-middle">
                                <input type="checkbox" name="design_ids[]" design_id="{{$d->id}}" id="checkbox{{$d->id}}">
                            </th>
                            <th scope="row" class="align-middle text-center">
                                <a href="javascript:loadMockup($('#checkbox{{$d->id}}'))" class="remockup">
                                    <i class="fas fa-redo-alt"></i>mockup
                                </a>
                                <label class="form-check-label align-middle d-none" id="design_loading">
                                    <img style="width:25px" src="{{asset('images/loading.gif')}}" alt='adding...'>
                                </label>
                            </th>
                            <td class="align-middle design_thumbnail">
                                <!-- 2021-07: move to S3 -->
{{--                                <img id="mockup{{ $d->id }}" style="width:125px" class="img img-thumbnail" src="{{asset('storage/' . $d->thumbnail)}}">--}}
                                <img id="mockup{{ $d->id }}" style="width:125px" class="img img-thumbnail" src="{{ $d->thumbnail_url }}" />
                            </td>
                            <td class="align-middle design_details">
                                <!--<span class="design_title">{{ $d->title }}</span><br />-->
                                <span class="design_title">{{ $d->title80 }}</span><br />
                                <!--<small class="design_title text-secondary">{{ $d->title80 }}</small><br />-->
                                <small class="design_tags text-muted">{{ $d->tags }}</small>
                            </td>
                            <td class="align-middle" id="{{$d->id}}"><img id="listing_id{{ $d->id }}" style="width:25px" class="d-none" src="{{asset('images/loading.gif')}}" alt='creating...'></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="tab-pane fade" id="nav-general" role="tabpanel" aria-labelledby="nav-general-tab">
            <div class="col-12 d-flex justify-content-between m-0 p-0">
                <h4>general information (for each listing)</h4>
            </div>
            <div class="col-12 m-0 p-0 mt-3">
                <form id="general_form">
                    <div class="form-group row">
                        <label class="col-12 col-form-label"><strong>images</strong></label>
                        <div class="col-sm-12">
                            <div class="row">
                                <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-3 h-100" style="font-size: 75%;">
                                    <div class="card">
                                        <img class="card-img-top img-thumbnail" src="{{ $data['collection']->image_url_1 }}" alt="image_url_1">
                                    </div>
                                    <div class="m-0 p-0 text-muted">
                                    image_url_1
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-3 h-100" style="font-size: 75%;">
                                    <div class="card">
                                        <img class="card-img-top img-thumbnail" src="{{ $data['collection']->image_url_2 }}" alt="image_url_2">
                                    </div>
                                    <div class="m-0 p-0 text-muted">
                                    image_url_2
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-3 h-100" style="font-size: 75%;">
                                    <div class="card">
                                        <img class="card-img-top img-thumbnail" src="{{ $data['collection']->image_url_3 }}" alt="image_url_3">
                                    </div>
                                    <div class="m-0 p-0 text-muted">
                                    image_url_3
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-12 col-form-label"><strong>keywords</strong></label>
                        <div class="col-12">
                        {{ $data['collection']->tags }}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-12 col-form-label"><strong>description</strong></label>
                        <div class="col-12" style="white-space:pre-wrap; word-wrap:break-word;">
                        {{ $data['collection']->description }}
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

@endsection
