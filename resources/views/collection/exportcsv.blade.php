@extends('layouts.app')

@section('content')
<script>
    var collection = {!! $data['collection']->toJson() !!};
    var designs = {!! $data['designs']->keyBy('id')->toJson() !!};

    var mockups = {!! $data['mockups']->keyBy('id')->toJson() !!};

    var asset ="{{ asset('') }}";
    var total = 0;
    var current = 0;

    var current_progress = 0;  //amazon make mockup
    var total_progress = 0; //amazon make mockup

    var dark_color_maps = [];
    var light_color_maps = [];

    var dark_primary_color = 'Black';
    var light_primary_color = 'White';

    var black_word_list = [];

    @foreach ($black_word_list as $bw)
        black_word_list.push("{{$bw->keyword}}");
    @endforeach

    function checkHasBlackWord(title) {
        var eg
        var match

        black_word_list.some(item=> {
            eg =new RegExp(item, "gi")
            match = title.match(eg)

            if (match && match.length) {
                return true;
            }

            return false;
        })
    }

    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('.color_maps').each(function () {
            //console.log('each:'+$(this).attr('value')+this.checked);
            if (this.checked) {
                $(this).parent().removeClass('color_unselected').addClass('color_selected');
                $(this).parent().find('i').removeClass('d-none');
                $(this).parent().button('toggle');
            } else {
                $(this).parent().removeClass('color_selected').addClass('color_unselected');
                $(this).parent().find('i').addClass('d-none');
                $(this).parent().button('dispose');
            }
            $(this).trigger('change');
        });

        $('.color_maps').change(function(event) {
            if (this.checked) {
                $(this).parent().removeClass('color_unselected').addClass('color_selected');
                $(this).parent().find('i').removeClass('d-none');
                //$(this).parent().button('toggle');
            } else {
                $(this).parent().removeClass('color_selected').addClass('color_unselected');
                $(this).parent().find('i').addClass('d-none');
                //$(this).parent().button('dispose');
            }
        });

        $("#design_check_all").change(function() {
            $('input[name*=design_ids]').prop('checked', this.checked);

            var x = document.querySelectorAll('input[name*=design_ids]:checked').length;
            $("#y").html(x);

        });

        $(".design_row").click(function(event) {
            if ((event.target.type !== 'checkbox') && (!$(event.target).hasClass('remockup'))) {
                var checked = $(':checkbox', this).prop('checked');
                $(':checkbox', this).prop('checked', !checked);
                if (!checked) {
                    $('#design_check_all').prop('checked', false);
                }
            }

            var x = document.querySelectorAll('input[name*=design_ids]:checked').length;
            $("#y").html(x);
        });

        $('#variations').val(etsy_variations(false));
        $('#bonanza_trait').val(bonanza_trait(false));
    });

    function makeMockups() {
        current = 0;
        total = $("input[name*=design_ids]:checked").length;

        $("input[name*=design_ids]:checked").each(function(event, obj){
            //console.log(obj);
            var id = $(obj).attr('design_id');
            console.log(id);
            if (id != '') {

                //data.push(id);

                loadMockup(obj);
            }
        });
    }

    function loadMockup(element) {
        var design_id = $(element).attr('design_id');
        $('#mockup'+design_id).attr('src', asset + 'images/loading.gif');

        var collection_id = collection.id;

        var posting = $.post("{{url('/collection/new_mockup')}}", {
            collection_id: collection_id,
            design_id: design_id
        });
        posting.done(function(response) {
            console.log(response);

            if (response.success == 1) {
                $('#mockup'+design_id).attr('src', response.data);

                current++;
                console.log(current + ":" + total);
                if (current == total) {
                    //doExport();
                    $('#download-link').removeClass('d-none');
                    current++;
                }
            } else {
                showAlert('Something wrong!');
            }
        });
        posting.fail(function(response) {
            showAlert("Error: " + response);
        });
        posting.always(function(response) {
            //alert( "finished" );
        });
    }

    function makeAmazonMockups() {
        current_progress = 0;
        //total_progress = $("input[name*=design_ids]:checked").length - 1;
        console.log('total_progress:'+total_progress);

        $(".progress").removeClass('d-none').addClass('d-block');
        $("#progress").attr("aria-valuenow", 0).css("width", "0%").attr("aria-valuemax", total_progress).text("making new mockup by color: 0% complete");
        $("#amazon_export_csv").attr("disabled","disabled");

        $('#tblData > tbody  > tr').each(function(event, obj) {
            var design_id = $(obj).attr('design_id');
            if (design_id) {
                var design = designs[design_id];

                var color_maps = (design.color == 'light') ? dark_color_maps : light_color_maps;

                $.each(color_maps, function(imap, mockup) {
                    console.log('start making: ' + design_id + ' - ' + mockup.color_name);
                    loadAmazonMockup(design_id, mockup.id);
                });
            }
        });
    }

    function loadAmazonMockup(design_id, mockup_id) {
        // var design_id = $(element).attr('design_id');
        // var mockup_id = $(element).attr('mockup_id');

        var posting = $.post("{{url('/design/new_mockup')}}",{mockup_id:mockup_id, design_id:design_id});
        posting.done(function(response) {
            //console.log(response);
            var img = '.design_' + design_id + '_mockup_' + mockup_id + ' > img';
            //console.log('img:'+img);
            if (response.success == 1)
            {
                $(img).attr('src', response.data);
                $('.child_main_image_url' + design_id + '_mockup_' + mockup_id).append(response.data);
                $(img).parent().parent().addClass('text-success'); //tr
            } else {
                $(img).parent().parent().addClass('text-danger'); //tr
                showAlert('Something wrong!');
            }
        });
        posting.fail(function(response) {
            $('.design_' + design_id + '_mockup_' + mockup_id).parent().addClass('text-danger'); //tr
            showAlert( "Error: " + response );
        });
        posting.always(function(response) {
            //update progress
            current_progress = parseInt($("#progress").attr("aria-valuenow")) + 1;
            var percent = parseInt(current_progress / total_progress * 100);
            $("#progress")
                .css("width", percent + "%")
                .attr("aria-valuenow", current_progress)
                .text("making new mockup by color: " + percent + "% complete");

            if (current_progress >= total_progress) {
                $("#amazon_export_csv").removeAttr("disabled");
            }
        });
    }

    function getSelectedDesigns() {
        var selected_designs = array();
        $("input[name*=design_ids]:checked").each(function(event, obj){
            //console.log(obj);
            var id = $(obj).attr('design_id');
            console.log(id);
            if (id != '') {
                selected_designs.push(designs[id]);
            }
        });

        console.log(selected_designs);
    }

    function doExportEtsy() {
        console.log('export etsy');
        //shop_section_id,
        //var header="title,description,quantity,price,is_supply,who_made,is_customizable,when_made,tags,processing_min,processing_max,shipping_template_id,images,variations,state,materials,taxonomy_id";
        var header = '"title","description","quantity","price","is_supply","who_made","is_customizable","when_made","tags","processing_min","processing_max","shipping_template_id","images","variations","state","materials","taxonomy_id"';

        var csvContent = ""; //"data:text/csv;charset=utf-8,";
        //var csvContent = "data:text/csv;charset=utf-8,%EF%BB%BF";
        csvContent += header + "\r\n";

        var shipping_template_id = $('#shipping_template_id').val();
        var variations = $('#variations').val();
        var collection_images = [];
        if (collection.image_url_1 != null) collection_images.push(collection.image_url_1.replace(',', '%2C'));
        if (collection.image_url_2 != null) collection_images.push(collection.image_url_2.replace(',', '%2C'));
        if (collection.image_url_3 != null) collection_images.push(collection.image_url_3.replace(',', '%2C'));

        //$(designs).each(function(i, design) {
        //var selected_designs = getSelectedDesigns();
        //$.each(selected_designs, function(i, design) {
        $("input[name*=design_ids]:checked").each(function(event, obj){
            //console.log(obj);
            var id = $(obj).attr('design_id');
            var design = designs[id];

            //console.log(design);

            var design_image = $('#mockup' + design.id).attr('src');
            //console.log(design);
            var row = '';
            row += '"' + design.title.substr(0,140).replace(/"/g, '""') + '",';
            row += '"' + (collection.description != null ? collection.description.replace(/"/g, '""') : '') + '",';
            row += "999" + ','; //quantity
            row += "17.99" + ','; //price
            row += "0" + ','; //is_supply
            row += '"i_did",'; //who_made
            row += "1" + ','; //is_customizable
            row += "made_to_order" + ','; //when_made
            row += '"' + design.tags + '",';
            row += "2" + ','; //processing_min
            row += "14" + ','; //processing_max
            row += shipping_template_id + ','; //shipping_template_id
            row += '"' + (design_image.replace(',', '%2C') + ',' + collection_images.join(',')) + '",';
            row += '"' + variations.replace(/"/g, '""') + '",'; //variations
            row += '"' + "draft" + '",'; //state
            row += '"' + "cotton" + '",'; //materials
            row += "11165" + ','; //taxonomy_id

            csvContent += row + "\r\n";
            //console.log(row);
        });

        //var encodedUri = encodeURI(csvContent);

        downloadFile('etsy',csvContent, collection.title + "-etsy-" + nowToString() + ".csv");

        // var link = document.createElement("a");
        // link.setAttribute("href", encodedUri);
        // link.setAttribute("download", collection.title + "-etsy-" + nowToString() + ".csv");
        // document.body.appendChild(link); // Required for FF

        // link.click(); // This will download the data file named "my_data.csv".
    }

    function doExportBonanza() {
        console.log('export bonanza');
        var header = '"title","description","price","trait","category","shipping_price","worldwide_shipping_price","image1","image2","image3","image4","image5","image6"';

        var csvContent = ""; //"data:text/csv;charset=utf-8,";
        csvContent += header + "\r\n";

        var price = $('#bonanza_price').val();
        var trait = $('#bonanza_trait').val();
        var category = $('#bonanza_category').val();
        var shipping_price = $('#bonanza_shipping_price').val();
        var worldwide_shipping_price = $('#bonanza_worldwide_shipping_price').val();
        var image_url1 = collection.image_url_1 ? collection.image_url_1.replace(',', '%2C') : '';
        var image_url2 = collection.image_url_2 ? collection.image_url_2.replace(',', '%2C') : '';
        var image_url3 = collection.image_url_3 ? collection.image_url_3.replace(',', '%2C') : '';

        $("input[name*=design_ids]:checked").each(function(event, obj){
            var id = $(obj).attr('design_id');
            var design = designs[id];
            var design_image = $('#mockup' + design.id).attr('src');

            var row = '';
            row += '"' + design.title80.substr(0,80).replace(/"/g, '""') + '",';
            row += '"' + (collection.description != null ? collection.description.replace(/"/g, '""') : '') + '",';
            row += '"' + price + '",'; //price
            row += '"' + trait + '",'; //trait
            row += '"' + category + '",'; //trait
            row += '"' + shipping_price + '",'; //shipping_price
            row += '"' + worldwide_shipping_price + '",'; //worldwide_shipping_price
            row += '"' + design_image.replace(',', '%2C') + '",'; //image1
            row += '"' + image_url1 + '",'; //image2
            row += '"' + image_url2 + '",'; //image3
            row += '"' + image_url3 + '",'; //image4
            row += '"' + '' + '",'; //image5
            row += '"' + '' + '",'; //image6

            csvContent += row + "\r\n";
        });

        //var encodedUri = encodeURI(csvContent);

        downloadFile('bonanza',csvContent, collection.title + "-bonanza-" + nowToString() + ".csv");

        // var link = document.createElement("a");
        // link.setAttribute("href", encodedUri);
        // link.setAttribute("download", collection.title + "-bonanza-" + nowToString() + ".csv");
        // document.body.appendChild(link); // Required for FF

        // link.click(); // This will download the data file named "my_data.csv".
    }

    function titleCase(str) {
        return str.toLowerCase().replace(/\b(\w)/g, s => s.toUpperCase());
    }

    function array_rand (array, num) {
        const keys = Object.keys(array);
        if (typeof num === 'undefined' || num === null) {
            num = 1;
        } else {
            num = +num;
        }
        if (isNaN(num) || num < 1 || num > keys.length) {
            return null;
        }
        // shuffle the array of keys
        for (let i = keys.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1)) // 0 ≤ j ≤ i
            const tmp = keys[j];
            keys[j] = keys[i];
            keys[i] = tmp;
        }
        return num === 1 ? keys[0] : keys.slice(0, num);
    }

    function doExportAmazon() {
        console.log('export amazon');
        //var header = '"feed_product_type","item_sku","brand_name","item_name","outer_material_type1","color_name","color_map","size_name","size_map","material_composition1","department_name","standard_price","quantity","merchant_shipping_group_name","main_image_url","other_image_url1","other_image_url2","other_image_url3","other_image_url4","parent_child","parent_sku","relationship_type","variation_theme","update_delete","product_description","item_type","bullet_point1","bullet_point2","bullet_point3","bullet_point4","bullet_point5","generic_keywords1","import_designation","condition_type","fulfillment_latency","product_site_launch_date"';

        var th = "<tr>"
                +"<th>feed_product_type</th>"
                +"<th>item_sku</th>"
                +"<th>brand_name</th>"
                +"<th>item_name</th>"
                +"<th>manufacturer</th>"
                +"<th>outer_material_type1</th>"
                +"<th>color_name</th>"
                +"<th>color_map</th>"
                +"<th>size_name</th>"
                +"<th>size_map</th>"
                +"<th>material_composition1</th>"
                +"<th>department_name</th>"
                +"<th>unit_count</th>"
                +"<th>unit_count_type</th>" // new
                +"<th>standard_price</th>"
                +"<th>quantity</th>"
                +"<th>merchant_shipping_group_name</th>"
                +"<th>main_image_url</th>"
                +"<th>other_image_url1</th>"
                +"<th>other_image_url2</th>"
                +"<th>other_image_url3</th>"
                //+"<th>other_image_url4</th>"
                +"<th>parent_child</th>"
                +"<th>parent_sku</th>"
                +"<th>relationship_type</th>"
                +"<th>variation_theme</th>"
                +"<th>update_delete</th>"
                +"<th>product_description</th>"
                +"<th>item_type</th>"
                +"<th>bullet_point1</th>"
                +"<th>bullet_point2</th>"
                +"<th>bullet_point3</th>"
                +"<th>bullet_point4</th>"
                +"<th>bullet_point5</th>"
                +"<th>generic_keywords1</th>"
                +"<th>item_type_name</th>"
                +"<th>import_designation</th>"
                +"<th>fulfillment_center_id</th>"
                +"<th>country_of_origin</th>"
                +"<th>condition_type</th>"
                +"<th>fulfillment_latency</th>"
                +"<th>product_site_launch_date</th>"
                +"</tr>";

        var tableContent = '<div class="table-responsive"><table id="tblData" class="table table-sm table-bordered"><thead>' + th + '</thead>';

        var amazon_brand_name = $('#amazon_brand_name').val();
        var amazon_department_name = $('#amazon_department_name').val();
        var amazon_bullet_point1 = $('#amazon_bullet_point1').val();
        var amazon_bullet_point2 = $('#amazon_bullet_point2').val();
        var amazon_bullet_point3 = $('#amazon_bullet_point3').val();
        var amazon_bullet_point4 = $('#amazon_bullet_point4').val();
        var amazon_bullet_point5 = $('#amazon_bullet_point5').val();

        dark_color_maps = [];
        $('.color_dark_maps').each(function () {
            if ($(this).prop('checked')) {
                dark_color_maps.push(mockups[$(this).attr('value')]);
            }
        });

        light_color_maps = [];
        $('.color_light_maps').each(function () {
            if ($(this).prop('checked')) {
                light_color_maps.push(mockups[$(this).attr('value')]);
            }
        });

        console.log(dark_color_maps);
        console.log(light_color_maps);

        var size_maps = JSON.parse($('#amazon_size_maps').val());

        var image_url1 = collection.image_url_1 ? collection.image_url_1.replace(',', '%2C') : '';
        var image_url2 = collection.image_url_2 ? collection.image_url_2.replace(',', '%2C') : '';
        var image_url3 = collection.image_url_3 ? collection.image_url_3.replace(',', '%2C') : '';

        total_progress = 0;

        tableContent += '<tbody>';
        $("input[name*=design_ids]:checked").each(function(event, obj){
            var parent_sku = randomAmazonSku(); //parent sku

            var id = $(obj).attr('design_id');
            var design = designs[id];
            var design_image = $('#mockup' + design.id).attr('src');

            var arr = (design.tags).split(",");

            console.log('> design id:',design.id);

            var title_arr = [];
            title_arr.push(design.title80);
            array_rand(arr,2).forEach(element => title_arr.push(arr[element]));
            console.log('> title:', titleCase(title_arr.join(' ')));

            if (checkHasBlackWord(titleCase(title_arr.join(' ')))) {
                return;
            }

            var tags_arr = [];
            var tags = [];
            if(arr.length >= 10){
                array_rand(arr,10).forEach(element => tags_arr.push(arr[element]));
                tags = tags_arr.join();
            }else{
                tags = arr.join();
            }
            // var tags = tags_arr.join();
            console.log('> tags:', tags);

            if (checkHasBlackWord(tags)) {
                return;
            }

            var row = '<tr design_id=' + id + '>';
            row += '<td>' + 'multitool' + '</td>';  //feed_product_type : shirt, multitool
            row += '<td>' + parent_sku  + '</td>';   //item_sku
            row += '<td>' + amazon_brand_name + '</td>';   //brand_name
            // row += '<td>' + design.title80 + '</td>'; //item_name
            row += '<td>' + titleCase(title_arr.join(', ')) + '</td>'; //item_name
            row += '<td>' + amazon_brand_name + '</td>'; // manufactuer
            row += '<td>' + 'Cotton' + '</td>';   //outer_material_type1
            row += '<td>' + '' + '</td>';   //color_name
            row += '<td>' + '' + '</td>';   //color_map
            row += '<td>' + '' + '</td>';   //size_name
            row += '<td>' + '' + '</td>';   //size_map
            row += '<td>' + 'Cotton' + '</td>';   //material_composition1
            row += '<td>' + amazon_department_name + '</td>';   //department_name
            row += '<td>' + '1' + '</td>';   //unit_count
            row += '<td>' + 'Count' + '</td>';   //unit_count_type
            row += '<td>' + '' + '</td>';   //standard_price
            row += '<td>' + '' + '</td>';   //quantity
            row += '<td>' + 'Migrated Template' + '</td>';   //merchant_shipping_group_name
            row += '<td id="main_image_url"' + design.id + '>' + design_image.replace(',', '%2C') + '</td>';   //main_image_url
            row += '<td>' + image_url1 + '</td>';   //other_image_url1
            row += '<td>' + image_url2 + '</td>';   //other_image_url2
            row += '<td>' + image_url3 + '</td>';   //other_image_url3
            //row += '<td>' + '' + '</td>';   //other_image_url4
            row += '<td>' + 'Parent' + '</td>';   //parent_child
            row += '<td>' + '' + '</td>';   //parent_sku
            row += '<td>' + '' + '</td>';   //relationship_type
            row += '<td>' + 'colorsize' + '</td>';   //variation_theme
            row += '<td>' + 'Update' + '</td>';   //update_delete
            row += '<td>' + '' + '</td>';    //product_description
            row += '<td>' + 'multitools' + '</td>';   //item_type : fashion-t-shirts ,  multitools
            row += '<td>' + amazon_bullet_point1 + '</td>';   //bullet_point1
            row += '<td>' + amazon_bullet_point2 + '</td>';   //bullet_point2
            row += '<td>' + amazon_bullet_point3 + '</td>';   //bullet_point3
            row += '<td>' + amazon_bullet_point4  + '</td>';   //bullet_point4
            row += '<td>' + amazon_bullet_point5 + '</td>';   //bullet_point5
            row += '<td>' + tags.substring(0,249) + '</td>';   //generic_keywords1
            row += '<td>' + 'Fashion T-Shirt' + '</td>';   //item_type_name
            row += '<td>' + 'Made in USA' + '</td>';   //import_designation
            row += '<td>' + 'DEFAULT' + '</td>';   //fulfillment_center_id
            row += '<td>' + 'US' + '</td>';   //country_of_origin
            row += '<td>' + 'New' + '</td>';   //condition_type
            row += '<td>' + '5' + '</td>';   //fulfillment_latency
            row += '<td>' + dateToString() + '</td>';   //product_site_launch_date
            row += '</tr>';

            tableContent += row;

            //child rows
            var color_maps = (design.color == 'light') ? dark_color_maps : light_color_maps;
            total_progress += color_maps.length;

            $.each(color_maps, function(imap, mockup) {

                $.each(size_maps, function(size_name, size_price) {
                    //child_sku = randomAmazonSku() + mockup.color_name.replace(' ', '').replace('-', '') + size_name.replace(' ', '').replace('-', '');
                    child_sku = randomAmazonSku() + '_' + mockup.color_name.replace(' ', '').replace('-', '') + '_' + size_name.replace(' ', '').replace('-', '') + '_' + design.id;
                    console.log(child_sku);
                    row = '<tr>';
                    row += '<td class="design_' + design.id + '_mockup_' + mockup.id + '">' + 'multitool' + '<img class="img img-thumbnail" src="' + asset + 'images/loading.gif">' + '</td>';  //feed_product_type
                    row += '<td>' + child_sku + '</td>';   //item_sku
                    row += '<td>' + amazon_brand_name + '</td>';   //brand_name
                    //row += '<td>' + (design.title80 + ' ' + mockup.color_name + ' ' + size_name).substr(0,80) + '</td>'; //item_name
                    row += '<td>' + titleCase(title_arr.join(', ')) + '</td>'; //item_name
                    row += '<td>' + amazon_brand_name + '</td>'; // manufactuer
                    row += '<td>' + 'Cotton' + '</td>';   //outer_material_type1
                    row += '<td>' + 'Multicoloured' + '</td>';   //color_name
                    row += '<td>' + 'Multicoloured' + '</td>';   //color_map
                    row += '<td>' + 'Option' + '</td>';   //size_name
                    row += '<td>' + 'Option' + '</td>';   //size_map
                    // row += '<td>' + mockup.color_name + '</td>';   //color_name
                    // row += '<td>' + mockup.color_map + '</td>';   //color_map
                    // row += '<td>' + size_name + '</td>';   //size_name
                    // row += '<td>' + size_name + '</td>';   //size_map
                    row += '<td>' + 'Cotton' + '</td>';   //material_composition1
                    row += '<td>' + amazon_department_name + '</td>';   //department_name
                    row += '<td>' + '1' + '</td>';   //unit_count
                    row += '<td>' + 'Count' + '</td>';   //unit_count_type
                    row += '<td>' + size_price + '</td>';   //standard_price
                    row += '<td>' + '999' + '</td>';   //quantity
                    row += '<td>' + 'Migrated Template' + '</td>';   //merchant_shipping_group_name
                    row += '<td class="design_' + design.id + '_mockup_' + mockup.id + ' child_main_image_url' + design.id + '_mockup_' + mockup.id + '">' + '<img class="img img-thumbnail" src="' + asset + 'images/loading.gif">' + '</td>';   //main_image_url
                    row += '<td>' + image_url1 + '</td>';   //other_image_url1
                    row += '<td>' + image_url2 + '</td>';   //other_image_url2
                    row += '<td>' + image_url3 + '</td>';   //other_image_url3
                    //row += '<td>' + '' + '</td>';   //other_image_url4
                    row += '<td>' + 'Child' + '</td>';   //parent_child
                    row += '<td>' + parent_sku + '</td>';   //parent_sku
                    row += '<td>' + 'Variation' + '</td>';   //relationship_type
                    row += '<td>' + 'colorsize' + '</td>';   //variation_theme
                    row += '<td>' + 'Update' + '</td>';   //update_delete
                    row += '<td>' + 'High quality and reasonable price with various colors and sizes to choose, our shirt makes a perfect and timeless gift. Sure to be one of your favorites, our T-Shirt will catch people’s attention when you walk down the road. Our t-shirts are printed on demand in USA, high quality, clean, bright, accurate color, soft material for outstanding finished garments. Please see the size chart carefully when placing the order. We also have shirts in a variety of different colors please message us with the color number in our color image to pick your favorite color.' + '</td>';    //product_description
                    row += '<td>' + 'multitools' + '</td>';   //item_type : fashion-t-shirts ,  multitools
                    //row += '<td>' + amazon_bullet_point1 + ' ' + design.title + ' ' + mockup.color_name + ' ' + size_name + '</td>';   //bullet_point1
                    row += '<td>' + amazon_bullet_point1 + '</td>';   //bullet_point1
                    row += '<td>' + amazon_bullet_point2 + '</td>';   //bullet_point2
                    row += '<td>' + amazon_bullet_point3 + '</td>';   //bullet_point3
                    row += '<td>' + amazon_bullet_point4  + '</td>';   //bullet_point4
                    row += '<td>' + amazon_bullet_point5 + '</td>';   //bullet_point5
                    row += '<td>' + tags.substring(0,249) + '</td>';   //generic_keywords1
                    row += '<td>' + 'Fashion T-Shirt' + '</td>';   //item_type_name
                    row += '<td>' + 'Made in USA' + '</td>';   //import_designation
                    row += '<td>' + 'DEFAULT' + '</td>';   //fulfillment_center_id
                    row += '<td>' + 'US' + '</td>';   //import_designation
                    row += '<td>' + 'New' + '</td>';   //condition_type
                    row += '<td>' + '5' + '</td>';   //fulfillment_latency
                    row += '<td>' + dateToString() + '</td>';   //product_site_launch_date
                    row += '</tr>';

                    tableContent += row;
                });
            });
        });

        tableContent += '</tbody></table></div>';
        $('#amazon_data').html(tableContent);

        makeAmazonMockups();

        //exportTableToExcel("tblData", collection.title + "-amazon-" + nowToString() + ".xls");

    }
    // Kieu Pro
    function doExportAmazon2() {
        console.log('export amazon');
        //var header = '"feed_product_type","item_sku","brand_name","item_name","outer_material_type1","color_name","color_map","size_name","size_map","material_composition1","department_name","standard_price","quantity","merchant_shipping_group_name","main_image_url","other_image_url1","other_image_url2","other_image_url3","other_image_url4","parent_child","parent_sku","relationship_type","variation_theme","update_delete","product_description","item_type","bullet_point1","bullet_point2","bullet_point3","bullet_point4","bullet_point5","generic_keywords1","import_designation","condition_type","fulfillment_latency","product_site_launch_date"';

        var th = "<tr>"
                +"<th>feed_product_type</th>"
                +"<th>item_sku</th>"
                +"<th>brand_name</th>"
                +"<th>item_name</th>"
                +"<th>outer_material_type1</th>"
                +"<th>color_name</th>"
                +"<th>color_map</th>"
                +"<th>size_name</th>"
                +"<th>size_map</th>"
                +"<th>material_composition1</th>"
                +"<th>department_name</th>"
                +"<th>is_adult_product</th>"
                +"<th>standard_price</th>"
                +"<th>quantity</th>"
                +"<th>merchant_shipping_group_name</th>"
                +"<th>target_gender</th>"
                +"<th>age_range_description</th>"
                +"<th>main_image_url</th>"
                +"<th>other_image_url1</th>"
                +"<th>other_image_url2</th>"
                +"<th>other_image_url3</th>"
                //+"<th>other_image_url4</th>"
                +"<th>parent_child</th>"
                +"<th>parent_sku</th>"
                +"<th>relationship_type</th>"
                +"<th>variation_theme</th>"
                +"<th>update_delete</th>"
                +"<th>product_description</th>"
                +"<th>shirt_size_system</th>" // shirt_size_system
                +"<th>shirt_size_class</th>" // shirt_size_class
                +"<th>shirt_size</th>" // shirt_size
                +"<th>shirt_body_type</th>" // shirt_body_type
                +"<th>shirt_height_type</th>" // shirt_height_type
                +"<th>item_type</th>"
                +"<th>bullet_point1</th>"
                +"<th>bullet_point2</th>"
                +"<th>bullet_point3</th>"
                +"<th>bullet_point4</th>"
                +"<th>bullet_point5</th>"
                +"<th>generic_keywords1</th>"
                +"<th>import_designation</th>"
                +"<th>condition_type</th>"
                +"<th>fulfillment_latency</th>"
                +"<th>product_site_launch_date</th>"
                +"</tr>";

        var tableContent = '<div class="table-responsive"><table id="tblData" class="table table-sm table-bordered"><thead>' + th + '</thead>';

        var amazon_brand_name = $('#amazon_brand_name').val();
        var amazon_department_name = $('#amazon_department_name').val();
        var amazon_bullet_point1 = $('#amazon_bullet_point1').val();
        var amazon_bullet_point2 = $('#amazon_bullet_point2').val();
        var amazon_bullet_point3 = $('#amazon_bullet_point3').val();
        var amazon_bullet_point4 = $('#amazon_bullet_point4').val();
        var amazon_bullet_point5 = $('#amazon_bullet_point5').val();

        dark_color_maps = [];
        $('.color_dark_maps').each(function () {
            if ($(this).prop('checked')) {
                dark_color_maps.push(mockups[$(this).attr('value')]);
            }
        });

        light_color_maps = [];
        $('.color_light_maps').each(function () {
            if ($(this).prop('checked')) {
                light_color_maps.push(mockups[$(this).attr('value')]);
            }
        });

        console.log(dark_color_maps);
        console.log(light_color_maps);

        var size_maps = JSON.parse($('#amazon_size_maps').val());

        var image_url1 = collection.image_url_1 ? collection.image_url_1.replace(',', '%2C') : '';
        var image_url2 = collection.image_url_2 ? collection.image_url_2.replace(',', '%2C') : '';
        var image_url3 = collection.image_url_3 ? collection.image_url_3.replace(',', '%2C') : '';

        total_progress = 0;

        tableContent += '<tbody>';
        $("input[name*=design_ids]:checked").each(function(event, obj){
            var parent_sku = randomAmazonSku(); //parent sku

            var id = $(obj).attr('design_id');
            var design = designs[id];
            var design_image = $('#mockup' + design.id).attr('src');

            var arr = (design.tags).split(",");

            console.log('> design id:',design.id);

            var title_arr = [];
            title_arr.push(design.title80);
            array_rand(arr,2).forEach(element => title_arr.push(arr[element]));
            console.log('> title:', titleCase(title_arr.join(', ')));

            if (checkHasBlackWord(titleCase(title_arr.join(' ')))) {
                return;
            }

            var tags_arr = [];
            var tags = [];
            if(arr.length >= 10){
                array_rand(arr,10).forEach(element => tags_arr.push(arr[element]));
                tags = tags_arr.join();
            }else{
                tags = arr.join();
            }
            // var tags = tags_arr.join();
            console.log('> tags:', tags);

            if (checkHasBlackWord(tags)) {
                return;
            }

            var row = '<tr design_id=' + id + '>';
            row += '<td>' + 'shirt' + '</td>';  //feed_product_type
            row += '<td>' + parent_sku  + '</td>';   //item_sku
            row += '<td>' + amazon_brand_name + '</td>';   //brand_name
            // row += '<td>' + design.title80 + '</td>'; //item_name
            row += '<td>' + titleCase(title_arr.join(', ')) + '</td>'; //item_name
            row += '<td>' + 'cotton' + '</td>';   //outer_material_type1
            row += '<td>' + '' + '</td>';   //color_name
            row += '<td>' + '' + '</td>';   //color_map
            row += '<td>' + '' + '</td>';   //size_name
            row += '<td>' + '' + '</td>';   //size_map
            row += '<td>' + 'Cotton.' + '</td>';   //material_composition1
            row += '<td>' + amazon_department_name + '</td>';   //department_name
            row += '<td>' + 'Yes' + '</td>';   //is_adult_product
            row += '<td>' + '' + '</td>';   //standard_price
            row += '<td>' + '' + '</td>';   //quantity
            row += '<td>' + 'Migrated Template' + '</td>';   //merchant_shipping_group_name
            row += '<td>' + 'Unisex' + '</td>';   //target_gender
            row += '<td>' + 'Adult' + '</td>';   //age_range_description
            row += '<td id="main_image_url"' + design.id + '>' + design_image.replace(',', '%2C') + '</td>';   //main_image_url
            row += '<td>' + image_url1 + '</td>';   //other_image_url1
            row += '<td>' + image_url2 + '</td>';   //other_image_url2
            row += '<td>' + image_url3 + '</td>';   //other_image_url3
            //row += '<td>' + '' + '</td>';   //other_image_url4
            row += '<td>' + 'Parent' + '</td>';   //parent_child
            row += '<td>' + '' + '</td>';   //parent_sku
            row += '<td>' + '' + '</td>';   //relationship_type
            row += '<td>' + 'colorsize' + '</td>';   //variation_theme
            row += '<td>' + 'Update' + '</td>';   //update_delete
            row += '<td>' + '' + '</td>';    //product_description
            row += '<td>' + 'US' + '</td>';   //shirt_size_system
            row += '<td>' + 'Alpha' + '</td>';   //shirt_size_class
            row += '<td>' + 'Small' + '</td>';   //shirt_size
            row += '<td>' + 'Regular' + '</td>';   //shirt_body_type
            row += '<td>' + 'Regular' + '</td>';   //shirt_height_type
            row += '<td>' + 'fashion-t-shirts' + '</td>';   //item_type
            row += '<td>' + amazon_bullet_point1 + '</td>';   //bullet_point1
            row += '<td>' + amazon_bullet_point2 + '</td>';   //bullet_point2
            row += '<td>' + amazon_bullet_point3 + '</td>';   //bullet_point3
            row += '<td>' + amazon_bullet_point4  + '</td>';   //bullet_point4
            row += '<td>' + amazon_bullet_point5 + '</td>';   //bullet_point5
            row += '<td>' + tags.substring(0,249) + '</td>';   //generic_keywords1
            row += '<td>' + 'Made in USA' + '</td>';   //import_designation
            row += '<td>' + 'New' + '</td>';   //condition_type
            row += '<td>' + '5' + '</td>';   //fulfillment_latency
            row += '<td>' + dateToString() + '</td>';   //product_site_launch_date
            row += '</tr>';

            tableContent += row;

            //child rows
            var color_maps = (design.color == 'light') ? dark_color_maps : light_color_maps;
            total_progress += color_maps.length;

            $.each(color_maps, function(imap, mockup) {

                $.each(size_maps, function(size_name, size_price) {
                    //child_sku = randomAmazonSku() + mockup.color_name.replace(' ', '').replace('-', '') + size_name.replace(' ', '').replace('-', '');
                    child_sku = randomAmazonSku() + '_' + mockup.color_name.replace(' ', '').replace('-', '') + '_' + size_name.replace(' ', '').replace('-', '') + '_' + design.id;
                    console.log(child_sku);
                    row = '<tr>';
                    row += '<td class="design_' + design.id + '_mockup_' + mockup.id + '">' + 'shirt' + '<img class="img img-thumbnail" src="' + asset + 'images/loading.gif">' + '</td>';  //feed_product_type
                    row += '<td>' + child_sku + '</td>';   //item_sku
                    row += '<td>' + amazon_brand_name + '</td>';   //brand_name
                    //row += '<td>' + (design.title80 + ' ' + mockup.color_name + ' ' + size_name).substr(0,80) + '</td>'; //item_name
                    row += '<td>' + titleCase(title_arr.join(', ')) + '</td>'; //item_name
                    row += '<td>' + 'Cotton' + '</td>';   //outer_material_type1
                    row += '<td>' + mockup.color_name + '</td>';   //color_name
                    row += '<td>' + mockup.color_map + '</td>';   //color_map
                    row += '<td>' + size_name + '</td>';   //size_name
                    row += '<td>' + size_name + '</td>';   //size_map
                    row += '<td>' + 'cotton.' + '</td>';   //material_composition1
                    row += '<td>' + amazon_department_name + '</td>';   //department_name
                    row += '<td>' + 'Yes' + '</td>';   //is_adult_product
                    row += '<td>' + size_price + '</td>';   //standard_price
                    row += '<td>' + '99' + '</td>';   //quantity
                    row += '<td>' + 'Migrated Template' + '</td>';   //merchant_shipping_group_name
                    row += '<td>' + 'Unisex' + '</td>';   //target_gender
                    row += '<td>' + 'Adult' + '</td>';   //age_range_description
                    row += '<td class="design_' + design.id + '_mockup_' + mockup.id + ' child_main_image_url' + design.id + '_mockup_' + mockup.id + '">' + '<img class="img img-thumbnail" src="' + asset + 'images/loading.gif">' + '</td>';   //main_image_url
                    row += '<td>' + image_url1 + '</td>';   //other_image_url1
                    row += '<td>' + image_url2 + '</td>';   //other_image_url2
                    row += '<td>' + image_url3 + '</td>';   //other_image_url3
                    //row += '<td>' + '' + '</td>';   //other_image_url4
                    row += '<td>' + 'Child' + '</td>';   //parent_child
                    row += '<td>' + parent_sku + '</td>';   //parent_sku
                    row += '<td>' + 'Variation' + '</td>';   //relationship_type
                    row += '<td>' + 'colorsize' + '</td>';   //variation_theme
                    row += '<td>' + 'Update' + '</td>';   //update_delete
                    row += '<td>' + '' + '</td>';    //product_description
                    row += '<td>' + 'US' + '</td>';   //shirt_size_system
                    row += '<td>' + 'Alpha' + '</td>';   //shirt_size_class
                    row += '<td>' + 'Small' + '</td>';   //shirt_size
                    row += '<td>' + 'Regular' + '</td>';   //shirt_body_type
                    row += '<td>' + 'Regular' + '</td>';   //shirt_height_type
                    row += '<td>' + 'fashion-t-shirts' + '</td>';   //item_type
                    //row += '<td>' + amazon_bullet_point1 + ' ' + design.title + ' ' + mockup.color_name + ' ' + size_name + '</td>';   //bullet_point1
                    row += '<td>' + amazon_bullet_point1 + '</td>';   //bullet_point1
                    row += '<td>' + amazon_bullet_point2 + '</td>';   //bullet_point2
                    row += '<td>' + amazon_bullet_point3 + '</td>';   //bullet_point3
                    row += '<td>' + amazon_bullet_point4  + '</td>';   //bullet_point4
                    row += '<td>' + amazon_bullet_point5 + '</td>';   //bullet_point5
                    row += '<td>' + tags.substring(0,249) + '</td>';   //generic_keywords1
                    row += '<td>' + 'Made in USA' + '</td>';   //import_designation
                    row += '<td>' + 'New' + '</td>';   //condition_type
                    row += '<td>' + '5' + '</td>';   //fulfillment_latency
                    row += '<td>' + dateToString() + '</td>';   //product_site_launch_date
                    row += '</tr>';

                    tableContent += row;
                });
            });
        });

        tableContent += '</tbody></table></div>';
        $('#amazon_data').html(tableContent);

        makeAmazonMockups();

        //exportTableToExcel("tblData", collection.title + "-amazon-" + nowToString() + ".xls");
    }

    function doExportShopify() {
        console.log('export mockup');

        var url =  "http://"+window.location.hostname;
        var elements = [];

        $("input[name*=design_ids]:checked").each(function(event, obj){
            var id = $(obj).attr('design_id');
            var design = designs[id];
            // 2021-07: move to S3
            // console.log('>> design: '+url+'/public/storage/'+design.filename);
            console.log('>> design: ' + design.file_url);
            // 2021-07: move to S3
            // elements[titleCase(design.title80.replace(/"/g, '""')+'_'+design.id)] = url+'/public/storage/'+design.filename;
            elements[titleCase(design.title80.replace(/"/g, '""')+'_'+design.id)] = design.file_url;
        });
        downloadAll(elements);
    }

    function pause(msec) {
        return new Promise(
            (resolve, reject) => {
                setTimeout(resolve, msec || 1000);
            }
        );
    }

    async function downloadAll(elements) {
        var count = 0;
        for (var e in elements) {
            // download(elements[e]); // your custom download code here, click or whatever
            var link = document.createElement("a");
            link.href = elements[e];
            link.download = e; // file name
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            delete link;

            if (++count >= 10) {
                await pause(1000);
                count = 0;
            }
        }
    }
    //platform = etsy, bonanza
    function downloadFile(platform, data, fileName, fileType='csv') {

        var dataType;
        switch (fileType) {
            case 'xls':
                dataType = "application/vnd.ms-excel";
                break;

            default: // 'csv'
                dataType = "application/csv;charset=utf-8;";
                break;
        }
        var blob = new Blob([data], {
                    type : dataType
                });

        if (window.navigator.msSaveBlob) {
            // FOR IE BROWSER
            navigator.msSaveBlob(blob, fileName);
        } else {
            // FOR OTHER BROWSERS
            var link = document.createElement("a");
            var csvUrl = URL.createObjectURL(blob);
            link.href = csvUrl;

            //console.log(link.href);

            link.style = "visibility:hidden";
            link.download = fileName;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            uploadFile(platform, blob, fileName);
        }
    }

    function exportTableToExcel(tableID, filename = ''){
        // Specify file name
        filename = filename ? filename : (collection.title + "-amazon-" + nowToString() + ".xls");

        var tab_text = "<table><tr>";
        var textRange; var j = 0;
        tab = document.getElementById(tableID);
        if (tab==null) {
            return false;
        }
        if (tab.rows.length == 0) {
            return false;
        }

        for (j = 0 ; j < tab.rows.length ; j++) {
            tab_text = tab_text + tab.rows[j].innerHTML + "</tr>";
        }

        tab_text = tab_text + "</table>";
        tab_text = tab_text.replace(/<a[^>]*>|<\/a>/g, "");//remove if u want links in your table
        tab_text = tab_text.replace(/<img[^>]*>/gi, ""); // remove if u want images in your table
        tab_text = tab_text.replace(/<input[^>]*>|<\/input>/gi, ""); // reomves input params

        var ua = window.navigator.userAgent;
        var msie = ua.indexOf("MSIE ");

        if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./))      // If Internet Explorer
        {
            txtArea1.document.open("txt/html", "replace");
            txtArea1.document.write(tab_text);
            txtArea1.document.close();
            txtArea1.focus();
            sa = txtArea1.document.execCommand("SaveAs", true, filename);
        }
        else                 //other browser not tested on IE 11
            //sa = window.open('data:application/vnd.ms-excel,' + encodeURIComponent(tab_text));
            try {
                var blob = new Blob(['\ufeff',tab_text], { type: "application/vnd.ms-excel" });
                window.URL = window.URL || window.webkitURL;
                link = window.URL.createObjectURL(blob);
                a = document.createElement("a");
                a.download = filename;
                a.href = link;
                document.body.appendChild(a);
                a.click();

                document.body.removeChild(a);

                uploadFile('amazon', blob, filename);
            } catch (e) {
            }

        return true;
        //return (sa);
    }

    function nowToString() {
        var today = new Date();
        return today.getFullYear().toString().substr(2,2)
            + ((today.getMonth()<10) ? '0' : '') + (today.getMonth()+1)
            + ((today.getDate()<10) ? '0' : '') + today.getDate()
            + '-'
            + ((today.getHours()<10) ? '0' : '') + today.getHours()
            + ((today.getMinutes()<10) ? '0' : '') + today.getMinutes()
            + ((today.getSeconds()<10) ? '0' : '') + today.getSeconds();
    }

    function nowToStringShort() {
        var today = new Date();
        return today.getFullYear().toString().substr(2,2)
            + ((today.getMonth()<10) ? '0' : '') + (today.getMonth()+1)
            + ((today.getDate()<10) ? '0' : '') + today.getDate();
    }

    function dateToString() {
        var today = new Date();
        return (today.getMonth()+1)
            + '/' + today.getDate()
            + '/' + today.getFullYear();
    }

    function etsy_variations(csv_escape = false) {
        var styles=['Unisex T-Shirt','Unisex V-Neck','Unisex Tank','Long Sleeve','Hoodie','Kids Tee'];
        var colors=['Black','White','Dark Heather','Navy','Royal Blue','Deep Red','Purple','Forest Green','-Another Color-'];
        var sizes=['S','M','L','XL','2XL','3XL','4XL'];
        var allPrices={
            "Unisex T-Shirt" : [17.99 , 18.99, 19.99, 21.99, 23.99, 25.99, 27.99],
            "Unisex V-Neck" : [19.99 , 20.99, 21.99, 23.99, 25.99, 27.99, 29.99],
            "Unisex Tank" : [19.99 , 20.99, 21.99, 23.99, 25.99, 27.99, 29.99],
            "Long Sleeve" : [27.99 , 28.99, 29.99, 31.99, 33.99, 35.99, 37.99],
            "Hoodie" : [35.99 , 36.99, 37.99, 39.99, 41.99, 43.99, 45.99],
            "Kids Tee" : [17.99 , 18.99, 19.99, 21.99, 23.99, 25.99, 27.99],
        };

        // var allPrices={
        //     "Unisex T-Shirt" : [22.99 , 23.99, 24.99, 26.99, 28.99, 30.99, 32.99],
        //     "Unisex V-Neck" : [24.99 , 25.99, 26.99, 28.99, 30.99, 32.99, 34.99],
        //     "Unisex Tank" : [24.99 , 25.99, 26.99, 28.99, 30.99, 32.99, 34.99],
        //     "Long Sleeve" : [32.99 , 33.99, 34.99, 36.99, 38.99, 40.99, 42.99],
        //     "Hoodie" : [40.99 , 41.99, 42.99, 44.99, 46.99, 48.99, 50.99],
        //     "Kids Tee" : [22.99 , 23.99, 24.99, 26.99, 28.99, 30.99, 32.99],
        // };

        var v='[';
        styles.forEach(function(style, istyle) {
            colors.forEach(function(color, icolor) {
                sizes.forEach(function(size, isize) {
                    prices = allPrices[style];
                    v+='{"Style & Size":"' + style + ' - ' + size + '",';
                    v+='"price":"'+prices[isize] + '",';
                    v+='"Color":"'+color + '",';
                    v+='"quantity":999},';
                });
            });
        });
        v = v.substring(0,v.length-1) + ']';
        if (csv_escape === true) {
            return '"' + v.replace(/"/g, '""') + '"';
        } else {
            return v;
        }
    }

    function bonanza_trait(csv_escape = false) {
        //var styles=['Unisex T-Shirt','Unisex V-Neck','Unisex Tank','Long Sleeve','Hoodie','Kids Tee'];
        var styles=['Unisex T-Shirt'];
        var colors=['Black','White','Dark Heather','Navy','Royal Blue','Charcoal','Purple','Forest Green','Sport Grey','Maroon'];
        var sizes=['S','M','L','XL','2XL','3XL'];
        var allPrices={
            "Unisex T-Shirt" : [17.99 , 18.99, 19.99, 21.99, 23.99, 25.99],
            // "Unisex V-Neck" : [19.99 , 20.99, 21.99, 23.99, 25.99, 27.99],
            // "Unisex Tank" : [19.99 , 20.99, 21.99, 23.99, 25.99, 27.99, 29.99],
            // "Long Sleeve" : [27.99 , 28.99, 29.99, 31.99, 33.99, 35.99],
            // "Hoodie" : [35.99 , 36.99, 37.99, 39.99, 41.99, 43.99],
            // "Kids Tee" : [17.99 , 18.99, 19.99, 21.99, 23.99, 25.99, 27.99],
        };

        var v='';
        styles.forEach(function(style, istyle) {
            colors.forEach(function(color, icolor) {
                sizes.forEach(function(size, isize) {
                    prices = allPrices[style];
                    v+='[[style:' + style + ' - ' + size + ']';
                    v+='[price:'+prices[isize] + ']';
                    v+='[color:'+color + ']';
                    v+='[quantity:999]]';
                });
            });
        });
        v += '[[condition: new]]';
        if (csv_escape === true) {
            return '"' + v.replace(/"/g, '""') + '"';
        } else {
            return v;
        }
    }

    //upload file to CollectionExport, platform = etsy, bonanza, amazon
    function uploadFile(platform, blob, filename) {
        var formData = "";

        formData = new FormData();
        formData.append("fileToUpload", blob, filename);
        formData.append("collection_id", collection.id);
        formData.append("type", platform);

        $.ajax({
            url: "{{ url('/collection/addCollectionExport') }}",
            type: 'POST',
            data: formData,
            //async: false,
            cache: false,
            contentType: false,
            enctype: 'multipart/form-data',
            processData: false,
            success: function (response) {
                console.log(response);

                if (response.success == 1)
                {

                } else {
                    showAlert('Something wrong!');
                }
            }
        });

    }

    function randomAmazonSku() {
        var prefix = $('#amazon_sku_prefix').val();

        var today = new Date();
        var time = today.getFullYear().toString().substr(2,2)
            + ((today.getMonth()<10) ? '0' : '') + (today.getMonth()+1)
            + ((today.getDate()<10) ? '0' : '') + today.getDate();

        var sku = prefix + time + randomString(5);
        console.log(sku);
        return sku;
    }

    function randomString(len, charSet) {
        //charSet = charSet || 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        charSet = charSet || 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        var randomString = '';
        for (var i = 0; i < len; i++) {
            var randomPoz = Math.floor(Math.random() * charSet.length);
            randomString += charSet.substring(randomPoz,randomPoz+1);
        }
        return randomString;
    }

    window.updateCount = function() {
        var x = $(".z:checked").length;
        document.getElementById("y").innerHTML = x;
    };
</script>

<style>
.table-responsive{
    width:100%; height: 500px;
    overflow: scroll;
    font-size: 80%;
}
.table-responsive td {
    height: 30px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.table-responsive .img {
    width: 30px;
    height: 30px;
}
.color_maps_label {
    width: 28px;
    padding: 3px;
}
.color_selected {
    box-shadow: 0 0 0 1px #E86F68;
    border: 1px solid #F2F0E3;
}
.color_unselected {
    box-shadow: 0 0 0 1px #F2F0E3;
    border: 1px solid #F2F0E3;
}
</style>

<div class="container">
    <div class="row">
        <div class="col-sm-12 btn-toolbar justify-content-between" role="toolbar">
            <h4>export collection: <strong>{{ $data['collection']->title }}</strong></h4>
            <div class="justify-content-end">
                <div class="btn-group btn-group-sm ml-2 float-left" role="group">
                    <a class="btn btn-link" href="{{ url('/collection/'.$data['collection']->id.'/edit') }}" role="button" target="_blank"><i class="far fa-edit"></i> edit this collection</a>
                </div>
            </div>
        </div>
    </div>

    <nav class="my-3">
        <div class="nav nav-tabs" id="nav-tab" role="tablist">
            <a class="nav-item nav-link active" id="nav-list-tab" data-toggle="tab" href="#nav-list" role="tab" aria-controls="nav-list" aria-selected="true">1. designs to export</a>
            <a class="nav-item nav-link" id="nav-general-tab" data-toggle="tab" href="#nav-general" role="tab" aria-controls="nav-general" aria-selected="false">2. general information</a>
            @if (Auth::user()->isAdmin()) <!-- admin only -->
            <a class="nav-item nav-link" id="nav-etsy-tab" data-toggle="tab" href="#nav-etsy" role="tab" aria-controls="nav-etsy" aria-selected="false" style="color:#F45800">3. etsy export</a>
            <a class="nav-item nav-link" id="nav-bonanza-tab" data-toggle="tab" href="#nav-bonanza" role="tab" aria-controls="nav-bonanza" aria-selected="false" style="color:#62764A">4. bonanza export</a>
            @endif
            @if (Auth::user()->isAdmin() || in_array(Auth::user()->id, [13,15,16,18,25,26])) <!-- admin + sinh + suong + oanh + my + uyen-->
            <a class="nav-item nav-link" id="nav-amazon-tab" data-toggle="tab" href="#nav-amazon" role="tab" aria-controls="nav-amazon" aria-selected="false" style="color:#FF9900">5. amazon export</a>
            @endif
            @if (Auth::user()->isAdmin()) <!-- admin only -->
            <a class="nav-item nav-link" id="nav-shopify-tab" data-toggle="tab" href="#nav-shopify" role="tab" aria-controls="nav-shopify" aria-selected="false" style="color:#4C822A">6. shopify export</a>
            <a class="nav-item nav-link" id="nav-raw-tab" data-toggle="tab" href="#nav-raw" role="tab" aria-controls="nav-raw" aria-selected="false">7. raw CSV export</a>
            @endif
        </div>
    </nav>
    <div class="tab-content" id="nav-tabContent">
        <div class="tab-pane fade show active" id="nav-list" role="tabpanel" aria-labelledby="nav-list-tab">
            <div class="col-12 d-flex justify-content-between m-0 p-0">
                <h4>select designs and make mockups <span id="y" class="badge badge-danger">0</span></h4>
                <div class="btn-group" role="group">
                    <a href="javascript:makeMockups();" class="btn btn-primary" role="button">make mockups</a>
                    <a href="javascript:$('#nav-general-tab').tab('show');" class="btn btn-primary" role="button">next <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            <div id="listDesigns" class="col-12 mt-3 p-0">
                        <table class="table table-sm table-hover" id="design_table">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col"><input type="checkbox" id="design_check_all"></th>
                                    <th scope="col"></th>
                                    <th scope="col">thumbnail</th>
                                    <th scope="col">details</th>
                                </tr>
                            </thead>
                            <tbody>

                                @foreach ($data['designs'] as $d)
                                <tr class="design_row" id="design_row{{$d->id}}">
                                    <th scope="row" class="align-middle">
                                        <input type="checkbox" name="design_ids[]" design_id="{{$d->id}}" id="checkbox{{$d->id}}">
                                    </th>
                                    <th scope="row" class="align-middle text-center">
                                        <a href="javascript:loadMockup($('#checkbox{{$d->id}}'))" class="remockup">
                                            <i class="fas fa-redo-alt"></i>mockup
                                        </a>
                                        <label class="form-check-label align-middle d-none" id="design_loading">
                                            <img style="width:25px" src="{{asset('images/loading.gif')}}" alt='adding...'>
                                        </label>
                                    </th>
                                    <td class="align-middle design_thumbnail">
                                        <!-- 2021-07: move to S3 -->
{{--                                        <img id="mockup{{ $d->id }}" style="width:125px" class="img img-thumbnail" src="{{asset('storage/' . $d->thumbnail)}}">--}}
                                        <img id="mockup{{ $d->id }}" style="width:125px" class="img img-thumbnail" src="{{ $d->thumbnail_url }}">
                                    </td>
                                    <td class="align-middle design_details">
                                        <span class="design_title">{{ $d->title }}</span><br />
                                        <small class="design_title text-secondary">{{ $d->title80 }}</small><br />
                                        <small class="design_tags text-muted">{{ $d->tags }}</small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
        </div>
        <div class="tab-pane fade" id="nav-general" role="tabpanel" aria-labelledby="nav-general-tab">
            <div class="col-12 d-flex justify-content-between m-0 p-0">
                <h4>general information (for each listing)</h4>
                <div class="btn-group" role="group">
                    <a href="javascript:$('#nav-list-tab').tab('show');" class="btn btn-primary" role="button"><i class="fas fa-arrow-left"></i> back</a>
                    <a href="javascript:$('#nav-etsy-tab').tab('show');" class="btn btn-primary" role="button">next <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>

            <div class="col-12 m-0 p-0 mt-3">
                <form id="general_form">
                    <div class="form-group row">
                        <label class="col-12 col-form-label"><strong>images</strong></label>
                        <div class="col-sm-12">
                            <div class="row">
                                <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-3 h-100" style="font-size: 75%;">
                                    <div class="card">
                                        <img class="card-img-top img-thumbnail" src="{{ $data['collection']->image_url_1 }}" alt="image_url_1">
                                    </div>
                                    <div class="m-0 p-0 text-muted">
                                    image_url_1
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-3 h-100" style="font-size: 75%;">
                                    <div class="card">
                                        <img class="card-img-top img-thumbnail" src="{{ $data['collection']->image_url_2 }}" alt="image_url_2">
                                    </div>
                                    <div class="m-0 p-0 text-muted">
                                    image_url_2
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-3 h-100" style="font-size: 75%;">
                                    <div class="card">
                                        <img class="card-img-top img-thumbnail" src="{{ $data['collection']->image_url_3 }}" alt="image_url_3">
                                    </div>
                                    <div class="m-0 p-0 text-muted">
                                    image_url_3
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-12 col-form-label"><strong>keywords</strong></label>
                        <div class="col-12">
                        {{ $data['collection']->tags }}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-12 col-form-label"><strong>description</strong></label>
                        <div class="col-12" style="white-space:pre-wrap; word-wrap:break-word;">
                        {{ $data['collection']->description }}
                        </div>
                    </div>
                </form>
            </div>

        </div>
        <div class="tab-pane fade" id="nav-etsy" role="tabpanel" aria-labelledby="nav-etsy-tab">
            <div class="col-12 d-flex justify-content-between m-0 p-0">
                <h4>export csv for etsy</h4>
                <div class="btn-group" role="group">
                    <a href="javascript:$('#nav-general-tab').tab('show');" class="btn btn-primary" role="button"><i class="fas fa-arrow-left"></i> back</a>
                    <a href="javascript:doExportEtsy();" class="btn btn-primary" role="button"><i class="fas fa-file-csv"></i> export csv for etsy</a>
                </div>
            </div>
            <div class="col-12 m-0 p-0 mt-3">
                <form id="etsy_form">
                    <div class="form-group row">
                        <label for="shipping_template_id" class="col-sm-2 col-form-label">shipping template id</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="shipping_template_id" name="shipping_template_id" placeholder="you have to create at least 1 shipping template on Etsy account first and put it here (numeric format)" value="75748862063">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="variations" class="col-sm-2 col-form-label">variations <small class="text-muted"></small></label>
                        <div class="col-sm-10">
                            <textarea id="variations" name="variations" class="form-control" rows="10" style="white-space: pre-wrap;"></textarea>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="tab-pane fade" id="nav-bonanza" role="tabpanel" aria-labelledby="nav-bonanza-tab">
            <div class="col-12 d-flex justify-content-between m-0 p-0">
                <h4>export csv for bonanza</h4>
                <div class="btn-group" role="group">
                    <a href="javascript:doExportBonanza();" class="btn btn-primary" role="button"><i class="fas fa-file-csv"></i> export csv for bonanza</a>
                </div>
            </div>
            <div class="col-12 m-0 p-0 mt-3">
                <form id="bonanza_form">
                    <div class="form-group row">
                        <label for="bonanza_category" class="col-sm-2 col-form-label">category</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" id="bonanza_category" name="bonanza_category" value="155193" required>
                            <small id="bonanza_category_help" class="form-text text-muted">
                                <a href="https://www.bonanza.com/site_help/booths_setup/complete_category_list?layout=&title=Complete+List+of+Categories" target="_blank">find list of categories here</a>
                            </small>
                        </div>

                        <label for="bonanza_price" class="col-sm-2 col-form-label">price</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" id="bonanza_price" name="bonanza_price" value="17.99" required>
                            <small id="bonanza_price_help" class="form-text text-muted">
                                sale price
                            </small>
                        </div>

                        <label for="bonanza_shipping_price" class="col-sm-2 col-form-label mt-3">US shipping price</label>
                        <div class="col-sm-4 mt-3">
                            <input type="text" class="form-control" id="bonanza_shipping_price" name="bonanza_shipping_price" value="5.99" required>
                            <small id="bonanza_shipping_price_help" class="form-text text-muted">
                                flat shipping price in United State
                            </small>
                        </div>

                        <label for="bonanza_worldwide_shipping_price" class="col-sm-2 col-form-label mt-3">worldwide shipping price</label>
                        <div class="col-sm-4 mt-3">
                            <input type="text" class="form-control" id="bonanza_worldwide_shipping_price" name="bonanza_worldwide_shipping_price" value="9.99" required>
                            <small id="bonanza_worldwide_shipping_price_help" class="form-text text-muted">
                                flat shipping price for international
                            </small>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="bonanza_trait" class="col-sm-2 col-form-label">bonanza_trait <small class="text-muted"></small></label>
                        <div class="col-sm-10">
                            <textarea id="bonanza_trait" name="bonanza_trait" class="form-control" rows="10" style="white-space: pre-wrap;"></textarea>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="tab-pane fade" id="nav-amazon" role="tabpanel" aria-labelledby="nav-amazon-tab">
            <div class="col-12 d-flex justify-content-between m-0 p-0">
                <h4>export csv for amazon</h4>
                <div class="btn-group" role="group">
                    <a href="javascript:doExportAmazon();" class="btn btn-primary mr-1" role="button"><i class="fas fa-file-csv"></i> export csv for amazon generic</a>
                    <a href="javascript:doExportAmazon2();" class="btn btn-primary" role="button"><i class="fas fa-file-csv"></i> export csv for amazon</a>
                </div>
            </div>
            <div class="col-12 m-0 p-0 mt-3">
                <form id="amazon_form">
                    <div class="form-group row">
                        <label for="amazon_sku_prefix" class="col-sm-2 col-form-label mt-2">sku prefix</label>
                        <div class="col-sm-2 mt-2">
                            <input type="text" class="form-control form-control-sm" id="amazon_sku_prefix" name="amazon_sku_prefix" value="{{ $data['collection']->uid }}" required>
                        </div>

                        <label for="amazon_brand_name" class="col-sm-2 col-form-label mt-2">brand name</label>
                        <div class="col-sm-2 mt-2">
                            <input type="text" class="form-control form-control-sm" id="amazon_brand_name" name="amazon_brand_name" value="{{ $data['collection']->brand_name }}" required>
                        </div>

                        <label for="amazon_department_name" class="col-sm-2 col-form-label mt-2">department name</label>
                        <div class="col-sm-2 mt-2">
                            <select id="amazon_department_name" name="amazon_department_name" class="btn btn-sm form-control form-control-sm border bg-light">
                                <?php $department_name = ["baby-boys","baby-girls","boys","girls","mens","unisex-adult","unisex-baby","womens"]; ?>
                                @foreach ($department_name as $dn)
                                <option value="{{ $dn }}" {{ ($dn == 'unisex-adult') ? 'selected' : '' }}>{{ $dn }}</option>
                                @endforeach
                            </select>
                        </div>

                        <label for="amazon_dark_colors" class="col-sm-2 col-form-label mt-2">dark colors</label>
                        <div class="col-sm-10 mt-2 btn-group-toggle" data-toggle="buttons">
                            @foreach ($data['mockups'] as $m)
                            @if ($m->color == 'dark')
                            <label class="btn btn-sm text-white mr-1 color_maps_label text-center" style="background-color:{{ $m->color_code }};" data-toggle="tooltip" data-placement="top" title="{{ $m->color_name }}">
                                <!-- <input type="checkbox" name="amazon_dark_colors[]" id="amazon_dark_colors{{$m->id}}" autocomplete="off" class="form-control form-control-sm color_maps color_dark_maps mx-auto" value="{{$m->id}}" {{ (in_array($m->color_name,['Black', 'Dark Heather', 'Royal Blue', 'Purple', 'Sport Grey'])) ? 'checked' : '' }}>&nbsp;<i class="fas fa-check d-none"></i>&nbsp; -->
                                <input type="checkbox" name="amazon_dark_colors[]" id="amazon_dark_colors{{$m->id}}" autocomplete="off" class="form-control form-control-sm color_maps color_dark_maps mx-auto" value="{{$m->id}}" checked>&nbsp;<i class="fas fa-check d-none"></i>&nbsp;
                            </label>
                            @endif
                            @endforeach
                        </div>

                        <label for="amazon_light_colors" class="col-sm-2 col-form-label mt-2">light colors</label>
                        <div class="col-sm-10 mt-2 btn-group-toggle" data-toggle="buttons">
                            @foreach ($data['mockups'] as $m)
                            @if ($m->color == 'light')
                            <label class="btn btn-sm mr-1 color_maps_label text-center" style="background-color:{{ $m->color_code }};" data-toggle="tooltip" data-placement="top" title="{{ $m->color_name }}">
                                <!-- <input type="checkbox" name="amazon_light_colors[]" id="amazon_light_colors{{$m->id}}" autocomplete="off" class="form-control form-control-sm color_maps color_light_maps mx-auto" value="{{$m->id}}" {{ (in_array($m->color_name,['White', 'Light Pink', 'Light Blue', 'Yellow', 'Sport Grey'])) ? 'checked' : '' }}>&nbsp;<i class="fas fa-check d-none"></i>&nbsp; -->
                                <input type="checkbox" name="amazon_light_colors[]" id="amazon_light_colors{{$m->id}}" autocomplete="off" class="form-control form-control-sm color_maps color_light_maps mx-auto" value="{{$m->id}}" checked>&nbsp;<i class="fas fa-check d-none"></i>&nbsp;
                            </label>
                            @endif
                            @endforeach
                        </div>

                        <label for="amazon_size_maps" class="col-sm-2 col-form-label mt-2">size maps</label>
                        <div class="col-sm-10 mt-2">
                            <input type="text" class="form-control form-control-sm" id="amazon_size_maps" name="amazon_size_maps" value='{"Small":"17.99","Medium":"18.99","Large":"19.99","X-Large":"21.99","2X-Large":"23.99","3X-Large":"25.99","4X-Large":"27.99"}'>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="amazon_bullet_point1" class="col-sm-2 col-form-label">amazon_bullet_point1 <small class="text-muted"></small></label>
                        <div class="col-sm-10">
                            <textarea id="amazon_bullet_point1" name="amazon_bullet_point1" class="form-control form-control-sm" rows="3" style="white-space: pre-wrap;">CUSTOM AND PERSONALIZED ORDERS: * You can request a custom change on any of our products. * You can give us your idea, and we will make a new unique design for you. * We can print on items besides t-shirts such as long sleeves, sweatshirt, hoodies, tank tops, v-neck,...</textarea>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="amazon_bullet_point2" class="col-sm-2 col-form-label">amazon_bullet_point2 <small class="text-muted"></small></label>
                        <div class="col-sm-10">
                            <textarea id="amazon_bullet_point2" name="amazon_bullet_point2" class="form-control form-control-sm" rows="3" style="white-space: pre-wrap;">MATERIALS: * 100% Cotton (fiber content may vary for different colors) * Medium fabric (5.3 oz/yd² (180 g/m²)) * Classic fit * Tear away label * Runs true to size.</textarea>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="amazon_bullet_point3" class="col-sm-2 col-form-label">amazon_bullet_point3 <small class="text-muted"></small></label>
                        <div class="col-sm-10">
                            <textarea id="amazon_bullet_point3" name="amazon_bullet_point3" class="form-control form-control-sm" rows="3" style="white-space: pre-wrap;">CARE INSTRUCTIONS: * Machine wash cold with like colors, dry low heat. * Machine wash: warm (max 40C or 105F); * Non-chlorine: bleach as needed; * Tumble dry: medium; * Do not iron; * Do not dryclean..</textarea>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="amazon_bullet_point4" class="col-sm-2 col-form-label">amazon_bullet_point4 <small class="text-muted"></small></label>
                        <div class="col-sm-10">
                            <textarea id="amazon_bullet_point4" name="amazon_bullet_point4" class="form-control form-control-sm" rows="3" style="white-space: pre-wrap;">HOW TO ORDER: * Select your size and color from the drop-down menus on the right, then click the ""Add to Cart"" button. * If you are unsure of sizing, order a larger one to ensure a good fit. </textarea>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="amazon_bullet_point5" class="col-sm-2 col-form-label">amazon_bullet_point5 <small class="text-muted"></small></label>
                        <div class="col-sm-10">
                            <textarea id="amazon_bullet_point5" name="amazon_bullet_point5" class="form-control form-control-sm" rows="3" style="white-space: pre-wrap;">Feel free to contact us with any questions, we will be happy to help.</textarea>
                        </div>
                    </div>
                    <div class="progress w-100 my-2">
                        <div id="progress" class="progress-bar bg-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                            <span id="current-progress"></span>
                        </div>
                    </div>
                    <div id="amazon_data"></div>
                    <div class="form-group row mt-2">
                        <div class="col-sm-12">
                            <a id="amazon_export_csv" href="javascript:exportTableToExcel('tblData');" class="btn btn-success float-right" disabled role="button"><i class="far fa-file-excel"></i> download here</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="tab-pane fade" id="nav-shopify" role="tabpanel" aria-labelledby="nav-shopify-tab">
            <div class="col-12 d-flex justify-content-between m-0 p-0">
                <h4>export csv for shopify</h4>
                <div class="btn-group" role="group">
                    <a href="javascript:doExportShopify();" class="btn btn-primary" role="button"><i class="fas fa-file-csv"></i> export csv for shopify</a>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="nav-raw" role="tabpanel" aria-labelledby="nav-raw-tab">
            <div class="col-12 d-flex justify-content-between m-0 p-0">
                <h4>export raw csv</h4>
                <div class="btn-group" role="group">
                    <a href="javascript:doExportRaw();" class="btn btn-primary" role="button"><i class="fas fa-file-csv"></i> export raw csv</a>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection
