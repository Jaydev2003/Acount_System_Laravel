<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Constants\HttpStatusCode;
use App\Models\Account;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class AccountController extends Controller
{
    
    public function create(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (TokenExpiredException $e) {
            return response()->json([
                'status' => HttpStatusCode::UNAUTHORIZED,
                'message' => 'Token has expired. Please login again.'
            ]);
        }
         catch (\Exception $e) {
            return response()->json([
                'status' => HttpStatusCode::UNAUTHORIZED,
                'message' => 'Unauthorized: Please login first'
            ]);
        }
        $validateAccount = Validator::make($request->all(), [
        'user_id' => 'required', 
            'amount' => 'required|numeric',
            'date' => 'required|date_format:d/m/Y',
            'type' => 'required|in:expense,income',
            'description' => 'nullable|string|max:255'
        ]);

        if ($validateAccount->fails()) {
            return response()->json([
                'errors' => $validateAccount->errors(),
                'status' => HttpStatusCode::BAD_REQUEST,
                'message' => 'Validation errors'
            ]);
        }

        $formattedDate = Carbon::createFromFormat('d/m/Y', $request->date)->format('Y-m-d');

        $account = Account::create([
            'user_id' => $request->user_id, 
            'amount' => $request->amount,
            'date' => $formattedDate,
            'type' => $request->type,
            'description' => $request->description,
        ]);

        return response()->json([
            'status' => HttpStatusCode::CREATED,
            'message' => 'Account record created successfully',
            'data' => $account,
        ]);
    }

   


    public function view(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate()->load('role');
        }
        catch (TokenExpiredException $e) {
            return response()->json([
                'status' => HttpStatusCode::UNAUTHORIZED,
                'message' => 'Token has expired. Please login again.'
            ]);
        }
         catch (\Exception $e) {
            return response()->json([
                'status' => HttpStatusCode::UNAUTHORIZED,
                'message' => 'Unauthorized: Please login first'
            ]);
        }
    
        if ($user->hasRole('user')) {
            $users = User::select('id', 'name','email')->where('id', $user->id)->get();
        } else {
            $users = User::select('id', 'name','email')->get();
        }
    
        if ($users->isEmpty()) {
            return response()->json([
                'status' => HttpStatusCode::NOT_FOUND,
                'message' => 'No users found'
            ]);
        }
    
        return response()->json([
            'status' => HttpStatusCode::SUCCESS,
            'message' => 'User details fetched successfully',
            'data' => $users
        ]);
    }
    
    
public function Userview(Request $request)
{
    try {
        $authUser = JWTAuth::parseToken()->authenticate(); 
    }catch (TokenExpiredException $e) {
        return response()->json([
            'status' => HttpStatusCode::UNAUTHORIZED,
            'message' => 'Token has expired. Please login again.'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => HttpStatusCode::UNAUTHORIZED,
            'message' => 'Unauthorized: Please login first'
        ]);
    }

    $userId = $request->input('user_id'); 

    if (!$userId) {
        return response()->json([
            'status' => HttpStatusCode::BAD_REQUEST,
            'message' => 'User ID is required'
        ]);
    }

    $user = User::find($userId);

    if (!$user) {
        return response()->json([
            'status' => HttpStatusCode::NOT_FOUND,
            'message' => 'User not found'
        ]);
    }

    return response()->json([
        'status' => HttpStatusCode::SUCCESS,
        'message' => 'User details fetched successfully',
        'data' => $user
    ]);
}



    public function update(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        }catch (TokenExpiredException $e) {
            return response()->json([
                'status' => HttpStatusCode::UNAUTHORIZED,
                'message' => 'Token has expired. Please login again.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => HttpStatusCode::UNAUTHORIZED,
                'message' => 'Unauthorized: Please login first'
            ]);
        }



        $validateAccount = Validator::make($request->all(), [
            'id' => 'required|exists:account,id',
            'amount' => 'required|numeric',
            'date' => 'required|date_format:d/m/Y',
            'type' => 'required|in:expense,income',
            'description' => 'nullable|string|max:255'
        ]);

        if ($validateAccount->fails()) {
            return response()->json([
                'errors' => $validateAccount->errors(),
                'status' => HttpStatusCode::BAD_REQUEST,
                'message' => 'Validation errors'
            ]);
        }

        $account = Account::where('id', $request->id)->first();
        $formattedDate = Carbon::createFromFormat('d/m/Y', $request->date)->format('Y-m-d');

        $account->update([
            'amount' => $request->amount,
            'date' => $formattedDate,
            'type' => $request->type,
            'description' => $request->description,
        ]);

        return response()->json([
            'status' => HttpStatusCode::SUCCESS,
            'message' => 'Account record updated successfully',
            'data' => $account,
        ]);
    }

    public function delete(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        }catch (TokenExpiredException $e) {
            return response()->json([
                'status' => HttpStatusCode::UNAUTHORIZED,
                'message' => 'Token has expired. Please login again.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => HttpStatusCode::UNAUTHORIZED,
                'message' => 'Unauthorized: Please login first'
            ]);
        }
        
        $validateAccount = Validator::make($request->all(), [
            'id' => 'required|exists:account,id',
        ]);

        if ($validateAccount->fails()) {
            return response()->json([
                'errors' => $validateAccount->errors(),
                'status' => HttpStatusCode::BAD_REQUEST,
                'message' => 'Validation errors'
            ]);
        }

        $account = Account::where('id', $request->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$account) {
            return response()->json([
                'status' => HttpStatusCode::FORBIDDEN,
                'message' => 'Unauthorized: You can only delete your own account records'
            ]);
        }

        $account->delete();

        return response()->json([
            'status' => HttpStatusCode::SUCCESS,
            'message' => 'Account record deleted successfully'
        ]);
    }

    public function getTransaction(Request $request)
{
    try {
        $user = JWTAuth::parseToken()->authenticate(); 
    } catch (TokenExpiredException $e) {
        return response()->json([
            'status' => HttpStatusCode::UNAUTHORIZED,
            'message' => 'Token has expired. Please login again.'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => HttpStatusCode::UNAUTHORIZED,
            'message' => 'Unauthorized: Please login first'
        ]);
    }


    $validateTransaction = Validator::make($request->all(), [
        'id' => 'required|exists:users,id',     
        'start_date' => 'required|date_format:d/m/Y', 
        'end_date' => 'required|date_format:d/m/Y|after_or_equal:start_date',
    ]);

    if ($validateTransaction->fails()) {
        return response()->json([
            'errors' => $validateTransaction->errors(),
            'status' => HttpStatusCode::BAD_REQUEST,
            'message' => 'Validation errors'
        ]);
    }

    $startDate = Carbon::createFromFormat('d/m/Y', $request->start_date)->format('Y-m-d');
    $endDate = Carbon::createFromFormat('d/m/Y', $request->end_date)->format('Y-m-d');

    $transactions = Account::where('user_id', $request->id)
        ->whereBetween('date', [$startDate, $endDate])
        ->get();

    if ($transactions->isEmpty()) {
        return response()->json([
            'status' => HttpStatusCode::NOT_FOUND,
            'message' => 'No transactions found for this user within the given date range'
        ]);
    }

    return response()->json([
        'status' => HttpStatusCode::SUCCESS,
        'message' => 'Transactions fetched successfully',
        'data' => $transactions->map(function ($transaction) {
            return [
                'id' => $transaction->id,
                'user_id' => $transaction->user_id,
                'amount' => $transaction->amount,
                'date' => Carbon::parse($transaction->date)->format('d/m/Y'),
                'type' => $transaction->type,
                'description' => $transaction->description,
            ];
        })
    ]);
}
public function report(Request $request)
{
    try {
        $user = JWTAuth::parseToken()->authenticate();
    } catch (TokenExpiredException $e) {
        return response()->json([
            'status' => HttpStatusCode::UNAUTHORIZED,
            'message' => 'Token has expired. Please login again.'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 401,
            'message' => 'Authentication failed.',
        ]);
    }

   
    $userId = $request->input('user_id'); 
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');

    if (!$userId || !$startDate || !$endDate) {
        return response()->json([
            'status' => HttpStatusCode::BAD_REQUEST,
            'message' => 'User ID, start date, and end date are required.'
        ]);
    }

    $accounts = Account::where('user_id', $userId)
        ->whereBetween('date', [$startDate, $endDate])
        ->orderBy('date', 'asc')
        ->get();

    if ($accounts->isEmpty()) {
        return response()->json([
            'status' => 404,
            'message' => 'No account records found for this user within the selected date range.'
        ]);
    }

    $groupedByMonth = $accounts->groupBy(function ($transaction) {
        return Carbon::parse($transaction->date)->format('F Y');
    });

    $monthlyReports = [];

    foreach ($groupedByMonth as $month => $transactions) {
        $totalIncome = $transactions->where('type', 'income')->sum('amount');
        $totalExpense = $transactions->where('type', 'expense')->sum('amount');
        $profitOrLoss = $totalIncome - $totalExpense;

        $transactionsData = $transactions->map(function ($transaction) {
            return [
                'trans_id' => $transaction->id, 
                'trans_amount' => $transaction->amount,
                'type' => $transaction->type,
                'description' => $transaction->description,
                'date' => $transaction->date, 
            ];
        });

        $monthlyReports[] = [
            'month' => $month,
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'status' => $profitOrLoss >= 0 ? 'profit' : 'loss',
            'amount' => abs($profitOrLoss),
            'transactions' => $transactionsData, 
        ];
    }

    return response()->json([
        'status' => HttpStatusCode::SUCCESS,
        'message' => 'Account details fetched successfully',
        'data' => [
            [
                'user_id' => $userId,
                'user_name' => $accounts->first()->user->name,
                'monthly_reports' => $monthlyReports,
            ]
        ]
    ]);
}
    

}