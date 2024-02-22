@php $userInfo = $post->user @endphp
<div class="bravo-post-item">
    <div class="post-top">
        <div class="post-author">
            <div class="media">
                <div class="media-left">
                    @if($avatar_url = $userInfo->getAvatarUrl())
                        <img class="avatar" src="{{$avatar_url}}" alt="{{$userInfo->getDisplayName()}}">
                    @else
                        <span class="avatar-text">{{ucfirst($userInfo->getDisplayName()[0])}}</span>
                    @endif
                </div>
                <div class="media-body">
                    <h4 class="media-heading">{{$userInfo->getDisplayName()}}</h4>
                    <div class="date">{{display_datetime($post->created_at)}}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="post-body">
        {{$post->content}}
    </div>
    <div class="post-action">
        <span><i class="fa fa-like"></i> {{__('Like')}}</span>
        <span><i class="fa fa-comments"></i> {{__('Comments')}}</span>
        <span><i class="fa fa-share"></i> {{__('Share')}}</span>
    </div>
</div>
