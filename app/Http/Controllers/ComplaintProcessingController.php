<?php

namespace App\Http\Controllers;

use App\Mail\StatusChangedMail;
use App\Models\Complain;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;

class ComplaintProcessingController extends Controller
{
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $user     = $request->user();
        $complain = Complain::with(['user', 'authority', 'department'])->findOrFail($id);

        // Check authorization
        if (!$user->isEmployee() && !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only employees and admins can process complaints.',
            ], 403);
        }

        // Employee can only process complaints in their department
        if ($user->isEmployee()) {
            if ($complain->auth_id !== $user->authority_id ||
                $complain->department_id !== $user->department_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to process this complaint.',
                ], 403);
            }
        }

        // Check allowed transition
        $allowedNextStatus = Complain::STATUS_TRANSITIONS[$complain->status] ?? null;

        if (!$allowedNextStatus) {
            return response()->json([
                'success' => false,
                'message' => 'Complaint is already resolved and cannot be updated.',
                'data'    => [
                    'complain_number' => $complain->complain_number,
                    'current_status'  => $complain->status,
                ],
            ], 422);
        }

        $previousStatus   = $complain->status;
        $complain->status = $allowedNextStatus;

        if ($allowedNextStatus === Complain::STATUS_RESOLVED) {
            $complain->resolved_at = now();
        }

        $complain->save();

        // Notify user via email
        if ($complain->user && $complain->user->email) {
            try {
                Mail::to($complain->user->email)->send(new StatusChangedMail(
                    name:          $complain->user->name,
                    complainNumber:$complain->complain_number,
                    title:         $complain->title,
                    status:        $allowedNextStatus,
                ));
            } catch (\Exception $e) {
                // Log error but don't fail the request
                \Log::error('Failed to send status change email: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Complaint status updated from ' . $previousStatus . ' to ' . $allowedNextStatus,
            'data'    => [
                'id'              => $complain->id,
                'complain_number' => $complain->complain_number,
                'title'           => $complain->title,
                'previous_status' => $previousStatus,
                'current_status'  => $complain->status,
                'resolved_at'     => $complain->resolved_at,
                'user'            => [
                    'id'    => $complain->user->id,
                    'name'  => $complain->user->name,
                    'email' => $complain->user->email,
                ],
            ],
        ], 200);
    }

    public function getComplaintDetails(Request $request, $id): JsonResponse
    {
        $user     = $request->user();
        $complain = Complain::with([
            'user',
            'authority',
            'department',
            'attachments',
            'chat.messages.sender',
        ])->findOrFail($id);

        if (!$user->isEmployee() && !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        if ($user->isEmployee()) {
            if ($complain->auth_id !== $user->authority_id ||
                $complain->department_id !== $user->department_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not authorized to view this complaint.',
                ], 403);
            }
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'              => $complain->id,
                'complain_number' => $complain->complain_number,
                'title'           => $complain->title,
                'description'     => $complain->description,
                'status'          => $complain->status,
                'is_valid'        => $complain->is_valid,
                'assigned_level'  => $complain->assigned_level,
                'level_name'      => $complain->level_name,
                'next_status'     => Complain::STATUS_TRANSITIONS[$complain->status] ?? null,
                'submitted_at'    => $complain->created_at,
                'resolved_at'     => $complain->resolved_at,
                'user'            => [
                    'id'       => $complain->user->id,
                    'name'     => $complain->user->name,
                    'phone'    => $complain->user->phone,
                    'email'    => $complain->user->email,
                    'score'    => $complain->user->score,
                    'priority' => $complain->user->score >= 10 ? 'High' : ($complain->user->score >= 5 ? 'Medium' : 'Low'),
                ],
                'authority'       => [
                    'id'   => $complain->authority->id,
                    'name' => $complain->authority->name,
                ],
                'department'      => [
                    'id'   => $complain->department->id,
                    'name' => $complain->department->name,
                ],
                'attachments'     => $complain->attachments->map(function ($attachment) {
                    return [
                        'id'        => $attachment->id,
                        'file_path' => asset('storage/' . $attachment->file_path),
                        'file_type' => $attachment->file_type,
                    ];
                }),
                'chat'            => $complain->chat ? [
                    'chat_id'  => $complain->chat->id,
                    'is_open'  => $complain->chat->is_open,
                    'messages' => $complain->chat->messages->map(function ($message) {
                        return [
                            'id'        => $message->id,
                            'message'   => $message->message,
                            'file_path' => $message->file_path ? asset('storage/' . $message->file_path) : null,
                            'sent_at'   => $message->sent_at,
                            'sender'    => [
                                'id'   => $message->sender->id,
                                'name' => $message->sender->name,
                            ],
                        ];
                    }),
                ] : null,
            ],
        ], 200);
    }
}