<div>
    <x-flash-session />
    <x-slot name="header">
        <h2 class="mt-6 text-3xl font-bold text-slate-800 dark:text-white leading-tight">
            Welcome Back
        </h2>
        <p class="mt-3 text-base text-slate-600 dark:text-slate-400">
            Access your Nice Smile Dental Clinic dashboard
        </p>
    </x-slot>

    <x-form.auth.login-form />
</div>
