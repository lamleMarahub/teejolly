@extends('layouts.app')

@section('content')
<script>
    $(document).ready(function() {
        $(".collection_row").click(function(event) {   
            if (!(event.target.tagName.toLowerCase() === 'a')) {         
                window.location.href=$('a.blacklist_edit', this).attr('href');
            }
        });
    });
</script>
<div class="container">
	<div class="row">
        <div class="col-sm-12 btn-toolbar justify-content-between" role="toolbar">
            <h4>blacklist keywords <small class="text-muted">{{$blacklist_ws->firstItem()}}-{{$blacklist_ws->lastItem()}}/{{$blacklist_ws->total()}} </small></h4>
            <div class="justify-content-end">
            	<a href="{{ asset('blacklist/create')}}" class="btn btn-sm btn-warning ml-2" role="button">create</a>               
            </div>
        </div>
        <div class="col-sm-12 btn-toolbar justify-content-between mt-2" role="toolbar">            
            <div class="pagination pagination-sm mx-auto">{{ $blacklist_ws->links() }}</div>            
        </div>
    </div>
    <div class="row">
        <div class="col-12 table-responsive">
        	@if(Session::has('message'))
	        <p class="alert alert-info">{{ Session::get('message') }}</p>
	        @endif
            <table class="table table-hover">
                <thead class="thead-light">
                    <tr>
	                    <th scope="col">keywords</th>
	                    <th scope="col">type</th>	                    
	                    <th scope="col" class="text-right" nowrap>last updated</th>
	                    <th scope="col" class="text-right" nowrap>actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($blacklist_ws as $bw)
                <tr class="collection_row">                  
                    <td>{{$bw->keyword}}</td>
                    <td>{{$bw->type}}</td>                    
                    <td class="text-right" nowrap>{{$bw->updated_at}}</td>   
                    <td class="text-right" nowrap>
                        <a class="text-success blacklist_edit" href="{{ url('blacklist/'.$bw->id.'/edit') }}">edit</a>&nbsp;|&nbsp;
                        <a class="text-danger" href="{{ url('blacklist/delete/'.$bw->id) }}" onclick="return confirm('Are you sure?')">delete</a>
                    </td>                
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="col-sm-12 btn-toolbar justify-content-between mt-2" role="toolbar">            
            <div class="pagination pagination-sm mx-auto">{{ $blacklist_ws->links() }}</div>            
        </div>
    </div>
</div>
@endsection

