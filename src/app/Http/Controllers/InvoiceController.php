<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\InvoiceHeader;
use App\Models\InvoiceLine;
use App\Models\Item;
use App\Models\SalesEmployee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

/**
 * ============================================================================
 * InvoiceController
 * ============================================================================
 * Purpose: Core transactional controller for handling SAP Business One style
 * A/R Invoice operations, line calculations, auto-suggest APIs, and SQL transactions.
 * 
 * Key Functions:
 *  - index()            : Renders the protected main invoice interface.
 *  - searchCustomers()  : Live search endpoint for real-time customer code/name lookup.
 *  - store()            : Validates input, calculates totals, handles approval checks,
 *                         and performs atomic DB writes.
 * ============================================================================
 */
class InvoiceController extends Controller
{
    /**
     * Display the invoice creation interface.
     *
     * @return View
     */
    public function index(): View
    {
        $salesEmployees = SalesEmployee::all();
        $items = Item::all();
        $nextDocNum = (InvoiceHeader::max('doc_num') ?? 14228865) + 1;
        
        return view('invoice.index', compact('salesEmployees', 'items', 'nextDocNum'));
    }

 /**
     * Live search endpoint for sale employee code or name lookup.
     *
     * @param  Request  $request
     * @return JsonResponse
     */


    public function searchSalesEmployees(Request $request): JsonResponse
{
    $query = trim($request->input('q', ''));

    $employees = SalesEmployee::query()
        ->when($query !== '', function ($q) use ($query) {
            // Group OR conditions so they don't break future query chains
            $q->where(function ($subQuery) use ($query) {
                $subQuery->where('emp_id', 'LIKE', "%{$query}%")
                         ->orWhere('emp_name', 'LIKE', "%{$query}%");
            });
        })
        ->limit(10)
        ->get(['emp_id', 'emp_name']);

    return response()->json($employees);
}
    /**
     * Live search endpoint for customer code or name lookup.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
   public function searchCustomers(Request $request): JsonResponse
   {
    // Retrieve search input from 'q', 'query', or 'search' parameter and trim whitespace
    $query = trim($request->input('q', $request->input('query', $request->input('search', ''))));

    $customers = Customer::query()
        ->when($query !== '', function ($q) use ($query) {
            $q->where(function ($sub) use ($query) {
                // Using ILIKE or lower-cased LIKE for cross-database case-insensitivity
                $sub->where('customer_code', 'LIKE', "%{$query}%")
                    ->orWhere('customer_name', 'LIKE', "%{$query}%");
            });
        })
        ->limit(10)
        ->get(['customer_code', 'customer_name']);

    return response()->json($customers);
}

    /**
     * Store a newly created A/R Invoice in the database.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_code'                 => ['required', 'exists:customers,customer_code'],
            'posting_date'                  => ['required', 'date'],
            'sales_employee_id'             => ['required', 'exists:sales_employees,emp_id'],
            'remarks'                       => ['required', 'string', 'min:1'],
            'discount'                      => ['nullable', 'numeric', 'min:0', 'max:100'],
            'lines'                         => ['required', 'array', 'min:1'],
            'lines.*.item_no'               => ['required', 'exists:items,item_no'],
            'lines.*.item_description'      => ['required', 'string'],
            'lines.*.quantity'              => ['required', 'numeric', 'min:1'],
            'lines.*.price_before_discount' => ['required', 'numeric', 'min:0'],
            'lines.*.discount'              => ['nullable', 'numeric', 'min:0', 'max:50'],
        ], [
            'remarks.required'      => 'The Remarks field is mandatory.',
            'lines.*.discount.max'  => 'Item discount cannot exceed 50%.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false, 
                'errors'  => $validator->errors()->all()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $totalBeforeDiscount = 0;
            $sumLinesAfterDiscount = 0;

            // 1. Calculate line-item totals
            foreach ($request->lines as $line) {
                $priceBefore = (float) $line['price_before_discount'];
                $qty         = (float) $line['quantity'];
                $discount    = (float) ($line['discount'] ?? 0);

                $lineTotal = $qty * ($priceBefore * (1 - ($discount / 100)));
                
                $totalBeforeDiscount += ($qty * $priceBefore);
                $sumLinesAfterDiscount += $lineTotal;
            }

            // 2. Apply document-level header discount if supplied
            $headerDiscount = (float) ($request->discount ?? 0);
            $totalAfterDiscount = $sumLinesAfterDiscount * (1 - ($headerDiscount / 100));

            // 3. Document approval status threshold check
            $requiresApproval = $totalAfterDiscount > 10000;

            // 4. Create Header Record
            $header = InvoiceHeader::create([
                'customer_code'         => $request->customer_code,
                'posting_date'          => $request->posting_date,
                'sales_employee_id'     => $request->sales_employee_id,
                'remarks'               => $request->remarks,
                'total_before_discount' => $totalBeforeDiscount,
                'discount_percent'      => $headerDiscount,
                'total_after_discount'  => $totalAfterDiscount,
                'requires_approval'     => $requiresApproval,
            ]);

            // 5. Create Line Items
            foreach ($request->lines as $index => $line) {
                $priceBefore = (float) $line['price_before_discount'];
                $discount    = (float) ($line['discount'] ?? 0);
                $qty         = (float) $line['quantity'];

                $priceAfterDisc = $priceBefore * (1 - ($discount / 100));
                
                InvoiceLine::create([
                    'doc_num'               => $header->doc_num,
                    'line_num'              => $index + 1,
                    'item_no'               => $line['item_no'],
                    'item_description'      => $line['item_description'],
                    'quantity'              => $qty,
                    'price_before_discount' => $priceBefore,
                    'discount_percent'      => $discount,
                    'price_after_discount'  => $priceAfterDisc,
                    'line_total'            => $qty * $priceAfterDisc,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true, 
                'message' => 'Invoice saved successfully!', 
                'doc_num' => $header->doc_num
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false, 
                'errors'  => [$e->getMessage()]
            ], 500);
        }
    }
}