<?php

namespace App\Http\Controllers;

use App\Models\Ratting;
use App\Models\Complain;
use App\Models\Authority;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RatingController extends Controller
{
    public function submitRating(Request $request, $complainId): JsonResponse
    {
        $complain = Complain::findOrFail($complainId);

        if ($complain->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Not authorized.'], 403);
        }

        if ($complain->status !== Complain::STATUS_RESOLVED) {
            return response()->json([
                'success' => false,
                'message' => 'You can only rate a resolved complain.',
                'data'    => ['current_status' => $complain->status],
            ], 422);
        }

        $existingRating = Ratting::where('complain_id', $complainId)
                                  ->where('user_id', $request->user()->id)
                                  ->first();

        if ($existingRating) {
            return response()->json([
                'success' => false,
                'message' => 'You have already rated this complain.',
                'data'    => $existingRating,
            ], 422);
        }

        $request->validate([
            'response_speed_score' => 'required|integer|min:1|max:5',
            'comment'              => 'nullable|string|max:500',
        ]);

        $ratting = Ratting::create([
            'complain_id'          => $complainId,
            'user_id'              => $request->user()->id,
            'authority_id'         => $complain->auth_id,
            'response_speed_score' => $request->response_speed_score,
            'comment'              => $request->comment,
        ]);

        $authority = Authority::find($complain->auth_id);

        return response()->json([
            'success' => true,
            'message' => 'Rating submitted successfully.',
            'data'    => [
                'ratting'   => $ratting,
                'authority' => [
                    'id'             => $authority->id,
                    'name'           => $authority->name,
                    'average_rating' => $authority->average_rating,
                    'total_ratings'  => $authority->total_ratings,
                ],
            ],
        ], 201);
    }

    public function getAuthorityRatings($authorityId): JsonResponse
    {
        $authority = Authority::findOrFail($authorityId);

        $rattings = Ratting::with(['user', 'complain'])
                           ->where('authority_id', $authorityId)
                           ->latest()
                           ->paginate(10);

        return response()->json([
            'success' => true,
            'data'    => [
                'authority' => [
                    'id'             => $authority->id,
                    'name'           => $authority->name,
                    'average_rating' => $authority->average_rating,
                    'total_ratings'  => $authority->total_ratings,
                    'score_breakdown' => [
                        '5_stars' => Ratting::where('authority_id', $authorityId)->where('response_speed_score', 5)->count(),
                        '4_stars' => Ratting::where('authority_id', $authorityId)->where('response_speed_score', 4)->count(),
                        '3_stars' => Ratting::where('authority_id', $authorityId)->where('response_speed_score', 3)->count(),
                        '2_stars' => Ratting::where('authority_id', $authorityId)->where('response_speed_score', 2)->count(),
                        '1_star'  => Ratting::where('authority_id', $authorityId)->where('response_speed_score', 1)->count(),
                    ],
                ],
                'rattings' => $rattings,
            ],
        ], 200);
    }
}