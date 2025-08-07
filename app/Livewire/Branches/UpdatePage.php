<?php

namespace App\Livewire\Branches;

use App\Models\Branch;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdatePage extends Component
{
    public Branch $branch;
    public $name;
    public $address;
    public $phone;
    public $email;

    public function mount(Branch $branch)
    {
        $this->branch = $branch;
        $this->name = $branch->name;
        $this->address = $branch->address;
        $this->phone = $branch->phone;
        $this->email = $branch->email;
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('branches')->ignore($this->branch->id)],
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => ['nullable', 'email', 'max:255', Rule::unique('branches')->ignore($this->branch->id)],
        ];
    }

    public function update()
    {
        $this->authorize('update', $this->branch);
        $this->validate();

        $this->branch->update([
            'name' => $this->name,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email ?: null,
        ]);

        session()->flash('success', 'Branch updated successfully!');

        return $this->redirect(route('branches.index'), navigate: true);
    }

    public function render()
    {
        $this->authorize('update', $this->branch);
        return view('livewire.branches.update-page');
    }
}
