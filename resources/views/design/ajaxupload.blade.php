@extends('layouts.app')

@section('content')
<style>
    #filesInfo {
        border: 2px dotted #CCC;
        background-color: none;        
    }

    .image-preview img {        
        padding: 3px;
        margin: 3px;
        border: 1px dotted #FFF;
    }
</style>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 mt-5">
            <form id="form" class="row">
                @csrf
                <div class="col-12">
                    <h4>upload designs</h4>
                </div>
                <div class="col-4 text-muted">                                    
                    <ol>
                        <li>select multiple images <span class="badge badge-pill badge-light">png</span> <span class="badge badge-pill badge-light">jpg</span></li>
                        <li>wait for all files to finish loading</li>
                        <li>confirm options: dark/light, artwork/mockup?</li>
                        <li>click upload (and repeat to upload new files).</li>
                    </ol>
                </div>
                <div class="col-8">
                    <div class="row">
                        <div class="col-4">
                            <div class="btn-group btn-group-toggle btn-block" data-toggle="buttons">                                        
                                <label class="btn btn-light">
                                    <input type="radio" name="color" id="color_dark" autocomplete="off" class="form-control" value="dark"> dark
                                </label>
                                <label class="btn btn-light active">
                                    <input type="radio" name="color" id="color_light" autocomplete="off" class="form-control" value="light" checked> light
                                </label>
                            </div>                   
                        </div>
                        <div class="col-8">
                            <div class="btn-group btn-group-toggle btn-block" data-toggle="buttons">                                        
                                <label class="btn btn-light active">
                                    <input type="radio" name="artwork_or_mockup" id="artwork" autocomplete="off" class="form-control" value="artwork" checked> artwork
                                </label>
                                <label class="btn btn-light">
                                    <input type="radio" name="artwork_or_mockup" id="mockup" autocomplete="off" class="form-control" value="mockup"> mockup only (without artwork)
                                </label>
                            </div>                   
                        </div>
                        <div class="col-12 my-3">
                            <button id="upload" class="btn btn-success btn-block" disabled>
                                upload
                            </button>        
                        </div>
                    </div>            
                </div>
                <div class="col-12">
                @error('filesToUpload')
                <div class="alert alert-danger">{{ $message }}</div>
                @enderror
                <div class="progress d-none w-100 my-2">
                    <div id="dynamic" class="progress-bar bg-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                        <span id="current-progress"></span>
                    </div>
                </div>

                <div class="d-none w-100 my-2" id="uploadList">
                    <ul id="list">
                    </ul>
                </div>
                
                <div class="form-group">
                    <input type="file" class="w-100 @error('filesToUpload') is-invalid @enderror btn btn-light" id="filesToUpload" name="filesToUpload[]" multiple="multiple" />                    
                    <div class="w-100 my-3 p-3 text-center align-middle" id="filesInfo"><p class="m-5">click here to select files <i class="fas fa-upload"></i> or drop files here</p></div>                                                            
                </div>
                </div>                
            </form>
        </div>
    </div>
</div>

<script>
    function filePreview(files) {
        var filesInfo = document.getElementById('filesInfo');
        while (filesInfo.firstChild && filesInfo.removeChild(filesInfo.firstChild));
        
        $(".progress").removeClass('d-none').addClass('d-block');
        $("#dynamic").attr("aria-valuenow", 0).css("width", "0%").attr("aria-valuemax", files.length).text("loading files: initial");
        $("#upload").attr("disabled","disabled");
        $("#filesInfo").css("background-color","#83B799");

        var i = 0;
        var result = '';
        (function doPreview() {                        
            // if the file is not an image, continue
            var file = files[i];
            if (file.type.match('image.*')) {
                reader = new FileReader();
                reader.onload = (function(tFile) {
                    return function(evt) {
                        var div = document.createElement('span');
                        div.innerHTML = '<img style="width: 100px;" src="' + evt.target.result + '" />';
                        div.classList.add("image-preview");
                        filesInfo.appendChild(div);

                        var current_progress = parseInt($("#dynamic").attr("aria-valuenow")) + 1;
                        var percent = parseInt(current_progress / files.length * 100);
                        $("#dynamic")
                            .css("width", percent + "%")
                            .attr("aria-valuenow", current_progress)
                            .text("loading files: " + percent + "% complete");

                        if (percent >= 100) { $("#upload").removeAttr("disabled"); }
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
            // document.getElementById('filesInfo').innerHTML = '<ul>' + result + '</ul>';
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

    //upload 1 file
    function upload(file) {        
        var formData = "";

        formData = new FormData();
        formData.append("filesToUpload[]", file);        
        formData.append("color", $("input[name='color']:checked").val()); 
        formData.append("artwork_or_mockup", $("input[name='artwork_or_mockup']:checked").val()); 

        $.ajax({
            url: "{{ url('/design/ajaxUpload') }}",
            type: 'POST',
            data: formData,
            //async: false,
            cache: false,
            contentType: false,
            enctype: 'multipart/form-data',
            processData: false,
            xhr: function () {
                var myXhr = $.ajaxSettings.xhr();
                // if (myXhr.upload) {
                //     // For handling the progress of the upload
                //     myXhr.upload.addEventListener('progress', function (e) {
                //     if (e.lengthComputable) {
                //         $('#progress').attr({
                //         value: e.loaded,
                //         max: e.total,
                //         });
                //     }
                //     }, false);
                // }
                return myXhr;
            },
            success: function (response) {
                console.log(response);
                
                if (response.success == 1)
                {
                    current_progress = parseInt($("#dynamic").attr("aria-valuenow")) + 1;
                    var percent = parseInt(current_progress / document.getElementById('filesToUpload').files.length * 100);
                    $("#dynamic")
                        .css("width", percent + "%")
                        .attr("aria-valuenow", current_progress)
                        .text("uploaded files: " + percent + "% complete");

                    // if (percent >= 100) { 
                    //     $("#upload").removeAttr("disabled"); 
                    //     window.location = "{{ url('design') }}";
                    // }

                    data = response.data;

                    data.forEach(function(d) {
                        console.log('uploaded: ' + d.title);                                   
                        $("#uploadList #list").append('<li><a href="' + d.id + '/edit" target="_blank"><i class="fas fa-external-link-alt"></i> '+ d.title +'</a></li>');
                    });
                } else {
                    showAlert('Something wrong!');
                }
            }
        });

    }

    
$(document).ready(function(){
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('#filesInfo').click(function() { $('#filesToUpload').click(); });

    $('form').submit(function(evt){
          evt.preventDefault();// to stop form submitting
      });

    if (window.File && window.FileReader && window.FileList && window.Blob) {        
        $('#upload').click(function() {                
            
            var files = document.getElementById('filesToUpload').files;

            $("#uploadList").html("<h4>uploaded:</h4><ol id='list'></ol>");
            $("#uploadList").removeClass('d-none').addClass('d-block');
            $(".progress").removeClass('d-none').addClass('d-block');
            $("#dynamic").attr("aria-valuenow", 0).css("width", "0%").attr("aria-valuemax", files.length).text("uploaded files: initial");
            $("#upload").attr("disabled","disabled");

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
});
</script>

@endsection