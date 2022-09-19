<?php

namespace App\Http\Controllers;

use App\Loan;
use App\Payment;
use App\RepaymentSchedule;
use Illuminate\Http\Request;

class LoansController extends Controller
{
    private function checkLoanExistence($id)
    {
        if (!empty($id)) {
            $existence_result = Loan::find($id);

            if (!empty($existence_result)) {
                return $this->finalResponse(true, 'Loan record found!', NULL, $existence_result);
            } else {
                return $this->finalResponse(false, 'Loan record not found!');
            }
        } else {
            return $this->finalResponse(false, 'Invalid Loan Id!');
        }
    }

    private function calculateEMIAmount($amount, $tenor)
    {
        if (!empty($amount) && !empty($tenor)) {
            return ceil($amount / $tenor);
        }
        return 0;
    }

    private function checkLoanRepaymentUpdateStatus($loan_id)
    {
        if (!empty($loan_id)) {
            $existence_result = Loan::find($loan_id);

            if (!empty($existence_result)) {
                $repayment_result = RepaymentSchedule::where(['loan_id' => $loan_id])
                    ->where('pending_amount','>', 0)->first();
                
                if(empty($repayment_result)){
                    $existence_result->status = 2;
                    $existence_result->save();
                    return $this->finalResponse(true, 'Loan status updated!');
                }
                return $this->finalResponse(false, 'Loan status not updated!');
            } else {
                return $this->finalResponse(false, 'Loan record not found!');
            }
        } else {
            return $this->finalResponse(false, 'Invalid Loan Id!');
        }
    }

    function applyforLoan(Request $request)
    {
        $request_data = $request->all();
        $required_keys = ['username','secret','amount','loan_tenure'];
        $missing_keys = [];
        foreach($required_keys as $key){
            if(!array_key_exists($key, $request_data)){
                $missing_keys[] = $key;
            }
        }
        
        if(!empty($missing_keys)){
            $missing_keys_str = implode(',',$missing_keys);
            return $this->finalResponse(false, 'Input data missing ('.$missing_keys_str.')');
        }
        $user_response = $this->doesUserExist($request->username, $request->secret);
        $status = data_get($user_response,'status');
        $user_data = data_get($user_response,'data');
        if (!$status) {
            return $user_response;
        }

        $user_id = data_get($user_data,'id');
        $amount = data_get($request,'amount');
        if($amount <= 0){
            return $this->finalResponse(false, 'Loan amount is invalid');
        }
        $loan = new Loan();

        $loan->status = 0;
        $loan->user_id = $user_id;
        $loan->loan_amt = $amount;
        $loan->loan_tenure = $request->loan_tenure;

        try {
            $result = $loan->save();

            if ($result) {
                $loan_tenure = data_get($request,'loan_tenure');
                $loan_id = data_get($loan,'id');
                $repayment_amount = $this->calculateEMIAmount($amount, $loan_tenure);
                if(empty($repayment_amount)) {
                    return $this->finalResponse(false, 'Invalid Repayment Amount!');
                }
                
                for($i=1; $i <= $loan_tenure; $i++){
                    $repaymentSchedule = new RepaymentSchedule();

                    $repaymentSchedule->status = 0;
                    $repaymentSchedule->loan_id = $loan_id;
                    $repaymentSchedule->repayment_date = date('Y-m-d', strtotime('+'.(7*$i).' day'));
                    $repaymentSchedule->repayment_amount = $repayment_amount;
                    $repaymentSchedule->pending_amount = $repayment_amount;
                    $repaymentSchedule->amount_received = 0;

                    $result = $repaymentSchedule->save();
                }
                return $this->finalResponse(true, 'Loan applicaton successful!');
            } else {
                return $this->finalResponse(false, 'Unable to apply for loan!');
            }
        } catch (\Exception $e) {
            return $this->finalResponse();
        }
    }

    function approveLoan(Request $request)
    {
        $request_data = $request->all();
        $required_keys = ['username','secret','loan_id'];
        $missing_keys = [];
        foreach($required_keys as $key){
            if(!array_key_exists($key, $request_data)){
                $missing_keys[] = $key;
            }
        }
        
        if(!empty($missing_keys)){
            $missing_keys_str = implode(',',$missing_keys);
            return $this->finalResponse(false, 'Input data missing ('.$missing_keys_str.')');
        }
        $userResponse = $this->doesUserExist($request->username, $request->secret);
        $status = data_get($userResponse,'status');
        $userData = data_get($userResponse,'data');
        if ($status) {
            $loan_id = data_get($request,'loan_id');
            $response_result = $this->checkLoanExistence($loan_id);
            $response_status = data_get($response_result,'status');
            $loan_data = data_get($response_result,'data');
            if(!$response_status){
                return $response_result;
            }

            
            $loan_result = Loan::where(['id' => $loan_id])->update(['status' => 1]);

            if (empty($loan_result)) {
                return $this->finalResponse();
            }

            $repayment_result = RepaymentSchedule::where(['loan_id' => $loan_id])->update(['status' => 1]);

            if (empty($repayment_result)) {
                return $this->finalResponse();
            }
            return $this->finalResponse(true, 'Loan applicaton approved!');
        
        }
    }

    function payLoanEMI(Request $request)
    {
        $request_data = $request->all();
        $required_keys = ['username','secret','schedule_id','amount'];
        $missing_keys = [];
        foreach($required_keys as $key){
            if(!array_key_exists($key, $request_data)){
                $missing_keys[] = $key;
            }
        }
        
        if(!empty($missing_keys)){
            $missing_keys_str = implode(',',$missing_keys);
            return $this->finalResponse(false, 'Input data missing ('.$missing_keys_str.')');
        }
        if ($this->doesUserExist($request->username, $request->secret)) {
            $schedule_id = data_get($request,'schedule_id');
            $repayment_result = RepaymentSchedule::find($schedule_id);

            if (!empty($repayment_result)) {

                if ($repayment_result->pending_amount <= 0) {
                    return $this->finalResponse(true, 'EMI Paid!');
                }

                if ($request->amount < $repayment_result->repayment_amount) {
                    return $this->finalResponse(false, 'EMI amount mismatched!', 'You have to pay min ' . $repayment_result->repayment_amount . ' every EMI cycle.');
                }

                if ($request->amount > $repayment_result->repayment_amount) {
                    return $this->finalResponse(false, 'EMI amount mismatched!', 'You can only pay max ' . $repayment_result->repayment_amount . ' every EMI cycle.');
                }

                $payment = new Payment();

                $payment->payment_status = 1;
                $payment->repayment_schedule_id = $schedule_id;
                $payment->amount_received = $request->amount;

                $result = $payment->save();

                if ($result) {
                    $pending_amount = ($repayment_result->pending_amount - $request->amount);

                    $schedule_update_result = RepaymentSchedule::where(['id' => $schedule_id])->update(['status' => 2, 'pending_amount' => $pending_amount, 'amount_received' => $request->amount]);

                    if ($schedule_update_result) {
                        $loan_id = data_get($repayment_result,'loan_id');
                        $this->checkLoanRepaymentUpdateStatus($loan_id);
                        return $this->finalResponse(true, 'EMI Paid successfully!');
                    } else {
                        return $this->finalResponse();
                    }
                } else {
                    return $this->finalResponse(false, 'Unable to Pay EMI!');
                }
            } else {
                return $this->finalResponse(false, 'Unable to load payment infromation!');
            }
        }
    }
}
