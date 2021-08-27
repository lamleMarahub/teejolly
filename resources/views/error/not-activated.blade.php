@extends('layouts.blank')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 m-auto">
            
            <div class="bd-callout bd-callout-danger">
                <h4>uh oh! something went wrong</h4>
                
                <p>
                @if (isset($message))
                {{ $message }}
                @else
                please ask an administrator for activation!
                @endif
                </p>

                <a class="btn btn-primary" href="{{ route('login') }}">login again</a>
            </div>

            
        </div>
    </div>
</div>
@endsection
