@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-sm-8 mt-5">
            <h4>upload images</h4>
            <form id="form" action="upload" method="post" enctype="multipart/form-data">
                @csrf                
                <div class="form-group my-5">
                    <label class="btn btn-light align-top" for="filesToUpload">                        
                        <input type="file" class="form-row @error('filesToUpload') is-invalid @enderror" id="filesToUpload" name="filesToUpload[]" id="filesToUpload" multiple="multiple" />
                    </label>

                    <label class="align-top" for="file">
                        <ul>
                            <li>you can upload multiple images</li>
                            <li>file types: <span class="badge badge-pill badge-light">png</span> <span class="badge badge-pill badge-light">jpg</span></li>
                        </ul>
                    </label>
                    @error('images')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
                <!-- <div id="dropTarget" style="width: 100%; height: 100px; border: 1px #ccc solid; padding: 10px;">Drop some files here</div>                 -->
                <output id="filesInfo"></output>
                <div class="form-row">
                    <button type="submit" class="btn btn-primary">
                        upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function filePreview(files) {
    var filesInfo = document.getElementById('filesInfo');
    while(filesInfo.firstChild && filesInfo.removeChild(filesInfo.firstChild));

    var result = '';
    var file;
    for (var i = 0; file = files[i]; i++) {
        // if the file is not an image, continue
        if (!file.type.match('image.*')) {
            continue;
        }

        reader = new FileReader();
        reader.onload = (function (tFile) {
            return function (evt) {
                var div = document.createElement('div');
                div.innerHTML = '<img style="width: 90px;" src="' + evt.target.result + '" />';
                filesInfo.appendChild(div);
            };
        }(file));
        reader.readAsDataURL(file);
    }
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
filesToUpload.addEventListener('dragover', dragOver, false);
filesToUpload.addEventListener('drop', fileDrop, false);
filesToUpload.addEventListener('change', fileSelect, false);


if (window.File && window.FileReader && window.FileList && window.Blob) {
    document.getElementById('form').onSubmit = function(){
        var files = document.getElementById('filesToUpload').files;
        for(var i = 0; i < files.length; i++) {
            resizeAndUpload(files[i]);
        }
    };
} else {
    alert('The File APIs are not fully supported in this browser.');
}
 
function resizeAndUpload(file) {
var reader = new FileReader();
    reader.onloadend = function() {
 
    var tempImg = new Image();
    tempImg.src = reader.result;
    tempImg.onload = function() {
 
        var MAX_WIDTH = 400;
        var MAX_HEIGHT = 300;
        var tempW = tempImg.width;
        var tempH = tempImg.height;
        if (tempW > tempH) {
            if (tempW > MAX_WIDTH) {
               tempH *= MAX_WIDTH / tempW;
               tempW = MAX_WIDTH;
            }
        } else {
            if (tempH > MAX_HEIGHT) {
               tempW *= MAX_HEIGHT / tempH;
               tempH = MAX_HEIGHT;
            }
        }
 
        var canvas = document.createElement('canvas');
        canvas.width = tempW;
        canvas.height = tempH;
        var ctx = canvas.getContext("2d");
        ctx.drawImage(this, 0, 0, tempW, tempH);
        var dataURL = canvas.toDataURL("image/jpeg");
 
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function(ev){
            document.getElementById('filesInfo').innerHTML = 'Done!';
        };
 
        xhr.open('POST', 'uploadResized.php', true);
        xhr.setRequestHeader("Content-type","application/x-www-form-urlencoded");
        var data = 'image=' + dataURL;
        xhr.send(data);
      }
 
   }
   reader.readAsDataURL(file);
}
</script>

@endsection