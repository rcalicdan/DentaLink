<?php

namespace App\Livewire\Auth;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class LoginPage extends Component
{
    public function render()
    {
        return view('livewire.auth.login-page');
    }
}
