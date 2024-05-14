{{-- user info and avatar --}}
<div class="chatify-d-flex"></div>
{{-- <p class="info-name">{{ config('chatify.name') }}</p> --}}

<div class="messenger-infoView-btns">
<a class="danger delete-conversation" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
    <i class="danger bx bx-log-out"></i>
    تسجيل خروج
</a>
<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>
{{-- <form action="{{ route('logout') }}" method="POST">
    @csrf
    <button type="submit">تسجيل الخروج</button>
</form> --}}
</div>

{{-- <div class="messenger-infoView-btns">
    <a href="#" class="danger delete-conversation">Delete Conversation</a> --}}
{{-- shared photos --}}
<div class="messenger-infoView-shared">
    <p class="messenger-title"><span>Shared Photos</span></p>
    <div class="shared-photos-list"></div>
</div>
