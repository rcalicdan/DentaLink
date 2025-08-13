<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - Clinic Management System</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #334155;
        }

        .error-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            padding: 40px;
            text-align: center;
            max-width: 500px;
            width: 100%;
            border: 1px solid #e2e8f0;
        }

        .clinic-header {
            margin-bottom: 30px;
        }

        .clinic-logo {
            width: 60px;
            height: 60px;
            background: #0f4c75;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .clinic-logo svg {
            width: 30px;
            height: 30px;
            fill: white;
        }

        .clinic-title {
            font-size: 20px;
            font-weight: 600;
            color: #0f4c75;
            margin-bottom: 30px;
        }

        .error-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
        }

        .error-icon svg {
            width: 40px;
            height: 40px;
        }

        .error-icon.auth {
            background: #fef2f2;
            color: #dc2626;
        }

        .error-icon.not-found {
            background: #eff6ff;
            color: #2563eb;
        }

        .error-icon.server {
            background: #fff7ed;
            color: #ea580c;
        }

        .error-icon.general {
            background: #fefce8;
            color: #ca8a04;
        }

        .error-code {
            display: inline-block;
            background: #f1f5f9;
            color: #475569;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 20px;
            border: 1px solid #e2e8f0;
        }

        .error-title {
            font-size: 24px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 15px;
        }

        .error-description {
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 30px;
            font-size: 16px;
        }

        .button-group {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .btn {
            flex: 1;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s ease;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-width: 120px;
        }

        .btn-secondary {
            background: white;
            color: #0f4c75;
            border: 1px solid #0f4c75;
        }

        .btn-secondary:hover {
            background: #f8fafc;
            transform: translateY(-1px);
        }

        .btn-primary {
            background: #0f4c75;
            color: white;
            border: 1px solid #0f4c75;
        }

        .btn-primary:hover {
            background: #3282b8;
            transform: translateY(-1px);
        }

        .btn svg {
            width: 16px;
            height: 16px;
        }

        .support-section {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid #e2e8f0;
        }

        .support-text {
            font-size: 14px;
            color: #64748b;
        }

        .support-link {
            color: #0f4c75;
            text-decoration: none;
            font-weight: 500;
        }

        .support-link:hover {
            color: #3282b8;
        }

        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #94a3b8;
        }

        @media (max-width: 480px) {
            .error-container {
                padding: 30px 20px;
            }

            .button-group {
                flex-direction: column;
            }

            .btn {
                flex: none;
            }
        }
    </style>
</head>

<body>
    @php
        $errorCode = trim(View::yieldContent('code'));
        $isAuthError = in_array($errorCode, ['401', '403']);
        $isNotFound = $errorCode === '404';
        $isServerError = in_array($errorCode, ['500', '503']);
        $showSupport = in_array($errorCode, ['500', '503']);
    @endphp

    <div class="error-container">
        <!-- Header -->
        <div class="clinic-header">
            <div class="clinic-logo">
                <svg viewBox="0 0 24 24">
                    <path
                        d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                </svg>
            </div>
            <h1 class="clinic-title">Clinic Management System</h1>
        </div>

        <!-- Error Icon -->
        <div
            class="error-icon {{ $isAuthError ? 'auth' : ($isNotFound ? 'not-found' : ($isServerError ? 'server' : 'general')) }}">
            @if ($isAuthError)
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            @elseif($isNotFound)
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            @elseif($isServerError)
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.864-.833-2.634 0L4.168 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
            @else
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            @endif
        </div>

        <!-- Error Information -->
        <div class="error-code">Error @yield('code')</div>
        <h2 class="error-title">@yield('message')</h2>

        <p class="error-description">
            @switch($errorCode)
                @case('401')
                    You need to be logged in to access this resource. Please authenticate and try again.
                @break

                @case('403')
                    You don't have permission to access this resource. Contact your administrator if you believe this is an
                    error.
                @break

                @case('404')
                    The page you're looking for doesn't exist. It may have been moved or deleted.
                @break

                @case('419')
                    Your session has expired for security reasons. Please refresh the page and try again.
                @break

                @case('429')
                    Too many requests have been made. Please wait a moment before trying again.
                @break

                @case('500')
                    An internal server error occurred. Our technical team has been notified.
                @break

                @case('503')
                    The system is temporarily unavailable for maintenance. Please try again shortly.
                @break

                @default
                    An unexpected error occurred. Please try again or contact support if the problem persists.
            @endswitch
        </p>

        <!-- Action Buttons -->
        <div class="button-group">
            <a wire:navigate href="{{ url()->previous() }}" class="btn btn-secondary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Go Back
            </a>

            @php
                $url = '';
                if (Auth::user()->isAdmin() || Auth::user()->isSuperadmin()) {
                    $url = '/dashboard';
                } else {
                    $url = '/patients';
                }
            @endphp
            <a href="{{ $url }}" wire:navigate class="btn btn-primary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Home
            </a>
        </div>

        <!-- Support Section for Critical Errors -->
        @if ($showSupport)
            <div class="support-section">
                <p class="support-text">
                    Need immediate assistance? Contact IT support at
                    <a href="mailto:support@clinic.com" class="support-link">support@clinic.com</a>
                </p>
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            © {{ date('Y') }} Clinic Management System. All rights reserved.
        </div>
    </div>
</body>

</html>
