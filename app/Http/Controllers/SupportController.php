<?php

namespace App\Http\Controllers;

// Added: Import Controller base class
use App\Http\Controllers\Controller;

// Added: Import models
use App\Models\SupportTicket;

// Added: Import Request and Auth facade
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    /**
     * Create support ticket.
     */
    public function createTicket(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        // Fixed: Use Auth facade for proper type inference
        $ticket = SupportTicket::create([
            'user_id' => Auth::id(),
            'email' => $request->email,
            'subject' => $request->subject,
            'message' => $request->message,
            'status' => 'PENDING',
        ]);

        return response()->json([
            'success' => true,
            'ticket' => $ticket,
            'message' => 'Support ticket created successfully',
        ]);
    }

    /**
     * Get all tickets (admin only).
     */
    /**
     * Get all tickets (admin only).
     * Fixed: Use Auth facade for authentication
     */
    public function getAllTickets(Request $request)
    {
        // Fixed: Use Auth facade for proper type inference
        if (!Auth::check() || !Auth::user()->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $tickets = SupportTicket::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'tickets' => $tickets,
        ]);
    }

    /**
     * Update ticket status (admin only).
     * Fixed: Use Auth facade for authentication
     */
    public function updateTicketStatus(Request $request, $id)
    {
        // Fixed: Use Auth facade for proper type inference
        if (!Auth::check() || !Auth::user()->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $request->validate([
            'status' => 'required|in:PENDING,IN_PROGRESS,RESOLVED,CLOSED',
        ]);

        $ticket = SupportTicket::find($id);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found',
            ], 404);
        }

        $ticket->status = $request->status;
        $ticket->save();

        return response()->json([
            'success' => true,
            'ticket' => $ticket,
            'message' => 'Ticket status updated',
        ]);
    }

    /**
     * Get user's tickets.
     * Fixed: Use Auth facade for authentication
     */
    public function getMyTickets(Request $request)
    {
        // Fixed: Use Auth facade for proper type inference
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        // Fixed: Use Auth facade for proper type inference
        $tickets = SupportTicket::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'tickets' => $tickets,
        ]);
    }
}
