<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\StallPayment;
use App\Models\Vendor;
use App\Models\Stall;

class AdminStallPaymentController extends Controller
{
    public function index()
    {
        // Auto-mark unpaid bills past their due date as overdue
        StallPayment::where('status', 'unpaid')
            ->where('due_date', '<', now()->toDateString())
            ->update(['status' => 'overdue']);

        // Get all vendor rent records with stall info
        $payments = StallPayment::with(['vendor', 'stall'])
            ->orderBy('due_date', 'desc')
            ->get();

        // Summary stats
        $totalUnpaid = $payments->whereIn('status', ['unpaid', 'overdue'])->sum('amount_due');
        $totalPaid = $payments->where('status', 'paid')->sum('amount_paid');
        $overdueCount = $payments->where('status', 'overdue')->count();

        // Get vendors with stall assignments for the create form
        $vendors = DB::table('vendors')
            ->join('stall_assignments', 'vendors.id', '=', 'stall_assignments.vendor_id')
            ->join('stalls', 'stall_assignments.stall_id', '=', 'stalls.id')
            ->whereNull('stall_assignments.end_date')
            ->select('vendors.id as vendor_id', 'vendors.business_name', 'stalls.id as stall_id', 'stalls.stall_number')
            ->get();

        return view('admin.stall_payments', compact('payments', 'totalUnpaid', 'totalPaid', 'overdueCount', 'vendors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'amount_due' => 'required|numeric|min:1',
            'due_date' => 'required|date',
            'billing_period' => 'required|string|max:100',
        ]);

        // Get the vendor's stall
        $stallAssignment = DB::table('stall_assignments')
            ->where('vendor_id', $request->vendor_id)
            ->whereNull('end_date')
            ->first();

        if (!$stallAssignment) {
            return redirect()->back()->with('error', 'Vendor has no active stall assignment.');
        }

        StallPayment::create([
            'vendor_id' => $request->vendor_id,
            'stall_id' => $stallAssignment->stall_id,
            'amount_due' => $request->amount_due,
            'due_date' => $request->due_date,
            'billing_period' => $request->billing_period,
            'status' => 'unpaid',
        ]);

        return redirect()->back()->with('success', 'Rent bill created successfully.');
    }

    public function markOverdue()
    {
        $updated = StallPayment::where('status', 'unpaid')
            ->where('due_date', '<', now()->toDateString())
            ->update(['status' => 'overdue']);

        return redirect()->back()->with('success', "$updated payment(s) marked as overdue.");
    }
}
