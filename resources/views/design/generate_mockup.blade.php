@extends('layouts.app')

@section('content')
<style>
.img-thumbnail { border: none;}
</style>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <h4>generate mockups</h4>
            <a class="btn btn-primary" href="{{ url('/design/upload') }}" role="button">Upload Designs</a>
            <a class="btn btn-success" href="{{ url('/design/uploadmockup') }}" role="button">Upload Mockups</a>
        </div>
    </div>
    <div class="row mt-3 p-0">
        @foreach ($data as $d)
        <div class="col-sm-3 mb-1 p-1">
            <div class="card h-100">
                <div class="card-body p-2"> 
                    <img src="{{ $d }}" class="img-thumbnail p-0 mb-1" style="border:none;">                    
                </div>                
            </div>
        </div>
        @endforeach
    </div>
</div>

@endsection