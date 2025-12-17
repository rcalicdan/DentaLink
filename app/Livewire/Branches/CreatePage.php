<?php

namespace App\Livewire\Branches;

use App\Models\Branch;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class CreatePage extends Component
{
    public $name = '';
    public $phone = '';
    public $email = '';
    public $street = '';
    public $barangay = '';
    public $town_city = '';
    public $province = '';

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:branches,name',
            'phone' => 'nullable|string|max:20',
            'email' => ['nullable', 'email', 'max:255', 'unique:branches,email', 'regex:/^.+@\w+\.\w{2,}$/'],
            'street' => 'nullable|string|max:255',
            'barangay' => 'nullable|string|max:255',
            'town_city' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
        ];
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }
    
    private function combineAddress(): ?string
    {
        $parts = array_filter([$this->street, $this->barangay, $this->town_city, $this->province]);
        if (empty($parts)) {
            return null;
        }
        return implode(', ', $parts);
    }

    public function save()
    {
        $this->authorize('create', Branch::class);
        $this->validate();

        Branch::create([
            'name' => $this->name,
            'address' => $this->combineAddress(),
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