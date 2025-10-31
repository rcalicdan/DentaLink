<?php

namespace App\Livewire\DentalServices;

use App\Models\DentalService;
use App\Models\DentalServiceType;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class CreatePage extends Component
{
    public $name = '';
    public $description = '';
    public $dental_service_type_id = '';
    public $price = '';
    public $is_quantifiable = true;

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'dental_service_type_id' => 'required|exists:dental_service_types,id',
            'price' => 'nullable|numeric|min:0|max:999999.99',
            'is_quantifiable' => 'required|boolean',
        ];
    }

    public function save()
    {
        $this->authorize('create', DentalService::class);
        $this->validate();

        DentalService::create([
            'name' => $this->name,
            'description' => $this->description ?: null,
            'dental_service_type_id' => $this->dental_service_type_id,
            'price' => $this->price ?: null,
            'is_quantifiable' => $this->is_quantifiable,
        ]);

        session()->flash('success', 'Dental service created successfully!');

        return $this->redirect(route('dental-services.index'), navigate: true);
    }

    public function render()
    {
        $this->authorize('create', DentalService::class);
        return view('livewire.dental-services.create-page', [
            'serviceTypeOptions' => $this->getServiceTypeOptions(),
        ]);
    }

    private function getServiceTypeOptions()
    {
        $options = ['' => 'Select a service type'];

        $serviceTypes = DentalServiceType::orderBy('name')->get();

        foreach ($serviceTypes as $serviceType) {
            $options[$serviceType->id] = $serviceType->name;
        }

        return $options;
    }
}