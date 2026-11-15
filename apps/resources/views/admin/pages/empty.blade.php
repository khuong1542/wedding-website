@extends('admin.layouts.index')

@section('content')
    <section class="admin-empty"><x-admin::icon name="folder" />
        <h2>{{ __('admin.empty_title') }}</h2>
        <p>{{ __('admin.empty_description') }}</p><a href="{{ route('admin.dashboard') }}">{{ __('admin.back') }}
            <x-admin::icon name="arrow-right" /></a>
    </section>
@endsection
