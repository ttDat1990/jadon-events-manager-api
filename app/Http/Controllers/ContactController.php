<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;


class ContactController extends Controller
{
    public function index()
    {
        $contacts = Contact::orderBy('created_at', 'desc')->get();
        return response()->json($contacts);
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

        $contact = Contact::create($validatedData);

        return response()->json($contact, 201);
    }

    public function getUncheckedContacts()
    {
        $uncheckedContacts = Contact::where('isChecked', false)->get();
        return response()->json($uncheckedContacts);
    }

    public function updateIsChecked($id)
    {
        $contact = Contact::find($id);

        if (!$contact) {
            return response()->json(['message' => 'Contact not exist'], 404);
        }

        $contact->isChecked = true;
        $contact->save();

        return response()->json(['message' => 'Contact is checked']);
    }
}
