<?php

namespace App\Livewire\Users;

use App\Models\User;
use App\Models\Branch;
use App\Enums\UserRoles;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UpdatePage extends Component
{
    public User $user;
    public $first_name;
    public $last_name;
    public $email;
    public $phone;
    public $password = '';
    public $password_confirmation = '';
    public $role;
    public $branch_id;
    public $showPassword = false;

    public function mount(User $user)
    {
        $this->user = $user;
        $this->first_name = $user->first_name;
        $this->last_name = $user->last_name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->role = $user->role;
        $this->branch_id = $user->branch_id;
    }

    public function rules()
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($this->user->id)],
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'role' => ['required', Rule::in(UserRoles::getAllRoles())],
            'branch_id' => 'nullable|exists:branches,id',
        ];
    }

    public function togglePasswordVisibility()
    {
        $this->showPassword = !$this->showPassword;
    }

    public function update()
    {
        $this->validate();

        $updateData = [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'branch_id' => $this->branch_id ?: null,
        ];

        if (!empty($this->password)) {
            $updateData['password'] = Hash::make($this->password);
        }

        $this->user->update($updateData);

        session()->flash('message', 'User updated successfully!');

        return $this->redirect(route('users.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.users.update-page', [
            'roleOptions' => $this->getRoleOptions(),
            'branchOptions' => $this->getBranchOptions()
        ]);
    }

    private function getRoleOptions()
    {
        $options = ['' => 'Select a role'];

        foreach (UserRoles::cases() as $role) {
            $options[$role->value] = match ($role) {
                UserRoles::SUPER_ADMIN => 'Super Admin',
                UserRoles::ADMIN => 'Admin',
                UserRoles::EMPLOYEE => 'Employee',
            };
        }

        return $options;
    }

    private function getBranchOptions()
    {
        $options = ['' => 'Select a branch (optional)'];

        $branches = Branch::orderBy('name')
            ->get();

        foreach ($branches as $branch) {
            $options[$branch->id] = $branch->name;
        }

        return $options;
    }
}
