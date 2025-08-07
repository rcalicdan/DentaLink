<?php

namespace App\Livewire\Auth;

use App\ActionService\AuthService;
use App\Traits\DispatchFlashMessage;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Logout extends Component
{
    use DispatchFlashMessage;

    public function logout()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        session()->flash('success', 'Logout Succesfully');
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }

    public function mount()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }
    }

    public function render()
    {
        return view('livewire.auth.logout');
    }
}
