<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactInquiry;
use Illuminate\Http\Request;

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
        $authenticatedUser = $request->user('sanctum');

        $validated = $request->validate([
            'full_name' => 'required|string|max:160',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:180',
            'message' => 'required|string|max:5000',
        ]);

        $inquiry = ContactInquiry::create([
            'sender_user_id' => $authenticatedUser?->id,
            'full_name' => trim($validated['full_name']),
            'email' => trim($validated['email']),
            'subject' => trim($validated['subject']),
            'message' => trim($validated['message']),
            'leido' => false,
            'contestado' => false,
            'fecha_contacto' => now(),
            'fecha_respuesta' => null,
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
                'sender_user_id' => $item->sender_user_id,
                'contact_origin' => $item->sender_user_id ? 'internal' : 'external',
                'leido' => (bool) $item->leido,
                'contestado' => (bool) $item->contestado,
                'fecha_contacto' => optional($item->fecha_contacto)->toISOString(),
                'fecha_respuesta' => optional($item->fecha_respuesta)->toISOString(),
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
        $inquiry->load('repliedByUser:id,username,nombre,apellido');

        return response()->json([
            'estado' => 'ok',
            'message' => 'Consulta obtenida correctamente.',
            'code' => 200,
            'errors' => null,
            'data' => [
                'id' => $inquiry->id,
                'sender_user_id' => $inquiry->sender_user_id,
                'contact_origin' => $inquiry->sender_user_id ? 'internal' : 'external',
                'leido' => (bool) $inquiry->leido,
                'contestado' => (bool) $inquiry->contestado,
                'fecha_contacto' => optional($inquiry->fecha_contacto)->toISOString(),
                'fecha_respuesta' => optional($inquiry->fecha_respuesta)->toISOString(),
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

    public function adminUpdateInquiryState(Request $request, ContactInquiry $inquiry)
    {
        $validated = $request->validate([
            'leido' => 'required|boolean',
            'contestado' => 'required|boolean',
        ]);

        $leido = (bool) $validated['leido'];
        $contestado = (bool) $validated['contestado'];

        $inquiry->update([
            'leido' => $leido,
            'contestado' => $contestado,
            'read_at' => $leido ? ($inquiry->read_at ?: now()) : null,
            'fecha_respuesta' => $contestado ? ($inquiry->fecha_respuesta ?: now()) : null,
            'replied_at' => $contestado ? ($inquiry->replied_at ?: now()) : null,
            'status' => $contestado ? 'replied' : ($leido ? 'read' : 'new'),
        ]);

        $inquiry->refresh();

        return response()->json([
            'estado' => 'ok',
            'message' => 'Estado actualizado correctamente.',
            'code' => 200,
            'errors' => null,
            'data' => [
                'id' => $inquiry->id,
                'leido' => (bool) $inquiry->leido,
                'contestado' => (bool) $inquiry->contestado,
                'fecha_contacto' => optional($inquiry->fecha_contacto)->toISOString(),
                'fecha_respuesta' => optional($inquiry->fecha_respuesta)->toISOString(),
                'status' => $inquiry->status,
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
            'leido' => true,
            'contestado' => true,
            'read_at' => $inquiry->read_at ?: now(),
            'fecha_respuesta' => $inquiry->fecha_respuesta ?: now(),
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
}
