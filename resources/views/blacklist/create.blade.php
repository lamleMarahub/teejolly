@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
    	<div class="col" style="background: #ffffff; border-radius: 5px; padding-top: 10px; padding-bottom: 10px;">
    		@if(Session::has('message'))
	        <p class="alert alert-warning">{{ Session::get('message') }}</p>
	        @endif
	        @if (count($errors) > 0)
	        <div class="alert alert-warning">
	             @foreach ($errors->all() as $error)
	                    <li>{{ $error }}</li>
	                @endforeach
	        </div>
	        @endif
    		<form action="{{ asset('blacklist/store') }}" method="POST">
			@csrf
			<div class="form-group">
				<label for="keyword">keyword</label>
				<input type="text" class="form-control" name="keyword">
			</div>
			<div class="form-group">
				<label for="type">type</label>
				<select class="form-control" name="type">
					<option value="Copyright Infringement">Copyright Infringement</option>
					<option value="Trademark Infringement">Trademark Infringement</option>
					<option value="Potential Trademark Misuse">Potential Trademark Misuse</option>
					<option value="Potential Trademark Logo Misuse">Potential Trademark Logo Misuse</option>
					<option value="Infringement">Infringement</option>
					<option value="Trademark on Product">Trademark on Product</option>
					<option value="Copyright Piracy">Copyright Piracy</option>
				</select>
			</div>
			<button type="submit" class="btn btn-primary">create</button>
			</form>
    	</div>
    </div>
</div>

@endsection

