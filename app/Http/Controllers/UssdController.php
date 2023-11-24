<?php

namespace App\Http\Controllers;

use App\Traits\ResponseTrait;
use App\Traits\SessionTrait;
use App\Traits\UserTrait;
use GuzzleHttp\Psr7\MessageTrait;
use Illuminate\Http\Request;

class UssdController extends Controller
{
    use ResponseTrait, UserTrait, MessageTrait, SessionTrait;
    //

    public  function process(Request $request)
    {
        try {
            $user = $this->checkIfUserExists($request->phoneNumber);

            if (!$user) {
                return $this->writeResponse("Your not registered on this service", true);
            }
            if ($request->text == "") {
                //welcome the user
                $this->storeUserSession($request, "00");
                $companyDetails =  $this->getUserCompanyDetails($request->phoneNumber);
                return $this->welcomeUser($request, $companyDetails->name);
            } else {
                //get last user code
                $lastUserCode = $this->getLastUserSession($request->phoneNumber)->last_user_code;
                switch ($lastUserCode) {
                    case '00':
                        if ($request->text == "1") {
                            $this->storeUserSession($request, "Request Loan");
                            //check if loan exists
                            $res =  $this->getLoanDetails($request->phoneNumber);
                            if ($res) {
                                return $this->writeResponse("You have a loan", true);
                            } else {
                                return $this->loanType($request, $request->phoneNumber);
                            }
                        } elseif ($request->text == "2") {
                            $this->storeUserSession($request, "Loan Status");
                            $res =  $this->getLoanDetails($request->phoneNumber);
                            if ($res) {
                                $total_amount = intval($res->amount) + intval($res->interest);
                                return $this->writeResponse("You have a loan of UGX $total_amount", true);
                            } else {
                                return $this->writeResponse("You dont have any loan", true);
                            }
                        } elseif ($request->text == "3") {
                            $this->storeUserSession($request, "Loan Balance");
                            $res =  $this->getLoanDetails($request->phoneNumber);
                            if ($res) {
                                $total_amount = intval($res->amount) + intval($res->interest);
                                return $this->writeResponse("You have a loan of UGX $total_amount", true);
                            } else {
                                return $this->writeResponse("You dont have any loan", true);
                            }
                        } elseif ($request->text == "4") {
                            $this->storeUserSession($request, "Pay Back");
                            //check if loan exists
                            $res =  $this->getLoanDetails($request->phoneNumber);
                            if ($res) {
                                $total_amount = intval($res->amount) + intval($res->interest);
                                $response = "You have a loan of UGX $total_amount\n";
                                $response .= "1. Pay Back\n";
                                $this->storeUserSession($request, "Pay Back");
                                return $this->writeResponse($response, false);
                            } else {
                                return $this->writeResponse("You dont have any loan", true);
                            }
                        } else if ($request->text == "5") {
                            $this->storeUserSession($request, "Old Pin");
                            return $this->writeResponse("Enter your pin", false);
                        } elseif ($request->text == "6") {
                            return $this->writeResponse("Your to be contacted soon", true);
                        } else {
                            return $this->writeResponse("We did not understand your request 00", true);
                        }
                        break;
                    case "Old Pin":
                        //extract  pin
                        $pin = $request->text;
                        $actualPin =  explode("*", $request->text);
                        $pin = $actualPin[1];
                        $checkPin = $this->checkPin($pin, $request->phoneNumber);
                        if ($checkPin) {
                            $this->storeUserSession($request, "Reset Pin");
                            return $this->writeResponse("Enter new pin", false);
                        } else {
                            return $this->writeResponse("You entered an invalid pin", true);
                        }
                        break;
                    case "Reset Pin":
                        $pin = $request->text;
                        $actualPin =  explode("*", $request->text);
                        $pin = $actualPin[2];
                        $checkPin = $this->updatePin($pin, $request->phoneNumber);
                        if ($checkPin) {
                            return $this->writeResponse("Pin reset successfully", true);
                        } else {
                            return $this->writeResponse("You entered an invalid pin", true);
                        }
                        break;
                    case 'Request Loan':
                        if ($request->text == "1*1") {

                            $this->storeUserSession($request, "Personal Loan");
                            //show package
                            return $this->personalAvailablePackages($request, $request->phoneNumber);
                        } elseif ($request->text == "1*2") {
                            $this->storeUserSession($request, "Business Loan");
                            //show package
                            return $this->personalAvailablePackages($request, $request->phoneNumber);
                        } elseif ($request->text == "1*3") {
                            $this->personalAvailablePackages($request, $request->phoneNumber);
                            //show package
                            return $this->loanType($request, $request->phoneNumber);
                        } elseif ($request->text == "1*4") {
                            $this->storeUserSession($request, "Property Loan");
                        }
                    case "Personal Loan":
                        //ask for pin
                        $this->storeUserSession($request, "ConformPersonalLoan");
                        return $this->writeResponse("Enter your pin to confirm", false);
                        break;
                    case "Business Loan":
                        $this->storeUserSession($request, "ConformBusinessLoan");
                        return $this->writeResponse("Enter your pin to confirm", false);
                        break;
                    case "Property Loan":
                        $this->storeUserSession($request, "ConformPropertyLoan");
                        return $this->writeResponse("Enter your pin to confirm", false);
                        break;

                    case "ConformPersonalLoan":

                        $pin = explode("*", $request->text);
                        $pin = $pin[3];
                        $checkPin = $this->checkPin($pin, $request->phoneNumber);
                        if (!$checkPin) {
                            return $this->writeResponse("You entered an invalid pin", true);
                        }
                        //["personal", "business", 'property']
                        $this->createUserLoan($request->phoneNumber, "10000", "personal");
                        return $this->writeResponse("You have successfully requested a loan", true);
                        break;
                    case "ConformBusinessLoan":
                        $pin = explode("*", $request->text);
                        $pin = $pin[3];
                        $checkPin = $this->checkPin($pin, $request->phoneNumber);

                        if (!$checkPin) {
                            return $this->writeResponse("You entered an invalid pin", true);
                        }
                        $this->createUserLoan($request->phoneNumber, "10000", "business");
                        return $this->writeResponse("You have successfully requested a loan", true);
                        break;
                    case "ConformPropertyLoan":
                        $pin = explode("*", $request->text);
                        $pin = $pin[3];
                        $checkPin = $this->checkPin($pin, $request->phoneNumber);
                        if (!$checkPin) {
                            return $this->writeResponse("You entered an invalid pin", true);
                        }
                        $this->createUserLoan($request->phoneNumber, "10000", "property");
                        return $this->writeResponse("You have successfully requested a loan", true);
                        break;
                    case "Pay Back":
                        $this->payBackLoan($request->phoneNumber, "11000");
                        return $this->writeResponse("You have successfully paid back your loan", true);
                        break;
                    default:
                        break;
                }
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    private function welcomeUser(Request $request, string $name = "user")
    {
        $response  = "Welcome to $name:\n";
        $response .= "1. Request Loan\n";
        $response .= "2. Check Loan Status\n";
        $response .= "3. Check Loan Balance\n";
        $response .= "4. PayBack\n";
        $response .= "5. Reset Pin\n";
        $response .= "6. Help\n";

        //store user session
        $this->storeUserSession($request, "00");

        return $this->writeResponse($response, false);
    }

    private function loanType(Request $request, string $phoneNumber)
    {
        //get user loan type
        $response = "What type of loan would you like to apply for?\n";
        $response .= "1. Personal\n";
        $response .= "2. Business\n";
        $response .= "3. Property Loan \n";
        return $this->writeResponse($response, false);
    }

    private function personalAvailablePackages(Request $request, string $phoneNumber)
    {
        //only one package fo 10,000
        $response = " Available packages for the loan paid per month\n";
        $response .= "1. Akendo (10,000)\n";
        //talk about the repayment cycle as monthly

        // $this->storeUserSession($request, "Personal Loan");
        return $this->writeResponse($response, false);
    }

    private function paymentCycle(Request $request, string $phoneNumber)
    {
        $response = "How often would you like to make payments?\n";
        $response .= "1. Monthly\n";
        $this->storeUserSession($request, "Payment Cycle");
        return $this->writeResponse($response, false);
    }
}
