<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Helpers\GenerateCode;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->validate($request, [
            'search' => 'nullable|string',
            'take'   => 'nullable|numeric',
            'skip'   => 'nullable|numeric'
        ]);

        $products = Product::when(
            $request->search,
            fn ($q) => $q->where(
                'name',
                'like',
                '%' . $request->search . '%'
            )->orWhere(
                'code',
                'like',
                '%' . $request->search . '%'
            )
        )->when(
            $request->take,
            fn ($q) => $q->take($request->take)
        )->when(
            $request->skip,
            fn ($q) => $q->skip($request->skip)
        )->get();

        return response()->json([
            'code'    => 200,
            'message' => 'Success fetch products.',
            'data'    => $products
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name'         => 'required|string',
            'brand'        => 'required|string',
            'categories'   => 'required|array',
            'categories.*' => 'required|string',
            'price'        => 'required|numeric',
            'image'        => 'required'
        ]);

        if ($request->hasFile('image')) {
            $this->validate($request, [
                'image' => 'file|image|mimes:jpg,jpeg,png'
            ]);

            $image         = $request->file('image');
            $imageFilename = Str::random(15) . '-' . time() . '.webp';
            $image->storeAs('products/', $imageFilename);
        } else {
            $this->validate($request, [
                'image' => 'string'
            ]);

            $imageFilename = Str::random(15) . '_' . time() . '.webp';
            $imageFile = base64_decode(preg_replace(
                '#^data:image/\w+;base64,#i',
                '',
                $request->input('image')
            ));

            Storage::put('products/' . $imageFilename, $imageFile);
        }

        $imageUrl = Storage::url('products/'.$imageFilename);

        try {
            $product = Product::create([
                'name'       => ucwords($request->name),
                'code'       => GenerateCode::productCode(),
                'brand'      => $request->brand,
                'categories' => $request->categories,
                'price'      => $request->price,
                'image'      => $imageUrl
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code'    => 500,
                'message' => 'Failed add products.',
                'error'   => $e->getMessage()
            ], 500);
        }

        return response()->json([
            'code'    => 200,
            'message' => 'Success add products.',
            'data'    => $product
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $product = Product::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'code'    => 404,
                'message' => 'Product not found.',
                'error'   => $e->getMessage()
            ], 404);
        }

        return response()->json([
            'code'    => 200,
            'message' => 'Success fetch products.',
            'data'    => $product
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $product = Product::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'code'    => 404,
                'message' => 'Product not found.',
                'error'   => $e->getMessage()
            ], 404);
        }

        $this->validate($request, [
            'name'         => 'required|string',
            'brand'        => 'required|string',
            'categories'   => 'required|array',
            'categories.*' => 'required|string',
            'price'        => 'required|numeric',
        ]);

        $dataRequest = [
            'name'       => ucwords($request->name),
            'brand'      => $request->brand,
            'categories' => $request->categories,
            'price'      => $request->price,
        ];

        if ($request->has('image') && $request->image !== null) {
            if ($request->hasFile('image')) {
                $this->validate($request, [
                    'image' => 'required|file|image|mimes:jpg,jpeg,png'
                ]);

                $image         = $request->file('image');
                $imageFilename = Str::random(15) . '-' . time() . '.webp';
                $image->storeAs('products/', $imageFilename);
            } else {
                $this->validate($request, [
                    'image' => 'required|string'
                ]);

                $imageFilename = Str::random(15) . '_' . time() . '.webp';
                $imageFile = base64_decode(preg_replace(
                    '#^data:image/\w+;base64,#i',
                    '',
                    $request->input('image')
                ));

                Storage::put('products/' . $imageFilename, $imageFile);
            }

            $dataRequest['image'] = Storage::url('products/' . $imageFilename);
        }

        try {
            $product->update($dataRequest);
        } catch (\Exception $e) {
            return response()->json([
                'code'    => 500,
                'message' => 'Failed update products.',
                'error'   => $e->getMessage()
            ], 500);
        }

        return response()->json([
            'code'    => 200,
            'message' => 'Success update products.',
            'data'    => $product
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $product = Product::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'code'    => 404,
                'message' => 'Product not found.',
                'error'   => $e->getMessage()
            ], 404);
        }

        try {
            $product->delete();
        } catch (\Exception $e) {
            return response()->json([
                'code'    => 500,
                'message' => 'Failed delete products.',
                'error'   => $e->getMessage()
            ], 500);
        }

        return response()->json([
            'code'    => 200,
            'message' => 'Success delete products.',
            'data'    => $product
        ], 200);
    }
}
