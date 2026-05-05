<?php

namespace App\Livewire;

use App\Models\DataQualityJustification;
use App\Models\GmsAlbum;
use App\Models\GmsUserPreference;
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
    protected $listeners = ['progressOnlyModeChanged' => 'handleProgressOnlyModeChanged'];

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

    public bool $progressOnlyMode = false;

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

    // Scoring properties
    public float $geotagScore = 0;

    public float $progressAlbumScore = 0;

    public array $applicable = [];

    public int $basedPhotosWeight = 10;

    public float $maxScore = 0;

    public float $achieved = 0;

    public float $totalScore = 0;

    public float $basicScore = 0;

    public float $progressScore = 0;

    public function mount(string $spId): void
    {
        $this->spId = $spId;

        // Load user's progress only preference
        $preference = GmsUserPreference::firstOrCreate(
            ['user_id' => Auth::id()],
            ['progress_only' => false]
        );
        $this->progressOnlyMode = $preference->progress_only;

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
        $this->computeScores();

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

    // private function computeAnalytics(): void
    // {
    //     $this->analytics = [];

    //     // Analyze GMS Album Compliance only
    //     if (! empty($this->sidlanData)) {
    //         $compliance = [
    //             'issues' => [],
    //         ];

    //         $stage = strtolower($this->sidlanData['stage'] ?? '');

    //         // GMS Album Compliance Issues - specific months with insufficient geotags
    //         foreach ($this->progressAnalytics['months_with_insufficient_geotags'] as $month) {
    //             if (! in_array('insufficient_geotags_'.$month, $this->justifications)) {
    //                 try {
    //                     $formattedMonth = Carbon::createFromFormat('Y-m', $month)->format('F Y');
    //                 } catch (\Exception $e) {
    //                     $formattedMonth = $month;
    //                 }
    //                 $compliance['issues'][] = ['type' => 'insufficient_geotags_'.$month, 'text' => $formattedMonth.' has insufficient geotags (need ≥500)'];
    //             }
    //         }

    //         // Missing albums for specific months with progress
    //         foreach ($this->monthsWithProgressNoAlbum as $month) {
    //             if (! in_array('missing_album_'.$month, $this->justifications)) {
    //                 try {
    //                     $formattedMonth = Carbon::createFromFormat('Y-m', $month)->format('F Y');
    //                 } catch (\Exception $e) {
    //                     $formattedMonth = $month;
    //                 }
    //                 $compliance['issues'][] = ['type' => 'missing_album_'.$month, 'text' => 'Missing album for '.$formattedMonth];
    //             }
    //         }

    //         // Calculate GMS Album Compliance score (100% weight)
    //         $album_score = 0;

    //         // Based Photos: 20% if present or justified
    //         $based_ok = $this->hasBasedPhotos || in_array('based_photos_missing', $this->justifications);
    //         if ($based_ok) {
    //             $album_score += 20;
    //         }

    //         // Completed Album: 10% only if stage is completed and present, or justified
    //         $completed_ok = (strtolower($stage) === 'completed' && $this->hasCompleted) || in_array('completed_album_missing', $this->justifications);
    //         if ($completed_ok) {
    //             $album_score += 10;
    //         }

    //         // Geotag compliance: 30% proportional to months with ≥500 geotags
    //         if ($this->progressAnalytics['total_months_with_progress'] > 0) {
    //             $hasUnjustifiedGeotagIssues = ! empty(array_filter($this->progressAnalytics['months_with_insufficient_geotags'], function ($month) {
    //                 return ! in_array('insufficient_geotags_'.$month, $this->justifications) && ! in_array('gms_album_compliance', $this->justifications);
    //             }));

    //             if ($hasUnjustifiedGeotagIssues) {
    //                 $geotagScore = ($this->progressAnalytics['progress_months_with_sufficient_geotags'] / $this->progressAnalytics['total_months_with_progress']) * 30;
    //             } else {
    //                 $geotagScore = 30; // no unjustified issues = full score
    //             }
    //             $album_score += $geotagScore;
    //         }

    //         // Progress album compliance: 50% proportional to months with albums
    //         $progressScore = 0; // Initialize to avoid undefined variable error
    //         if ($this->progressAnalytics['total_months_with_progress'] > 0) {
    //             $hasUnjustifiedAlbumIssues = ! empty(array_filter($this->monthsWithProgressNoAlbum, function ($month) {
    //                 return ! in_array('missing_album_'.$month, $this->justifications);
    //             }));

    //             if ($hasUnjustifiedAlbumIssues) {
    //                 $progressScore = ($this->progressAnalytics['progress_with_albums'] / $this->progressAnalytics['total_months_with_progress']) * 50;
    //             } else {
    //                 $progressScore = 50; // no unjustified issues = full score
    //             }
    //             $album_score += $progressScore;
    //         }
    //         $compliance['progress_score'] = round($progressScore, 1);

    //         $compliance['album_score'] = $album_score;

    //         // GMS Album Compliance is now 100% of the score
    //         $compliance['overall_pct'] = $album_score;

    //         $this->analytics[$this->sidlanData['sp_id'] ?? 'unknown'] = $compliance;
    //     }
    // }

    // private function computeScores(): void
    // {
    //     // Scoring breakdown
    //     $progressMonths = $this->progressAnalytics['total_months_with_progress'] ?? 0;
    //     $albumsMonths = $this->progressAnalytics['progress_with_albums'] ?? 0;
    //     $sufficientGeotagsMonths = $this->progressAnalytics['progress_months_with_sufficient_geotags'] ?? 0;

    //     // Calculate scores rounded to 2 decimal places for consistency
    //     $this->geotagScore = $progressMonths > 0 ? round(($sufficientGeotagsMonths / $progressMonths) * 30, 2) : 0;
    //     $this->progressAlbumScore = $progressMonths > 0 ? round(($albumsMonths / $progressMonths) * 50, 2) : 0;

    //     // Determine applicable components
    //     $this->applicable = [
    //         'based_photos' => true,
    //         'completed_album' => strtolower($this->stage) === 'completed',
    //         'geotag' => $progressMonths > 0,
    //         'progress_album' => $progressMonths > 0,
    //     ];

    //     // Calculate weights based on stage
    //     $this->basedPhotosWeight = strtolower($this->stage) === 'construction' ? 20 : 10;

    //     // Calculate max possible score based on applicable components
    //     $this->maxScore = 0;
    //     if ($this->applicable['based_photos']) {
    //         $this->maxScore += $this->basedPhotosWeight;
    //     }
    //     if ($this->applicable['completed_album']) {
    //         $this->maxScore += 10;
    //     }
    //     if ($this->applicable['geotag']) {
    //         $this->maxScore += 30;
    //     }
    //     if ($this->applicable['progress_album']) {
    //         $this->maxScore += 50;
    //     }

    //     // Calculate achieved score
    //     $this->achieved = 0;
    //     if ($this->hasBasedPhotos) {
    //         $this->achieved += $this->basedPhotosWeight;
    //     }
    //     if ($this->applicable['completed_album'] && $this->hasCompleted) {
    //         $this->achieved += 10;
    //     }
    //     $this->achieved += $this->geotagScore;
    //     $this->achieved += $this->progressAlbumScore;

    //     // Calculate total score as percentage
    //     $this->totalScore = $this->maxScore > 0 ? round(($this->achieved / $this->maxScore) * 100, 2) : 0;

    //     // Additional scores for breakdown
    //     $this->basicScore = ($this->hasBasedPhotos ? $this->basedPhotosWeight : 0) + (($this->applicable['completed_album'] && $this->hasCompleted) ? 10 : 0);
    //     $this->progressScore = $this->geotagScore + $this->progressAlbumScore;
    // }

    private function computeAnalytics(): void
    {
        $this->analytics = [];

        if (empty($this->sidlanData)) {
            return;
        }

        $compliance = [
            'issues' => [],
        ];

        $stage = strtolower($this->sidlanData['stage'] ?? '');
        $progressMonths = $this->progressAnalytics['total_months_with_progress'] ?? 0;

        /**
         * -----------------------------
         * 🚨 ISSUE DETECTION (UNCHANGED)
         * -----------------------------
         */

        // Insufficient geotags
        foreach ($this->progressAnalytics['months_with_insufficient_geotags'] as $month) {
            if (! in_array('insufficient_geotags_'.$month, $this->justifications)) {
                try {
                    $formattedMonth = Carbon::createFromFormat('Y-m', $month)->format('F Y');
                } catch (\Exception $e) {
                    $formattedMonth = $month;
                }

                $compliance['issues'][] = [
                    'type' => 'insufficient_geotags_'.$month,
                    'text' => $formattedMonth.' has insufficient geotags (need ≥500)',
                ];
            }
        }

        // Missing albums
        foreach ($this->monthsWithProgressNoAlbum as $month) {
            if (! in_array('missing_album_'.$month, $this->justifications)) {
                try {
                    $formattedMonth = Carbon::createFromFormat('Y-m', $month)->format('F Y');
                } catch (\Exception $e) {
                    $formattedMonth = $month;
                }

                $compliance['issues'][] = [
                    'type' => 'missing_album_'.$month,
                    'text' => 'Missing album for '.$formattedMonth,
                ];
            }
        }

        /**
         * -----------------------------
         * 🎯 SCORING
         * -----------------------------
         */
        if ($progressMonths <= 0) {
            $compliance['album_score'] = 0;
            $compliance['overall_pct'] = 0;

            $this->analytics[$this->sidlanData['sp_id'] ?? 'unknown'] = $compliance;

            return;
        }

        $album_score = 0;

        if ($this->progressOnlyMode) {

            /**
             * 🔵 PROGRESS-ONLY MODE
             * (Geotag 30%, Progress Albums 70%)
             */

            // Geotag
            $hasUnjustifiedGeotagIssues = ! empty(array_filter(
                $this->progressAnalytics['months_with_insufficient_geotags'],
                fn ($m) => ! $this->isJustified('insufficient_geotags_'.$m)
            ));

            $justifiedGeotagCount = count(array_filter(
                $this->progressAnalytics['months_with_insufficient_geotags'],
                fn ($m) => $this->isJustified('insufficient_geotags_'.$m)
            ));

            $effectiveSufficientGeotags = $this->progressAnalytics['progress_months_with_sufficient_geotags'] + $justifiedGeotagCount;

            $geotagScore = $hasUnjustifiedGeotagIssues
                ? ($effectiveSufficientGeotags / $progressMonths) * 30
                : 30;

            // Progress Albums
            $hasUnjustifiedAlbumIssues = ! empty(array_filter(
                $this->monthsWithProgressNoAlbum,
                fn ($m) => ! $this->isJustified('missing_album_'.$m)
            ));

            $justifiedAlbumCount = count(array_filter(
                $this->monthsWithProgressNoAlbum,
                fn ($m) => $this->isJustified('missing_album_'.$m)
            ));

            $effectiveAlbums = $this->progressAnalytics['progress_with_albums'] + $justifiedAlbumCount;

            $progressScore = $hasUnjustifiedAlbumIssues
                ? ($effectiveAlbums / $progressMonths) * 70
                : 70;

            $album_score = $geotagScore + $progressScore;

            $compliance['geotag_score'] = round($geotagScore, 1);
            $compliance['progress_score'] = round($progressScore, 1);
        } else {

            /**
             * 🟢 DEFAULT MODE
             */

            // 🎯 Dynamic weights
            if ($stage === 'construction') {
                $basedWeight = 20;
                $completedWeight = 0;
            } else {
                $basedWeight = 10;
                $completedWeight = 10;
            }

            // Based Photos
            $based_ok = $this->hasBasedPhotos || in_array('based_photos_missing', $this->justifications);
            if ($based_ok) {
                $album_score += $basedWeight;
            }

            // Completed Album
            if ($completedWeight > 0) {
                $completed_ok =
                    ($stage === 'completed' && $this->hasCompleted) ||
                    in_array('completed_album_missing', $this->justifications);

                if ($completed_ok) {
                    $album_score += $completedWeight;
                }
            }

            // Geotag (30%)
            $hasUnjustifiedGeotagIssues = ! empty(array_filter(
                $this->progressAnalytics['months_with_insufficient_geotags'],
                fn ($m) => ! $this->isJustified('insufficient_geotags_'.$m)
            ));

            $justifiedGeotagCount = count(array_filter(
                $this->progressAnalytics['months_with_insufficient_geotags'],
                fn ($m) => $this->isJustified('insufficient_geotags_'.$m)
            ));

            $effectiveSufficientGeotags = $this->progressAnalytics['progress_months_with_sufficient_geotags'] + $justifiedGeotagCount;

            $geotagScore = $hasUnjustifiedGeotagIssues
                ? ($effectiveSufficientGeotags / $progressMonths) * 30
                : 30;

            $album_score += $geotagScore;

            // Progress Albums (50%)
            $hasUnjustifiedAlbumIssues = ! empty(array_filter(
                $this->monthsWithProgressNoAlbum,
                fn ($m) => ! $this->isJustified('missing_album_'.$m)
            ));

            $justifiedAlbumCount = count(array_filter(
                $this->monthsWithProgressNoAlbum,
                fn ($m) => $this->isJustified('missing_album_'.$m)
            ));

            $effectiveAlbums = $this->progressAnalytics['progress_with_albums'] + $justifiedAlbumCount;

            $progressScore = $hasUnjustifiedAlbumIssues
                ? ($effectiveAlbums / $progressMonths) * 50
                : 50;

            $album_score += $progressScore;

            $compliance['geotag_score'] = round($geotagScore, 1);
            $compliance['progress_score'] = round($progressScore, 1);
        }

        /**
         * -----------------------------
         * 📊 FINAL OUTPUT
         * -----------------------------
         */
        $compliance['album_score'] = round($album_score, 2);
        $compliance['overall_pct'] = min(100, round($album_score, 2));

        $this->analytics[$this->sidlanData['sp_id'] ?? 'unknown'] = $compliance;
    }

    private function computeScores(): void
    {
        $progressMonths = $this->progressAnalytics['total_months_with_progress'] ?? 0;
        $albumsMonths = $this->progressAnalytics['progress_with_albums'] ?? 0;
        $sufficientGeotagsMonths = $this->progressAnalytics['progress_months_with_sufficient_geotags'] ?? 0;

        // Avoid division by zero
        if ($progressMonths <= 0) {
            $this->geotagScore = 0;
            $this->progressAlbumScore = 0;
            $this->totalScore = 0;

            return;
        }

        if ($this->progressOnlyMode) {
            // ✅ NEW MODE: Progress-only (30% / 70%)

            // Check for unjustified geotag issues
            $hasUnjustifiedGeotagIssues = ! empty(array_filter(
                $this->progressAnalytics['months_with_insufficient_geotags'],
                fn ($m) => ! $this->isJustified('insufficient_geotags_'.$m)
            ));

            $justifiedGeotagCount = count(array_filter(
                $this->progressAnalytics['months_with_insufficient_geotags'],
                fn ($m) => $this->isJustified('insufficient_geotags_'.$m)
            ));

            $effectiveSufficientGeotags = $this->progressAnalytics['progress_months_with_sufficient_geotags'] + $justifiedGeotagCount;

            $this->geotagScore = $hasUnjustifiedGeotagIssues
                ? ($effectiveSufficientGeotags / $progressMonths) * 30
                : 30;

            // Check for unjustified album issues
            $hasUnjustifiedAlbumIssues = ! empty(array_filter(
                $this->monthsWithProgressNoAlbum,
                fn ($m) => ! $this->isJustified('missing_album_'.$m)
            ));

            $justifiedAlbumCount = count(array_filter(
                $this->monthsWithProgressNoAlbum,
                fn ($m) => $this->isJustified('missing_album_'.$m)
            ));

            $effectiveAlbums = $this->progressAnalytics['progress_with_albums'] + $justifiedAlbumCount;

            $this->progressAlbumScore = $hasUnjustifiedAlbumIssues
                ? ($effectiveAlbums / $progressMonths) * 70
                : 70;

            $this->maxScore = 100;
            $this->achieved = $this->geotagScore + $this->progressAlbumScore;
            $this->totalScore = round($this->achieved, 2);

            // Optional breakdown
            $this->basicScore = 0;
            $this->progressScore = $this->achieved;
        } else {
            // ✅ EXISTING MODE (updated to account for justifications)

            // Check for unjustified geotag issues
            $hasUnjustifiedGeotagIssues = ! empty(array_filter(
                $this->progressAnalytics['months_with_insufficient_geotags'],
                fn ($m) => ! $this->isJustified('insufficient_geotags_'.$m)
            ));

            $justifiedGeotagCount = count(array_filter(
                $this->progressAnalytics['months_with_insufficient_geotags'],
                fn ($m) => $this->isJustified('insufficient_geotags_'.$m)
            ));

            $effectiveSufficientGeotags = $this->progressAnalytics['progress_months_with_sufficient_geotags'] + $justifiedGeotagCount;

            $this->geotagScore = $hasUnjustifiedGeotagIssues
                ? ($effectiveSufficientGeotags / $progressMonths) * 30
                : 30;

            // Check for unjustified album issues
            $hasUnjustifiedAlbumIssues = ! empty(array_filter(
                $this->monthsWithProgressNoAlbum,
                fn ($m) => ! $this->isJustified('missing_album_'.$m)
            ));

            $justifiedAlbumCount = count(array_filter(
                $this->monthsWithProgressNoAlbum,
                fn ($m) => $this->isJustified('missing_album_'.$m)
            ));

            $effectiveAlbums = $this->progressAnalytics['progress_with_albums'] + $justifiedAlbumCount;

            $this->progressAlbumScore = $hasUnjustifiedAlbumIssues
                ? ($effectiveAlbums / $progressMonths) * 50
                : 50;

            $this->applicable = [
                'based_photos' => true,
                'completed_album' => strtolower($this->stage) === 'completed',
                'geotag' => true,
                'progress_album' => true,
            ];

            $this->basedPhotosWeight = strtolower($this->stage) === 'construction' ? 20 : 10;

            $this->maxScore = 0;
            if ($this->applicable['based_photos']) {
                $this->maxScore += $this->basedPhotosWeight;
            }
            if ($this->applicable['completed_album']) {
                $this->maxScore += 10;
            }
            if ($this->applicable['geotag']) {
                $this->maxScore += 30;
            }
            if ($this->applicable['progress_album']) {
                $this->maxScore += 50;
            }

            $this->achieved = 0;

            // Include justifications for basic components
            $based_ok = $this->hasBasedPhotos || in_array('based_photos_missing', $this->justifications);
            if ($based_ok) {
                $this->achieved += $this->basedPhotosWeight;
            }

            if ($this->applicable['completed_album']) {
                $completed_ok = $this->hasCompleted || in_array('completed_album_missing', $this->justifications);
                if ($completed_ok) {
                    $this->achieved += 10;
                }
            }

            $this->achieved += $this->geotagScore;
            $this->achieved += $this->progressAlbumScore;

            $this->totalScore = $this->maxScore > 0
                ? round(($this->achieved / $this->maxScore) * 100, 2)
                : 0;

            // Include justifications in basic score calculation
            $this->basicScore =
                ($based_ok ? $this->basedPhotosWeight : 0) +
                (($this->applicable['completed_album'] && ($this->hasCompleted || in_array('completed_album_missing', $this->justifications))) ? 10 : 0);

            $this->progressScore = $this->geotagScore + $this->progressAlbumScore;
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

    public function updatedProgressOnlyMode()
    {
        // Save the preference
        $preference = GmsUserPreference::firstOrCreate(
            ['user_id' => Auth::id()],
            ['progress_only' => false]
        );
        $preference->update(['progress_only' => $this->progressOnlyMode]);

        $this->recalculate();
    }

    public function handleProgressOnlyModeChanged()
    {
        // Reload preference and recalculate
        $preference = GmsUserPreference::where('user_id', Auth::id())->first();
        $this->progressOnlyMode = $preference ? $preference->progress_only : false;
        $this->recalculate();
    }

    private function recalculate(): void
    {
        $this->computeProgressAnalytics();
        $this->computeAnalytics();
        $this->computeScores();
    }

    public function justifyIssue(string $type): void
    {
        $this->justifyingIssueType = $type;
        $this->justificationText = '';
    }

    public function getScore(SidlanProject $project, bool $progressOnlyMode = false): float
    {
        $this->sidlanData = $project->toArray();
        $this->spId = $project->sp_id;
        $this->stage = $project->stage ?? '';

        $this->progressOnlyMode = $progressOnlyMode;

        // Load SAME DATA as Livewire
        $this->allAlbums = $project->gmsAlbums->toArray();

        $this->fetchProgressData();
        $this->loadJustifications(); // 🔥 IMPORTANT
        $this->checkAlbumStatus();

        $this->computeProgressAnalytics();
        $this->computeAnalytics();
        $this->computeScores();

        return (float) $this->totalScore;
    }

    private function isJustified(string $type): bool
    {
        return in_array($type, $this->justifications);
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
        $this->computeScores(); // Recompute scores

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
            $this->computeScores();
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
