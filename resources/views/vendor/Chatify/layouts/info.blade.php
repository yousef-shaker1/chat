{{-- user info and avatar --}}
<div class="chatify-d-flex"></div>
{{-- <p class="info-name">{{ config('chatify.name') }}</p> --}}

<div class="messenger-infoView-btns">
    <a class="danger delete-conversation" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
        تسجيل الخروج
    </a>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
</div>


<div class="messenger-infoView-shared">
    <p class="messenger-title"><span>Shared Photos</span></p>
    <div class="shared-photos-list"></div>
</div>
