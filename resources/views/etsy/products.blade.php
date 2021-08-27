@extends('layouts.app')

@section('content')
<script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $("#shop_check_all").change(function() {
        	$('input[name*=listing_ids]').prop('checked', this.checked);
        });
        
        $("#filter_form").find("select").on('change', function (e) {        
            $("#filter_form").submit();
        });

    });

    function activeListing(){
    	if (!$("input[name*=listing_ids]").is(":checked")) {
  		  showAlert('please select items');
		}
		
		var elements = [];
		
    	$("input[name*=listing_ids]:checked").each(function(event, obj){  
    	    
            var id = $(obj).attr('listing_id');
    		console.log('>> listing id:'+id);
    		$('#listing_id'+id).show().removeClass('d-none').addClass('d-block');
    		elements.push(id);
    		
        });
        activeListings(elements);
    }
    
    function pause(msec) {
        return new Promise(
            (resolve, reject) => {
                setTimeout(resolve, msec || 2000);
            }
        );
    }
    
    async function activeListings(elements) {
        var count = 0;
        for (var e in elements) {
            var posting = $.post("{{url('/etsy/activeListing')}}", {
                listing_id: elements[e],
                '_token': $('input[name="_token"]').val(),
            });
            
            posting.always(function(response) {
                console.log(response);
                if (response.success == true) {
                    showAlert('success')
                    $('#listing_id'+elements[e]).hide().removeClass('d-block');
                }
            }); 
            
            if (++count >= 1) {
                await pause(2000);
                count = 0;
            }
        }
    }
    
    function uploadListingImage(){
    	if (!$("input[name*=listing_ids]").is(":checked")) {
  		  showAlert('please select items');
		}
		
		var elements = [];
		
    	$("input[name*=listing_ids]:checked").each(function(event, obj){  
    	    
            var id = $(obj).attr('listing_id');
    		console.log('>> listing id:'+id);
    		$('#listing_id'+id).show().removeClass('d-none').addClass('d-block');
    		elements.push(id);
    		
        });
        uploadListingImages(elements);
    }
    
    async function uploadListingImages(elements) {
        var count = 0;
        for (var e in elements) {
            var posting = $.post("{{url('/etsy/uploadListingImage')}}", {
                listing_id: elements[e],
                '_token': $('input[name="_token"]').val(),
            });
            
            posting.always(function(response) {
                console.log(response);
                if (response.success == true) {
                    showAlert('success')
                    $('#listing_id'+elements[e]).hide().removeClass('d-block');
                }
            }); 
            
            if (++count >= 1) {
                await pause(2000);
                count = 0;
            }
        }
    }
    
    function inactiveListings(){
    	if (!$("input[name*=listing_ids]").is(":checked")) {
  		  showAlert('please select items');
		}
    	$("input[name*=listing_ids]:checked").each(function(event, obj){            
            // console.log(obj); 
            var id = $(obj).attr('listing_id');
    		console.log('>> Upload a new listing image:'+id);
    		$('#listing_id'+id).show().removeClass('d-none').addClass('d-block');
    		
            var posting = $.post("{{url('/etsy/inactiveListing')}}", {
                listing_id: id,
                '_token': $('input[name="_token"]').val(),
            });

            posting.done(function(response) {
                console.log(response);
                if (response.success == true) {
                    showAlert('success')
                    // location.reload(true);
                    $('#listing_id'+id).hide().removeClass('d-block');
                } else {
                    showAlert('Something wrong!');
                }
            });
            posting.fail(function(response) {
                // showAlert("Error: " + response);
                console.log(response);
            });
            posting.always(function(response) {
                console.log(response);
                // alert( "finished" );
            });        
        });  
    	
    }
    
//     function uploadListingImages(){
//     	if (!$("input[name*=listing_ids]").is(":checked")) {
//   		  showAlert('please select items');
// 		}
//     	$("input[name*=listing_ids]:checked").each(function(event, obj){            
//             // console.log(obj); 
//             var id = $(obj).attr('listing_id');
//     		console.log('>> Upload a new listing image:'+id);
//     		$('#listing_id'+id).show().removeClass('d-none').addClass('d-block');
    		
//             var posting = $.post("{{url('/etsy/uploadListingImage')}}", {
//                 listing_id: id,
//                 '_token': $('input[name="_token"]').val(),
//             });

//             posting.done(function(response) {
//                 console.log(response);
//                 if (response.success == true) {
//                     showAlert('success')
//                     // location.reload(true);
//                     $('#listing_id'+id).hide().removeClass('d-block');
//                 } else {
//                     showAlert('Something wrong!');
//                 }
//             });
//             posting.fail(function(response) {
//                 // showAlert("Error: " + response);
//                 console.log(response);
//             });
//             posting.always(function(response) {
//                 console.log(response);
//                 // alert( "finished" );
//             });        
//         }); 
//     }
    
    function updateInventory(){
    	if (!$("input[name*=listing_ids]").is(":checked")) {
  		  showAlert('please select items');
		}
		
    	$("input[name*=listing_ids]:checked").each(function(event, obj){           
    	    
            var id = $(obj).attr('listing_id');
            
    		$('#listing_id'+id).show().removeClass('d-none').addClass('d-block');
    		
            var posting = $.post("{{url('/etsy/updateInventory')}}", {
                listing_id: id,
                '_token': $('input[name="_token"]').val(),
            });

            posting.done(function(response) {
                if (response.success == 1) {
                    showAlert('success')
                    $('#listing_id'+id).hide().removeClass('d-block');
                } else {
                    showAlert('Something wrong!');
                }
            });
            
            posting.fail(function(response) {
                console.log(response);
            });
            
            posting.always(function(response) {
                console.log(response);
            });        
        }); 
    }
    
    function deleteListings(){
        $("input[name*=listing_ids]:checked").each(function(event, obj){            
            // console.log(obj); 
            var id = $(obj).attr('listing_id');

            console.log('>> Delete a Listing: '+id); 
            $('#listing_id'+id).show().removeClass('d-none').addClass('d-block');
    		
            var posting = $.post("{{url('/etsy/deleteListing')}}", {
                listing_id: id,
                '_token': $('input[name="_token"]').val(),
            });

            posting.done(function(response) {
                console.log(response);
                if (response.success == true) {
                    showAlert('success')
                    location.reload(true);
                    $('#listing_id'+id).hide().removeClass('d-block');
                } else {
                    showAlert('Something wrong!');
                }
            });
            posting.fail(function(response) {
                // showAlert("Error: " + response);
                console.log(response);
            });
            posting.always(function(response) {
                console.log(response);
                // alert( "finished" );
            });         
        });  
    }
    
    function makeMockups(){
        if (!$("input[name*=listing_ids]").is(":checked")) {
          showAlert('please select items');
        }
        $("input[name*=listing_ids]:checked").each(function(event, obj){            
            // console.log(obj); 
            var id = $(obj).attr('listing_id');
            var shop_id = $(obj).attr('shop_id');
            
            console.log('>> make mockups for listing:'+id);
            
            $('#listing_id'+id).show().removeClass('d-none').addClass('d-block');
            
            var posting = $.post("{{url('/etsy/generate_mockup')}}", {
                listing_id: id,
                shop_id: shop_id,
                '_token': $('input[name="_token"]').val(),
            });
            // console.log(posting);
            posting.done(function(response) {
                // console.log(response);
                if (response.success == true) {
                    showAlert('success')
                    // location.reload(true);
                    $('#listing_id'+id).hide().removeClass('d-block');
                } else {
                    showAlert('Something wrong!');
                }
            });
            posting.fail(function(response) {
                showAlert("Error: " + response);
                // console.log(response);
            });
            posting.always(function(response) {
                console.log(response);
                // alert( "finished" );
            });        
        });  
        
    }
</script>
<style type="text/css">
	a:hover {
	    text-decoration: none;
	}
</style>

<div class="container">    
    <div class="row">
        <div class="col-sm-12 btn-toolbar justify-content-between" role="toolbar">
            <h4><a href="{{ url('/etsy/'.$colEtsy[0]->shop_id.'/edit') }}" role="button" target="_blank"><i class="far fa-edit"></i> edit this shop</a> <small class="text-muted">{{$colEtsy->firstItem()}}-{{$colEtsy->lastItem()}}/{{$colEtsy->total()}}</small></h4>
            <div class="justify-content-end">
                <form id="filter_form" action="{{ url('etsy') }}" method="get">
                @csrf                
                <div class="input-group float-left">
                    <a href="javascript:makeMockups();" class="btn btn-sm btn-primary ml-1" role="button">1. make mockups</a>
                    <a href="javascript:uploadListingImage();" class="btn btn-sm btn-primary ml-1" role="button">2. upload listing image</a>
                    <a href="javascript:updateInventory();" class="btn btn-sm btn-primary ml-1" role="button">3. update inventory</a>
                    <a href="javascript:activeListing();" class="btn btn-sm btn-primary ml-1" role="button">4. active listing</a>
                    <a class="btn btn-sm btn-danger ml-1" href="javascript:deleteListings();" role="button" onclick="return confirm('Are you sure?')">5. delete listing</a> 
                </div>
                </form>
            </div>
        </div>        
        <div class="col-sm-12 btn-toolbar justify-content-between mt-2" role="toolbar">            
            <div class="pagination pagination-sm mx-auto">{{$colEtsy->links()}}</div>            
        </div>
    </div>

    <div class="row mt-3 p-0">   
        <div class="col-12 my-2 table-responsive">
            <table class="table table-hover">
                <thead class="thead-light">
                    <tr>            
	                    <th scope="col"><input type="checkbox" id="shop_check_all"></th>       
	                    <th scope="col">listing id</th>
	                    <th scope="col">thumnail</th>
	                    <th scope="col">details</th>
	                    <th scope="col"></th>
	                    <th scope="col">last updated</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($colEtsy as $d)
                <tr>    
                    <th scope="row" class="align-middle">
                        <input type="checkbox" name="listing_ids[]" listing_id="{{$d->listing_id}}" id="checkbox{{$d->listing_id}}" shop_id="{{$d->shop_id}}">
                    </th>
                    <th class="align-middle">
                        {{$d->listing_id}} <img id="listing_id{{ $d->listing_id }}" style="width:25px" class="d-none" src="{{asset('images/loading.gif')}}" alt='creating...'>
                    </th>
                    <td class="align-middle" nowrap>
                        <img src="{{$d->main_image_url ? $d->main_image_url : $d->additional_image_url}}" class="img img-thumbnail" style="width:125px">
                    </td>
                    <td class="align-middle">
                        <span class="design_title">{{$d->getDetail($d->design_id) ? $d->getDetail($d->design_id)->title : 'not found'}}</span><br />
                        <small class="design_tags text-muted">{{$d->getDetail($d->design_id) ? $d->getDetail($d->design_id)->tags : 'null'}}</small>
                    </td>
                    <td class="align-middle text-center" nowrap>
                        <input type="checkbox" @if ($d->inventory == 1 || $d->is_digital == 1) ? checked : '' @endif disabled>
                    </td>  
                    <td class="align-middle" nowrap>
                        {{ $d->updated_at->diffForHumans() }}
                    </td>                   
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="col-sm-12 d-flex justify-content-center pagination pagination-sm">{{$colEtsy->links()}}</div>
    </div>

</div>

@endsection