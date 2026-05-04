<?php

namespace App\Livewire;

use App\Models\DataQualityJustification;
use App\Models\GmsAlbum;
use App\Models\SidlanProgress;
use App\Models\SidlanProject;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('GMS Album Compliance')]
class GmsCompliance extends Component
{
    public string $spId = '';

    public string $projectName = '';

    public string $stage = '';

    public array $allAlbums = [];

    public array $filteredAlbums = [];

    public bool $hasBasedPhotos = false;

    public bool $hasCompleted = false;

    public bool $loading = false;

    public bool $showIssuesAccordion = false;

    public array $expandedTimelineMonths = [];

    public string $error = '';

    public function toggleTimelineMonth($monthKey)
    {
        if (in_array($monthKey, $this->expandedTimelineMonths)) {
            $this->expandedTimelineMonths = array_filter($this->expandedTimelineMonths, function ($key) use ($monthKey) {
                return $key !== $monthKey;
            });
        } else {
            $this->expandedTimelineMonths[] = $monthKey;
        }
    }

    public array $sidlanData = [];

    public array $progressData = [];

    public array $analytics = [];

    public array $progressAnalytics = [];

    public array $monthsWithProgressNoAlbum = [];

    public string $alertMessage = '';

    public array $justifications = [];

    public array $auditTrail = [];

    public bool $showJustificationModal = false;

    public bool $showRatingDetails = true;

    public string $justifyingIssueType = '';

    public string $justificationText = '';

    public function mount(string $spId): void
    {
        $this->spId = $spId;

        $this->fetchSidlanData();

        if (empty($this->sidlanData)) {
            $this->error = 'Subproject not found';

            return;
        }

        // derive everything from SIDLAN data only
        $this->projectName = $this->sidlanData['project_name'] ?? '';
        $this->stage = $this->sidlanData['stage'] ?? '';

        $this->fetchAlbums();
        $this->fetchProgressData();

        $this->loadJustifications();
        $this->loadAuditTrail();
        $this->computeProgressAnalytics();
        $this->computeAnalytics();

        // Check for fix attempt
        if (Session::has('fix_attempted')) {
            Session::forget('fix_attempted');
            $spId = $this->sidlanData['sp_id'] ?? 'unknown';
            $complianceIssues = $this->analytics[$spId]['issues'] ?? [];
            $albumIssues = [];
            if (! $this->hasBasedPhotos && ! in_array('based_photos_missing', $this->justifications)) {
                $albumIssues[] = 'based_photos_missing';
            }
            if (strtolower($this->stage) === 'completed' && ! $this->hasCompleted && ! in_array('completed_album_missing', $this->justifications)) {
                $albumIssues[] = 'completed_album_missing';
            }
            if (! empty($complianceIssues) || ! empty($albumIssues)) {
                $this->alertMessage = 'No updated data has been fetched. Check SIDLAN or GMS if the data has been updated.';
                $this->js('alert("'.addslashes($this->alertMessage).'")');
            }
        }
    }

    public function fetchAlbums(): void
    {
        $this->loading = true;
        $this->error = '';

        try {
            // Fetch albums from database using GmsAlbum model
            $albums = GmsAlbum::where('sp_id', $this->spId)->get()->toArray();
            $this->allAlbums = $albums;
            $this->applyFilters();
            $this->checkAlbumStatus();
        } catch (\Throwable $e) {
            Log::error('GmsCompliance fetchAlbums error: '.$e->getMessage());
            $this->error = 'Something went wrong fetching albums.';
            $this->allAlbums = [];
            $this->filteredAlbums = [];
        } finally {
            $this->loading = false;
        }
    }

    protected function applyFilters(): void
    {
        $stage = strtolower($this->stage);

        $this->filteredAlbums = array_filter($this->allAlbums, function ($album) use ($stage) {
            $itemOfWork = isset($album['item_of_work']) ? strtolower($album['item_of_work']) : '';

            // Show albums with item_of_work = "Based Photos" regardless of stage
            if ($itemOfWork === 'based photos') {
                return true;
            }

            // Show albums with item_of_work = "Completed" only if stage = Completed
            if ($itemOfWork === 'completed' && $stage === 'completed') {
                return true;
            }

            return false;
        });

        // Re-index array
        $this->filteredAlbums = array_values($this->filteredAlbums);
    }

    protected function checkAlbumStatus(): void
    {
        $this->hasBasedPhotos = false;
        $this->hasCompleted = false;

        foreach ($this->allAlbums as $album) {
            $itemOfWork = isset($album['item_of_work']) ? strtolower($album['item_of_work']) : '';
            if ($itemOfWork === 'based photos') {
                $this->hasBasedPhotos = true;
            }
            if ($itemOfWork === 'completed') {
                $this->hasCompleted = true;
            }
        }
    }

    public function fetchSidlanData(): void
    {
        try {
            // Fetch data directly from SidlanProject model with relationships
            $project = SidlanProject::with(['annex', 'package'])->where('sp_id', $this->spId)->first();

            if ($project) {
                // Convert to array and ensure the structure matches the API JSON exactly
                $data = $project->toArray();

                // Ensure annex and package are properly structured
                if (isset($data['annex'])) {
                    // Add any missing fields from the API structure if needed
                    // The data should already match since we have all fields in fillable
                }

                if (isset($data['package'])) {
                    // Add any missing fields from the API structure if needed
                    // The data should already match since we have all fields in fillable
                }

                $this->sidlanData = $data;
            }
        } catch (\Throwable $e) {
            Log::error('GmsCompliance fetchSidlanData error: '.$e->getMessage());
            $this->sidlanData = [];
        }
    }

    public function fetchProgressData(): void
    {
        try {
            $this->progressData = [];

            // Fetch progress from SidlanProgress model
            $progress = SidlanProgress::where('sp_index', $this->sidlanData['sp_index'] ?? null)->first();

            if (! $progress) {
                return;
            }

            // Collect all unique months from progress accomplishmentDates and album report_dates
            $allMonths = [];

            // From progress accomplishmentDates
            $progressByMonth = [];
            foreach (($progress->accomplishment_dates ?? []) as $date) {
                $month = date('Y-m', strtotime($date));
                $allMonths[$month] = true;
                $progressByMonth[$month] = true;
            }

            // From album report_dates
            foreach ($this->allAlbums as $album) {
                if (($album['sp_id'] ?? null) !== $this->spId) {
                    continue;
                }
                if (empty($album['report_date'])) {
                    continue;
                }
                $timestamp = strtotime($album['report_date']);
                if (! $timestamp) {
                    continue;
                }
                $monthKey = date('Y-m', $timestamp);
                $allMonths[$monthKey] = true;
            }

            $months = array_keys($allMonths);

            // Sort latest month first
            usort($months, function ($a, $b) {
                return strtotime($b) <=> strtotime($a);
            });

            // Create month data with actual
            $monthData = [];
            foreach ($months as $month) {
                $actual = 0;

                // Check if there's progress for this month
                $progressDate = null;
                foreach (($progress->accomplishment_dates ?? []) as $date) {
                    if (date('Y-m', strtotime($date)) === $month) {
                        $progressDate = $date;
                        break;
                    }
                }

                if ($progressDate) {
                    $report = $progress->progress_report[$progressDate] ?? [];
                    $actualValue = $report['actual'] ?? 0;
                    if (is_numeric($actualValue)) {
                        $actual = (float) $actualValue;
                    }
                }

                $monthData[] = [
                    'month' => $month,
                    'actual' => $actual,
                    'has_progress' => isset($progressByMonth[$month]),
                ];
            }

            // Group albums by month
            $groupedAlbums = $this->mapAlbumsByMonth($this->allAlbums, $this->spId);

            // Add albums to each month
            foreach ($monthData as &$month) {
                $month['albums'] = $groupedAlbums[$month['month']] ?? [];
            }

            $this->progressData = [
                'sp_id' => $this->spId,
                'months' => $monthData,
            ];
        } catch (\Throwable $e) {
            Log::error('GmsCompliance fetchProgressData error: '.$e->getMessage());
            $this->progressData = [];
        }
    }

    protected function loadJustifications(): void
    {
        $this->justifications = DataQualityJustification::where('sp_id', $this->spId)->pluck('issue_type')->toArray();
    }

    protected function loadAuditTrail(): void
    {
        $this->auditTrail = DataQualityJustification::where('sp_id', $this->spId)
            ->with('user:id,name')
            ->withTrashed()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($justification) {
                $deletedBy = null;
                if ($justification->deleted_by) {
                    $deleter = User::find($justification->deleted_by);
                    $deletedBy = $deleter ? $deleter->name : 'Unknown';
                }

                return [
                    'id' => $justification->id,
                    'issue_type' => $justification->issue_type,
                    'justification' => $justification->justification_text,
                    'user' => $justification->user->name ?? 'Unknown',
                    'timestamp' => $justification->created_at->format('Y-m-d H:i:s'),
                    'deleted_at' => $justification->deleted_at,
                    'deleted_by' => $deletedBy,
                ];
            })
            ->toArray();
    }

    protected function mapAlbumsByMonth(array $albums, string $spId): array
    {
        $grouped = [];

        foreach ($albums as $album) {

            // ✅ FIX: use sp_id (not sp_index)
            if (($album['sp_id'] ?? null) !== $spId) {
                continue;
            }

            if (empty($album['report_date'])) {
                continue;
            }

            $timestamp = strtotime($album['report_date']);
            if (! $timestamp) {
                continue;
            }

            $monthKey = date('Y-m', $timestamp);

            $grouped[$monthKey][] = $album;
        }

        krsort($grouped);

        return $grouped;
    }

    private function computeAnalytics(): void
    {
        $this->analytics = [];

        // Analyze GMS Album Compliance only
        if (! empty($this->sidlanData)) {
            $compliance = [
                'issues' => [],
            ];

            $stage = strtolower($this->sidlanData['stage'] ?? '');

            // GMS Album Compliance Issues - specific months with insufficient geotags
            foreach ($this->progressAnalytics['months_with_insufficient_geotags'] as $month) {
                if (! in_array('insufficient_geotags_'.$month, $this->justifications)) {
                    try {
                        $formattedMonth = Carbon::createFromFormat('Y-m', $month)->format('F Y');
                    } catch (\Exception $e) {
                        $formattedMonth = $month;
                    }
                    $compliance['issues'][] = ['type' => 'insufficient_geotags_'.$month, 'text' => $formattedMonth.' has insufficient geotags (need ≥500)'];
                }
            }

            // Missing albums for specific months with progress
            foreach ($this->monthsWithProgressNoAlbum as $month) {
                if (! in_array('missing_album_'.$month, $this->justifications)) {
                    try {
                        $formattedMonth = Carbon::createFromFormat('Y-m', $month)->format('F Y');
                    } catch (\Exception $e) {
                        $formattedMonth = $month;
                    }
                    $compliance['issues'][] = ['type' => 'missing_album_'.$month, 'text' => 'Missing album for '.$formattedMonth];
                }
            }

            // Calculate GMS Album Compliance score (100% weight)
            $album_score = 0;

            // Based Photos: 20% if present or justified
            $based_ok = $this->hasBasedPhotos || in_array('based_photos_missing', $this->justifications);
            if ($based_ok) {
                $album_score += 20;
            }

            // Completed Album: 10% only if stage is completed and present, or justified
            $completed_ok = (strtolower($stage) === 'completed' && $this->hasCompleted) || in_array('completed_album_missing', $this->justifications);
            if ($completed_ok) {
                $album_score += 10;
            }

            // Geotag compliance: 30% proportional to months with ≥500 geotags
            if ($this->progressAnalytics['total_months_with_progress'] > 0) {
                $hasUnjustifiedGeotagIssues = ! empty(array_filter($this->progressAnalytics['months_with_insufficient_geotags'], function ($month) {
                    return ! in_array('insufficient_geotags_'.$month, $this->justifications) && ! in_array('gms_album_compliance', $this->justifications);
                }));

                if ($hasUnjustifiedGeotagIssues) {
                    $geotagScore = ($this->progressAnalytics['progress_months_with_sufficient_geotags'] / $this->progressAnalytics['total_months_with_progress']) * 30;
                } else {
                    $geotagScore = 30; // no unjustified issues = full score
                }
                $album_score += $geotagScore;
            }

            // Progress album compliance: 50% proportional to months with albums
            $progressScore = 0; // Initialize to avoid undefined variable error
            if ($this->progressAnalytics['total_months_with_progress'] > 0) {
                $hasUnjustifiedAlbumIssues = ! empty(array_filter($this->monthsWithProgressNoAlbum, function ($month) {
                    return ! in_array('missing_album_'.$month, $this->justifications);
                }));

                if ($hasUnjustifiedAlbumIssues) {
                    $progressScore = ($this->progressAnalytics['progress_with_albums'] / $this->progressAnalytics['total_months_with_progress']) * 50;
                } else {
                    $progressScore = 50; // no unjustified issues = full score
                }
                $album_score += $progressScore;
            }
            $compliance['progress_score'] = round($progressScore, 1);

            $compliance['album_score'] = $album_score;

            // GMS Album Compliance is now 100% of the score
            $compliance['overall_pct'] = $album_score;

            $this->analytics[$this->sidlanData['sp_id'] ?? 'unknown'] = $compliance;
        }
    }

    private function computeProgressAnalytics(): void
    {
        $this->progressAnalytics = [
            'total_months_with_progress' => 0,
            'progress_with_albums' => 0,
            'progress_months_with_sufficient_geotags' => 0,
            'months_with_insufficient_geotags' => [],
            'total_geotags' => 0,
            'required_geotags' => 0,
            'geotag_compliance' => 0,
        ];

        $this->monthsWithProgressNoAlbum = [];

        foreach ($this->progressData['months'] ?? [] as $month) {

            $actual = $month['actual'] ?? null;

            // only valid numeric progress
            if (! is_numeric($actual) || $actual <= 0) {
                continue;
            }

            $this->progressAnalytics['total_months_with_progress']++;

            $albums = $month['albums'] ?? [];

            if (! empty($albums)) {

                $this->progressAnalytics['progress_with_albums']++;

                $totalGeotags = 0;

                foreach ($albums as $album) {
                    $totalGeotags += (int) ($album['geotag_count'] ?? 0);
                }

                $this->progressAnalytics['total_geotags'] += $totalGeotags;

                if ($totalGeotags >= 500) {
                    $this->progressAnalytics['progress_months_with_sufficient_geotags']++;
                } else {
                    $this->progressAnalytics['months_with_insufficient_geotags'][] = $month['month'];
                }
            } else {
                $this->monthsWithProgressNoAlbum[] = $month['month'];
            }
        }

        $this->progressAnalytics['required_geotags'] = $this->progressAnalytics['total_months_with_progress'] * 500;

        if ($this->progressAnalytics['required_geotags'] > 0) {
            $this->progressAnalytics['geotag_compliance'] = round(
                ($this->progressAnalytics['total_geotags'] / $this->progressAnalytics['required_geotags']) * 100,
                2
            );
        }
    }

    public function justifyIssue(string $type): void
    {
        $this->justifyingIssueType = $type;
        $this->justificationText = '';
    }

    public function saveJustification(): void
    {
        if (empty(trim($this->justificationText))) {
            // Add error message if needed
            return;
        }

        DataQualityJustification::create([
            'sp_id' => $this->spId,
            'issue_type' => $this->justifyingIssueType,
            'justification_text' => $this->justificationText,
            'user_id' => Auth::id(),
        ]);

        $this->loadJustifications();
        $this->loadAuditTrail();
        $this->computeAnalytics(); // Recompute to update issues

        // Close the modal after saving
        $this->modal('justification-modal')->close();
    }

    public function deleteJustification(int $justificationId): void
    {
        $justification = DataQualityJustification::find($justificationId);

        if ($justification && $justification->sp_id === $this->spId) {
            $justification->deleted_by = Auth::id();
            $justification->save();
            $justification->delete(); // Soft delete
            $this->loadJustifications();
            $this->loadAuditTrail();
            $this->computeAnalytics();
        }
    }

    public function toggleRatingDetails(): void
    {
        $this->showRatingDetails = ! $this->showRatingDetails;
    }

    public function fixIssue(string $type): void
    {
        // Mark that fix was attempted and reload the page for external fix
        Session::put('fix_attempted', true);
        $this->js('window.location.reload()');
    }

    public function render()
    {
        return view('livewire.gms-compliance');
    }
}
