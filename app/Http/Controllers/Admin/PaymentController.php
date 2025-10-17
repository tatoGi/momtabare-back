<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BogPayment;
use App\Models\WebUser;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Display a listing of payments
     */
    public function index(Request $request)
    {
        $query = BogPayment::with(['products', 'webUser']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search by order ID or user
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('bog_order_id', 'like', "%{$search}%")
                  ->orWhere('external_order_id', 'like', "%{$search}%")
                  ->orWhereRaw("JSON_EXTRACT(request_payload, '$.web_user_id') = ?", [$search]);
            });
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get statistics
        $stats = [
            'total_payments' => BogPayment::count(),
            'completed_payments' => BogPayment::whereIn('status', ['completed', 'approved', 'succeeded'])->count(),
            'pending_payments' => BogPayment::where('status', 'pending')->count(),
            'failed_payments' => BogPayment::whereIn('status', ['failed', 'error', 'rejected'])->count(),
            'total_amount' => BogPayment::whereIn('status', ['completed', 'approved', 'succeeded'])->sum('amount'),
        ];

        return view('admin.payments.index', compact('payments', 'stats'));
    }

    /**
     * Display the specified payment
     */
    public function show($id)
    {
        // Validate that ID is numeric
        if (!is_numeric($id)) {
            abort(404, 'Invalid payment ID');
        }

        $payment = BogPayment::with(['products', 'webUser'])->findOrFail($id);

        // Get web user from request_payload if not found via relationship
        $webUserId = $payment->request_payload['web_user_id'] ?? null;
        $webUser = null;

        if ($webUserId) {
            $webUser = WebUser::find($webUserId);
        }

        // Extract rental information from products
        $productsWithRental = $payment->products->map(function($product) {
            $rentalInfo = null;

            if ($product->rental_start_date && $product->rental_end_date) {
                $start = \Carbon\Carbon::parse($product->rental_start_date);
                $end = \Carbon\Carbon::parse($product->rental_end_date);
                $days = $start->diffInDays($end);

                $rentalInfo = [
                    'start_date' => $start->format('Y-m-d H:i'),
                    'end_date' => $end->format('Y-m-d H:i'),
                    'duration_days' => $days,
                    'duration_text' => $days . ' ' . ($days == 1 ? 'day' : 'days'),
                ];
            }

            return [
                'id' => $product->id,
                'name_ka' => $product->name_ka,
                'name_en' => $product->name_en,
                'name_ru' => $product->name_ru,
                'slug' => $product->slug,
                'price' => $product->price,
                'quantity' => $product->pivot->quantity,
                'unit_price' => $product->pivot->unit_price,
                'total_price' => $product->pivot->total_price,
                'rental_info' => $rentalInfo,
                'is_ordered' => $product->is_ordered,
                'ordered_at' => $product->ordered_at,
            ];
        });

        return view('admin.payments.show', compact('payment', 'webUser', 'productsWithRental'));
    }

    /**
     * Export payments to CSV
     */
    public function export(Request $request)
    {
        $query = BogPayment::with(['products', 'webUser']);

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $payments = $query->orderBy('created_at', 'desc')->get();

        $filename = 'payments_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($payments) {
            $file = fopen('php://output', 'w');

            // CSV Headers
            fputcsv($file, [
                'Payment ID',
                'BOG Order ID',
                'External Order ID',
                'User ID',
                'User Name',
                'User Email',
                'Amount',
                'Currency',
                'Status',
                'Products Count',
                'Created At',
                'Verified At',
            ]);

            foreach ($payments as $payment) {
                $webUserId = $payment->request_payload['web_user_id'] ?? null;
                $webUser = $webUserId ? WebUser::find($webUserId) : null;

                fputcsv($file, [
                    $payment->id,
                    $payment->bog_order_id,
                    $payment->external_order_id,
                    $webUserId,
                    $webUser ? $webUser->name : 'N/A',
                    $webUser ? $webUser->email : 'N/A',
                    $payment->amount,
                    $payment->currency,
                    $payment->status,
                    $payment->products->count(),
                    $payment->created_at->format('Y-m-d H:i:s'),
                    $payment->verified_at ? $payment->verified_at->format('Y-m-d H:i:s') : 'N/A',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
