<?php

namespace App\Http\Controllers\Admin;

use App\Models\Tournament;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class TournamentController extends Controller
{
    /**
     * Display tournaments listing
     */
    public function index(): View
    {
        $tournaments = Tournament::withCount('players')
            ->latest()
            ->paginate(15);

        return view('admin.tournaments.index', ['tournaments' => $tournaments]);
    }

    /**
     * Show tournament creation form
     */
    public function create(): View
    {
        $tournament = new Tournament();

        return view('admin.tournaments.create', ['tournament' => $tournament]);
    }

    /**
     * Store new tournament
     */
    public function store(Request $request): RedirectResponse
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'game_type' => ['required', 'in:poker,blot'],
            'max_players' => ['required', 'integer', 'min:2', 'max:1000'],
            'prize_pool' => ['required', 'numeric', 'min:0'],
            'buy_in' => ['required', 'numeric', 'min:0'],
            'registration_opens_at' => ['required', 'date'],
            'starts_at' => ['required', 'date', 'after:registration_opens_at'],
            'status' => ['required', 'in:draft,registration_open,registration_closed,in_progress,completed,cancelled'],
        ], [
            'name.required' => 'Tournament name is required.',
            'starts_at.after' => 'Start date must be after registration opens date.',
            'buy_in.min' => 'Buy-in must be a positive number.',
            'prize_pool.min' => 'Prize pool must be a positive number.',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Tournament::create($validator->validated());

        return redirect()->route('admin.tournaments.index')
            ->with('success', 'Tournament created successfully.');
    }

    /**
     * Show tournament edit form
     */
    public function edit(Tournament $tournament): View
    {
        $tournamentStats = $this->getTournamentStats($tournament);

        return view('admin.tournaments.edit', [
            'tournament' => $tournament,
            'tournamentStats' => $tournamentStats
        ]);
    }

    /**
     * Update tournament
     */
    public function update(Request $request, Tournament $tournament): RedirectResponse
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'game_type' => ['required', 'in:poker,blot'],
            'max_players' => ['required', 'integer', 'min:2', 'max:1000'],
            'prize_pool' => ['required', 'numeric', 'min:0'],
            'buy_in' => ['required', 'numeric', 'min:0'],
            'registration_opens_at' => ['required', 'date'],
            'starts_at' => ['required', 'date', 'after:registration_opens_at'],
            'status' => ['required', 'in:draft,registration_open,registration_closed,in_progress,completed,cancelled'],
        ], [
            'name.required' => 'Tournament name is required.',
            'starts_at.after' => 'Start date must be after registration opens date.',
            'buy_in.min' => 'Buy-in must be a positive number.',
            'prize_pool.min' => 'Prize pool must be a positive number.',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $tournament->update($validator->validated());

        return redirect()->route('admin.tournaments.index')
            ->with('success', 'Tournament updated successfully.');
    }

    /**
     * Delete tournament
     */
    public function destroy(Tournament $tournament): RedirectResponse
    {
        try {
            // Check if tournament can be deleted
            if ($tournament->status === Tournament::STATUS_IN_PROGRESS) {
                return redirect()->back()
                    ->with('error', 'Cannot delete tournament that is in progress.');
            }
            
            if ($tournament->players()->exists()) {
                // Refund players if needed
                // TODO: Implement refund logic
            }
            
            $tournament->delete();

            return redirect()->route('admin.tournaments.index')
                ->with('success', 'Tournament deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show tournament details
     */
    public function show(Tournament $tournament): View
    {
        $tournament->load(['players' => function ($query) {
            $query->select('users.id', 'users.username', 'users.avatar');
        }]);

        $tournamentStats = $this->getTournamentStats($tournament);

        return view('admin.tournaments.show', [
            'tournament' => $tournament,
            'tournamentStats' => $tournamentStats
        ]);
    }

    /**
     * Start tournament
     */
    public function start(Tournament $tournament): RedirectResponse
    {
        try {
            if ($tournament->status !== Tournament::STATUS_REGISTRATION_OPEN) {
                return redirect()->back()
                    ->with('error', 'Tournament registration is not open.');
            }

            if ($tournament->players()->count() < 2) {
                return redirect()->back()
                    ->with('error', 'At least 2 players required to start tournament.');
            }

            $tournament->update([
                'status' => Tournament::STATUS_IN_PROGRESS,
                'started_at' => now(),
            ]);

            return redirect()->back()
                ->with('success', 'Tournament started successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel tournament
     */
    public function cancel(Request $request, Tournament $tournament): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'reason' => ['required', 'string', 'max:255'],
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            if ($tournament->status === Tournament::STATUS_COMPLETED) {
                return redirect()->back()
                    ->with('error', 'Cannot cancel completed tournament.');
            }

            $tournament->update([
                'status' => Tournament::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'cancellation_reason' => $validator->validated()['reason'],
            ]);

            // TODO: Implement refund logic for players

            return redirect()->back()
                ->with('success', 'Tournament cancelled successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Get tournament statistics
     */
    private function getTournamentStats(Tournament $tournament): array
    {
        return [
            'total_players' => $tournament->players()->count(),
            'total_prize_pool' => $tournament->prize_pool,
            'registration_progress' => $tournament->players()->count() . '/' . $tournament->max_players,
            'estimated_start_time' => $tournament->starts_at,
        ];
    }
}