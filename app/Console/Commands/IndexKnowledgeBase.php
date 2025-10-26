<?php

namespace App\Console\Commands;

use App\Services\GeminiKnowledgeService;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\DentalService;
use App\Models\PatientVisit;
use Illuminate\Console\Command;

class IndexKnowledgeBase extends Command
{
    protected $signature = 'knowledge:index 
                            {--type=all : Type to index (all, patients, appointments, services, visits)}
                            {--fresh : Drop and recreate all embeddings}';
    
    protected $description = 'Index database records into knowledge base with Gemini embeddings';

    public function handle(GeminiKnowledgeService $service)
    {
        if ($this->option('fresh')) {
            $this->info('Clearing existing embeddings...');
            \App\Models\KnowledgeBase::truncate();
        }

        $type = $this->option('type');

        if ($type === 'all' || $type === 'patients') {
            $this->info('Indexing patients...');
            $count = Patient::count();
            $bar = $this->output->createProgressBar($count);
            
            Patient::chunk(50, function ($patients) use ($service, $bar) {
                foreach ($patients as $patient) {
                    $service->indexPatient($patient);
                    $bar->advance();
                }
            });
            
            $bar->finish();
            $this->newLine();
            $this->info("✓ {$count} patients indexed!");
        }

        if ($type === 'all' || $type === 'appointments') {
            $this->info('Indexing appointments...');
            $count = Appointment::count();
            $bar = $this->output->createProgressBar($count);
            
            Appointment::with(['patient', 'branch'])->chunk(50, function ($appointments) use ($service, $bar) {
                foreach ($appointments as $appointment) {
                    $service->indexAppointment($appointment);
                    $bar->advance();
                }
            });
            
            $bar->finish();
            $this->newLine();
            $this->info("✓ {$count} appointments indexed!");
        }

        if ($type === 'all' || $type === 'services') {
            $this->info('Indexing dental services...');
            $count = DentalService::count();
            $bar = $this->output->createProgressBar($count);
            
            DentalService::with('dentalServiceType')->chunk(50, function ($services) use ($service, $bar) {
                foreach ($services as $dentalService) {
                    $service->indexDentalService($dentalService);
                    $bar->advance();
                }
            });
            
            $bar->finish();
            $this->newLine();
            $this->info("✓ {$count} dental services indexed!");
        }

        if ($type === 'all' || $type === 'visits') {
            $this->info('Indexing patient visits...');
            $count = PatientVisit::count();
            $bar = $this->output->createProgressBar($count);
            
            PatientVisit::with(['patient', 'branch', 'patientVisitServices.dentalService'])
                ->chunk(50, function ($visits) use ($service, $bar) {
                    foreach ($visits as $visit) {
                        $service->indexPatientVisit($visit);
                        $bar->advance();
                    }
                });
            
            $bar->finish();
            $this->newLine();
            $this->info("✓ {$count} patient visits indexed!");
        }

        $this->newLine();
        $this->info('✓ Knowledge base indexing complete!');
        
        $totalEmbeddings = \App\Models\KnowledgeBase::count();
        $this->info("Total embeddings in database: {$totalEmbeddings}");
    }
}