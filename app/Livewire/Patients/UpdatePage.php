<?php

namespace App\Livewire\Patients;

use App\Models\Patient;
use App\Models\Branch;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdatePage extends Component
{
    public Patient $patient;
    public $first_name;
    public $last_name;
    public $phone;
    public $email;
    public $birthday;
    public $street;
    public $barangay;
    public $town_city;
    public $province;
    
    public $registration_branch_id;

    public function mount(Patient $patient)
    {
        if (Auth::user()->isAdmin() && $patient->registration_branch_id !== Auth::user()->branch_id) {
            abort(403, 'You can only edit patients in your branch.');
        }

        $this->patient = $patient;
        $this->first_name = $patient->first_name;
        $this->last_name = $patient->last_name;
        $this->phone = $patient->phone;
        $this->email = $patient->email;
        $this->birthday = $patient->birthday ? $patient->birthday->format('Y-m-d') : '';
        
        $this->parseAddress($patient->address);
        
        $this->registration_branch_id = $patient->registration_branch_id;
    }

    private function parseAddress(?string $address)
    {
        if (!$address) {
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
        $rules = [
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'phone' => 'required|string|max:20',
            'email' => [
                'nullable',
                'email',
                'max:100', 
                Rule::unique('patients')->ignore($this->patient->id),
                'regex:/^.+@\w+\.\w{2,}$/'
            ],
            'birthday' => 'nullable|date|before:today|after:1900-01-01',
            'street' => 'nullable|string|max:255',
            'barangay' => 'nullable|string|max:255',
            'town_city' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            
            'registration_branch_id' => 'required|exists:branches,id',
        ];

        if (Auth::user()->isAdmin()) {
            $rules['registration_branch_id'] = 'required|exists:branches,id|in:' . Auth::user()->branch_id;
        }

        return $rules;
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
        $this->authorize('update', $this->patient);
        $this->validate();

        $this->patient->update([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'email' => $this->email ?: null,
            'birthday' => $this->birthday ?: null,
            'address' => $this->combineAddress(),
            'registration_branch_id' => $this->registration_branch_id,
        ]);

        session()->flash('success', 'Patient updated successfully!');

        return $this->redirect(route('patients.index'), navigate: true);
    }

    public function render()
    {
        $this->authorize('update', $this->patient);
        return view('livewire.patients.update-page', [
            'branchOptions' => $this->getBranchOptions(),
            'isAdmin' => Auth::user()->isAdmin()
        ]);
    }

    private function getBranchOptions()
    {
        $currentUser = Auth::user();

        if ($currentUser->isAdmin()) {
            $adminBranch = $currentUser->branch;
            return $adminBranch ? [$adminBranch->id => $adminBranch->name] : [];
        }

        $options = ['' => 'Select a branch'];

        $branches = Branch::orderBy('name')->get();

        foreach ($branches as $branch) {
            $options[$branch->id] = $branch->name;
        }

        return $options;
    }
}