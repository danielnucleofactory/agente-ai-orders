<?php

namespace App\Livewire\Partials;

use Livewire\Component;

class CompanySelector extends Component
{
    public $selectedCompany;
    public $companies = [];

    public function mount()
    {
        $user = auth()->user();
        $this->companies = $user->companies
            ->sortBy(fn ($c) => mb_strtolower($c->name ?? '', 'UTF-8'), SORT_NATURAL)
            ->values();

        // Get the current company ID from the user
        $this->selectedCompany = $user->getCurrentCompanyId();

        // If no company is selected yet, set the first one
        if (!$this->selectedCompany && $this->companies->isNotEmpty()) {
            $this->selectedCompany = $this->companies->first()->id;
            $user->setCurrentCompany($this->selectedCompany);
        }
    }

    public function updatedSelectedCompany($value)
    {
        $user = auth()->user();

        // Update the user's company_id
        if ($user->setCurrentCompany($value)) {
            // Emit event to notify other components
            $this->dispatch('company-changed', companyId: $value);

            // Refresh the page to reload all components with the new company
            $this->redirect(request()->header('Referer') ?: route('dashboard'), navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.partials.company-selector');
    }
}
