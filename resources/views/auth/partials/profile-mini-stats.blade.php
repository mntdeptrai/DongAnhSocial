<!-- Mini Top Key Stat Cards (Tailored by Role) -->
@if($user->isPrincipal() || $user->role === 'principal')
    @include('auth.partials.roles.principal')
@elseif($user->isSeller() || $user->role === 'seller')
    @include('auth.partials.roles.seller')
@elseif($user->isAdmin() || $user->role === 'admin')
    @include('auth.partials.roles.admin')
@else
    @include('auth.partials.roles.user')
@endif
