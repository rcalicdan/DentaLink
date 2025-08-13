<?php

namespace App\Livewire\Profile;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ProfilePage extends Component
{
    public User $user;

    public $first_name;
    public $last_name;
    public $email;
    public $phone;

    public $current_password = '';
    public $password = '';
    public $password_confirmation = '';
    public $showPassword = false;

    public function mount()
    {
        $this->user = Auth::user();
        $this->first_name = $this->user->first_name;
        $this->last_name = $this->user->last_name;
        $this->email = $this->user->email;
        $this->phone = $this->user->phone;
    }

    public function togglePasswordVisibility()
    {
        $this->showPassword = !$this->showPassword;
    }

    /**
     * Update the user's personal information.
     */
    public function updateProfile()
    {
        $this->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($this->user->id)],
            'phone' => 'nullable|string|max:20',
        ]);

        $this->user->update([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
        ]);

        session()->flash('success', 'Profile updated successfully!');

        return $this->redirect(route('profile.edit'), true);
    }

    /**
     * Update the user's password.
     */
    public function updatePassword()
    {
        $this->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $this->user->update([
            'password' => Hash::make($this->password),
        ]);

        $this->reset(['current_password', 'password', 'password_confirmation']);

        session()->flash('success', 'Password changed successfully!');

        return $this->redirect(route('profile.edit'), true);
    }

    public function render()
    {
        return view('livewire.profile.profile-page');
    }
}
