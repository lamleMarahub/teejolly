@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">        
        <div class="col-sm-8 mt-5">
            <h4>upload mockups</h4>
            <form action="upload" method="post" enctype="multipart/form-data">
                @csrf
                <div class="form-group my-5">
                    <label class="btn btn-light align-top" for="file">
                        <input type="file" class="form-row @error('images') is-invalid @enderror" name="images[]" multiple />
                    </label>

                    <label class="align-top" for="file">
                        <ul>
                            <li>you can upload multiple images</li>
                            <li>file types: <span class="badge badge-pill badge-light">png</span> <span class="badge badge-pill badge-light">jpg</span></li>  
                            <!-- <li>max size: <span class="badge badge-pill badge-light">2 MB</span> min dimensions: <span class="badge badge-pill badge-light">700x700</span></li>   -->
                        </ul>
                    </label>
                    @error('images')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-row">
                    <button type="submit" class="btn btn-primary">
                        upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection