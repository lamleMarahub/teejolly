@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div id="appReact"></div>
            <script src="{{ asset('js/app.js') }}"></script>
        </div>
    </div>
</div>
@endsection
