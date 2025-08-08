<?php

namespace App\Livewire\DentalServices;

use App\Models\DentalService;
use App\Models\DentalServiceType;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class UpdatePage extends Component
{
    public DentalService $dentalService;
    public $name;
    public $dental_service_type_id;
    public $price;

    public function mount(DentalService $dentalService)
    {
        $this->dentalService = $dentalService;
        $this->name = $dentalService->name;
        $this->dental_service_type_id = $dentalService->dental_service_type_id;
        $this->price = $dentalService->price;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'dental_service_type_id' => 'required|exists:dental_service_types,id',
            'price' => 'required|numeric|min:0|max:999999.99',
        ];
    }

    public function update()
    {
        $this->authorize('update', $this->dentalService);
        $this->validate();

        $this->dentalService->update([
            'name' => $this->name,
            'dental_service_type_id' => $this->dental_service_type_id,
            'price' => $this->price,
        ]);

        session()->flash('success', 'Dental service updated successfully!');

        return $this->redirect(route('dental-services.index'), navigate: true);
    }

    public function render()
    {
        $this->authorize('update', $this->dentalService);
        return view('livewire.dental-services.update-page', [
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