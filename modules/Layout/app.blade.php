<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{$html_class ?? ''}}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="google-site-verification" content="RoqwAvYjcUbjJ-YtidjWyg6vyUG-RxXgkALoY2Qy27E" />
    @php event(new \Modules\Layout\Events\LayoutBeginHead()); @endphp
    @php
        $favicon = setting_item('site_favicon');
    @endphp
    @if($favicon)
        @php
            $file = (new \Modules\Media\Models\MediaFile())->findById($favicon);
        @endphp
        @if(!empty($file))
            <link rel="icon" type="{{$file['file_type']}}" href="{{asset('uploads/'.$file['file_path'])}}" />
        @else:
            <link rel="icon" type="image/png" href="{{url('images/favicon.png')}}" />
        @endif
    @endif

    @include('Layout::parts.seo-meta')
    <link href="{{ asset('libs/bootstrap/css/bootstrap.css') }}" rel="stylesheet">
    <link href="{{ asset('libs/font-awesome/css/font-awesome.css') }}" rel="stylesheet">
    <link href="{{ asset('libs/ionicons/css/ionicons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('libs/icofont/icofont.min.css') }}" rel="stylesheet">
    <link href="{{ asset('libs/select2/css/select2.min.css') }}" rel="stylesheet">
    
    <link href="{{ asset('dist/frontend/css/app.css?_ver='.config('app.version')) }}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset("libs/daterange/daterangepicker.css") }}" >
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link rel='stylesheet' id='google-font-css-css'  href='https://fonts.googleapis.com/css?family=Poppins%3A300%2C400%2C500%2C600' type='text/css' media='all' />
    {!! \App\Helpers\Assets::css() !!}
    {!! \App\Helpers\Assets::js() !!}
     
    <!--Obitech Invent GTag monitorinng-->
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-3F6J4G0G1Z"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
    
      gtag('config', 'G-3F6J4G0G1Z');
    </script>


    {{-- google tag gaskid --}}

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-16458592239">
    </script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'AW-16458592239');
    </script>

    
    
    <script>
        var bookingCore = {
            url:'{{url( app_get_locale() )}}',
            url_root:'{{ url('') }}',
            booking_decimals:{{(int)get_current_currency('currency_no_decimal',2)}},
            thousand_separator:'{{get_current_currency('currency_thousand')}}',
            decimal_separator:'{{get_current_currency('currency_decimal')}}',
            currency_position:'{{get_current_currency('currency_format')}}',
            currency_symbol:'{{currency_symbol()}}',
			currency_rate:'{{get_current_currency('rate',1)}}',
            date_format:'{{get_moment_date_format()}}',
            map_provider:'{{setting_item('map_provider')}}',
            map_gmap_key:'{{setting_item('map_gmap_key')}}',
            routes:{
                login:'{{route('auth.login')}}',
                register:'{{route('auth.register')}}',
            },
            currentUser:{{(int)Auth::id()}},
            rtl: {{ setting_item_with_lang('enable_rtl') ? "1" : "0" }}
        };
        var i18n = {
            warning:"{{__("Warning")}}",
            success:"{{__("Success")}}",
        };
        var daterangepickerLocale = {
            "applyLabel": "{{__('Apply')}}",
            "cancelLabel": "{{__('Cancel')}}",
            "fromLabel": "{{__('From')}}",
            "toLabel": "{{__('To')}}",
            "customRangeLabel": "{{__('Custom')}}",
            "weekLabel": "{{__('W')}}",
            "first_day_of_week": {{ setting_item("site_first_day_of_the_weekin_calendar","1") }},
            "daysOfWeek": [
                "{{__('Su')}}",
                "{{__('Mo')}}",
                "{{__('Tu')}}",
                "{{__('We')}}",
                "{{__('Th')}}",
                "{{__('Fr')}}",
                "{{__('Sa')}}"
            ],
            "monthNames": [
                "{{__('January')}}",
                "{{__('February')}}",
                "{{__('March')}}",
                "{{__('April')}}",
                "{{__('May')}}",
                "{{__('June')}}",
                "{{__('July')}}",
                "{{__('August')}}",
                "{{__('September')}}",
                "{{__('October')}}",
                "{{__('November')}}",
                "{{__('December')}}"
            ],
        };
    </script>

    <!-- Styles -->
    @yield('head')
    {{--Custom Style--}}
    <link href="{{ route('core.style.customCss') }}" rel="stylesheet">
    <link href="{{ asset('libs/carousel-2/owl.carousel.css') }}" rel="stylesheet">
    @if(setting_item_with_lang('enable_rtl'))
        <link href="{{ asset('dist/frontend/css/rtl.css') }}" rel="stylesheet">
    @endif

    {!! setting_item('head_scripts') !!}
    {!! setting_item_with_lang_raw('head_scripts') !!}

    @php event(new \Modules\Layout\Events\LayoutEndHead()); @endphp
</head>
<body class="frontend-page {{$body_class ?? ''}} @if(setting_item_with_lang('enable_rtl')) is-rtl @endif">
    @php event(new \Modules\Layout\Events\LayoutBeginBody()); @endphp

    {!! setting_item('body_scripts') !!}
    {!! setting_item_with_lang_raw('body_scripts') !!}
    <div class="bravo_wrap">
        @include('Layout::parts.topbar')
        @include('Layout::parts.header')
        @yield('content')
        @include('Layout::parts.footer')
    </div>
    {!! setting_item('footer_scripts') !!}
    {!! setting_item_with_lang_raw('footer_scripts') !!}
    @php event(new \Modules\Layout\Events\LayoutEndBody()); @endphp

</body>
<script>
	var special  = document.querySelector(".btn-default");
	special.href="special-offer";
	special.addEventListener("click",
	   function(){ location.href="special-offer";
	}
	);
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script type='text/javascript'>
//     if(typeof sessionStorage.has_download_catalogue === 'undefined'){
//           Swal.fire({
//               title: '<strong>Get <u>Special</u> Discount on your Accommodation</strong>',
//               icon: 'info',
//               html:
//                 `
//                 <p>Find below and download our special discounted prices accommodation catalogues on the upcoming events.</p>
//                 <br />
                
//                 <a href="{{ asset('downloadable/NACO-LOGISTICS-ABUJA-SLP-HOTEL-CATALOGUE.pdf') }}" class="btn btn-danger" role="button" download style="font-size:12px !important;">DOWNLOAD 2023 NBA-SLP ANNUAL CONFERENCE CATALOGUE</a>
//                 <br />
//                 <br />
//                 <a href="{{ asset('downloadable/NACO-2023-NBA-HOTEL-CATALOG-JULY-EDITION.pdf') }}" class="btn btn-danger" role="button" download style="font-size:12px !important;">DOWLOAD 2023 NBA-AGC CATALOGUE</a>
//                 `,
//               showCloseButton: true,
//               showCancelButton: true,
//               focusConfirm: false,
//               confirmButtonText:
//                 '<i class="fa fa-thumbs-up"></i> Great!',
//               confirmButtonAriaLabel: 'Thumbs up, great!',
//               cancelButtonText:
//                 '<i class="fa fa-thumbs-down"></i>',
//               cancelButtonAriaLabel: 'Thumbs down'
//         }).then((result) => {
//           /* Read more about isConfirmed, isDenied below */
//           if (result.isConfirmed) {
//             Swal.fire('Thank you!', '', 'success')
            
//             sessionStorage.setItem('has_download_catalogue', 'yes')
//           } else if (result.isDenied) {
//              sessionStorage.setItem('has_download_catalogue', 'no')
//             Swal.fire('Great!', 'You can still download the catalogue on our special offer page.', 'info')
//           }
//         })
//     }
 
</script>


</html>
