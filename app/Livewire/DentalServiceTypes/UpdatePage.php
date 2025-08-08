<?php

namespace App\Livewire\DentalServiceTypes;

use App\Models\DentalServiceType;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdatePage extends Component
{
    public DentalServiceType $dentalServiceType;
    public $name;
    public $description;

    public function mount(DentalServiceType $dentalServiceType)
    {
        $this->dentalServiceType = $dentalServiceType;
        $this->name = $dentalServiceType->name;
        $this->description = $dentalServiceType->description;
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('dental_service_types')->ignore($this->dentalServiceType->id)],
            'description' => 'nullable|string|max:1000',
        ];
    }

    public function update()
    {
        $this->authorize('update', $this->dentalServiceType);
        $this->validate();

        $this->dentalServiceType->update([
            'name' => $this->name,
            'description' => $this->description,
        ]);

        session()->flash('success', 'Dental service type updated successfully!');

        return $this->redirect(route('dental-service-types.index'), navigate: true);
    }

    public function render()
    {
        $this->authorize('update', $this->dentalServiceType);
        return view('livewire.dental-service-types.update-page');
    }
}