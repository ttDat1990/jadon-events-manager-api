<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Models\Contact;


class ContactController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $query = Contact::query();

        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        if ($request->has('email')) {
            $query->where('email', 'like', '%' . $request->input('email') . '%');
        }

        $query->orderByRaw('isChecked ASC, created_at DESC');

        $contacts = $query->paginate($perPage);

        return response()->json($contacts);
    }

    public function uncheckedContact()
    {
        $count = Contact::where('isChecked', false)->count();

        return response()->json(['count' => $count]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'phone' => ['required', 'regex:/^[0-9]{10}$/'],
            'company_name' => 'required|string',
            'event_type' => 'required|string',
            'date_start' => 'date',
            'content' => 'required|string',
        ]);

        $recaptchaResponse = $request->input('captchaValue');
        $recaptchaSecretKey = '6LcoRJMoAAAAAC9pqc1w0i5ouV8aIXqNAMVPPZzz';

        $response = Http::asForm()
        ->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $recaptchaSecretKey,
            'response' => $recaptchaResponse,
        ]);

        $responseData = $response->json();

        if (!$responseData['success']) {
            return response()->json(['message' => 'reCAPTCHA validation failed'], 400);
        }

        $contact = Contact::create($validatedData);

        return response()->json($contact, 201);
    }

    public function updateIsChecked($id)
    {
        $contact = Contact::find($id);

        if (!$contact) {
            return response()->json(['message' => 'Contact not exist'], 404);
        }

        $contact->isChecked = true;
        $contact->save();

        return response()->json(['message' => 'Contact is checked','contact' => $contact]);
    }

    public function destroy($id)
    {
        $contact = Contact::find($id);

        if (!$contact) {
            return response()->json(['message' => 'Contact not exist'], 404);
        }

        $contact->delete();

        return response()->json(['message' => 'Contact deleted']);
    }
}
