<?php

namespace App\Console\Commands;

use App\Services\GeminiKnowledgeService;
use App\Models\User;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\DentalService;
use App\Models\PatientVisit;
use Illuminate\Console\Command;

class IndexKnowledgeBase extends Command
{
    protected $signature = 'knowledge:index 
                            {--type=all : Type to index (all, users, patients, appointments, services, visits)}
                            {--fresh : Drop and recreate all embeddings}';
    
    protected $description = 'Index database records into knowledge base with Gemini embeddings';

    public function handle(GeminiKnowledgeService $service)
    {
        if ($this->option('fresh')) {
            $this->info('Clearing existing embeddings...');
            \App\Models\KnowledgeBase::truncate();
        }

        $type = $this->option('type');

        // INDEX USERS
        if ($type === 'all' || $type === 'users') {
            $this->info('Indexing users...');
            $count = User::count();
            
            if ($count > 0) {
                $bar = $this->output->createProgressBar($count);
                
                User::with('branch')->chunk(50, function ($users) use ($service, $bar) {
                    foreach ($users as $user) {
                        try {
                            $service->indexUser($user);
                            $bar->advance();
                            usleep(100000); // 100ms delay
                        } catch (\Exception $e) {
                            $this->error("\nFailed to index user {$user->id}: {$e->getMessage()}");
                        }
                    }
                });
                
                $bar->finish();
                $this->newLine();
                $this->info("✓ {$count} users indexed!");
            } else {
                $this->warn('No users found to index.');
            }
        }

        if ($type === 'all' || $type === 'patients') {
            $this->info('Indexing patients...');
            $count = Patient::count();
            
            if ($count > 0) {
                $bar = $this->output->createProgressBar($count);
                
                Patient::chunk(50, function ($patients) use ($service, $bar) {
                    foreach ($patients as $patient) {
                        try {
                            $service->indexPatient($patient);
                            $bar->advance();
                            usleep(100000);
                        } catch (\Exception $e) {
                            $this->error("\nFailed to index patient {$patient->id}: {$e->getMessage()}");
                        }
                    }
                });
                
                $bar->finish();
                $this->newLine();
                $this->info("✓ {$count} patients indexed!");
            } else {
                $this->warn('No patients found to index.');
            }
        }

        if ($type === 'all' || $type === 'appointments') {
            $this->info('Indexing appointments...');
            $count = Appointment::count();
            
            if ($count > 0) {
                $bar = $this->output->createProgressBar($count);
                
                Appointment::with(['patient', 'branch'])->chunk(50, function ($appointments) use ($service, $bar) {
                    foreach ($appointments as $appointment) {
                        try {
                            $service->indexAppointment($appointment);
                            $bar->advance();
                            usleep(100000);
                        } catch (\Exception $e) {
                            $this->error("\nFailed to index appointment {$appointment->id}: {$e->getMessage()}");
                        }
                    }
                });
                
                $bar->finish();
                $this->newLine();
                $this->info("✓ {$count} appointments indexed!");
            } else {
                $this->warn('No appointments found to index.');
            }
        }

        if ($type === 'all' || $type === 'services') {
            $this->info('Indexing dental services...');
            $count = DentalService::count();
            
            if ($count > 0) {
                $bar = $this->output->createProgressBar($count);
                
                DentalService::with('dentalServiceType')->chunk(50, function ($services) use ($service, $bar) {
                    foreach ($services as $dentalService) {
                        try {
                            $service->indexDentalService($dentalService);
                            $bar->advance();
                            usleep(100000);
                        } catch (\Exception $e) {
                            $this->error("\nFailed to index service {$dentalService->id}: {$e->getMessage()}");
                        }
                    }
                });
                
                $bar->finish();
                $this->newLine();
                $this->info("✓ {$count} dental services indexed!");
            } else {
                $this->warn('No dental services found to index.');
            }
        }

        if ($type === 'all' || $type === 'visits') {
            $this->info('Indexing patient visits...');
            $count = PatientVisit::count();
            
            if ($count > 0) {
                $bar = $this->output->createProgressBar($count);
                
                PatientVisit::with(['patient', 'branch', 'patientVisitServices.dentalService'])
                    ->chunk(50, function ($visits) use ($service, $bar) {
                        foreach ($visits as $visit) {
                            try {
                                $service->indexPatientVisit($visit);
                                $bar->advance();
                                usleep(100000);
                            } catch (\Exception $e) {
                                $this->error("\nFailed to index visit {$visit->id}: {$e->getMessage()}");
                            }
                        }
                    });
                
                $bar->finish();
                $this->newLine();
                $this->info("✓ {$count} patient visits indexed!");
            } else {
                $this->warn('No patient visits found to index.');
            }
        }

        $this->newLine();
        $this->info('✓ Knowledge base indexing complete!');
        
        $totalEmbeddings = \App\Models\KnowledgeBase::count();
        $this->info("Total embeddings in database: {$totalEmbeddings}");
        
        // Show breakdown by type
        $this->newLine();
        $this->info('Embeddings by type:');
        $types = \App\Models\KnowledgeBase::select('entity_type')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('entity_type')
            ->get();
            
        foreach ($types as $type) {
            $this->line("  - {$type->entity_type}: {$type->count}");
        }
    }
}