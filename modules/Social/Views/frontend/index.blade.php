@extends('layouts.app',['container_class'=>'container-fluid','header_right_menu'=>true,'header_social'=>1])@section('head')
    <link href="{{ asset('dist/frontend/module/social/css/social.css?_ver='.config('app.version')) }}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset("libs/ion_rangeslider/css/ion.rangeSlider.min.css") }}"/>
    <style type="text/css">
        .bravo_topbar, .bravo_footer {
            display: none
        }
    </style>
@endsection
@section('content')
    <div class="social-page">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <div class="nav-group">
                        <div class="nav-title">{{__("Forums")}}</div>
                        <ul class="nav-items">
                            @foreach($forums as $forum)
                                <li><a href="{{$forum->getDetailUrl()}}">
                                    {!! $forum->icon_html !!}
                                    {{$forum->name}}
                                </a></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="row">
                        <div class="col-md-8">
                            @include("Social::frontend.posts.create")
                            @foreach($rows as $row)
                                @include("Social::frontend.posts.loop",['post'=>$row])
                            @endforeach
                        </div>
                        <div class="col-md-4"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
