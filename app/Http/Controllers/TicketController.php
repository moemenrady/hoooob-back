<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    public function index()
    {
        $tickets = Ticket::where('user_id', Auth::id())
            ->with(['user:id,full_name,profile_image', 'comments.user:id,full_name,profile_image'])
            ->get();

        $tickets = $tickets->map(function ($ticket) {
            // Build full URL for ticket owner profile image into the same field using asset()
            if ($ticket->user && $ticket->user?->profile_image) {
                $pi = $ticket->user->profile_image;
                if (Str::startsWith($pi, ['http://', 'https://'])) {
                    // already full URL
                    $ticket->user->profile_image = $pi;
                } elseif (Str::startsWith($pi, ['storage/', '/storage'])) {
                    $ticket->user->profile_image = asset(ltrim($pi, '/'));
                } else {
                    $ticket->user->profile_image = asset('storage/app/public/customer/profile/' . $pi);
                }
            }

            // Build full URL for each comment user's profile image into the same field using asset()
            if ($ticket->comments) {
                $ticket->comments->each(function ($comment) {
                    if ($comment->user && $comment->user?->profile_image) {
                        $pi = $comment->user->profile_image;
                        if (Str::startsWith($pi, ['http://', 'https://'])) {
                            $comment->user->profile_image = $pi;
                        } elseif (Str::startsWith($pi, ['storage/', '/storage'])) {
                            $comment->user->profile_image = asset(ltrim($pi, '/'));
                        } else {
                            $comment->user->profile_image = asset('storage/app/public/customer/profile/' . $pi);
                        }
                    }
                });
            }

            // Attach full URLs for ticket attachments (stored on public disk under tickets)
            if (!empty($ticket->attachments) && is_array($ticket->attachments)) {
                $ticket->attachments = array_map(function ($fileName) {
                    return asset('storage/app/public/tickets/' . $fileName);
                }, $ticket->attachments);
            }

            // Replace user_id with hydrated user object (as per original behavior)
            $ticket->user_id = $ticket->user;
            unset($ticket->user);

            return $ticket;
        });

        return response()->json($tickets);
    }

    public function store(Request $request)
    {
        // Validate input
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf',
        ]);

        $attachments = [];

        $files = $request->file('attachments');

        if ($files) {
            $files = is_array($files) ? $files : [$files];

            foreach ($files as $file) {
                $fileName = time() . '_' . $file->getClientOriginalName();
                $file->storeAs('tickets', $fileName, 'public');
                $attachments[] = $fileName;
            }
        }

        $ticket = Ticket::create([
            'title' => $request->title,
            'description' => $request->description,
            'attachments' => $attachments,
            'user_id' => auth()->id(),
        ]);

        return response()->json($ticket, 201);
    }

  
    public function show($id)
    {
        $ticket = Ticket::with([
            'user:id,full_name,profile_image',
            'comments.user:id,full_name,profile_image'
        ])->findOrFail($id);

        if ($ticket->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Build full URL for ticket owner profile image into the same field using asset()
        if ($ticket->user && $ticket->user?->profile_image) {
            $pi = $ticket->user->profile_image;
            if (Str::startsWith($pi, ['http://', 'https://'])) {
                $ticket->user->profile_image = $pi;
            } elseif (Str::startsWith($pi, ['storage/', '/storage'])) {
                $ticket->user->profile_image = asset(ltrim($pi, '/'));
            } else {
                $ticket->user->profile_image = asset('storage/app/public/customer/profile/' . $pi);
            }
        }

        // Build full URL for each comment user's profile image into the same field using asset()
        if ($ticket->comments) {
            $ticket->comments->each(function ($comment) {
                if ($comment->user && $comment->user?->profile_image) {
                    $pi = $comment->user->profile_image;
                    if (Str::startsWith($pi, ['http://', 'https://'])) {
                        $comment->user->profile_image = $pi;
                    } elseif (Str::startsWith($pi, ['storage/', '/storage'])) {
                        $comment->user->profile_image = asset(ltrim($pi, '/'));
                    } else {
                        $comment->user->profile_image = asset('storage/app/public/customer/profile/' . $pi);
                    }
                }
            });
        }

        // Attach full URLs for ticket attachments
        if (!empty($ticket->attachments) && is_array($ticket->attachments)) {
            $ticket->attachments = array_map(function ($fileName) {
                return asset('storage/app/public/tickets/' . $fileName);
            }, $ticket->attachments);
        }

        $ticket->user_id = $ticket->user;
        unset($ticket->user);

        return response()->json($ticket);
    }


public function updateStatus(Request $request, $id)
{
    $request->validate([
        'status' => 'required|in:open,in_progress,resolved,closed',
    ]);

    $ticket = Ticket::findOrFail($id);

    $user = Auth::user();

    if ($request->status === 'in_progress' || $request->status === 'resolved') {
        if ($user->user_type !== 'admin' && $user->user_type !== 'support') {
            return response()->json(['message' => 'Only admins can change to in_progress or resolved'], 403);
        }
    }

    if ($request->status === 'closed') {
        if ($ticket->user_id !== $user->id) {
            return response()->json(['message' => 'Only ticket owner can close the ticket'], 403);
        }
    }

    $ticket->status = $request->status;
    $ticket->save();

    return response()->json([
        'message' => 'Ticket status updated successfully',
        'ticket' => $ticket
    ]);
}
}
