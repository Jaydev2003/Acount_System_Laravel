<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Transaction;

class DashboardController extends Controller
{
   

  

    public function downloadReport(Request $request)
    {
        $userId = $request->user_id;
    
        if (!$userId) {
            return response()->json(['error' => 'User ID is required'], 400);
        }
    
        $transactions = Account::where('user_id', $userId)
            ->whereBetween('date', [$request->start_date, $request->end_date])
            ->get();
    
        $incomeTransactions = $transactions->where('type', 'income');
        $expenseTransactions = $transactions->where('type', 'expense');
    
        $totalIncome = $incomeTransactions->sum('amount');
        $totalExpense = $expenseTransactions->sum('amount');
        $profitLoss = $totalIncome - $totalExpense;
    
        $profitLossStatus = $profitLoss >= 0 ? "Profit: " . number_format($profitLoss, 2) : "Loss: " . number_format(abs($profitLoss), 2);
    
        $pdf = Pdf::loadView('pdf.report', compact(
            'incomeTransactions', 'expenseTransactions', 'totalIncome', 'totalExpense', 'profitLoss', 'profitLossStatus'
        ));
    
        return $pdf->download('balance_sheet.pdf');
    }
    
    

    public function dashboard()
{
    return view('dashboard');
}
    public function report()
{
    return view('report');
}

    
    
    public function temp()
    {
             $user = Auth::user();
        return view('Layout.account', compact('user'));
    }
    

    public function admin(){
        return view('admin');
    }
    public function balanceSheet(){
        return view('balancesheet');
    }
    public function user(){
        return view('user');
    }
    public function role(){
        return view('role');
    }
    public function login(){
        return view('login');
    }
    public function verifyOtp(){
        return view('verifyOtp');
    }
    public function fogotpassword(){
        return view('forgotpassword');
    }

    public function showResetPasswordForm(Request $request)
{
    $token = $request->query('token');

    if (!$token) {
        return abort(404, "Invalid reset token.");
    }

    return view('reset_password', compact('token'));
}




}
