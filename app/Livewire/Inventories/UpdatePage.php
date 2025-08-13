<?php

namespace App\Livewire\Inventories;

use App\Enums\InventoryCategory;
use App\Models\Branch;
use App\Models\Inventory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class UpdatePage extends Component
{
    public Inventory $inventory;
    public $name;
    public $category;
    public $current_stock;
    public $minimum_stock;
    public $branch_id;

    public function mount(Inventory $inventory)
    {
        $this->inventory = $inventory;
        $this->name = $inventory->name;
        $this->category = $inventory->category;
        $this->current_stock = $inventory->current_stock;
        $this->minimum_stock = $inventory->minimum_stock;
        $this->branch_id = $inventory->branch_id;

        if (!Auth::user()->isSuperadmin() && $inventory->branch_id !== Auth::user()->branch_id) {
            abort(403, 'You can only edit items in your branch.');
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

    public function update()
    {
        $this->authorize('update', $this->inventory);
        $this->validate();

        $this->inventory->update([
            'name' => $this->name,
            'category' => $this->category,
            'current_stock' => $this->current_stock,
            'minimum_stock' => $this->minimum_stock,
            'branch_id' => $this->branch_id,
        ]);

        session()->flash('success', 'Inventory item updated successfully!');
        return $this->redirect(route('inventory.index'), navigate: true);
    }

    public function render()
    {
        $this->authorize('update', $this->inventory);
        return view('livewire.inventories.update-page', [
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