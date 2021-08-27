@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        <!--------- BEGIN COLLECTION INFO -------->
        <form action="{{ url('etsy/update') }}" method="post" class="form w-100">
            @csrf
            @method('PUT')
            <input type="hidden" id="id" name="id" value="{{$data->id}}">
            <div class="col-sm-12 btn-toolbar justify-content-between align-middle" role="toolbar">
                <h3 class="h-100 my-auto">etsy #{{$data->id}} <small class="text-muted">by {{ $data->getOwner()->name }}</small></h3>
                <div class="">
                    <button class="btn btn-primary" type="submit">1. update infor</button>
                    <a role="button" class="btn btn-primary" href="{{ url('etsy/'.$data->id.'/connect') }}">2. connect api</a>
                    <a role="button" class="btn btn-primary" href="{{ url('etsy/'.$data->id.'/listing') }}">3. create listing</a>
                    <a role="button" class="btn btn-primary" href="{{ url('etsy/'.$data->id.'/product') }}">4. products</a>
                    <!--<a role="button" class="btn btn-primary" href="{{ url('etsy/'.$data->id.'/order') }}">5. get orders</a>-->
                    <a role="button" class="btn btn-primary" href="{{ url('etsy/'.$data->id.'/listings') }}">6. get products</a>
                </div>
            </div>

            <div class="col-12 mt-3">                
                <div class="card card-body">                    
                    <div class="form-group row">
                        <div class="col-4">
                            <label for="shop_url">shop url</label>
                            <input type="text" class="form-control @error('shop_url') is-invalid @enderror" value="{{$data->shop_url}}" id="shop_url" name="shop_url">                       
                        </div>
                        <div class="col-2">
                            <label for="shop_name">shop name</label>
                            <input type="text" class="form-control @error('shop_name') is-invalid @enderror" value="{{$data->shop_name}}" id="shop_name" name="shop_name">                         
                        </div> 
                        <div class="col-2">
                            <label for="bank_account">bank account</label>
                            <input type="text" class="form-control @error('bank_account') is-invalid @enderror" value="{{$data->bank_account}}" id="bank_account" name="bank_account">                         
                        </div>
                        <div class="col-4">
                            <label for="login_email">login email</label>
                            <input type="text" class="form-control @error('login_email') is-invalid @enderror" value="{{$data->login_email}}" id="login_email" name="login_email">                         
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-8">
                            <label for="key_string">key string (*)</label>
                            <input type="text" class="form-control @error('key_string') is-invalid @enderror" value="{{$data->key_string}}" id="key_string" name="key_string" required>                         
                        </div>
                        <div class="col-4">
                            <label for="share_secret">share secret (*)</label>
                            <input type="text" class="form-control @error('share_secret') is-invalid @enderror" value="{{$data->share_secret}}" id="share_secret" name="share_secret" required>                       
                        </div>
                        <div class="col-8">
                            <label for="access_token">access token</label>
                            <input type="text" class="form-control @error('access_token') is-invalid @enderror" value="{{$data->access_token}}" id="access_token" name="access_token">                        
                        </div>
                        <div class="col-4">
                            <label for="access_token_secret">access token secret</label>
                            <input type="text" class="form-control @error('access_token_secret') is-invalid @enderror" value="{{$data->access_token_secret}}" id="access_token_secret" name="access_token_secret">                        
                        </div>
                    </div>
                    <div class="form-group row">                        
                        <div class="col-4">
                            <label for="shipping_template_id">shipping template id (*)</label>
                            <input type="text" class="form-control @error('shipping_template_id') is-invalid @enderror" value="{{$data->shipping_template_id}}" id="shipping_template_id" name="shipping_template_id" required>                         
                        </div>  
                        <div class="col-2">
                            <label for="price">price</label>
                            <input type="text" class="form-control @error('price') is-invalid @enderror" value="{{$data->price}}" id="price" name="price">                        
                        </div> 
                        <div class="col-2">
                            <label for="quantity">quantity</label>
                            <input type="text" class="form-control @error('quantity') is-invalid @enderror" value="{{$data->quantity}}" id="quantity" name="quantity">                      
                        </div>
                        <div class="col-4">
                            <label for="taxonomy_id">taxonomy id</label>
                            <input type="text" class="form-control @error('taxonomy_id') is-invalid @enderror" value="{{$data->taxonomy_id}}" id="taxonomy_id" name="taxonomy_id">
                            <small id="bonanza_category_help" class="form-text text-muted">
                                <a href="{{ url('/etsy/'.$data->id.'/taxonomy') }}" target="_blank">find list of seller taxonomy here</a>
                            </small>
                        </div>
                    </div>
                    <div class="form-group row">                        
                        <div class="col-8">
                            <label for="image_url_1">image url 1 <small class="text-muted">(color chart)</small></label>
                            <input type="text" class="form-control @error('image_url_1') is-invalid @enderror" value="{{$data->image_url_1}}" id="image_url_1" name="image_url_1">                         
                        </div>  
                        <div class="col-4">
                            <label for="image_id_1">image id 1 <small class="text-muted">(color chart)</small></label>
                            <input type="text" class="form-control @error('image_id_1') is-invalid @enderror" value="{{$data->image_id_1}}" id="image_id_1" name="image_id_1">                         
                        </div>
                        <div class="col-8">
                            <label for="image_url_2">image url 2 <small class="text-muted tex">(size chart)</small></label>
                            <input type="text" class="form-control @error('image_url_2') is-invalid @enderror" value="{{$data->image_url_2}}" id="image_url_2" name="image_url_2">                        
                        </div> 
                        <div class="col-4">
                            <label for="image_id_2">image id 2 <small class="text-muted tex">(size chart)</small></label>
                            <input type="text" class="form-control @error('image_id_2') is-invalid @enderror" value="{{$data->image_id_2}}" id="image_id_2" name="image_id_2">                        
                        </div>
                        <div class="col-8">
                            <label for="image_url_3">image url 3 <small class="text-muted">(shipping policy)</small></label>
                            <input type="text" class="form-control @error('image_url_3') is-invalid @enderror" value="{{$data->image_url_3}}" id="image_url_3" name="image_url_3">                      
                        </div>  
                        <div class="col-4">
                            <label for="image_id_3">image id 3 <small class="text-muted">(shipping policy)</small></label>
                            <input type="text" class="form-control @error('image_id_3') is-invalid @enderror" value="{{$data->image_id_3}}" id="image_id_3" name="image_id_3">                      
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-4">
                            <label for="collection_id">shop's collection</label>
                            <select class="form-control" name="collection_id">
                                @foreach($collections as $col)
                                <option value="{{$col->id}}" @if($data->collection_id == $col->id) ? selected : '' @endif>{{$col -> title}}</option>
                                @endforeach
                            </select>                         
                        </div>
                        <div class="col-4">
                            <label for="note">note</label>
                            <input type="text" class="form-control @error('note') is-invalid @enderror" value="{{$data->note}}" id="note" name="note">                      
                        </div>
                        <div class="col-2">
                            <label for="is_active">status</label>
                            <select class="form-control form-control text-danger" id="is_active" name="is_active">
                                <option value="0" @if($data->is_active == 0) ? selected : '' @endif>Uh Oh !</option>
                                <option value="1" @if($data->is_active == 1) ? selected : '' @endif>Live</option>
                            </select>    
                        </div>
                        <div class="col-2">
                            <label for="owner_id">owner</label>
                            <select class="form-control form-control" id="owner_id" name="owner_id">
                                @foreach($users as $user)
                                <option value="{{$user->id}}" @if($data->owner_id == $user->id) ? selected : '' @endif>{{$user->name}}</option>
                                @endforeach
                            </select>    
                        </div>
                    </div>
                    <div class="form-group row">                        
                        <div class="col-12">
                            <label for="description">description</label>
                            <textarea name='description' id='description' class="form-control" rows="8">{{$data->description}}</textarea>                         
                        </div>              
                    </div>
                </div>
            </div>
        </form>
        <!--------- END COLLECTION INFO -------->
    </div>
</div>
@endsection

