<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Http\Requests\ProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        try {
            $products = Product::paginate($request->input('row', 5));
            $returnJson = Helpers::formatJson($products, 200, "Success getting paginated products", true);
        } catch (\Throwable $th) {
            $returnJson = Helpers::formatJson(null, 500, $th->getMessage() . ' line: ' . $th->getLine(), false);
        }
        return response()->json($returnJson['data'], $returnJson['status_response']);
    }


    public function store(ProductRequest $request)
    {
        try {
            $product = new Product();
            $product->name = $request->input('name');
            $product->price = $request->input('price');
            $product->stock = $request->input('stock');
            $product->description = $request->input('description');
            $product->save();
            $returnJson = Helpers::formatJson(null, 200, "Success save product", true);
        } catch (\Throwable $th) {
            $returnJson = Helpers::formatJson(null, 500, $th->getMessage() . ' line: ' . $th->getLine(), false);
        }
        return response()->json($returnJson['data'], $returnJson['status_response']);
    }

    public function update(ProductRequest $request, $id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->name = $request->input('name');
            $product->price = $request->input('price');
            $product->stock = $request->input('stock');
            $product->description = $request->input('description');
            $product->save();
            $returnJson = Helpers::formatJson(null, 200, "Product updated successfully", true);
        } catch (\Throwable $th) {
            $returnJson = Helpers::formatJson(null, 500, $th->getMessage() . ' line: ' . $th->getLine(), false);
        }

        return response()->json($returnJson['data'], $returnJson['status_response']);
    }

    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete();
            $returnJson = Helpers::formatJson(null, 200, "Product deleted successfully", true);
        } catch (\Throwable $th) {
            $returnJson = Helpers::formatJson(null, 500, $th->getMessage() . ' line: ' . $th->getLine(), false);
        }
        return response()->json($returnJson['data'], $returnJson['status_response']);
    }
}
