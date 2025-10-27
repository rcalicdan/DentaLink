<?php

namespace App\Console\Commands;

use App\Services\GeminiKnowledgeService;
use App\Models\User;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\DentalService;
use App\Models\PatientVisit;
use App\Models\KnowledgeBase;
use Illuminate\Console\Command;

class IndexKnowledgeBase extends Command
{
    protected $signature = 'knowledge:index 
                            {--type=all : Type to index (all, users, patients, appointments, services, visits)}
                            {--fresh : Drop and recreate all embeddings}';

    protected $description = 'Index database records into knowledge base with Gemini embeddings';

    private const CHUNK_SIZE = 50;
    private const DELAY_MICROSECONDS = 100000; // 100ms

    private GeminiKnowledgeService $service;

    public function handle(GeminiKnowledgeService $service)
    {
        $this->service = $service;

        if ($this->option('fresh')) {
            $this->clearExistingEmbeddings();
        }

        $type = $this->option('type');

        $this->indexEntities($type);
        $this->displaySummary();
    }

    private function clearExistingEmbeddings(): void
    {
        $this->info('Clearing existing embeddings...');
        KnowledgeBase::truncate();
    }

    private function indexEntities(string $type): void
    {
        $indexMethods = [
            'users' => 'indexUsers',
            'patients' => 'indexPatients',
            'appointments' => 'indexAppointments',
            'services' => 'indexDentalServices',
            'visits' => 'indexPatientVisits',
        ];

        foreach ($indexMethods as $entityType => $method) {
            if ($type === 'all' || $type === $entityType) {
                $this->$method();
            }
        }
    }

    private function indexUsers(): void
    {
        $this->indexEntityType(
            entityName: 'users',
            model: User::class,
            relations: ['branch'],
            indexMethod: 'indexUser'
        );
    }

    private function indexPatients(): void
    {
        $this->indexEntityType(
            entityName: 'patients',
            model: Patient::class,
            relations: [],
            indexMethod: 'indexPatient'
        );
    }

    private function indexAppointments(): void
    {
        $this->indexEntityType(
            entityName: 'appointments',
            model: Appointment::class,
            relations: ['patient', 'branch'],
            indexMethod: 'indexAppointment'
        );
    }

    private function indexDentalServices(): void
    {
        $this->indexEntityType(
            entityName: 'dental services',
            model: DentalService::class,
            relations: ['dentalServiceType'],
            indexMethod: 'indexDentalService'
        );
    }

    private function indexPatientVisits(): void
    {
        $this->indexEntityType(
            entityName: 'patient visits',
            model: PatientVisit::class,
            relations: ['patient', 'branch', 'patientVisitServices.dentalService'],
            indexMethod: 'indexPatientVisit'
        );
    }

    private function indexEntityType(
        string $entityName,
        string $model,
        array $relations,
        string $indexMethod
    ): void {
        $this->info("Indexing {$entityName}...");

        $count = $model::count();

        if ($count === 0) {
            $this->warn("No {$entityName} found to index.");
            return;
        }

        $bar = $this->output->createProgressBar($count);

        $query = empty($relations) ? $model::query() : $model::with($relations);

        $query->chunk(self::CHUNK_SIZE, function ($entities) use ($indexMethod, $bar, $entityName) {
            $this->processEntities($entities, $indexMethod, $bar, $entityName);
        });

        $bar->finish();
        $this->newLine();
        $this->info("✓ {$count} {$entityName} indexed!");
    }

    private function processEntities($entities, string $indexMethod, $bar, string $entityName): void
    {
        foreach ($entities as $entity) {
            try {
                $this->service->$indexMethod($entity);
                $bar->advance();
                usleep(self::DELAY_MICROSECONDS);
            } catch (\Exception $e) {
                $this->error("\nFailed to index {$entityName} {$entity->id}: {$e->getMessage()}");
            }
        }
    }

    private function displaySummary(): void
    {
        $this->newLine();
        $this->info('✓ Knowledge base indexing complete!');

        $totalEmbeddings = KnowledgeBase::count();
        $this->info("Total embeddings in database: {$totalEmbeddings}");

        $this->displayEmbeddingsByType();
    }

    private function displayEmbeddingsByType(): void
    {
        $this->newLine();
        $this->info('Embeddings by type:');

        $types = KnowledgeBase::select('entity_type')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('entity_type')
            ->get();

        foreach ($types as $type) {
            $this->line("  - {$type->entity_type}: {$type->count}");
        }
    }
}
