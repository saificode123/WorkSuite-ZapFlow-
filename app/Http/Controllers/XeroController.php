<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Services\XeroService;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\XeroToken;

class XeroController extends AccountBaseController
{
    protected $xeroService;

    public function __construct()
    {
        parent::__construct();
        $this->xeroService = new XeroService();
        $this->pageTitle = 'Xero Integration';
    }

    /**
     * Show Xero settings page
     */
    public function index()
    {
        $this->xeroConnected = $this->xeroService->isConnected(company()->id);
        $this->xeroToken = XeroToken::where('company_id', company()->id)->first();

        return view('xero.index', $this->data);
    }

    /**
     * Redirect to Xero for authorization
     */
    public function connect()
    {
        $authUrl = $this->xeroService->getAuthorizationUrl();
        return redirect($authUrl);
    }

    /**
     * Handle Xero OAuth callback
     */
    public function callback(Request $request)
    {
        // Check for errors from Xero
        if ($request->has('error')) {
            \Log::error('Xero OAuth Error', [
                'error' => $request->error,
                'error_description' => $request->error_description
            ]);
            
            return redirect()->route('xero.index')
                ->with('error', 'Xero authorization failed: ' . $request->error_description);
        }

        if (!$request->has('code')) {
            \Log::error('Xero OAuth Error: No authorization code received');
            return redirect()->route('xero.index')
                ->with('error', 'No authorization code received from Xero');
        }

        try {
            $success = $this->xeroService->getAccessToken($request->code, company()->id);

            if ($success) {
                \Log::info('Xero connected successfully for company ' . company()->id);
                return redirect()->route('xero.index')
                    ->with('success', 'Successfully connected to Xero!');
            }

            \Log::error('Xero connection failed: getAccessToken returned false');
            return redirect()->route('xero.index')
                ->with('error', 'Failed to connect to Xero. Please check your logs for details.');
                
        } catch (\Exception $e) {
            \Log::error('Xero callback exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('xero.index')
                ->with('error', 'Failed to connect to Xero: ' . $e->getMessage());
        }
    }

    /**
     * Disconnect from Xero
     */
    public function disconnect()
    {
        $this->xeroService->disconnect(company()->id);
        return Reply::success('Disconnected from Xero successfully');
    }

    /**
     * Test connection with Xero
     */
    public function testConnection()
    {
        try {
            if ($this->xeroService->isConnected(company()->id)) {
                return Reply::success('Xero connection is active!');
            }

            return Reply::error('Xero is not connected');
        } catch (\Exception $e) {
            return Reply::error('Connection test failed: ' . $e->getMessage());
        }
    }

    /**
     * Manually sync a specific invoice to Xero
     */
    public function syncInvoice($id)
    {
        $invoice = Invoice::findOrFail($id);
        
        if (!$this->xeroService->isConnected(company()->id)) {
            return Reply::error('Please connect to Xero first');
        }

        $result = $this->xeroService->createInvoice($invoice);

        if ($result['success']) {
            return Reply::success($result['message']);
        }

        return Reply::error($result['message']);
    }

    /**
     * Get Xero sync status for an invoice
     */
    public function getInvoiceStatus($id)
    {
        $invoice = Invoice::findOrFail($id);
        
        if ($invoice->xero_invoice_id) {
            return Reply::dataOnly([
                'synced' => true,
                'xero_invoice_id' => $invoice->xero_invoice_id,
                'message' => 'Invoice synced to Xero'
            ]);
        }

        return Reply::dataOnly([
            'synced' => false,
            'message' => 'Invoice not synced to Xero'
        ]);
    }
}