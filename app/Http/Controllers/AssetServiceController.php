<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetService;
use App\Models\AssetsServiceEmail;
use App\Models\AssetType;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssetServiceController extends Controller
{


    public function create(Asset $asset)
    {
        return view('admin.assets.service', compact('asset'));
    }

    public function store(Request $request, Asset $asset)
    {
        $request->validate([
            'service_type' => 'required',
            'service_location' => 'required',
            'issue_description' => 'required',
            'vendor_name' => 'nullable'
        ]);

        $asset->services()->create($request->all());
        $asset->update(['status' => 'In Service']);
        return redirect()->route('assets.services.index', $asset)->with('success', 'Service request created!');
    }

    public function edit(AssetService $service)
    {
        return view('admin.assets.service', compact('service'));
    }

    public function update(Request $request, AssetService $service)
    {
        $request->validate([
            'service_status' => 'required',
            'service_cost' => 'nullable|numeric',
            'service_start_date' => 'nullable|date',
            'service_end_date' => 'nullable|date'
        ]);

        $service->update($request->all());

        if ($service->service_status == 'Completed') {
            $service->asset->update(['status' => 'Available']);
        }

        return redirect()->route('assets.services.index', $service->asset)->with('success', 'Service updated!');
    }


    public function parseInvoice(Request $request)
    {
        $request->validate([
            'invoice' => 'required|file|mimes:pdf,xlsx,csv,jpg,png'
        ]);

        $file = $request->file('invoice');
        $fileName = time() . '_' . $file->getClientOriginalName();

        // create folder if not exist
        if (!file_exists(public_path('invoice'))) {
            mkdir(public_path('invoice'), 0777, true);
        }

        // move file to public/invoice
        $file->move(public_path('invoice'), $fileName);
        $filePath = public_path('invoice/' . $fileName);

        // --- Step 2: Parse Data ---
        $data = $this->extractDataFromFile($filePath, $file->getClientOriginalExtension());

        return response()->json([
            'file_name' => $fileName,
            'file_url' => asset('invoice/' . $fileName),
            'extracted' => $data
        ]);
    }


    private function extractDataFromFile($filePath, $ext)
    {
        $data = [
            'purchase_no' => null,
            'vendor' => null,
            'invoice_no' => null,
            'purchase_date' => null,
            'invoice_date' => null,
            'purchase_value' => null,
            'warranty_months' => null,
        ];

        if ($ext == 'pdf') {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($filePath);
            $text = $pdf->getText();

            // Extract using regex
            preg_match('/Purchase No[:\- ]+(\S+)/i', $text, $p);
            preg_match('/Invoice No[:\- ]+(\S+)/i', $text, $i);
            preg_match('/Vendor[:\- ]+(.+)/i', $text, $v);

            $data['purchase_no'] = $p[1] ?? null;
            $data['invoice_no']  = $i[1] ?? null;
            $data['vendor']      = $v[1] ?? null;

            // aur bhi fields similarly parse karo
        }

        if (in_array($ext, ['xlsx', 'csv'])) {
            // Excel parse using maatwebsite/excel
            $rows = \Maatwebsite\Excel\Facades\Excel::toArray(null, $filePath);
            $first = $rows[0][0];

            // Example: Assume known structure
            $data['purchase_no'] = $first['Purchase No'] ?? null;
            $data['invoice_no']  = $first['Invoice No'] ?? null;
        }

        if (in_array($ext, ['jpg', 'png'])) {
            // OCR (optional): thiagoalessio/tesseract_ocr use karna padega
            $ocr = new \TesseractOCR($filePath);
            $text = $ocr->run();
            // then regex as PDF case
        }

        return $data;
    }


    public function email_store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'email1' => 'required|email',
            'email2' => 'required|email',
        ]);

        AssetsServiceEmail::updateOrCreate(
            ['b_id' => $user->emp_b_id],
            [
                'email1' => $request->email1,
                'email2' => $request->email2,
            ]
        );

        return response()->json(['message' => 'Emails saved successfully!']);
    }


    public function getEmails()
    {
        $user = Auth::user();
        $emails = AssetsServiceEmail::where('b_id', $user->emp_b_id)->first();

        return response()->json($emails);
    }
}
