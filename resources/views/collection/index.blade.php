@extends('layouts.app')

@section('content')
<script>
    $(document).ready(function() {
        $(".collection_row").click(function(event) {   
            if (!(event.target.tagName.toLowerCase() === 'a')) {         
                window.location.href=$('a.collection_edit', this).attr('href');
            }
        });
    });
</script>

<div class="container">
    <div class="row">
        <div class="col-sm-12 btn-toolbar justify-content-between" role="toolbar">
            <h4>collections</h4>
            <form id="filter_form" action="{{ url('collection') }}" method="get">
            @csrf
            <div class="input-group float-left">
                <input type="search" class="form-control form-control-sm" id="keyword" name="keyword" placeholder="search by title" value="{{ $filters['keyword'] }}"> 
                <a type="button" class="btn btn-sm btn-link input-group-append" onclick="document.getElementById('filter_form').submit();"><i class="fas fa-search m-auto"></i>&nbsp;</a>
            </div>
            <div class="btn-group btn-group-sm ml-2 float-left" role="group">
                <a class="btn btn-sm btn-primary" data-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">create new</a>
            </div>
            </form>
        </div>       
       
        <div class="col-12 collapse" id="collapseExample">
            <div class="card card-body">
                <form action="collection" method="post" class="form">
                    @csrf                   
                    <div class="input-group col-10 mx-auto">
                        <div class="input-group-prepend">
                            <span class="input-group-text">collection title</span>
                        </div>
                        <input type="text" class="form-control" id="title" name="title" placeholder="ex: etsy collection for halloween 2019 vol.1 (account: abc@example.com)" autofocus>
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-primary">create new</button>    
                        </div>
                    </div>
                </form>                
            </div>
        </div>
    
        <div class="col-12 d-flex justify-content-center pagination pagination-sm">{{ $data->links() }}</div>

        <div class="col-12 my-2 table-responsive">
            <table class="table table-hover">
                <thead class="thead-light">
                    <tr>                    
                    <th scope="col">title</th>
                    <th scope="col">export csv</th>
                    <th scope="col">last updated</th>
                    <th scope="col">actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($data as $d)
                <tr class="collection_row">                    
                    <th scope="row">
                        <a class="" href="{{ url('collection/'.$d->id.'/edit') }}">{{ $d->title }}</a><br/>   
                        <small class="text-muted">                            
                            {{ $d->created_at->diffForHumans() }} by {{ $d->getOwner()->name }}                            
                        </small>
                    </th>                    
                    <td class="align-middle" nowrap>
                        <a style="color:#F45800" href="{{ url('collection/export/?id='.$d->id.'&target=etsycsv') }}">export csv</a>
                    </td>
                    <td class="align-middle shop_updated_at{{$d->id}}" nowrap>
                        {{ $d->updated_at->diffForHumans() }}
                    </td>  
                    <td class="align-middle" nowrap>
                        <a class="text-success collection_edit" href="{{ url('collection/'.$d->id.'/edit') }}">edit</a>&nbsp;|&nbsp;
                        <a class="text-danger" href="{{ url('collection/delete/'.$d->id) }}" onclick="return confirm('Are you sure?')">delete</a>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="col-12 d-flex justify-content-center pagination pagination-sm">{{ $data->links() }}</div>
    </div>

</div>

@endsection