@extends('layouts.app')

@section('content')
<style>
    #filesInfo {
        /* min-height: 1000px; */
        width: 100%;
        border: 1px dotted #CCC;
        position: relative;
    }

    .image-preview img {
        position: absolute;
        top: 0;
        left: 0;
    }

    .draggable {
        position: absolute;
        top: 250px;
        left: 250px;

        width: 225px;
        height: 270px;

        display: -moz-inline-stack;
        display: inline-block;
        vertical-align: top;
        zoom: 1;
        cursor: pointer;
    }

    .resizable {
        border: 1px solid #bb0000;
        opacity: 0.5;
        filter: alpha(opacity=50);
        z-index: 999;
    }

    .resizable img {
        width: 100%;
    }

    .ui-resizable-handle {
        background: #f5dc58;
        border: 1px solid #FFF;
        width: 9px;
        height: 9px;

        z-index: 2;
    }

    .ui-resizable-se {
        right: -5px;
        bottom: -5px;
    }

    .ui-rotatable-handle {
        background: #f5dc58;
        border: 1px solid #FFF;
        border-radius: 5px;
        -moz-border-radius: 5px;
        -o-border-radius: 5px;
        -webkit-border-radius: 5px;
        cursor: pointer;

        height: 10px;
        left: 50%;
        margin: 0 0 0 -5px;
        position: absolute;
        top: -5px;
        width: 10px;

        z-index: 3;
    }

    .ui-rotatable-handle.ui-draggable-dragging {
        /* visibility:  hidden; */
    }
</style>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<link rel="stylesheet" href="//cdn.jsdelivr.net/gh/godswearhats/jquery-ui-rotatable@1.1/jquery.ui.rotatable.css">

<div class="container">
    <!-- <form id="form" action="{{route('mockup.store')}}" method="POST" enctype="multipart/form-data"> -->
    <form id="form">
        @csrf
        <div class="row justify-content-center">
            <div class="col-12 mb-3">
                <h4>create mockup</h4>
            </div>

            <!-- right col: preview -->
            <div class="col-8">
                <input type="file" class="w-100 @error('filesToUpload') is-invalid @enderror" id="filesToUpload" name="filesToUpload" />
                @error('filesToUpload')
                <div class="alert alert-danger">{{ $message }}</div>
                @enderror
                <div class="progress d-none w-100 my-2">
                    <div id="dynamic" class="progress-bar bg-success " role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                        <span id="current-progress"></span>
                    </div>
                </div>

                <div class="w-100 text-center align-middle" id="filesInfo">
                    <div class="guide m-5">
                        <p class=""><i class="fas fa-upload"></i> drop file here</p>
                        <label>file types: <span class="badge badge-pill badge-danger">png</span> <span class="badge badge-pill badge-danger">jpg</span></label>
                    </div>
                    <img class="image-preview w-100" src="" />
                    <div class="draggable rotatable resizable d-none">
                        <img src="{{ asset('artwork.png') }}" alt="artwork" />
                    </div>
                </div>
            </div>

            <!-- left col -->
            <div class="col-4">
                <div class="form-group">
                    <label for="title">title</label>
                    <input type="text" class="form-control form-control-sm" id="title" name="title" placeholder="title">
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="color_name">color name</label>
                        <input type="text" class="form-control form-control-sm" id="color_name" name="color_name" placeholder="Black" value="Black">
                    </div>

                    <div class="form-group col-md-4">
                        <label for="color_map">color map</label>
                        <select id="color_map" name="color_map" class="btn btn-sm form-control form-control-sm border bg-light">
                            <?php $color_maps = ["Navy","Dark Heather","Beige","Black","Blue","Bronze","Brown","Gold","Green","Grey","Metallic","Multicoloured","Off-White","Orange","Pink","Purple","Red","Silver","Transparent","Turquoise","White","Yellow","gray","multicolored"]; ?>
                            @foreach ($color_maps as $color_map)
                            <option value="{{ $color_map }}" {{ ($color_map == 'Black') ? 'selected' : '' }}>{{ $color_map }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-md-4">
                        <label for="color_code">color code</label>
                        <div class="btn-group btn-group-sm btn-group-toggle btn-block" data-toggle="buttons">
                            <input type="text" class="form-control form-control-sm" id="color_code" name="color_code" value="#000000">
                            <label id="color_code_preview" class="btn btn-sm" style="background-color:#000">&nbsp;&nbsp;&nbsp;</label>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="color">mockup's color</label>
                        <div class="btn-group btn-group-sm btn-group-toggle btn-block" data-toggle="buttons">
                            <label class="btn btn-light active">
                                <input type="radio" name="color" id="color_dark" autocomplete="off" class="form-control form-control-sm" value="dark" checked> dark
                            </label>
                            <label class="btn btn-light">
                                <input type="radio" name="color" id="color_light" autocomplete="off" class="form-control form-control-sm" value="light"> light
                            </label>
                        </div>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="type">type</label>
                        <select id="type" name="type" class="btn btn-sm form-control form-control-sm border bg-light">
                            <?php $types = ["tee", "mug", "blanket"]; ?>
                            @foreach ($types as $type)
                            <option value="{{ $type }}" {{ ($type == 'tee') ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label for="design_x">x</label>
                        <input type="text" class="form-control form-control-sm" id="design_x" name="design_x" placeholder="x">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="design_y">y</label>
                        <input type="text" class="form-control form-control-sm" id="design_y" name="design_y" placeholder="y">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="design_width">width</label>
                        <input type="text" class="form-control form-control-sm" id="design_width" name="design_width" placeholder="width">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="design_height">height</label>
                        <input type="text" class="form-control form-control-sm" id="design_height" name="design_height" placeholder="height">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="design_angle">rotate (angle)</label>
                        <input type="text" class="form-control form-control-sm" id="design_angle" name="design_angle" placeholder="design_angle" value="0" readonly>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="design_opacity">opacity</label>
                        <input type="text" class="form-control form-control-sm" id="design_opacity" name="design_opacity" placeholder="design_opacity" value="100">
                    </div>
                </div>

                <div class="align-middle text-center">
                    <button id="upload" class="btn btn-primary btn-lg w-50" disabled>
                        finish
                    </button>
                </div>

                <div class="d-none w-100 my-2" id="uploadList">
                    <ol id="list">
                    </ol>
                </div>

                <canvas id="cs" class='d-none'></canvas>
            </div>


        </div>
    </form>
</div>

<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="https://cdn.jsdelivr.net/gh/godswearhats/jquery-ui-rotatable@1.1/jquery.ui.rotatable.min.js"></script>

<script>
    var is_first_load = true;

    function filePreview(files) {
        $('#filesInfo .guide').remove();
        //while (filesInfo.firstChild && filesInfo.removeChild(filesInfo.firstChild));

        var i = 0;
        var result = '';
        (function doPreview() {
            // if the file is not an image, continue
            var file = files[i];
            if (file.type.match('image.*')) {
                reader = new FileReader();
                reader.onload = (function(file) {
                    return function(evt) {
                        $('#filesInfo .image-preview').attr('src', evt.target.result);
                        $('#title').val(file.name.substring(0, file.name.lastIndexOf('.')) || file.name);

                        var img = new Image();
                        img.src = evt.target.result;

                        img.onload = function() {
                            var height = img.height;
                            var width = img.width;

                            // code here to use the dimensions
                            image_rate = width / $("#filesInfo").width();
                            //console.log('image_rate:'+image_rate);

                            $('.draggable').removeClass('d-none').addClass('d-inline-block');

                            if (is_first_load) {
                                is_first_load = false;

                                $(".draggable").css("width", parseInt($("#filesInfo").width() / 3));
                                $(".draggable").css("height", parseInt($('.draggable').width() * 54 / 45));

                                var top = parseInt(($("#filesInfo").height() - $('.draggable').height()) / 2);
                                var left = parseInt(($("#filesInfo").width() - $('.draggable').width()) / 2);
                                $('.draggable').css({
                                    top: top,
                                    left: left,
                                    position: 'absolute'
                                });
                            }

                            $("#upload").removeAttr("disabled");
                        }
                    };

                }(file));
                reader.readAsDataURL(file);

                i++;

                if (i < files.length) {
                    setTimeout(doPreview, 0);
                }
            }
        })();
    }

    function fileDrop(evt) {
        evt.stopPropagation();
        evt.preventDefault();
        if (window.File && window.FileReader && window.FileList && window.Blob) {
            var files = evt.dataTransfer.files;

            filePreview(files);

            evt.target.files = files;
            // var result = '';
            // var file;
            // for (var i = 0; file = files[i]; i++) {
            //     result += '<li>' + file.name + ' ' + file.size + ' bytes</li>';
            // }
            // document.getElementById('filesInfo').appendChild('<ul>' + result + '</ul>');
        } else {
            alert('The File APIs are not fully supported in this browser.');
        }
    }

    function fileSelect(evt) {
        if (window.File && window.FileReader && window.FileList && window.Blob) {
            var files = evt.target.files;
            filePreview(files);
        } else {
            alert('The File APIs are not fully supported in this browser.');
        }
    }

    function dragOver(evt) {
        evt.stopPropagation();
        evt.preventDefault();
        evt.dataTransfer.dropEffect = 'copy';
    }

    var filesToUpload = document.getElementById('filesToUpload');
    filesToUpload.addEventListener('change', fileSelect, false);

    var filesInfo = document.getElementById('filesInfo');
    filesInfo.addEventListener('dragover', dragOver, false);
    filesInfo.addEventListener('drop', fileDrop, false);

    function upload(file) {
        var form = $('#form')[0];
        var formData = new FormData(form);

        $.ajax({
            url: "{{ route('mockup.store') }}",
            type: 'POST',
            data: formData,
            //async: false,
            cache: false,
            contentType: false,
            enctype: 'multipart/form-data',
            processData: false,
            xhr: function() {
                var myXhr = $.ajaxSettings.xhr();
                if (myXhr.upload) {
                    // For handling the progress of the upload
                    myXhr.upload.addEventListener('progress', function(e) {
                        if (e.lengthComputable) {
                            // $('#progress').attr({
                            // value: e.loaded,
                            // max: e.total,
                            // });
                            var percent = parseInt(e.loaded / e.total * 100);
                            $("#dynamic").attr("aria-valuenow", percent).css("width", percent + "%").attr("aria-valuemax", 100).text((percent == 100 ? 90 : percent) + "% Complete");
                        }
                    }, false);
                }
                return myXhr;
            },
            success: function(response) {
                console.log(response);

                if (response.success == 1) {
                    $("#dynamic").attr("aria-valuenow", 100).css("width", "100%").attr("aria-valuemax", 100).text("100% Complete");

                    data = response.data;

                    data.forEach(function(d) {
                        console.log('uploaded: ' + d.title);
                        $("#uploadList #list").append('<li><a href="' + d.id + '/edit" target="_blank"><i class="fas fa-external-link-alt"></i> ' + d.title + '</a></li>');
                    });
                } else {
                    showAlert('Something wrong!');
                }
            }
        });

    }

    var image_rate = 1;

    var img = _('.image-preview'),
        canvas = _('#cs'),
        result = _('#color_code'),
        preview = _('#color_code_preview'),
        x = '',
        y = '';

    img.addEventListener('click', function(e) {
        if (e.offsetX) {
            x = e.offsetX;
            y = e.offsetY;
        } else if (e.layerX) {
            x = e.layerX;
            y = e.layerY;
        }
        useCanvas(canvas, img, function() {
            var p = canvas.getContext('2d').getImageData(x, y, 1, 1).data;
            result.value = rgbToHex(p[0], p[1], p[2]);
            preview.style.background =rgbToHex(p[0],p[1],p[2]);
        });
    }, false);

    img.addEventListener('mousemove', function(e) {
        if (e.offsetX) {
            x = e.offsetX;
            y = e.offsetY;
        } else if (e.layerX) {
            x = e.layerX;
            y = e.layerY;
        }

        useCanvas(canvas, img, function() {
            var p = canvas.getContext('2d').getImageData(x, y, 1, 1).data;
            //result.value = rgbToHex(p[0], p[1], p[2]);
            preview.style.background = rgbToHex(p[0], p[1], p[2]);
        });
    }, false);

    img.addEventListener('mouseout', function(e) {
        preview.style.background = result.value;
    }, false);

    function useCanvas(el, image, callback) {
        el.width = image.width;
        el.height = image.height;
        el.getContext('2d').drawImage(image, 0, 0, image.width, image.height);
        return callback();
    }

    function _(el) {
        return document.querySelector(el);
    };

    function componentToHex(c) {
        var hex = c.toString(16);
        return hex.length == 1 ? "0" + hex : hex;
    }

    function rgbToHex(r, g, b) {
        return "#" + componentToHex(r) + componentToHex(g) + componentToHex(b);
    }

    function findPos(obj) {
        var curleft = 0,
            curtop = 0;
        if (obj.offsetParent) {
            do {
                curleft += obj.offsetLeft;
                curtop += obj.offsetTop;
            } while (obj = obj.offsetParent);
            return {
                x: curleft,
                y: curtop
            };
        }
        return undefined;
    }

    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // $("#color_light").attr('checked',true).parent().button('toggle');
        // $('#color_dark').attr('checked',false).parent().button('dispose');

        $('form').submit(function(evt) {
            evt.preventDefault(); // to stop form submitting
        });

        if (window.File && window.FileReader && window.FileList && window.Blob) {
            $('#upload').click(function() {

                var files = document.getElementById('filesToUpload').files;

                //$("#uploadList").html("<h4>uploaded:</h4><ol id='list'></ol>");
                $("#uploadList").removeClass('d-none').addClass('d-block');

                $(".progress").removeClass('d-none').addClass('d-block');
                $("#dynamic").attr("aria-valuenow", 0).css("width", "0%").attr("aria-valuemax", files.length).text("0% Complete");
                //$("#upload").attr("disabled","disabled");

                var i = 0;
                (function doUpload() {
                    console.log(i);

                    // if the file is not an image, continue
                    var file = files[i];
                    if (file.type.match('image.*')) {
                        upload(file);
                    }

                    i++;
                    if (i < files.length) {
                        setTimeout(doUpload, 0);
                    }
                })();

            });
        } else {
            alert('The File APIs are not fully supported in this browser.');
        }


        $('.draggable').draggable();

        $('.resizable').resizable({
            aspectRatio: true,
            handles: 'ne, se, sw'
        });

        var params = {
            // Callback fired on rotation start.
            start: function(event, ui) {},
            // Callback fired during rotation.
            rotate: function(event, ui) {

                console.log("Rotating: " + ui.angle.current)
                $("#design_angle").val(parseInt(ui.angle.current / Math.PI * 180));

            },
            // Callback fired on rotation end.
            stop: function(event, ui) {
                console.log("Rotating: " + ui.angle.stop)
                $("#design_angle").val(parseInt(ui.angle.stop / Math.PI * 180));
            },
            rotationCenterOffset: {
                top: 0,
                left: 0
            },
            handleOffset: {
                top: 0,
                left: 0
            },
            wheelRotate: false,
        };
        $('.rotatable').rotatable(params);

        $(".draggable").mousemove(function(e) {
            console.log(image_rate);
            var parentOffset = $(this).parent().offset();

            var relativeXPosition = ($(this).offset().left - parentOffset.left); //offset -> method allows you to retrieve the current position of an element 'relative' to the document
            var relativeYPosition = ($(this).offset().top - parentOffset.top);

            $("#design_x").val(parseInt(relativeXPosition * image_rate));
            $("#design_y").val(parseInt(relativeYPosition * image_rate));
            $("#design_width").val(parseInt($(this).width() * image_rate));
            $("#design_height").val(parseInt($(this).height() * image_rate))
        });

        $("#color_code").keyup(function() {
            $("#color_code_preview").css('background-color', $(this).val());
        });

    });
</script>

@endsection
