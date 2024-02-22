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
                Special Offers
                <br />
                <br />
                <div class='d-flex'>
                    <a href="{{ asset('downloadable/NACO_CATALOG_YOUNG_LAWYERS_FORUM.pdf') }}" class="btn btn-danger" role="button" download style="font-size:12px !important;">Download NACO Catalog For YOUNG LAWYERS FORUM</a>
                </div>
            </div>
                <div class="list-item">
                            <div class="row">
   
                                     


<!--start-->
<?php
 
// From URL to get webpage contents.
$url = "https://search.nacobooking.com/special.php";


$cURLConnection = curl_init();

curl_setopt($cURLConnection, CURLOPT_URL, $url);
curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
curl_setopt($cURLConnection, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($cURLConnection);
curl_close($cURLConnection);
 
 


echo $response;
 
?>

 
		 
						
<!-- start-->
<!--end-->
             </div>
        </div>
    </div>
</div>
@endsection
