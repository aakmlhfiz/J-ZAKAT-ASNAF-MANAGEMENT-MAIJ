@php
    $profileStrings = [
        'toast_success' => __('profile.toast.success'),
        'toast_error' => __('profile.toast.error'),
        'toast_saved' => __('profile.toast.saved'),
        'toast_downloaded' => __('profile.toast.downloaded'),
        'toast_logout' => __('profile.toast.logout'),
        'photo_updated' => __('profile.messages.photo_updated'),
        'photo_upload_failed' => __('profile.messages.photo_upload_failed'),
        'photo_process_error' => __('profile.messages.photo_process_error'),
        'strength_weak' => __('profile.security.strength_weak'),
        'strength_fair' => __('profile.security.strength_fair'),
        'strength_good' => __('profile.security.strength_good'),
        'strength_strong' => __('profile.security.strength_strong'),
        'two_factor_start_error' => __('profile.messages.two_factor_start_error'),
        'two_factor_enabled_toast' => __('profile.messages.two_factor_enabled_toast'),
        'two_factor_disabled' => __('profile.messages.two_factor_disabled'),
        'invalid_code_short' => __('profile.messages.invalid_code_short'),
        'verify_error' => __('profile.messages.verify_error'),
        'password_incorrect' => __('profile.messages.password_incorrect'),
        'generic_error' => __('profile.messages.generic_error'),
        'notifications_saved' => __('profile.messages.notifications_saved'),
        'save_failed' => __('profile.messages.save_failed'),
        'account_downloaded' => __('profile.messages.account_downloaded'),
        'download_failed' => __('profile.messages.download_failed'),
        'all_devices_logged_out' => __('profile.messages.all_devices_logged_out'),
    ];
@endphp
<script>
    window.profileStrings = @json($profileStrings);
</script>
