<div class="admin-toast-region" data-toast-region data-save-error="{{ __('toast.save_error') }}"
    aria-label="{{ __('admin.notifications') }}" data-success="{{ __('toast.success') }}"
    data-error="{{ __('toast.error') }}" data-info="{{ __('toast.info') }}" data-warning="{{ __('toast.warning') }}"
    data-close="{{ __('toast.close') }}"></div>
@if (session('couple_saved'))
    <span hidden data-toast-message="{{ __('couple.saved') }}" data-toast-type="success"></span>
@endif
@if ($errors->any())
    <span hidden data-toast-message="{{ $errors->first() }}" data-toast-type="error"></span>
@endif
@foreach (['success', 'error', 'info', 'warning'] as $type)
    @if (is_string(session($type)) && session($type) !== '')
        <span hidden data-toast-message="{{ session($type) }}" data-toast-type="{{ $type }}"></span>
    @endif
@endforeach
