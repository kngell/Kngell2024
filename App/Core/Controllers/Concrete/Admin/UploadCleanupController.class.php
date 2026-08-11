<?php

declare(strict_types=1);

class UploadCleanupController extends Controller
{
    public function __construct(
        private OrphanFileCleanupService $cleanupService,
    ) {
        $this->layout('admin');
    }

    public function index(): string
    {
        $uploadsDir = realpath(STORAGE . '/uploads');

        $stats = $this->cleanupService->getUploadStats($uploadsDir);
        $stats = $this->formatStats($stats);
        return $this->render('uploads/cleanup', [
            'stats' => $stats,
            'uploads_dir' => $uploadsDir,
            'max_age_days' => 7,
            'temp_max_age_hours' => 1,
        ]);
    }

    public function cleanup(): Response
    {
        $uploadsDir = realpath(STORAGE . '/uploads');

        $data = $this->request->getPost()->getAll();
        $dryRun = (bool) $this->request->getPost()->get('dry_run', true);
        $maxAgeDays = (int) $this->request->getPost()->get('max_age_days', 7);
        $cleanTemp = (bool) $this->request->getPost()->get('clean_temp', true);
        $tempMaxAgeHours = (int) $this->request->getPost()->get('temp_max_age_hours', 1);

        // Validate inputs
        $maxAgeDays = max(0, min($maxAgeDays, 365)); // Limit to 0-365 days
        $tempMaxAgeHours = max(0, min($tempMaxAgeHours, 720)); // Limit to 0-720 hours (30 days)

        $result = $this->cleanupService->cleanupOrphanFiles($uploadsDir, [
            'dry_run' => $dryRun,
            'max_age_days' => $maxAgeDays,
            'clean_temp_files' => $cleanTemp,
            'temp_max_age_hours' => $tempMaxAgeHours,
        ]);

        $resultArray = $result->toArray();
        $summary = $resultArray['summary'];

        // Format sizes for display
        $summary['deleted_size_formatted'] = $this->formatBytes($summary['deleted_size'] ?? 0);
        $summary['candidate_size_formatted'] = $this->formatBytes($summary['candidate_size'] ?? 0);

        if ($dryRun) {
            $this->flash->add(
                sprintf(
                    'Found %d files that would be deleted (%s)',
                    $summary['candidate_count'],
                    $summary['candidate_size_formatted'],
                ),
                FlashType::INFO,
            );

            // Store candidates in session for display
            $this->session->set('cleanup_candidates', $resultArray['candidates'] ?? []);
        } else {
            $this->flash->add(
                sprintf(
                    'Deleted %d files, freed %s',
                    $summary['deleted_count'],
                    $summary['deleted_size_formatted'],
                ),
                FlashType::SUCCESS,
            );

            // Show failed deletions if any
            if (!empty($resultArray['failed'])) {
                $failedCount = count($resultArray['failed']);
                $this->flash->add(
                    sprintf(
                        '%d files could not be deleted (permission issues)',
                        $failedCount,
                    ),
                    FlashType::WARNING,
                );
            }

            // Clear candidates from session
            $this->session->delete('cleanup_candidates');
        }

        // Store summary in session for the results page
        $this->session->set('cleanup_summary', $summary);
        $this->session->set('cleanup_dry_run', $dryRun);

        return $this->redirect('/upload-cleanup/results');
    }

    public function results(): string
    {
        $summary = $this->session->get('cleanup_summary', []);
        $dryRun = (bool) $this->session->get('cleanup_dry_run', true);
        $candidates = $this->session->get('cleanup_candidates', []);

        // Clear session data after displaying
        $this->session->delete('cleanup_summary');
        $this->session->delete('cleanup_dry_run');
        $this->session->delete('cleanup_candidates');

        return $this->render('uploads/cleanup_results', [
            'summary' => $summary,
            'dry_run' => $dryRun,
            'candidates' => $candidates,
        ]);
    }

    public function refresh(): Response
    {
        return $this->redirect('/upload-cleanup/index');
    }

    private function formatStats(array $stats): array
    {
        return [
            'total_files' => $stats['total_files'] ?? 0,
            'total_size' => $stats['total_size'] ?? 0,
            'total_size_formatted' => $this->formatBytes($stats['total_size'] ?? 0),
            'temp_files' => $stats['temp_files'] ?? 0,
            'temp_size' => $stats['temp_size'] ?? 0,
            'temp_size_formatted' => $this->formatBytes($stats['temp_size'] ?? 0),
            'orphan_candidates' => $stats['orphan_candidates'] ?? 0,
            'orphan_size' => $stats['orphan_size'] ?? 0,
            'orphan_size_formatted' => $this->formatBytes($stats['orphan_size'] ?? 0),
        ];
    }
}