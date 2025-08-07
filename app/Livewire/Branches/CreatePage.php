<?php

namespace App\Livewire\Branches;

use App\Models\Branch;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class CreatePage extends Component
{
    public $name = '';
    public $address = '';
    public $phone = '';
    public $email = '';

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:branches,name',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:branches,email',
        ];
    }

    public function save()
    {
        $this->authorize('create', Branch::class);
        $this->validate();

        Branch::create([
            'name' => $this->name,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email ?: null,
        ]);

        session()->flash('success', 'Branch created successfully!');

        return $this->redirect(route('branches.index'), navigate: true);
    }

    public function render()
    {
        $this->authorize('create', Branch::class);
        return view('livewire.branches.create-page');
    }
}