@extends('layouts.app')

@section('content')
<div class="container">
    <div class="bravo-list-hotel layout_normal">
                <div class="title">
            <?php if(!empty($_GET["q"])){
                echo htmlspecialchars($_GET["q"]);
            } ?>
        </div>
                            <div class="sub-title">
                Search Result
            </div>
                <div class="list-item">
                            <div class="row">
   
                                    


<!--start-->

<?php
if(!empty($_GET["q"])){
    
$q =  urlencode($_GET["q"]);
 

 
// From URL to get webpage contents.
$url = "https://search.nacobooking.com/search.php?naco_search_q=$q";


$cURLConnection = curl_init();

curl_setopt($cURLConnection, CURLOPT_URL, $url);
curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
curl_setopt($cURLConnection, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($cURLConnection);
curl_close($cURLConnection);
 
 


echo $response;


}else{
    
}
?>
		 
						
<!-- start-->
<!--end-->
             </div>
        </div>
    </div>
</div>
@endsection
