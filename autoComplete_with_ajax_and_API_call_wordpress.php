<!-- Code for AutoComplete with API calll and wordpress Ajax-->
<!-- call API, data fetch and assign to source of auto complete-->
<!-- based on response display bussiness name and list ABN number for other fields-->


<!-- Step : 1 => Add input conteol for autoComplete -->
<input type="text" name="First Name" id="control_COLUMN136" label="First Name" class="textInput defaultText" style="margin: 0 3px 5px 3px; height: 20px; " placeholder="First Name">
<input type="hidden" name="cvals" id="cvalss" />



<!-- Step : 2 => enquee neccesary css and js for autocomplete and localize script for ajax -->
<?php 
function ja_global_enqueues() {

    wp_enqueue_style(
        'jquery-auto-complete',
        'https://cdnjs.cloudflare.com/ajax/libs/jquery-autocomplete/1.0.7/jquery.auto-complete.css',
        array(),
        '1.0.7'
    );

    wp_enqueue_script(
        'jquery-auto-complete',
        'https://cdnjs.cloudflare.com/ajax/libs/jquery-autocomplete/1.0.7/jquery.auto-complete.min.js',
        array( 'jquery' ),
        '1.0.7',
        true
    );

    wp_localize_script(
        'global',
        'global',
        array(
            'ajax' => admin_url( 'admin-ajax.php' ),
        )
    );
    ?>
      <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
      <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
      <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <?php
}
add_action( 'wp_enqueue_scripts', 'ja_global_enqueues' );

?>




<!-- Step : 3 => Wordpress ajax action function, call API and fetch response, push nessesary data on array and return response to ajax success  -->
<?php 
function ja_ajax_search() {

	$term = stripslashes( $_POST['search'] ); // put the channel id here
	//using curl
	$url = 'http://abr.business.gov.au/abrxmlsearchRPC/AbrXmlSearch.asmx/ABRSearchByNameSimpleProtocol?name='.$term.'&authenticationGuid=2db0504f-2530-48b8-a286-81766eaefb8d&postcode=&legalName=&tradingName=&NSW=&SA=&ACT=&VIC=&WA=&NT=&QLD=&TAS=';
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	$response  = curl_exec($ch);
	curl_close($ch);
	$response=simplexml_load_string($response);
	$json = json_encode($response);
	$youtube= json_decode($json, true);
	$count = 0;
	if(isset($youtube['response']))
	{
	    $items = array();
	    $count = 0;
	    $arr = $youtube['response']['searchResultsList']['searchResultsRecord'];
	    foreach ($arr as $k => $v) {
            if (array_key_exists('mainName', $v)) {
                $organization_name = $v['mainName']['organisationName'];
            }
            if (array_key_exists('mainTradingName', $v)) {
                $organization_name = $v['mainTradingName']['organisationName'];
            }
            if (array_key_exists('businessName', $v)) {
                $organization_name = $v['businessName']['organisationName'];
            }
            if (array_key_exists('otherTradingName', $arr)) {
                $organization_name = $v['otherTradingName']['organisationName'];
            }
            if (array_key_exists('ABN', $v)) {
                $abn_num = $v['ABN']['identifierValue'];
            }

	        if($v['ABN']['identifierStatus'] == 'Active'){
	            $items[] = $organization_name.'#'.$abn_num;        
	        }
	    }
	}
    wp_send_json_success( $items );
}
add_action( 'wp_ajax_search_site',        'ja_ajax_search' );
add_action( 'wp_ajax_nopriv_search_site', 'ja_ajax_search' );
?>




<!-- Step : 4 => call autoComplete event, use wordpress ajax, get response of api through wordpress ajax-->
<?php 
// add the ajax fetch js
add_action( 'wp_footer', 'ajax_fetch' );
function ajax_fetch() {
?>
<script type="text/javascript">

jQuery(document).ready(function($) {   
    $('#control_COLUMN104').attr('readonly', true);

    // call wordpress ajax and fetch response, onSelect method display ABN number based on API data response
    var searchRequest;
    $('.search-autocomplete').autoComplete({
        minChars: 1,
        source: function(name, response) {
            try { searchRequest.abort(); $('.search-autocomplete').addClass('loading-icon'); } catch(e){}
            searchRequest = $.ajax({
                type: 'POST',
                dataType: 'json',
                url: '/wp-admin/admin-ajax.php',
                data: 'action=search_site&search='+name,
                success: function(data) {
                    var objdata = [];
                    $('#cvalss').val(data.data);
                    $.each(data.data, function(i,val) {
                        objdata.push(val.split('#')[0]);
                    });
                    response(objdata);
                    $('.search-autocomplete').removeClass('loading-icon'); 
                }
            });
        },
        onSelect: function(e, term, item){
            $('.search-autocomplete').removeClass('loading-icon'); 
            console.log( "Handler for .blur() called." );
            var txtvaloforg = jQuery('input#control_COLUMN121').val();
            var maindata = jQuery('#cvalss').val();
            var s_main_array = maindata.split(',');
            var orgname = [];
            var abnnum = [];
            $.each(s_main_array, function(i,val) {
                orgname.push(val.split('#')[0]);
                abnnum.push(val.split('#')[1]);
            });
            $.each(orgname, function(i,val) {
                if(val == txtvaloforg){
                    // $('.search-autocomplete2').removeClass('loading-icon'); 
                    $('#control_COLUMN104').val(abnnum[i]);
                }
            });
        },
    });

    $('.search-autocomplete').on('input',function(e){
        console.log('changes made...');
        $('#control_COLUMN104').val('');
        $('#control_COLUMN104').attr("placeholder", "ABN(Will show you based on chosen Business Name)");
    });

});
</script>
<?php
}
?>