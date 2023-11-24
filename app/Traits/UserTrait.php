<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

trait UserTrait
{
    public function checkPin(string $pin, string $phoneNumber)
    {
        //check user pin
        $getUser = DB::table('users')->where('phone_number', $phoneNumber)->first();
        $hashedPin = $getUser->pin;
        $pin = str_replace(" ", "", $pin);
        if (Hash::check($pin, $hashedPin)) {
            return true;
        } else {
            return false;
        }
    }

    public function updatePin(string $pin, string $phoneNumber)
    {
        //remove any spaces
        $pin = str_replace(" ", "", $pin);
        //print_r($pin);
        //update user pin
        $hashedPin = Hash::make($pin);
        //print_r($hashedPin);
        DB::table('users')->where('phone_number', $phoneNumber)->update(['pin' => $hashedPin]);
        return true;
    }


    public function getAccountBalance(string $phoneNumber)
    {
        //get user account balance
        $getUser = DB::table('users')->where('phone_number', $phoneNumber)->first();
        return  "UGX " . "" . $getUser->account_balance;
    }

    public function checkIfUserExists(string $phoneNumber)
    {
        //check if user exists
        $getUser = DB::table('users')->where('phone_number', $phoneNumber)->first();
        if ($getUser) {
            return true;
        } else {
            return false;
        }
    }

    //get user details
    public function getUserDetails(string $phoneNumber)
    {
        return DB::table('users')->where('phone_number', $phoneNumber)->first();
    }

    //get user community details
    public function getUserCompanyDetails(string $phoneNumber)
    {
        $getUser = DB::table('users')->where('phone_number', $phoneNumber)->first();
        return DB::table('companies')->where('id', $getUser->company_id)->first();
    }

    public function deposit(string $phoneNumber, string $amount)
    {
        //deposit
        $getUser = DB::table('users')->where('phone_number', $phoneNumber)->first();
        //create a  transaction
        DB::table('transactions')->insert([
            'phone_number' => $phoneNumber,
            'amount' => $amount,
            'type' => 'credit',
            'status' => 'completed',
            'description' => 'Deposit',
            'community_id' => $getUser->community_id,
            'user_id' => $getUser->id,
            'reference' => Str::uuid()
        ]);
        //update the community account balance
        DB::table('communities')->where('id', $getUser->community_id)->update(['account_balance' => $getUser->account_balance + $amount]);
        //update user account balance
        DB::table('users')->where('phone_number', $phoneNumber)->update(['account_balance' => $getUser->account_balance + $amount]);
        return true;
    }

    //with draw
    public function withdraw(string $phoneNumber, string $amount)
    {
        //withdraw
        $getUser = DB::table('users')->where('phone_number', $phoneNumber)->first();
        //create a  transaction
        DB::table('transactions')->insert([
            'phone_number' => $phoneNumber,
            'amount' => $amount,
            'type' => 'debit',
            'status' => 'completed',
            'description' => 'Withdrawal',
            'community_id' => $getUser->community_id,
            'user_id' => $getUser->id,
            'reference' => Str::uuid()
        ]);
        //update the community account balance
        DB::table('communities')->where('id', $getUser->community_id)->update(['account_balance' => $getUser->account_balance - $amount]);
        //update user account balance
        DB::table('users')->where('phone_number', $phoneNumber)->update(['account_balance' => $getUser->account_balance - $amount]);
        return true;
    }

    public function getLoanDetails(string $phoneNumber)
    {
        $getUser = DB::table('users')->where('phone_number', $phoneNumber)->first();
        $loanDetails = DB::table('loans')->where('user_id', $getUser->id)->where('is_active', true)->first();
        if ($loanDetails) {
            return $loanDetails;
        } else {
            return false;
        }
    }

    public function createUserLoan(string $phoneNumber, string $amount, string $loanType)
    {
        try {
            //code...
            $getUser = DB::table('users')->where('phone_number', $phoneNumber)->first();
            $comapnyDetails = DB::table('companies')->where('id', $getUser->company_id)->first();
            //create loan
            DB::table('loans')->insert([
                'user_id' => $getUser->id,
                'amount' => $amount,
                'is_active' => true,
                'type' => $loanType,
                'company_id' => $comapnyDetails->id,
                'payment_frequency' => "monthly",
                'interest' => 1000,
                'term' => 2,
                'balance' => intval($amount) + 1000,
                'name' => $loanType
            ]);
            return true;
        } catch (\Throwable $th) {

            return false;
        }
    }

    public function payBackLoan(string $phoneNumber, string $amount)
    {
        //pay back loan
        $getUser = DB::table('users')->where('phone_number', $phoneNumber)->first();
        DB::table('loans')->where('user_id', $getUser->id)->update(['balance' => 0, "is_active" => false]);
        return true;
    }
}
