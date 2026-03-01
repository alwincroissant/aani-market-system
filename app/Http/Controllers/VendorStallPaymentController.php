<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\StallPayment;

class VendorStallPaymentController extends Controller
{
    public function index()
    {
        $vendor = Auth::user()->vendor;

        if (!$vendor) {
            return redirect()->route('vendor.dashboard')->with('error', 'Vendor profile not found.');
        }

        // Get stall assignment info
        $stallAssignment = DB::table('stall_assignments')
            ->join('stalls', 'stall_assignments.stall_id', '=', 'stalls.id')
            ->leftJoin('market_sections', 'stalls.section_id', '=', 'market_sections.id')
            ->where('stall_assignments.vendor_id', $vendor->id)
            ->whereNull('stall_assignments.end_date')
            ->select('stalls.id as stall_id', 'stalls.stall_number', 'market_sections.section_name')
            ->first();

        // Get unpaid/overdue payments
        $unpaidPayments = StallPayment::where('vendor_id', $vendor->id)
            ->whereIn('status', ['unpaid', 'overdue'])
            ->orderBy('due_date', 'asc')
            ->get();

        // Get payment history
        $paidPayments = StallPayment::where('vendor_id', $vendor->id)
            ->where('status', 'paid')
            ->orderBy('paid_at', 'desc')
            ->get();

        $totalOwed = $unpaidPayments->sum('amount_due');

        return view('vendor.stall_payments', compact('vendor', 'stallAssignment', 'unpaidPayments', 'paidPayments', 'totalOwed'));
    }

    public function pay(Request $request, $id)
    {
        $request->validate([
            'payment_method' => 'required|in:cash,gcash,bank_transfer',
            'payment_reference' => 'nullable|string|max:255',
        ]);

        $payment = StallPayment::where('id', $id)
            ->where('vendor_id', Auth::user()->vendor->id)
            ->whereIn('status', ['unpaid', 'overdue'])
            ->firstOrFail();
        $payment->amount_paid = $payment->amount_due;
        $payment->paid_at = now();
        $payment->status = 'paid';
        $payment->payment_method = $request->payment_method;
        $payment->payment_reference = $request->payment_reference;
        $payment->save();
        return redirect()->back()->with('success', 'Payment marked as paid.');
    }
}
