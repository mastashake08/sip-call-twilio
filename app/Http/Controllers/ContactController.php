<?php

namespace App\Http\Controllers;

use App\Events\CallContactRequested;
use App\Events\SmsContactRequested;
use App\Models\Contact;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the contacts.
     */
    public function index(Request $request): Response
    {
        $user = Auth::user();
        $search = $request->input('search');
        $filter = $request->input('filter', 'all'); // all, favorites

        $query = $user->contacts()->orderBy('name');

        if ($search) {
            $query->search($search);
        }

        if ($filter === 'favorites') {
            $query->favorites();
        }

        $contacts = $query->paginate(20)->withQueryString();

        return Inertia::render('Contacts/Index', [
            'contacts' => $contacts,
            'search' => $search,
            'filter' => $filter,
        ]);
    }

    /**
     * Show the form for creating a new contact.
     */
    public function create(): Response
    {
        return Inertia::render('Contacts/Create');
    }

    /**
     * Store a newly created contact in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'notes' => 'nullable|string|max:1000',
            'is_favorite' => 'boolean',
            'tags' => 'array',
            'tags.*' => 'string|max:50',
        ]);

        $contact = Auth::user()->contacts()->create($validated);

        return redirect()->route('contacts.index')
            ->with('success', 'Contact created successfully.');
    }

    /**
     * Display the specified contact.
     */
    public function show(Contact $contact): Response
    {
        $this->authorize('view', $contact);

        return Inertia::render('Contacts/Show', [
            'contact' => $contact->load('user'),
        ]);
    }

    /**
     * Show the form for editing the specified contact.
     */
    public function edit(Contact $contact): Response
    {
        $this->authorize('update', $contact);

        return Inertia::render('Contacts/Edit', [
            'contact' => $contact,
        ]);
    }

    /**
     * Update the specified contact in storage.
     */
    public function update(Request $request, Contact $contact)
    {
        $this->authorize('update', $contact);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'notes' => 'nullable|string|max:1000',
            'is_favorite' => 'boolean',
            'tags' => 'array',
            'tags.*' => 'string|max:50',
        ]);

        $contact->update($validated);

        return redirect()->route('contacts.index')
            ->with('success', 'Contact updated successfully.');
    }

    /**
     * Remove the specified contact from storage.
     */
    public function destroy(Contact $contact)
    {
        $this->authorize('delete', $contact);

        $contact->delete();

        return redirect()->route('contacts.index')
            ->with('success', 'Contact deleted successfully.');
    }

    /**
     * Toggle favorite status of a contact.
     */
    public function toggleFavorite(Contact $contact)
    {
        $this->authorize('update', $contact);

        $contact->update([
            'is_favorite' => !$contact->is_favorite
        ]);

        return response()->json([
            'is_favorite' => $contact->is_favorite
        ]);
    }

    /**
     * Initiate a call to a contact (returns Twilio instructions).
     */
    public function call(Contact $contact)
    {
        $this->authorize('view', $contact);

        $user = Auth::user();
        $twilioSettings = $user->twilioSettings;

        if (!$twilioSettings || !$twilioSettings->twilio_phone_number) {
            return response()->json([
                'error' => 'Twilio not configured'
            ], 400);
        }

        // Prepare the response
        $response = response()->json([
            'message' => "Calling {$contact->name} @ {$contact->formatted_phone} from your configured phone.",
            'contact' => $contact,
            'action' => 'call'
        ]);

        // Dispatch the event after the response is sent
        $response->headers->set('X-Accel-Buffering', 'no');
        
        // Use terminate callback to dispatch event after response
        CallContactRequested::dispatch($contact, $user);


        return $response;
    }

    /**
     * Send SMS to a contact (would integrate with Twilio).
     */
    public function sms(Request $request, Contact $contact)
    {
        $this->authorize('view', $contact);

        $validated = $request->validate([
            'message' => 'required|string|max:1600',
        ]);

        $user = Auth::user();
        $twilioSettings = $user->twilioSettings;

        if (!$twilioSettings || !$twilioSettings->twilio_phone_number) {
            return response()->json([
                'error' => 'Twilio not configured'
            ], 400);
        }

        // Prepare the response
        $response = response()->json([
            'message' => "SMS would be sent to {$contact->name} at {$contact->formatted_phone}",
            'contact' => $contact,
            'sms_content' => $validated['message'],
            'action' => 'sms'
        ]);

        // Dispatch the event after the response is sent
        $response->headers->set('X-Accel-Buffering', 'no');
        
        // Use terminate callback to dispatch event after response
        SmsContactRequested::dispatch($contact, $user, $validated['message']);
        

        return $response;
    }
}
