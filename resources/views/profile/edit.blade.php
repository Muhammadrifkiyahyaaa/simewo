@extends('layouts.app')

@section('title', 'Profile')

@section('content')
<h5 class="mb-3">Profile</h5>

<div class="bg-white p-4 rounded shadow-sm mb-3">
    @include('profile.partials.update-profile-information-form')
</div>

<div class="bg-white p-4 rounded shadow-sm mb-3">
    @include('profile.partials.update-password-form')
</div>

<div class="bg-white p-4 rounded shadow-sm mb-3">
    @include('profile.partials.delete-user-form')
</div>
@endsection