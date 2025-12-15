<?php

namespace App\Livewire\Patients;

use App\Models\Patient;
use App\Models\Branch;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class CreatePage extends Component
{
    public $first_name = '';
    public $last_name = '';
    public $phone = '';
    public $email = '';
    public $age = '';
    public $address = '';
    public $registration_branch_id = '';

    public function mount()
    {
        if (Auth::user()->isAdmin()) {
            $this->registration_branch_id = Auth::user()->branch_id;
        }
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
                'unique:patients,email',
                'regex:/^.+@\w+\.\w{2,}$/'
            ],
            'age' => 'nullable|integer|min:0|max:150',
            'address' => 'nullable|string|max:1000',
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
    
    public function save()
    {
        $this->authorize('create', Patient::class);
        
        $this->validate();

        Patient::create([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'email' => $this->email ?: null,
            'age' => $this->age ?: null,
            'address' => $this->address ?: null,
            'registration_branch_id' => $this->registration_branch_id,
        ]);

        session()->flash('success', 'Patient created successfully!');

        return $this->redirect(route('patients.index'), navigate: true);
    }

    public function render()
    {
        $this->authorize('create', Patient::class);
        return view('livewire.patients.create-page', [
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