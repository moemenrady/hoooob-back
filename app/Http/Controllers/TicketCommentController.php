<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketComment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TicketCommentController extends Controller
{
  public function index($ticketId)
    {
        $ticket = Ticket::with('user:id,full_name,profile_image')
            ->findOrFail($ticketId);

        $comments = TicketComment::where('ticket_id', $ticket->id)
            ->with('user:id,full_name,profile_image')
            ->get();
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

        $comments->each(function ($comment) {
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

        if (!empty($ticket->attachments) && is_array($ticket->attachments)) {
            $ticket->attachments = array_map(function ($fileName) {
                return asset('storage/app/public/tickets/' . $fileName);
            }, $ticket->attachments);
        }

        return response()->json([
            'ticket' => $ticket,
            'comments' => $comments,
        ]);
    }

    public function store(Request $request, $ticketId)
    {
        $request->validate([
            'comment' => 'required|string',
        ]);

        $ticket = Ticket::findOrFail($ticketId);

        if (auth()->user()->user_type !== 'admin' && $ticket->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $comment = TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'comment' => $request->comment,
        ]);

        return response()->json($comment, 201);
    }
}
