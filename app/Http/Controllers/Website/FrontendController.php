<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Services\Frontend\FrontendService;
use App\Models\Contact;
use App\Models\Subscriber;
use App\Models\Basket;
use App\Models\BasketItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Mail\ContactFormMail;
use Illuminate\Support\Facades\Mail;

class FrontendController extends Controller
{
    protected $frontendService;

    public function __construct(FrontendService $frontendService)
    {
        $this->frontendService = $frontendService;
    }

    public function homepage()
    {
        $data = $this->frontendService->getHomePageData();
        return response()->json($data);
    }

    public function index($slug = null)
    {
        if (empty($slug)) {
            return $this->homepage();
        }

        $data = $this->frontendService->getSectionData($slug);
        return response()->json($data);
    }

    public function pages()
    {
        $pages = $this->frontendService->getActivePages();
        return response()->json($pages);
    }

    public function show($url)
    {
        $data = $this->frontendService->getProductByUrl($url);
        
        if (isset($data['error'])) {
            return response()->json($data, 404);
        }
        
        return response()->json($data);
    }

    public function submitContactForm(Request $request)
    {
        // Validate the form data
        $validator = Validator::make($request->all(), [
            'customerName' => 'required|string|max:255',
            'customerEmail' => 'required|email|max:255',
            'contactSubject' => 'nullable|string|max:255',
            'contactMessage' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Save the contact form data to the database
        $contact = new Contact();
        $contact->name = $request->customerName;
        $contact->email = $request->customerEmail;
        $contact->subject = $request->contactSubject;
        $contact->message = $request->contactMessage;
        $contact->save();

        return response()->json(['message' => 'Thank you for your message! We will get back to you soon.'], 200);
    }
    public function subscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $existingSubscriber = Subscriber::where('email', $request->email)->first();

        if ($existingSubscriber) {
            return response()->json(['error' => 'This email is already subscribed!'], 409);
        }

        Subscriber::create(['email' => $request->email]);

        return response()->json(['message' => 'You have subscribed successfully!'], 200);
    }


}
