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
    public $phone;
    public $email;
    
    // New Address Fields
    public $street;
    public $barangay;
    public $town_city;
    public $province;

    public function mount(Branch $branch)
    {
        $this->branch = $branch;
        $this->name = $branch->name;
        $this->phone = $branch->phone;
        $this->email = $branch->email;
        
        $this->parseAddress($branch->address);
    }

    private function parseAddress(?string $address)
    {
        if (!$address) {
            $this->street = '';
            $this->barangay = '';
            $this->town_city = '';
            $this->province = '';
            return;
        }

        $parts = array_map('trim', explode(',', $address));
        
        $this->street = $parts[0] ?? '';
        $this->barangay = $parts[1] ?? '';
        $this->town_city = $parts[2] ?? '';
        $this->province = $parts[3] ?? '';
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('branches')->ignore($this->branch->id)],
            'phone' => 'nullable|string|max:20',
            'email' => ['nullable', 'email', 'max:255', Rule::unique('branches')->ignore($this->branch->id), 'regex:/^.+@\w+\.\w{2,}$/'],
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

    public function update()
    {
        $this->authorize('update', $this->branch);
        $this->validate();

        $this->branch->update([
            'name' => $this->name,
            'address' => $this->combineAddress(),
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