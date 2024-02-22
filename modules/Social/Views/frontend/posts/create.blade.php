@php $userInfo = Auth::user() @endphp
<div class="bravo-post-item">
    <div class="post-header">{{__("Add new post")}}</div>
    <div class="post-body">
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
                    <textarea name="" id="" cols="30" rows="10"></textarea>
                </div>
            </div>
        </div>
    </div>
</div>
