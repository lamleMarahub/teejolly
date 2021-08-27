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
                access denied!
                @endif
                </p>
            </div>

            
        </div>
    </div>
</div>
@endsection
