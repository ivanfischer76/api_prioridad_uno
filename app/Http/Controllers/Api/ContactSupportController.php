<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactInquiry;
use App\Models\SupportMessage;
use App\Models\SupportThread;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContactSupportController extends Controller
{
    public function getChannel()
    {
        $response = response()->json([
            'estado' => 'ok',
            'message' => 'Canales de contacto obtenidos correctamente.',
            'code' => 200,
            'errors' => null,
            'data' => [
                'support_email' => (string) config('site.contact_support_email', ''),
                'support_phone' => (string) config('site.contact_support_phone', ''),
            ],
        ]);

        return $response
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function submitPublicContact(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:160',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:180',
            'message' => 'required|string|max:5000',
        ]);

        $inquiry = ContactInquiry::create([
            'full_name' => trim($validated['full_name']),
            'email' => trim($validated['email']),
            'subject' => trim($validated['subject']),
            'message' => trim($validated['message']),
            'status' => 'new',
        ]);

        return response()->json([
            'estado' => 'ok',
            'message' => 'Mensaje de contacto registrado correctamente.',
            'code' => 201,
            'errors' => null,
            'data' => [
                'id' => $inquiry->id,
            ],
        ], 201);
    }

    public function adminInquiries()
    {
        $items = ContactInquiry::query()
            ->with('repliedByUser:id,username,nombre,apellido')
            ->orderByRaw("CASE WHEN status = 'new' THEN 0 WHEN status = 'read' THEN 1 ELSE 2 END")
            ->orderByDesc('created_at')
            ->get();

        $data = $items->map(function (ContactInquiry $item) {
            return [
                'id' => $item->id,
                'full_name' => $item->full_name,
                'email' => $item->email,
                'subject' => $item->subject,
                'message' => $item->message,
                'status' => $item->status,
                'read_at' => optional($item->read_at)->toISOString(),
                'admin_reply' => $item->admin_reply,
                'replied_at' => optional($item->replied_at)->toISOString(),
                'replied_by' => $item->repliedByUser
                    ? [
                        'id' => $item->repliedByUser->id,
                        'username' => $item->repliedByUser->username,
                        'nombre' => $item->repliedByUser->nombre,
                        'apellido' => $item->repliedByUser->apellido,
                    ]
                    : null,
                'created_at' => optional($item->created_at)->toISOString(),
            ];
        })->values();

        return response()->json([
            'estado' => 'ok',
            'message' => 'Consultas de contacto obtenidas correctamente.',
            'code' => 200,
            'errors' => null,
            'data' => $data,
        ]);
    }

    public function adminInquiryDetail(ContactInquiry $inquiry)
    {
        if ($inquiry->status === 'new') {
            $inquiry->update([
                'status' => 'read',
                'read_at' => now(),
            ]);
        }

        $inquiry->load('repliedByUser:id,username,nombre,apellido');

        return response()->json([
            'estado' => 'ok',
            'message' => 'Consulta obtenida correctamente.',
            'code' => 200,
            'errors' => null,
            'data' => [
                'id' => $inquiry->id,
                'full_name' => $inquiry->full_name,
                'email' => $inquiry->email,
                'subject' => $inquiry->subject,
                'message' => $inquiry->message,
                'status' => $inquiry->status,
                'read_at' => optional($inquiry->read_at)->toISOString(),
                'admin_reply' => $inquiry->admin_reply,
                'replied_at' => optional($inquiry->replied_at)->toISOString(),
                'replied_by' => $inquiry->repliedByUser
                    ? [
                        'id' => $inquiry->repliedByUser->id,
                        'username' => $inquiry->repliedByUser->username,
                        'nombre' => $inquiry->repliedByUser->nombre,
                        'apellido' => $inquiry->repliedByUser->apellido,
                    ]
                    : null,
                'created_at' => optional($inquiry->created_at)->toISOString(),
            ],
        ]);
    }

    public function adminReplyInquiry(Request $request, ContactInquiry $inquiry)
    {
        $validated = $request->validate([
            'reply' => 'required|string|max:5000',
        ]);

        $inquiry->update([
            'status' => 'replied',
            'read_at' => $inquiry->read_at ?: now(),
            'admin_reply' => trim($validated['reply']),
            'replied_by_user_id' => $request->user()?->id,
            'replied_at' => now(),
        ]);

        return response()->json([
            'estado' => 'ok',
            'message' => 'Respuesta registrada correctamente.',
            'code' => 200,
            'errors' => null,
            'data' => [
                'id' => $inquiry->id,
                'status' => $inquiry->status,
                'replied_at' => optional($inquiry->replied_at)->toISOString(),
            ],
        ]);
    }

    public function sendMessage(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'sender_email' => 'required|email|max:255',
            'subject' => 'required|string|max:180',
            'message' => 'required|string|max:5000',
        ]);

        $result = DB::transaction(function () use ($user, $validated) {
            $thread = SupportThread::create([
                'user_id' => $user->id,
                'subject' => trim($validated['subject']),
                'status' => 'open',
                'last_message_at' => now(),
            ]);

            $message = SupportMessage::create([
                'thread_id' => $thread->id,
                'sender_user_id' => $user->id,
                'sender_type' => 'user',
                'from_email' => trim($validated['sender_email']),
                'body' => trim($validated['message']),
                'read_at' => now(),
                'sent_via_email' => false,
            ]);

            return compact('thread', 'message');
        });

        return response()->json([
            'estado' => 'ok',
            'message' => 'Mensaje interno enviado correctamente.',
            'code' => 200,
            'errors' => null,
            'data' => [
                'thread_id' => $result['thread']->id,
                'message_id' => $result['message']->id,
                'mail_sent' => false,
            ],
        ], 200);
    }

    public function myThreads(Request $request)
    {
        $user = $request->user();

        $threads = SupportThread::query()
            ->where('user_id', $user->id)
            ->with(['messages' => function ($query) {
                $query->orderByDesc('id');
            }])
            ->orderByDesc('last_message_at')
            ->get();

        $data = $threads->map(function (SupportThread $thread) {
            $lastMessage = $thread->messages->first();

            return [
                'id' => $thread->id,
                'subject' => $thread->subject,
                'status' => $thread->status,
                'last_message_at' => optional($thread->last_message_at)->toISOString(),
                'last_message_preview' => $lastMessage ? mb_substr($lastMessage->body, 0, 140) : '',
                'unread_count' => $thread->messages
                    ->whereIn('sender_type', ['admin', 'system'])
                    ->whereNull('read_at')
                    ->count(),
                'created_at' => optional($thread->created_at)->toISOString(),
            ];
        })->values();

        return response()->json([
            'estado' => 'ok',
            'message' => 'Mensajes obtenidos correctamente.',
            'code' => 200,
            'errors' => null,
            'data' => $data,
        ]);
    }

    public function myThreadMessages(Request $request, SupportThread $thread)
    {
        $user = $request->user();

        if ($thread->user_id !== $user->id && !$user->hasPermissionTo('gestionar sistema', 'api')) {
            return response()->json([
                'estado' => 'error',
                'message' => 'No autorizado.',
                'code' => 403,
                'errors' => ['No autorizado.'],
                'data' => null,
            ], 403);
        }

        $thread->load(['messages' => function ($query) {
            $query->orderBy('id');
        }]);

        SupportMessage::query()
            ->where('thread_id', $thread->id)
            ->whereIn('sender_type', ['admin', 'system'])
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = $thread->messages->map(function (SupportMessage $message) {
            return [
                'id' => $message->id,
                'sender_type' => $message->sender_type,
                'from_email' => $message->from_email,
                'body' => $message->body,
                'sent_via_email' => $message->sent_via_email,
                'read_at' => optional($message->read_at)->toISOString(),
                'created_at' => optional($message->created_at)->toISOString(),
            ];
        })->values();

        return response()->json([
            'estado' => 'ok',
            'message' => 'Conversación obtenida correctamente.',
            'code' => 200,
            'errors' => null,
            'data' => [
                'id' => $thread->id,
                'subject' => $thread->subject,
                'status' => $thread->status,
                'messages' => $messages,
            ],
        ]);
    }

    public function adminReply(Request $request, SupportThread $thread)
    {
        $user = $request->user();

        $validated = $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $message = DB::transaction(function () use ($thread, $validated, $user) {
            $thread->update([
                'last_message_at' => now(),
                'status' => 'open',
            ]);

            return SupportMessage::create([
                'thread_id' => $thread->id,
                'sender_user_id' => $user?->id,
                'sender_type' => 'admin',
                'from_email' => trim((string) ($user?->email ?? 'soporte@local.test')),
                'body' => trim($validated['message']),
                'sent_via_email' => false,
                'read_at' => now(),
            ]);
        });

        return response()->json([
            'estado' => 'ok',
            'message' => 'Respuesta interna guardada correctamente.',
            'code' => 200,
            'errors' => null,
            'data' => [
                'thread_id' => $thread->id,
                'message_id' => $message->id,
                'mail_sent' => false,
            ],
        ]);
    }
}
