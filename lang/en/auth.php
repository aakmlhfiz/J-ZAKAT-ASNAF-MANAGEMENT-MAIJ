<?php

return [
    'brand' => [
        'tagline' => 'Majlis Agama Islam Johor',
    ],

    'back_home' => 'Back to home',
    'system_official' => 'Official MAIJ System',
    'roles' => [
        'admin' => 'Administrator Portal',
        'portal' => 'Applicant Portal',
    ],

    'tabs' => [
        'login' => 'Log In',
        'register' => 'Register',
    ],

    'aside' => [
        'admin' => [
            'headline' => 'Manage asnaf eligibility with confidence.',
        ],
        'portal' => [
            'headline' => 'Manage zakat assistance applications with ease.',
        ],
        'highlight' => 'Technology for the ummah.',
        'lede' => 'An integrated platform for applications, Had Kifayah calculations and asnaf eligibility analysis in one transparent system.',
        'points' => [
            'secure' => [
                'title' => 'Secure & protected',
                'desc' => 'Applicant data is protected and access is controlled.',
            ],
            'assessment' => [
                'title' => 'Eligibility assessment',
                'desc' => 'Had Kifayah and eligibility analysis in one workflow.',
            ],
            'audit' => [
                'title' => 'Transparent & auditable',
                'desc' => 'Every action is recorded for accountability.',
            ],
        ],
        'footer' => 'Official system of Majlis Agama Islam Johor',
    ],

    'login' => [
        'title' => 'Welcome back',
        'subtitle_admin' => 'Log in to continue managing asnaf profiles and applications.',
        'subtitle_portal' => 'Log in to check your applications and assistance status.',
        'remember' => 'Keep me logged in',
        'forgot' => 'Forgot password?',
        'submit' => 'Log In',
        'processing' => 'Processing...',
        'switch_admin' => 'Need assistance?',
        'switch_portal' => 'MAIJ administrator?',
        'switch_admin_link' => 'Applicant login',
        'switch_portal_link' => 'Administrator login',
        'signup_title_admin' => 'Register an administrator account',
        'signup_title_portal' => 'Register an applicant account',
        'signup_subtitle_admin' => 'Use the invitation code provided by the system administrator.',
        'signup_subtitle_portal' => 'Complete your details to apply for zakat assistance.',
        'signup_submit' => 'Register Account',
        'has_account' => 'Already have an account?',
        'signup_login' => 'Log in here',
    ],

    'fields' => [
        'name' => 'Full name',
        'name_placeholder' => 'Name as shown on your identity card',
        'email' => 'Email',
        'email_placeholder' => 'name@maij.gov.my',
        'email_placeholder_public' => 'name@example.com',
        'password' => 'Password',
        'password_placeholder' => 'Enter your password',
        'password_new_placeholder' => 'Minimum 8 characters',
        'confirm_password' => 'Confirm password',
        'confirm_placeholder' => 'Re-enter password',
        'password_hint' => 'Minimum 8 characters. Use a mix of letters and numbers.',
        'invitation' => 'Administrator invitation code',
        'invitation_placeholder' => 'MAIJ-XXXX-XXXX-XXXX-XXXX',
        'invitation_hint' => 'The invitation code is provided by the system administrator.',
        'ic' => 'Identity Card Number',
        'ic_placeholder' => '12 digits without dashes',
        'dob' => 'Date of birth',
        'gender' => 'Gender',
        'male' => 'Male',
        'female' => 'Female',
        'phone' => 'Phone number',
        'phone_placeholder' => '0123456789',
        'address' => 'Address',
        'address_placeholder' => 'Full address',
        'district' => 'District',
        'district_placeholder' => 'District',
        'state' => 'State',
        'postcode' => 'Postcode',
        'postcode_placeholder' => '5 digits',
    ],

    'terms' => 'I agree to the Terms & Conditions and Privacy Notice of J-ZAKAT.',
    'logout' => 'Log Out',
    'show_password' => 'Show password',
    'hide_password' => 'Hide password',

    'register' => [
        'admin' => [
            'badge' => 'Administrator Registration',
            'title' => 'Register an administrator account',
            'subtitle' => 'Use the invitation code provided by the system administrator to gain access.',
            'submit' => 'Register Account',
            'has_account' => 'Already have an account?',
            'login_link' => 'Log in here',
        ],
        'portal' => [
            'badge' => 'Applicant Registration',
            'title' => 'Register an applicant account',
            'subtitle' => 'Complete your details to apply for zakat assistance through the MAIJ portal.',
            'submit' => 'Register Account',
            'has_account' => 'Already have an account?',
            'login_link' => 'Log in here',
        ],
    ],

    'forgot' => [
        'badge' => 'Account Recovery',
        'title' => 'Forgot password?',
        'subtitle' => 'Enter your email address. We will send you a password reset link.',
        'submit' => 'Send Reset Link',
        'processing' => 'Sending...',
        'back' => 'Back to login',
    ],

    'reset' => [
        'badge' => 'Account Security',
        'title' => 'Reset password',
        'subtitle' => 'Choose a strong new password to protect your account.',
        'password' => 'New password',
        'confirm' => 'Confirm new password',
        'submit' => 'Reset Password',
        'processing' => 'Processing...',
        'back' => 'Back to login',
    ],

    'two_factor' => [
        'badge' => 'Two-Factor Authentication',
        'title' => 'Verify your identity',
        'subtitle' => 'Enter the 6-digit code from your authenticator app to continue logging in.',
        'code' => 'Verification code',
        'submit' => 'Verify & Log In',
        'processing' => 'Verifying...',
        'back' => 'Back to login',
    ],

    'meta' => [
        'login' => 'Log In',
        'register' => 'Register',
        'portal_login' => 'Applicant Login',
        'portal_register' => 'Applicant Registration',
    ],

    'failed' => 'These credentials do not match our records.',
    'password' => 'The provided password is incorrect.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',
    'errors' => [
        'invalid_credentials' => 'Invalid email or password.',
        'suspended' => 'Your account has been suspended. Please contact MAIJ.',
        'inactive' => 'Your account is inactive. Please contact MAIJ.',
        'too_many_attempts' => 'Too many attempts. Please log in again.',
        'invalid_2fa' => 'Invalid 2FA code. Please try again.',
        'reset_success' => 'Your password has been reset successfully.',
        'reset_invalid' => 'The reset link is invalid or has expired.',
        'reset_sent' => 'If an account exists, a password reset link has been sent to your email.',
        'invitation_invalid' => 'Invalid invitation code.',
        'invitation_used' => 'This invitation code has already been used.',
        'invitation_inactive' => 'This invitation code has been deactivated.',
        'invitation_expired' => 'This invitation code has expired.',
        'invitation_unusable' => 'This invitation code cannot be used.',
        'welcome_portal' => 'Welcome! Your account has been created successfully.',
    ],
];
