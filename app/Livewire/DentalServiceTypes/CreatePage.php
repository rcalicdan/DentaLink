<?php

namespace App\Livewire\DentalServiceTypes;

use App\Models\DentalServiceType;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class CreatePage extends Component
{
    public $name = '';
    public $description = '';

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:dental_service_types,name',
            'description' => 'nullable|string|max:1000',
        ];
    }

    public function save()
    {
        $this->authorize('create', DentalServiceType::class);
        $this->validate();

        DentalServiceType::create([
            'name' => $this->name,
            'description' => $this->description,
        ]);

        session()->flash('success', 'Dental service type created successfully!');

        return $this->redirect(route('dental-service-types.index'), navigate: true);
    }

    public function render()
    {
        $this->authorize('create', DentalServiceType::class);
        return view('livewire.dental-service-types.create-page');
    }
}