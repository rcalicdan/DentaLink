<?php

namespace App\Livewire\Inventories;

use App\Enums\InventoryCategory;
use App\Models\Branch;
use App\Models\Inventory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class CreatePage extends Component
{
    public $name = '';
    public $category = '';
    public $current_stock = 0;
    public $minimum_stock = 10;
    public $branch_id = '';

    public function mount()
    {
        if (!Auth::user()->isSuperadmin()) {
            $this->branch_id = Auth::user()->branch_id;
        }
    }

    public function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'category' => ['required', Rule::in(array_column(InventoryCategory::cases(), 'value'))],
            'current_stock' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'branch_id' => 'required|exists:branches,id',
        ];

        if (!Auth::user()->isSuperadmin()) {
            $rules['branch_id'] .= '|in:' . Auth::user()->branch_id;
        }

        return $rules;
    }

    public function save()
    {
        $this->authorize('create', Inventory::class);
        $this->validate();

        Inventory::create([
            'name' => $this->name,
            'category' => $this->category,
            'current_stock' => $this->current_stock,
            'minimum_stock' => $this->minimum_stock,
            'branch_id' => $this->branch_id,
        ]);

        session()->flash('success', 'Inventory item created successfully!');
        return $this->redirect(route('inventory.index'), navigate: true);
    }

    public function render()
    {
        $this->authorize('create', Inventory::class);
        return view('livewire.inventories.create-page', [
            'categoryOptions' => $this->getCategoryOptions(),
            'branchOptions' => $this->getBranchOptions(),
            'isSuperadmin' => Auth::user()->isSuperadmin(),
        ]);
    }

    private function getCategoryOptions()
    {
        $options = ['' => 'Select a category'];
        foreach (InventoryCategory::cases() as $case) {
            $options[$case->value] = $case->name;
        }
        return $options;
    }

    private function getBranchOptions()
    {
        if (!Auth::user()->isSuperadmin()) {
            $userBranch = Auth::user()->branch;
            return $userBranch ? [$userBranch->id => $userBranch->name] : [];
        }

        return Branch::orderBy('name')->pluck('name', 'id')->prepend('Select a branch', '');
    }
}