<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    /**
     * Display a listing of AI-generated products.
     */
    public function index(Request $request)
    {
        $query = Product::with(['cost', 'content'])
            ->orderBy('created_at', 'desc');

        // Filter by status if provided
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by approval status if provided
        if ($request->has('is_approved')) {
            $query->where('is_approved', $request->boolean('is_approved'));
        }

        // Search by name if provided
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->paginate(20)->withQueryString();

        return Inertia::render('Admin/Products/Index', [
            'products' => $products,
            'filters' => [
                'status' => $request->status ?? 'all',
                'is_approved' => $request->is_approved,
                'search' => $request->search ?? '',
            ],
        ]);
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        return Inertia::render('Admin/Products/Show', [
            'product' => $product->load(['cost', 'content', 'adCopies']),
        ]);
    }

    /**
     * Approve a product.
     */
    public function approve(Product $product)
    {
        $product->update([
            'is_approved' => true,
            'is_active' => true,
            'status' => 'approved',
        ]);

        return redirect()->back()->with('success', '상품이 승인되었습니다.');
    }

    /**
     * Reject a product.
     */
    public function reject(Product $product, Request $request)
    {
        $product->update([
            'is_approved' => false,
            'is_active' => false,
            'status' => 'rejected',
        ]);

        return redirect()->back()->with('success', '상품이 거부되었습니다.');
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        return Inertia::render('Admin/Products/Edit', [
            'product' => $product->load(['cost', 'content', 'adCopies']),
        ]);
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'target_customer' => 'required|string',
            'selling_points' => 'required|json',
            'recommended_price' => 'required|numeric|min:0',
            'profit_margin' => 'required|numeric|min:0|max:100',

            // Cost fields
            'cost.product_unit_price' => 'nullable|numeric|min:0',
            'cost.international_shipping' => 'nullable|numeric|min:0',
            'cost.customs_duty' => 'nullable|numeric|min:0',
            'cost.domestic_shipping' => 'nullable|numeric|min:0',
            'cost.risk_cost' => 'nullable|numeric|min:0',
            'cost.total_cost' => 'required|numeric|min:0',
            'cost.supplier_name' => 'nullable|string|max:255',
            'cost.supplier_url' => 'nullable|url',
            'cost.cost_notes' => 'nullable|string',

            // Content fields
            'content.seo_title' => 'nullable|string|max:255',
            'content.header_copy' => 'nullable|string',
            'content.key_features' => 'nullable|string',
            'content.full_description' => 'nullable|string',
            'content.usage_guide' => 'nullable|string',
            'content.precautions' => 'nullable|string',
            'content.recommendation' => 'nullable|string',

            // Ad copies
            'ad_copies' => 'nullable|array',
            'ad_copies.*.id' => 'nullable|integer',
            'ad_copies.*.platform' => 'required|string|in:naver,google,meta',
            'ad_copies.*.headline' => 'required|string|max:255',
            'ad_copies.*.description' => 'required|string',
            'ad_copies.*.variation' => 'required|integer|min:1',
        ]);

        // Update product
        $product->update([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'target_customer' => $validated['target_customer'],
            'selling_points' => $validated['selling_points'],
            'recommended_price' => $validated['recommended_price'],
            'profit_margin' => $validated['profit_margin'],
        ]);

        // Update cost
        if (isset($validated['cost'])) {
            $product->cost()->updateOrCreate(
                ['product_id' => $product->id],
                $validated['cost']
            );
        }

        // Update content
        if (isset($validated['content'])) {
            $product->content()->updateOrCreate(
                ['product_id' => $product->id],
                $validated['content']
            );
        }

        // Update ad copies
        if (isset($validated['ad_copies'])) {
            foreach ($validated['ad_copies'] as $adData) {
                if (isset($adData['id'])) {
                    // Update existing ad copy
                    $product->adCopies()->where('id', $adData['id'])->update([
                        'platform' => $adData['platform'],
                        'headline' => $adData['headline'],
                        'description' => $adData['description'],
                        'variation' => $adData['variation'],
                    ]);
                } else {
                    // Create new ad copy
                    $product->adCopies()->create([
                        'platform' => $adData['platform'],
                        'headline' => $adData['headline'],
                        'description' => $adData['description'],
                        'variation' => $adData['variation'],
                    ]);
                }
            }
        }

        return redirect()->route('admin.products.show', $product->id)
            ->with('success', '상품이 수정되었습니다.');
    }
}
