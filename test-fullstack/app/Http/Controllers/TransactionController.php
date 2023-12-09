<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Http\Requests\TransactionRequest;
use App\Models\Product;
use App\Models\Transaction;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        try {
            $Transactions = Transaction::paginate($request->input('row', 5));
            $returnJson = Helpers::formatJson($Transactions, 200, "Success getting paginated Transactions", true);
        } catch (\Throwable $th) {
            $returnJson = Helpers::formatJson(null, 500, $th->getMessage() . ' line: ' . $th->getLine(), false);
        }
        return response()->json($returnJson['data'], $returnJson['status_response']);
    }


    public function store(TransactionRequest $request)
    {
        try {
            DB::beginTransaction();
            $getProduct = Product::find($request->input('product_id'));
            if ($getProduct) {
                if ($getProduct->stock == 0) {
                    $returnJson = Helpers::formatJson(null, 404, "The product you selected is empty", false);
                    return response()->json($returnJson['data'], $returnJson['status_response']);
                }

                $headers = [
                    'x-api-key' => 'DATAUTAMA',
                    'X-SIGNATURE' => '8796a8cefcd8b6b00f028751f1f21df1f706e3fa08e8651ad3d32f7b85f9975e',
                ];

                $data = [
                    "quantity" => $request->input('quantity'),
                    "price" => intval($getProduct->price),
                    "payment_amount" => $getProduct->price * $request->input('quantity')
                ];

                $client = new Client();
                $response = $client->post('https://tes-skill.datautama.com/test-skill/api/v1/transactions', [
                    'json' =>  $data,
                    'headers' => $headers
                ]);
                // Get the response status code
                $statusCode = $response->getStatusCode();
                $body = json_decode($response->getBody()->getContents());
                if ($statusCode === 200) {
                    $Transaction = new Transaction();
                    $Transaction->reference_no = $body->data->reference_no;
                    $Transaction->price = intval($getProduct->price);
                    $Transaction->quantity = $request->input('quantity');
                    $Transaction->payment_amount = $getProduct->price * $request->input('quantity');
                    $Transaction->product_id = $request->input('product_id');
                    $Transaction->save();

                    $getProduct->update([
                        "stock" => $getProduct->stock - 1
                    ]);
                    $returnJson = Helpers::formatJson(null, 200, "Success save Transaction", true);
                } else {
                    $returnJson = Helpers::formatJson(null, $statusCode, $body->data->message, false);
                }
            } else {
                $returnJson = Helpers::formatJson(null, 404, "Product id not found", false);
            }
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            $returnJson = Helpers::formatJson(null, 500, $th->getMessage() . ' line: ' . $th->getLine(), false);
        }
        return response()->json($returnJson['data'], $returnJson['status_response']);
    }

    public function destroy($id)
    {
        try {
            $Transaction = Transaction::findOrFail($id);
            $Transaction->delete();
            $returnJson = Helpers::formatJson(null, 200, "Transaction deleted successfully", true);
        } catch (\Throwable $th) {
            $returnJson = Helpers::formatJson(null, 500, $th->getMessage() . ' line: ' . $th->getLine(), false);
        }
        return response()->json($returnJson['data'], $returnJson['status_response']);
    }
}
