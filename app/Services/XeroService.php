<?php

namespace App\Services;

use App\Models\XeroToken;
use App\Models\Invoice;
use XeroAPI\XeroPHP\Api\AccountingApi;
use XeroAPI\XeroPHP\Configuration;
use XeroAPI\XeroPHP\Models\Accounting\Invoice as XeroInvoice;
use XeroAPI\XeroPHP\Models\Accounting\Contact;
use XeroAPI\XeroPHP\Models\Accounting\LineItem;
use XeroAPI\XeroPHP\Models\Accounting\LineItemTracking;
use GuzzleHttp\Client;
use Exception;
use Log;

class XeroService
{
    protected $config;
    protected $provider;

    public function __construct()
    {
        $this->config = [
            'clientId' => config('xero.client_id'),
            'clientSecret' => config('xero.client_secret'),
            'redirectUri' => config('xero.redirect_uri'),
        ];
    }

    /**
     * Get authorization URL for OAuth2
     */
    public function getAuthorizationUrl()
    {
        $client = new Client();
        $params = [
            'response_type' => 'code',
            'client_id' => $this->config['clientId'],
            'redirect_uri' => $this->config['redirectUri'],
            'scope' => 'openid profile email accounting.transactions accounting.contacts accounting.settings',
        ];

        return 'https://login.xero.com/identity/connect/authorize?' . http_build_query($params);
    }

    /**
     * Exchange authorization code for access token
     */
    public function getAccessToken($code, $companyId)
    {
        $client = new Client();

        try {
            $response = $client->post('https://identity.xero.com/connect/token', [
                'form_params' => [
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'redirect_uri' => $this->config['redirectUri'],
                ],
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($this->config['clientId'] . ':' . $this->config['clientSecret']),
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            // Get tenant information
            $tenants = $this->getConnections($data['access_token']);
            $tenant = $tenants[0] ?? null;

            if ($tenant) {
                XeroToken::updateOrCreate(
                    ['company_id' => $companyId],
                    [
                        'access_token' => $data['access_token'],
                        'refresh_token' => $data['refresh_token'],
                        'expires_in' => $data['expires_in'],
                        'token_expires_at' => now()->addSeconds($data['expires_in']),
                        'tenant_id' => $tenant['tenantId'],
                        'tenant_name' => $tenant['tenantName'] ?? null,
                    ]
                );

                return true;
            }

            return false;
        } catch (Exception $e) {
            Log::error('Xero Token Exchange Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get Xero connections (tenants)
     */
    protected function getConnections($accessToken)
    {
        $client = new Client();

        try {
            $response = $client->get('https://api.xero.com/connections', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (Exception $e) {
            Log::error('Xero Connections Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Refresh the access token
     */
    public function refreshToken(XeroToken $xeroToken)
    {
        $client = new Client();

        try {
            $response = $client->post('https://identity.xero.com/connect/token', [
                'form_params' => [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $xeroToken->refresh_token,
                ],
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($this->config['clientId'] . ':' . $this->config['clientSecret']),
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            $xeroToken->update([
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'],
                'expires_in' => $data['expires_in'],
                'token_expires_at' => now()->addSeconds($data['expires_in']),
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Xero Token Refresh Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get Accounting API instance
     */
    protected function getAccountingApi($companyId)
    {
        $xeroToken = XeroToken::where('company_id', $companyId)->first();

        if (!$xeroToken) {
            throw new Exception('Xero not connected for this company');
        }

        // Refresh token if expired
        if ($xeroToken->isTokenExpired()) {
            $this->refreshToken($xeroToken);
            $xeroToken->refresh();
        }

        $config = Configuration::getDefaultConfiguration()->setAccessToken($xeroToken->access_token);
        return new AccountingApi(new Client(), $config);
    }

    /**
     * Create or update contact in Xero
     */
   /**
 * Create or update contact in Xero
 */
protected function syncContact($invoice, $tenantId, $apiInstance)
{
    try {
        $client = $invoice->client ?? $invoice->project->client;

        if (!$client) {
            throw new Exception('No client found for invoice');
        }

        // First, try to find existing contact in Xero by email or name
        $contactName = $client->name;
        
        if ($client->clientDetails && $client->clientDetails->company_name) {
            $contactName = $client->clientDetails->company_name;
        }

        \Log::info('Searching for existing Xero contact', [
            'contact_name' => $contactName,
            'client_email' => $client->email
        ]);

        // Search for existing contact
        $existingContacts = null;
        try {
            // Try to find by exact name match
            $where = 'Name=="' . addslashes($contactName) . '"';
            $existingContacts = $apiInstance->getContacts($tenantId, null, $where);
            
            if ($existingContacts && $existingContacts->getContacts() && count($existingContacts->getContacts()) > 0) {
                \Log::info('Found existing contact in Xero', [
                    'contact_id' => $existingContacts->getContacts()[0]->getContactId()
                ]);
                return $existingContacts->getContacts()[0];
            }
        } catch (Exception $e) {
            \Log::info('No existing contact found, will create new one');
        }

        // If no existing contact found, create a new one
        \Log::info('Creating new contact in Xero', ['contact_name' => $contactName]);

        $contact = new Contact();
        $contact->setName($contactName);
        $contact->setEmailAddress($client->email);

        if ($client->clientDetails) {
            $contact->setFirstName($client->clientDetails->name ?? $client->name);

            // Add address if available
            if ($client->clientDetails->address) {
                $address = new \XeroAPI\XeroPHP\Models\Accounting\Address();
                $address->setAddressType(\XeroAPI\XeroPHP\Models\Accounting\Address::ADDRESS_TYPE_STREET);
                $address->setAddressLine1($client->clientDetails->address);
                $contact->setAddresses([$address]);
            }

            // Add phone if available
            if ($client->clientDetails->mobile) {
                $phone = new \XeroAPI\XeroPHP\Models\Accounting\Phone();
                $phone->setPhoneType(\XeroAPI\XeroPHP\Models\Accounting\Phone::PHONE_TYPE_MOBILE);
                $phone->setPhoneNumber($client->clientDetails->mobile);
                $contact->setPhones([$phone]);
            }
        }

        $contacts = new \XeroAPI\XeroPHP\Models\Accounting\Contacts();
        $contacts->setContacts([$contact]);

        $result = $apiInstance->createContacts($tenantId, $contacts);
        
        \Log::info('New contact created in Xero', [
            'contact_id' => $result->getContacts()[0]->getContactId()
        ]);
        
        return $result->getContacts()[0];

    } catch (Exception $e) {
        Log::error('Xero Contact Sync Error: ' . $e->getMessage());
        throw $e;
    }
}

    /**
     * Create invoice in Xero
     */
    public function createInvoice(Invoice $invoice)
{
    try {
        $companyId = $invoice->company_id ?? company()->id;
        $apiInstance = $this->getAccountingApi($companyId);
        $xeroToken = XeroToken::where('company_id', $companyId)->first();
        $tenantId = $xeroToken->tenant_id;
        
        \Log::info('Starting Xero invoice creation', [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'tenant_id' => $tenantId
        ]);
        
        // Clear any previous error
        $invoice->xero_sync_error = null;
        
        // Sync/Create contact first
        \Log::info('Syncing contact to Xero');
        $xeroContact = $this->syncContact($invoice, $tenantId, $apiInstance);
        \Log::info('Contact synced', ['contact_id' => $xeroContact->getContactId()]);

        // Create Xero Invoice
        $xeroInvoice = new XeroInvoice();
        $xeroInvoice->setType(XeroInvoice::TYPE_ACCREC); // Accounts Receivable
        $xeroInvoice->setContact($xeroContact);
        $xeroInvoice->setInvoiceNumber($invoice->invoice_number);
        $xeroInvoice->setReference($invoice->invoice_number);
        
        // Format dates properly
        $issueDate = new \DateTime($invoice->issue_date);
        $dueDate = new \DateTime($invoice->due_date);
        $xeroInvoice->setDate($issueDate);
        $xeroInvoice->setDueDate($dueDate);

        // Set currency
        $currencyCode = $invoice->currency->currency_code ?? 'USD';
        $xeroInvoice->setCurrencyCode($currencyCode);

        // Set status - IMPORTANT: Use DRAFT first, then AUTHORISED
        $xeroInvoice->setStatus(XeroInvoice::STATUS_AUTHORISED);

        \Log::info('Invoice header set', [
            'type' => 'ACCREC',
            'status' => 'DRAFT',
            'currency' => $currencyCode,
            'issue_date' => $issueDate->format('Y-m-d'),
            'due_date' => $dueDate->format('Y-m-d')
        ]);

        // Add line items
        $lineItems = [];
        
        foreach ($invoice->items as $index => $item) {
            $lineItem = new LineItem();
            
            // Description
            $description = $item->item_name;
            if ($item->item_summary) {
                $description .= "\n" . strip_tags($item->item_summary);
            }
            $lineItem->setDescription($description);
            
            // Quantity and Unit Amount
            $lineItem->setQuantity((float)$item->quantity);
            $lineItem->setUnitAmount((float)$item->unit_price);
            
            // IMPORTANT: Set LineAmount (total before tax for this line)
            $lineItem->setLineAmount((float)$item->amount);
            
            // Don't set AccountCode - let Xero use default sales account
            // Or use a valid account code from your Xero organization
            $lineItem->setAccountCode('200'); // Remove this or use valid code
            
            // Tax handling - Use Xero's tax types
            if ($item->taxes) {
                $taxes = json_decode($item->taxes);
                
                if ($taxes && is_array($taxes) && count($taxes) > 0) {
                    // Calculate total tax rate
                    $totalTaxRate = 0;
                    foreach ($taxes as $taxId) {
                        $tax = \App\Models\Tax::find($taxId);
                        if ($tax) {
                            $totalTaxRate += $tax->rate_percent;
                        }
                    }
                    
                    if ($totalTaxRate > 0) {
                        // Use OUTPUT for taxable items (sales tax)
                        $lineItem->setTaxType('OUTPUT');
                        
                        // Calculate tax amount based on line amount
                        $taxAmount = ($item->amount * $totalTaxRate) / 100;
                        $lineItem->setTaxAmount((float)$taxAmount);
                    } else {
                        $lineItem->setTaxType('NONE');
                    }
                } else {
                    $lineItem->setTaxType('NONE');
                }
            } else {
                $lineItem->setTaxType('NONE');
            }

            \Log::info('Line item ' . ($index + 1), [
                'description' => substr($description, 0, 50),
                'quantity' => $item->quantity,
                'unit_amount' => $item->unit_price,
                'line_amount' => $item->amount,
                'tax_type' => $lineItem->getTaxType(),
                'tax_amount' => $lineItem->getTaxAmount()
            ]);

            $lineItems[] = $lineItem;
        }

        if (empty($lineItems)) {
            throw new \Exception('Invoice has no line items');
        }

        $xeroInvoice->setLineItems($lineItems);

        // Create the invoice in Xero
        $invoices = new \XeroAPI\XeroPHP\Models\Accounting\Invoices();
        $invoices->setInvoices([$xeroInvoice]);

        \Log::info('Sending invoice to Xero API', [
            'invoice_number' => $invoice->invoice_number,
            'line_items_count' => count($lineItems),
            'total' => $invoice->total,
            'currency' => $currencyCode
        ]);

        // summarizeErrors=false gives us detailed error messages
        $result = $apiInstance->createInvoices($tenantId, $invoices, false);
        
        \Log::info('Xero API response received');

        // Get the created invoice
        $invoicesArray = $result->getInvoices();
        
        if (!$invoicesArray || count($invoicesArray) === 0) {
            throw new \Exception('Xero returned empty invoices array');
        }

        $createdInvoice = $invoicesArray[0];

        // Check for validation errors first
        if ($createdInvoice->getValidationErrors() && count($createdInvoice->getValidationErrors()) > 0) {
            $errors = [];
            foreach ($createdInvoice->getValidationErrors() as $error) {
                $errorMsg = $error->getMessage();
                $errors[] = $errorMsg;
                \Log::error('Xero validation error', ['message' => $errorMsg]);
            }
            throw new \Exception('Xero validation errors: ' . implode('; ', $errors));
        }

        // Validate the invoice ID
        $xeroInvoiceId = $createdInvoice->getInvoiceId();
        
        \Log::info('Xero invoice created', [
            'xero_invoice_id' => $xeroInvoiceId,
            'invoice_number' => $createdInvoice->getInvoiceNumber(),
            'status' => $createdInvoice->getStatus(),
            'total' => $createdInvoice->getTotal(),
            'amount_due' => $createdInvoice->getAmountDue()
        ]);
        
        if (!$xeroInvoiceId || $xeroInvoiceId === '00000000-0000-0000-0000-000000000000') {
            throw new \Exception('Xero returned invalid invoice ID');
        }

        // Check for warnings
        if ($createdInvoice->getWarnings() && count($createdInvoice->getWarnings()) > 0) {
            $warnings = [];
            foreach ($createdInvoice->getWarnings() as $warning) {
                $warnings[] = $warning->getMessage();
            }
            \Log::warning('Xero invoice created with warnings', [
                'invoice_id' => $invoice->id,
                'warnings' => $warnings
            ]);
        }

        // Store Xero invoice ID
        $invoice->xero_invoice_id = $xeroInvoiceId;
        $invoice->xero_sync_error = null;
        $invoice->save();

        \Log::info('Invoice synced successfully', [
            'local_invoice_id' => $invoice->id,
            'xero_invoice_id' => $xeroInvoiceId
        ]);

        return [
            'success' => true,
            'xero_invoice_id' => $xeroInvoiceId,
            'message' => 'Invoice created in Xero successfully'
        ];

    } catch (\XeroAPI\XeroPHP\ApiException $e) {
        $errorMessage = 'Xero API Error: ' . $e->getMessage();
        $responseBody = $e->getResponseBody();
        
        if ($responseBody) {
            $errorMessage .= ' | Response: ' . $responseBody;
            \Log::error('Xero API Exception', [
                'message' => $e->getMessage(),
                'response' => $responseBody,
                'code' => $e->getCode()
            ]);
        }
        
        $invoice->xero_sync_error = substr($errorMessage, 0, 65535);
        $invoice->xero_invoice_id = null;
        $invoice->save();

        return [
            'success' => false,
            'message' => $errorMessage
        ];
        
    } catch (\Exception $e) {
        $errorMessage = $e->getMessage();
        
        \Log::error('Xero Invoice Creation Error', [
            'invoice_id' => $invoice->id,
            'message' => $errorMessage,
            'trace' => $e->getTraceAsString()
        ]);
        
        $invoice->xero_sync_error = substr($errorMessage, 0, 65535);
        $invoice->xero_invoice_id = null;
        $invoice->save();

        return [
            'success' => false,
            'message' => 'Failed to create invoice in Xero: ' . $errorMessage
        ];
    }
}
    /**
     * Update invoice in Xero
     */
    public function updateInvoice(Invoice $invoice)
    {
        if (!$invoice->xero_invoice_id) {
            return $this->createInvoice($invoice);
        }

        try {
            $companyId = $invoice->company_id ?? company()->id;
            $apiInstance = $this->getAccountingApi($companyId);
            $xeroToken = XeroToken::where('company_id', $companyId)->first();
            $tenantId = $xeroToken->tenant_id;

            // Get existing invoice
            $existingInvoice = $apiInstance->getInvoice($tenantId, $invoice->xero_invoice_id);
            $xeroInvoice = $existingInvoice->getInvoices()[0];

            // Update only if invoice is still draft or submitted
            if (in_array($xeroInvoice->getStatus(), ['DRAFT', 'SUBMITTED'])) {
                // Similar to create, but update existing invoice
                $xeroInvoice->setDueDate(new \DateTime($invoice->due_date));

                $status = $this->mapInvoiceStatus($invoice->status);
                $xeroInvoice->setStatus($status);

                // Update line items
                $lineItems = [];
                foreach ($invoice->items as $item) {
                    $lineItem = new LineItem();
                    $lineItem->setDescription($item->item_name . ($item->item_summary ? "\n" . $item->item_summary : ''));
                    $lineItem->setQuantity($item->quantity);
                    $lineItem->setUnitAmount($item->unit_price);
                    $lineItem->setAccountCode('200');

                    if ($item->taxes) {
                        $lineItem->setTaxType('OUTPUT');
                    } else {
                        $lineItem->setTaxType('NONE');
                    }

                    $lineItems[] = $lineItem;
                }

                $xeroInvoice->setLineItems($lineItems);

                $invoices = new \XeroAPI\XeroPHP\Models\Accounting\Invoices();
                $invoices->setInvoices([$xeroInvoice]);

                $apiInstance->updateInvoice($tenantId, $invoice->xero_invoice_id, $invoices);

                Log::info('Invoice updated in Xero successfully', [
                    'invoice_id' => $invoice->id,
                    'xero_invoice_id' => $invoice->xero_invoice_id
                ]);

                return [
                    'success' => true,
                    'message' => 'Invoice updated in Xero successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Invoice cannot be updated (status: ' . $xeroInvoice->getStatus() . ')'
                ];
            }

        } catch (Exception $e) {
            Log::error('Xero Invoice Update Error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to update invoice in Xero: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Map app invoice status to Xero status
     */
    protected function mapInvoiceStatus($status)
    {
        $statusMap = [
            'paid' => XeroInvoice::STATUS_PAID,
            'unpaid' => XeroInvoice::STATUS_AUTHORISED,
            'partial' => XeroInvoice::STATUS_AUTHORISED,
            'draft' => XeroInvoice::STATUS_DRAFT,
            'canceled' => XeroInvoice::STATUS_VOIDED,
        ];

        return $statusMap[$status] ?? XeroInvoice::STATUS_DRAFT;
    }

    /**
     * Check if Xero is connected
     */
    public function isConnected($companyId)
    {
        $token = XeroToken::where('company_id', $companyId)->first();
        return $token !== null;
    }

    /**
     * Disconnect Xero
     */
    public function disconnect($companyId)
    {
        XeroToken::where('company_id', $companyId)->delete();
        return true;
    }
}
