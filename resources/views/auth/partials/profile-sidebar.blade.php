<!-- LEFT SIDEBAR COLUMN (340px) -->
<div>
    @if($user->isAdmin() || $user->role === 'admin')
        @include('auth.partials.sidebar.admin')
    @elseif($user->isSeller() || $user->role === 'seller')
        @include('auth.partials.sidebar.seller')
    @elseif($user->isManager() || $user->role === 'manager')
        @include('auth.partials.sidebar.manager')
    @elseif($school || $user->role === 'principal')
        @include('auth.partials.sidebar.principal')
    @else
        @include('auth.partials.sidebar.user')
    @endif
</div>
