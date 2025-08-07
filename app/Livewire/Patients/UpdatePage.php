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
    public $date_of_birth;
    public $address;
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
        $this->date_of_birth = $patient->date_of_birth?->format('Y-m-d');
        $this->address = $patient->address;
        $this->registration_branch_id = $patient->registration_branch_id;
    }

    public function rules()
    {
        $rules = [
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'phone' => 'required|string|max:20',
            'email' => ['nullable', 'email', 'max:100', Rule::unique('patients')->ignore($this->patient->id)],
            'date_of_birth' => 'nullable|date|before:today',
            'address' => 'nullable|string|max:1000',
            'registration_branch_id' => 'required|exists:branches,id',
        ];

        if (Auth::user()->isAdmin()) {
            $rules['registration_branch_id'] = 'required|exists:branches,id|in:' . Auth::user()->branch_id;
        }

        return $rules;
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
            'date_of_birth' => $this->date_of_birth ?: null,
            'address' => $this->address ?: null,
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
